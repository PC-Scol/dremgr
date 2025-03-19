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
DREMGR_PROFILE_TEMPLATE=.prod_profile.env.dist
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
        "$DREMGR/.prod_profile.env.dist" \
        "$DREMGR/.forced.env"
}

function template_source_envs() {
    local -a source_envs
    source_envs=("$DREMGR/build.env" "$DREMGR/.defaults.env")
    if [ -n "$Profile" ]; then
        source_envs+=("$DREMGR/${Profile}_profile.env")
    elif [ -f "$DREMGR/dremgr.env" ]; then
        source_envs+=("$DREMGR/dremgr.env")
    fi
    source_envs+=("$DREMGR/.forced.env")
    _template_source_envs "${source_envs[@]}"
    template_vars+=(IS_DBINST IS_DBFRONT IS_WEBFRONT)
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

    # fix pour certaines variables
    [ -n "$DBVIP" ] && DBVIP="$DBVIP:"
    [ -n "$LBVIP" ] && LBVIP="$LBVIP:"
    [ -n "$INST_VIP" ] && INST_VIP="$INST_VIP:"
    [ -n "$PRIVAREG" ] && PRIVAREG="${PRIVAREG%/}/"
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
    local -a filter files; local file

    # si le fichier d'environnement n'existe pas, il faudra le configurer
    local please_configure
    if [ -n "$Profile" -a ! -e "${Profile}_profile.env" ]; then
        please_configure=1
    fi

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
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier ${Profile}_profile.env
    ${EDITOR:-nano} ${Profile}_profile.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}
