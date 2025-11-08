#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
source /etc/nulib.sh || exit 1

SOURCE=root@pegase-dre2023.univ.run:/data/dremgr

profile=
date=
sync=
import=
args=(
    "synchroniser les données depuis le serveur de prod (aide pour le développement)"
    #"usage"
    -g:,--profile profile=
    -P,--prod profile=prod
    -T,--test profile=test
    -@:,--date date=
    -u,--sync sync=1
    -i,--import import=1
)
parse_args "$@"; set -- "${args[@]}"

[ -n "$date" ] || setx date=date +%Y%m%d
[ -n "$sync" -o -n "$import" ] || {
    sync=1
}

cd "$MYDIR/.."

if [ -n "$sync" ]; then
    [ -n "$profile" ] || profile=prod
    args=(
        -vrltp
        --include "*$date*" --exclude "*" --delete-excluded
        "$SOURCE/${profile}-dredata/downloads/"
        "var/${profile}-dredata/downloads/"
    )
    rsync "${args[@]}"
fi

if [ -n "$import" ]; then
    ./dbinst -i -- "-@$date"
fi
