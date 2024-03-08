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

BUILD_TEMPLATE=.build.env.dist
PROFILE_TEMPLATE=.prod_profile.env.dist
ENV_TEMPLATE=..env.template

function _copy_template() {
    local src="$1" srcdir srcname dest
    setx srcdir=dirname "$src"
    setx srcname=basename "$src"
    dest="${srcname#.}"; dest="${dest%.template}"; dest="$srcdir/$dest"

    userfiles+=("$dest")
    cp -P "$src" "$dest"
    return 0
}

function _copy_dist() {
    local src="$1" srcdir srcname dest
    setx srcdir=dirname "$src"
    setx srcname=basename "$src"
    dest="${srcname#.}"; dest="${dest%.dist}"; dest="$srcdir/$dest"

    userfiles+=("$dest")
    if [ ! -e "$dest" ]; then
        cp -P "$src" "$dest"
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
    if [ -n "$Profile" ]; then
        source_envs+=("$DREINST/${Profile}_profile.env")
    elif [ -f "$DREINST/front.env" ]; then
        source_envs+=("$DREINST/front.env")
    fi
}

function _resolve_scripts() {
    local script1="$1" script2="$2" script3="$3"
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
        [ -n "$DBVIP" ] && DBVIP="$DBVIP:"
        [ -n "$LBVIP" ] && LBVIP="$LBVIP:"
        [ -n "$INST_VIP" ] && INST_VIP="$INST_VIP:"
        [ -n "$PRIVAREG" ] && PRIVAREG="$PRIVAREG/"

        NL=$'\n'
        # random
        cat >"$script1" <<EOF
@include "base.tools.awk"
{
  if (should_generate_password()) {
    generate_password()
  }
  print
}
EOF
        # each
        exec >"$script2"
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
        exec >"$script3"
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
    #etitle "script3" cat "$script3"
}

function build_check_env() {
    local -a source_envs; local updated
    local -a userfiles; local file script1 script2 script3 workfile

    local Profile=
    _set_source_envs
    _copy_dist "$DREINST/$BUILD_TEMPLATE" && updated=1

    ac_set_tmpfile script1
    ac_set_tmpfile script2
    ac_set_tmpfile script3
    _resolve_scripts "$script1" "$script2" "$script3"

    file="$DREINST/build.env"
    ac_set_tmpfile workfile
    cat "$file" | awk -f "$script1" | sed -f "$script2" | sed -f "$script3" >"$workfile" &&
        cat "$workfile" >"$file"

    ac_clean "$script1" "$script2" "$script3" "$workfile"

    if [ -n "$updated" ]; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier build.env
    ${EDITOR:-nano} build.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}

function inst_check_env() {
    local -a source_envs; local updated
    local -a filter files userfiles; local file script1 script2 workfile

    _set_source_envs

    # les fichiers .*.template sont systématiquement recréés
    filter=(
        -type d -name .git -prune -or
        -type d -name vendor -prune -or
        -type d -name var -prune -or
        -type l -name ".*.template" -print -or
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
        -type l -name ".*.dist" -print -or
        -type f -name ".*.dist" -print
    )
    setx -a files=find "$DREINST" "${filter[@]}"
    for file in "${files[@]}"; do
        _copy_dist "$file" && updated=1
    done

    # puis mettre à jour les fichiers
    ac_set_tmpfile script1
    ac_set_tmpfile script2
    ac_set_tmpfile script3
    _resolve_scripts "$script1" "$script2" "$script3"

    ac_set_tmpfile workfile
    for file in "${userfiles[@]}"; do
        cat "$file" | awk -f "$script1" | sed -f "$script2" | sed -f "$script3" >"$workfile" &&
            cat "$workfile" >"$file"
    done

    ac_clean "$script1" "$script2" "$script3" "$workfile"

    if [ -n "$updated" -a -n "$Profile" ]; then
        enote "IMPORTANT: Veuillez faire le paramétrage en éditant le fichier ${Profile}_profile.env
    ${EDITOR:-nano} ${Profile}_profile.env
ENSUITE, vous pourrez relancer la commande"
        return 1
    fi
}
