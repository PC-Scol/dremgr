#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
source /etc/nulib.sh || exit 1

SOURCE=root@pegase-dre2023.univ.run:dremgr

date=
import=
args=(
    "synchroniser les données depuis le serveur de prod (aide pour le développement)"
    #"usage"
    -@:,--date date=
    -i,--import import=1
)
parse_args "$@"; set -- "${args[@]}"

[ -n "$date" ] || setx date=date +%Y%m%d

cd "$MYDIR/.."
rsync -vrltp --include "*$date*" --exclude "*" --delete-excluded "$SOURCE/var/prod-dredata/downloads/" var/prod-dredata/downloads/

if [ -n "$import" ]; then
    ./inst -i -- "-@$date"
fi
