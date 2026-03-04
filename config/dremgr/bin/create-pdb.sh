#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

create_db=1
action=auto
revoke=
args="$(getopt -o ndpRK -l no-create,defaults,privileges,recreate,revoke -n "$0" -- "$@")" || exit 1
eval "set -- $args"
while [ $# -gt 0 ]; do
    arg="$1"; shift
    case "$arg" in
    --) break;;
    -n|--no-create) create_db=;;
    -d|--defaults) action=defaults;;
    -p|--privileges) action=privileges;;
    -R|--recreate) action=both;;
    -K|--revoke) revoke=1;;
    esac
done
if [ "$action" == auto ]; then
    [ -n "$create_db" ] && action=defaults || action=privileges
fi
defaults=
privileges=
both=
case "$action" in
defaults) defaults=1;;
privileges) privileges=1;;
both) defaults=1; privileges=1;;
esac

if [ -n "$PDBNAME" ]; then
    unset PGDATABASE
    psql -a <<EOF
${create_db:+create database $PDBNAME;}

\connect $PDBNAME

$(pg_grant_privileges ${defaults:+-d} ${privileges:+-p} ${revoke:+-K})
EOF
fi
