# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

inspath "$DREINST/sbin"

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

function should_update() {
    # faut-il mettre à jour le fichier $1 qui est construit à partir des
    # fichiers $2..@
    local dest="$1"; shift
    local source
    for source in "$@"; do
        [ -f "$source" ] || continue
        [ "$source" -nt "$dest" ] && return 0
    done
    return 1
}

function list_vars() {
    cat "$DREINST/.app.env.dist" "$DREINST/..env.dist" |
        grep -E '^[A-Z_]+=' |
        sed 's/=.*//'
}
function merge_vars() {
    local -a source_envs
    source_envs=(
        "$DREINST/.env"
        "$DREINST/app.env"
    )

    local -a filter files; local src srcdir srcname dest script
    local updated

    # les fichiers .*.template sont systématiquement recréés
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type f -name ".*.template" -print
    )
    setx -a files=find "$1" "${filter[@]}"
    for src in "${files[@]}"; do
        setx srcdir=dirname "$src"
        setx srcname=basename "$src"
        dest="${srcname#.}"; dest="${dest%.template}"; dest="$srcdir/$dest"
        if [ -n "$ForceUpdate" ] || should_update "$dest" "$src" "${source_envs[@]}"; then
            cp "$src" "$dest"
        fi
    done

    # les fichiers .*.dist sont créés uniquement s'ils n'existent pas déjà
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type f -name ".*.dist" -print
    )
    setx -a files=find "$1" "${filter[@]}"
    for src in "${files[@]}"; do
        setx srcdir=dirname "$src"
        setx srcname=basename "$src"
        dest="${srcname#.}"; dest="${dest%.dist}"; dest="$srcdir/$dest"
        if [ ! -f "$dest" ]; then
            cp "$src" "$dest"
            updated=1
        fi
    done

    local script
    script="$(
        for env in "${source_envs[@]}"; do
            source "$env"
        done
        setx -a vars=list_vars

        # fix pour certaines variables
        [ -n "$FE_VIP" ] && FE_VIP="$FE_VIP:"
        [ -n "$PRIVAREG" ] && PRIVAREG="$PRIVAREG/"

        NL=$'\n'
        for var in "${vars[@]}"; do
            value="${!var}"
            value="${value//\//\\\/}"
            value="${value//[/\\[}"
            value="${value//\*/\\\*}"
            value="${value//$NL/\\n}"
            if [ -n "$value" ]; then
                ifvalue=
                ulvalue="#"
            else
                ifvalue="#"
                ulvalue=
            fi
            echo "s/@@${var}@@/${value}/g"
            echo "s/#@@IF:${var}@@#/${ifvalue}/g"
            echo "s/#@@UL:${var}@@#/${ulvalue}/g"
        done
    )"

    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type f -name composer.phar -prune -or
        -type f -name "*.lock" -prune -or
        -type f -name ".*.dist" -prune -or
        -type f -name ".*.template" -prune -or
        -type f -print
    )
    setx -a files=find "$1" "${filter[@]}"
    sed -i "$script" "${files[@]}"

    [ -n "$updated" ]
}

function check_env() {
    if merge_vars "$DREINST"; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant les fichiers suivants:
    app.env
    .env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}
