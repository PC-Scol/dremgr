# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
require: template

inspath "$DREMGR/sbin"

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
    # afficher les containers correspondant à $1(=docker-compose.yml)
    docker compose ${1:+-f "$1"} ps -q
}

function dcrunning() {
    # vérifier si les containers correspondant à $1(=docker-compose.yml) tournent
    # si $2 est spécifié, c'est le nombre de service qui doit tourner
    if [ -n "$2" ]; then
        [ "$(dclsct "${@:1:1}" | wc -l)" -eq "$2" ]
    else
        [ -n "$(dclsct "${@:1:1}")" ]
    fi
}

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
    _template_dump_vars "$DREMGR/.prod_profile.env.dist" "$DREMGR/.build.env.dist"
}

function template_source_envs() {
    local -a source_envs
    source_envs=("$DREMGR/build.env")
    if [ -n "$Profile" ]; then
        source_envs+=("$DREMGR/${Profile}_profile.env")
    elif [ -f "$DREMGR/front.env" ]; then
        source_envs+=("$DREMGR/front.env")
    fi
    _template_source_envs "${source_envs[@]}"
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
    [ -n "$PRIVAREG" ] && PRIVAREG="$PRIVAREG/"
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

function inst_check_env() {
    eval "$(template_locals)"
    local -a filter files; local file

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

    if [ -n "$updated" -a -n "$Profile" ]; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier ${Profile}_profile.env
    ${EDITOR:-nano} ${Profile}_profile.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}
