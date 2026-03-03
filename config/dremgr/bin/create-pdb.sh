#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

create_db=1
defaults=
revoke=
args="$(getopt -o ndK -l no-create,defaults,revoke -n "$0" -- "$@")" || exit 1
eval "set -- $args"
while [ $# -gt 0 ]; do
    arg="$1"; shift
    case "$arg" in
    --) break;;
    -n|--no-create) create_db=;;
    -d|--defaults) defaults=1;;
    -K|--revoke) revoke=1;;
    esac
done

if [ -n "$PDBNAME" ]; then
    unset PGDATABASE
    psql <<EOF
${create_db:+create database $PDBNAME;}

\connect $PDBNAME

$(pg_grant_privileges ${defaults:+-d} ${revoke:+--revoke})
EOF
fi
