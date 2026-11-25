#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
source /g/nulib/load.sh || exit 1

function init_action() {
    estep "init"
    echo start >.pg18mstate
    sync
}

function start_action() {
    estep "prepare pg15"
    mkdir -p 15/backup
    chown postgres:root 15/backup
    chmod 700 15/backup
    sync
    echo started >.pg18mstate
    sync
}

function started_action() {
    estep "copy pg15"
    cp -al "${srcs[@]}" 15/backup
    sync
    echo cleanup >.pg18mstate
    sync
}

function cleanup_action() {
    estep "cleanup pg15"
    rm -rf "${srcs[@]}"
    sync
    echo done >.pg18mstate
    sync
}

function done_action() {
    estep "teardown"
    mkdir -p 18
    sync
    rm .pg18mstate
    sync
}

prepare=
args=(
    "Préparation à la migration postgresql 18"
    #"usage"
    --prepare .
)
parse_args "$@"; set -- "${args[@]}"

[ -n "$prepare" ] || die

cd /var/lib/postgresql
setx -a files=ls_all .

pg18m=
pg18=
srcs=()
for file in "${files[@]}"; do
    if [ "$file" == 15 ]; then :
    elif [ "$file" == .pg18mstate ]; then pg18m=1
    elif [ "$file" == 18 ]; then pg18=1
    else srcs+=("$file")
    fi
done

if [ ${#srcs[*]} -eq 0 -a -z "$pg18m" ]; then
    if [ -n "$pg18" ]; then
        enote "Migration postgresql 18 déjà effectuée ou aucune migration n'est nécessaire"
    else
        enote "Aucune données trouvées. Elles seront provisionnées lors du prochain démarrage"
    fi
    exit 0
fi

[ -f .pg18mstate ] || init_action
while [ -f .pg18mstate ]; do
    state="$(<.pg18mstate)"
    "${state}_action"
done
