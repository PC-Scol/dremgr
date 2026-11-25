#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
source /g/nulib/load.sh || exit 1

cleanup=
args=(
    "Nettoyage des fichiers postgresql 15"
    #"usage"
    --cleanup .
)
parse_args "$@"; set -- "${args[@]}"

[ -n "$cleanup" ] || die

cd /var/lib/postgresql

if [ -d 15 ]; then
    ewarn "Suppression des fichiers de sauvegarde"
    rm -rf 15
else
    enote "Suppression des fichiers déjà effectuée"
fi
