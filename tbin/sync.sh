#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
source /etc/nulib.sh || exit 1

prod_SOURCE=root@pegase-dre2023.univ.run:/data/dremgr
test_SOURCE="$prod_SOURCE"
demo_SOURCE=root@sda-demo2025.univ.run:dremgr/var

profile=
source_profile=
dest_profile=
date=
sync=
import=
args=(
    "synchroniser les données depuis le serveur de prod (aide pour le développement)"
    #"usage"
    -g:,--profile profile=
    -P,--prod profile=prod
    -T,--test profile=test
    -M,--demo profile=demo
    --sg:,--source-profile source_profile=
    --dg:,--dest-profile dest_profile=
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
    [ -n "$source_profile" ] || source_profile="$profile"
    [ -n "$dest_profile" ] || dest_profile="$source_profile"
    SOURCE="${source_profile}_SOURCE"; SOURCE="${!SOURCE}"
    args=(
        -vrltp
        --include "*$date*" --exclude "*"
        --delete-excluded --delete-after
        "$SOURCE/${source_profile}-dredata/downloads/"
        "var/${dest_profile}-dredata/downloads/"
    )
    rsync "${args[@]}"
fi

if [ -n "$import" ]; then
    ./dbinst -i -- "-@$date"
fi
