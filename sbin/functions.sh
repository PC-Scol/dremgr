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

BUILD_TEMPLATE=.build.env.dist
PROFILE_TEMPLATE=.prod_profile.env.dist
ENV_TEMPLATE=..env.template

function _copy_template() {
    local src="$1" srcdir srcname dest update
    setx srcdir=dirname "$src"
    setx srcname=basename "$src"
    dest="${srcname#.}"; dest="${dest%.template}"; dest="$srcdir/$dest"
    userfiles+=("$dest")
    if [ "$srcname" == "$ENV_TEMPLATE" ]; then update=1
    elif [ -n "$ForceUpdate" ]; then update=1
    elif should_update "$dest" "$src" "${source_envs[@]}"; then update=1
    else update=
    fi
    if [ -n "$update" ]; then
        cp "$src" "$dest"
        return 0
    fi
    return 1
}

function _copy_dist() {
    local src="$1" srcdir srcname dest
    setx srcdir=dirname "$src"
    setx srcname=basename "$src"
    dest="${srcname#.}"; dest="${dest%.dist}"; dest="$srcdir/$dest"
    userfiles+=("$dest")
    if [ ! -f "$dest" ]; then
        cp "$src" "$dest"
        return 0
    fi
    return 1
}

function list_vars() {
    echo Profile
    cat "$DREINST/.prod_profile.env.dist" "$DREINST/.build.env.dist" |
        grep -E '^[A-Z_]+=' |
        sed 's/=.*//'
}

function _set_source_envs() {
    source_envs=("$DREINST/build.env")
    [ -n "$Profile" ] && source_envs+=("$DREINST/${Profile}_profile.env")
}

function _resolve_scripts() {
    local script1="$1" script2="$2"
    (
        for env in "${source_envs[@]}"; do
            [ -f "$env" ] && source "$env"
        done
        setx -a vars=list_vars

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
        [ -n "$FE_VIP" ] && FE_VIP="$FE_VIP:"
        [ -n "$PRIVAREG" ] && PRIVAREG="$PRIVAREG/"

        NL=$'\n'
        # each
        exec >"$script1"
        for varz in "${vars[@]}"; do
            values="${!varz}"; read -a values <<<"${values//
/ }"
            if [ "${varz%S}" != "$varz" ]; then
                var="${varz%S}"
            elif [ "${varz%s}" != "$varz" ]; then
                var="${varz%s}"
            else
                var="$varz"
            fi
            echo "/@@EACH:${varz}@@/{"
            echo "  s/@@EACH:${varz}@@//; h"
            max="${#values[*]}"
            case $max in
            0) indexes=();;
            1) max=0; indexes=(0);;
            *) let max=max-1; eval "indexes=({0..$max})";;
            esac
            for index in "${indexes[@]}"; do
                value="${values[$index]}"
                value="${value//\//\\\/}"
                value="${value//[/\\[}"
                value="${value//\*/\\\*}"
                value="${value//$NL/\\n}"
                echo "  g; s/@@${var}@@/${value}/g"
                [ $index -lt $max ] && echo "  p"
            done
            echo "}"
        done
        # var, if, ul
        exec >"$script2"
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
    )
    #etitle "script1" cat "$script1"
    #etitle "script2" cat "$script2"
}

function build_check_env() {
    local -a source_envs; local updated
    local -a userfiles; local file script1 script2 workfile

    local Profile=
    _set_source_envs
    _copy_dist "$DREINST/$BUILD_TEMPLATE" && updated=1
    ac_set_tmpfile script1
    ac_set_tmpfile script2
    _resolve_scripts "$script1" "$script2"
    ac_set_tmpfile workfile

    file="$DREINST/build.env"
    cat "$file" | sed -f "$script1" | sed -f "$script2" >"$workfile" &&
        cat "$workfile" >"$file"
    ac_clean "$script1" "$script2" "$workfile"

    if [ -n "$updated" ]; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier build.env
    ${EDITOR:-nano} build.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}

function start_check_env() {
    local -a source_envs; local updated
    local -a filter files userfiles; local file script1 script2 workfile

    _set_source_envs

    # les fichiers .*.template sont systématiquement recréés
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type f -name ".*.template" -print
    )
    setx -a files=find "$DREINST" "${filter[@]}"
    for file in "${files[@]}"; do
        _copy_template "$file"
    done

    # les fichiers .*.dist sont créés uniquement s'ils n'existent pas déjà
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type f -name ".*.dist" -print
    )
    setx -a files=find "$DREINST" "${filter[@]}"
    for file in "${files[@]}"; do
        _copy_dist "$file" && updated=1
    done

    # puis mettre à jour les fichiers
    ac_set_tmpfile script1
    ac_set_tmpfile script2
    _resolve_scripts "$script1" "$script2"
    ac_set_tmpfile workfile

    for file in "${userfiles[@]}"; do
        cat "$file" | sed -f "$script1" | sed -f "$script2" >"$workfile" &&
            cat "$workfile" >"$file"
    done
    ac_clean "$script1" "$script2" "$workfile"

    if [ -n "$updated" ]; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier ${Profile}_profile.env
    ${EDITOR:-nano} ${Profile}_profile.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}
