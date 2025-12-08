# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
require: template

inspath "$DREMGR/sbin"

# recenser les valeur de proxy
declare -A PROXY_VARS
for var in {HTTPS,ALL,NO}_PROXY {http,https,all,no}_proxy; do
    is_defined "$var" && PROXY_VARS[${var,,}]="${!var}"
done
if [ ! -f "$DREMGR/.proxy.env" ]; then
    # et créer le fichier .proxy.env
    >"$DREMGR/.proxy.env"
    for var in "${!PROXY_VARS[@]}"; do
        echo "$var=${PROXY_VARS[$var]}" >>"$DREMGR/.proxy.env"
    done
fi

function verifix_vars() {
    # Corriger certaines variables
    DlProfile="$DRE_FILES_FROM"
    [ -n "$DBVIP" ] && DBVIP="$DBVIP:"
    [ -n "$LBVIP" ] && LBVIP="$LBVIP:"
    [ -n "$INST_VIP" ] && INST_VIP="$INST_VIP:"
    [ -n "$PRIVAREG" ] && PRIVAREG="${PRIVAREG%/}/"
    [ "${DATADIR#/}" == "$DATADIR" ] && DATADIR="./$DATADIR"
    if [ -z "$WEBFRONT_URL" ]; then
        if [ -n "$LBHTTPS" ]; then
            WEBFRONT_URL="https://$LBHOST:$LBHTTPS"
            WEBFRONT_URL="${WEBFRONT_URL%:443}"
        else
            WEBFRONT_URL="http://$LBHOST:$LBHTTP"
            WEBFRONT_URL="${WEBFRONT_URL%:80}"
        fi
    fi

    # définir plusieurs profils désactive automatiquement le mode simple
    local -a profiles
    read -a profiles <<<"${APP_PROFILES//
/ }"
    [ ${#profiles[*]} -gt 1 ] && MODE_SIMPLE=

    # dans le mode simple, DBNET est supprimé, et INST_PORT est initialisé si
    # nécessaire
    local profile inst_port
    if [ -n "$MODE_SIMPLE" ]; then
        DBNET=
        profile="${profiles[0]}"
        inst_port="${profile}_INST_PORT"
        [ -n "${!inst_port}" ] || eval "$inst_port=$DBPORT"
        [ -n "$INST_PORT" ] || INST_PORT="$DBPORT"
    fi
}

function get_envfile() {
    echo "$DREMGR/dremgr.env"
}
function get_envfiles() {
    echo "$DREMGR/.defaults.env"
    get_envfile
    echo "$DREMGR/.forced.env"
}
function load_envfiles() {
    local envfile; local -a envfiles
    setx -a envfiles=get_envfiles
    for envfile in "${envfiles[@]}"; do
        source "$envfile"
    done
}
function load_envs() {
    eval "$(
        load_envfiles
        verifix_vars
        for param in "$@"; do
            if [ "$param" == DATADIR ]; then
                setx DATADIR=abspath "$DATADIR" "$DREMGR"
            fi
            echo_setv2 "$param"
        done
    )"
}

function ensure_dirs() {
    # créer les répertoires de profil
    local -a profiles; local profile datadir dredata
    if [ -n "$Profile" ]; then
        profiles=("$Profile")
    elif [ -n "$APP_PROFILES" ]; then
        read -a profiles <<<"${APP_PROFILES//
/ }"
    else
        profiles=(prod)
    fi
    for profile in "${profiles[@]}"; do
        dredata="$DATADIR/${profile}-dredata"
        mkdir -p "$dredata/downloads"
        mkdir -p "$dredata/addons"
        mkdir -p "$dredata/cron-config"
    done
}

function dklsnet() {
    docker network ls --no-trunc --format '{{.Name}}' -f name="$1" 2>/dev/null
}

function dklsimg() {
    local image="$1" version="$2"
    docker image ls --no-trunc --format '{{.Repository}}:{{.Tag}}' "$image${version:+:$version}" 2>/dev/null
}

function dklsct() {
    # afficher le container dont l'image correspondante est $1
    docker ps --no-trunc --format '{{.Image}} {{.Names}}' | awk -v image="$1" '$1 == image { print $2 }'
}

function dkrunning() {
    # vérifier si le container d'image $1 tourne
    [ -n "$(dklsct "$@")" ]
}

function dclsct() {
    # afficher les containers correspondant à $@ (docker-compose.yml)
    local composefile; local -a composeargs
    for composefile in "$@"; do
        [ -n "$composefile" ] || continue
        composeargs+=(-f "$composefile")
    done
    docker compose "${composeargs[@]}" ps -q
}

function dcrunning() {
    # vérifier si les containers correspondant à $@ (docker-compose.yml) tournent
    # si le premier argument est "-c count", c'est le nombre de service qui doit
    # tourner
    local count
    if [ "$1" == -c ]; then
        count="$2"; shift; shift
    elif [[ "$1" == -c* ]]; then
        count="${1#-c}"; shift
    fi
    if [ -n "$count" ]; then
        [ "$(dclsct "$@" | wc -l)" -eq "$count" ]
    else
        [ -n "$(dclsct "$@")" ]
    fi
}

IS_DBINST=
IS_DBFRONT=
IS_WEBFRONT=
DREMGR_ENV_TEMPLATE=..env.template
DREMGR_BUILD_TEMPLATE=.build.env.dist
DREMGR_PROFILE_TEMPLATE=.dremgr.env.dist
DREMGR_TEMPLATE_LIST_VARS=(
    HOST_MAPPINGS
    ADDON_URLS
    APP_PROFILES
    APP_PROFILE_VARS
    POSTGRES_PROFILES
)

function template_dump_vars() {
    echo Profile
    _template_dump_vars \
        "$DREMGR/.build.env.dist" \
        "$DREMGR/.defaults.env" \
        "$DREMGR/.dremgr.env.dist" \
        "$DREMGR/.forced.env"
}

function template_source_envs() {
    local -a source_envs; local envfile
    source_envs=("$DREMGR/build.env" "$DREMGR/.defaults.env")
    setx envfile=get_envfile
    [ -f "$envfile" ] && source_envs+=("$envfile")
    source_envs+=("$DREMGR/.forced.env")
    _template_source_envs "${source_envs[@]}"
    template_vars+=(DlProfile IS_DBINST IS_DBFRONT IS_WEBFRONT)
    template_lists=("${DREMGR_TEMPLATE_LIST_VARS[@]}")

    if [ -n "$Profile" ]; then
        # Créer les variables de profils
        read -a pvars <<<"${APP_PROFILE_VARS//
/ }"
        for var in "${pvars[@]}"; do
            pvar="${Profile}_${var}"
            is_defined "$pvar" && setv "$var=${!pvar}"
        done
    fi

    ## fix pour certaines variables
    verifix_vars
}

function build_check_env() {
    eval "$(template_locals)"

    local Profile=
    template_copy_missing "$DREMGR/$DREMGR_BUILD_TEMPLATE" && updated=1
    template_process_userfiles

    if [ -n "$updated" ]; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier build.env
    ${EDITOR:-nano} build.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}

function run_check_env() {
    eval "$(template_locals)"
    local -a filter files; local file envfile

    # si le fichier d'environnement n'existe pas, il faudra le configurer
    local please_configure
    Profile= setx envfile=get_envfile
    [ -f "$envfile" ] || please_configure=1

    # les fichiers .*.template sont systématiquement recréés
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type l -name ".*.template" -print -or
        -type f -name ".*.template" -print
    )
    setx -a files=find "$DREMGR" "${filter[@]}"
    for file in "${files[@]}"; do
        template_copy_replace "$file"
    done

    # les fichiers .*.dist sont créés uniquement s'ils n'existent pas déjà
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type l -name ".*.dist" -print -or
        -type f -name ".*.dist" -print
    )
    setx -a files=find "$DREMGR" "${filter[@]}"
    for file in "${files[@]}"; do
        template_copy_missing "$file" && updated=1
    done

    # puis mettre à jour les fichiers
    template_process_userfiles

    if [ -n "$updated" -a -n "$please_configure" ]; then
        setx envfile=basename "$envfile"
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier $envfile
    ${EDITOR:-nano} $envfile
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}
