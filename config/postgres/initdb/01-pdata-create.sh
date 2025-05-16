#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

read -a user_access <<<"$FE_USER:rw ${FE_ACCESSS//
/ }"
declare -A tgrants sgrants
for user in "${user_access[@]}"; do
    access="${user#*:}"
    user="${user%%:*}"
    case "$access" in
    admin|all|a)
        tgrant="all privileges"
        sgrant="update"
        ;;
    write|rw|w)
        tgrant="select, insert, update, delete, truncate"
        sgrant="usage"
        ;;
    read|ro|r|*)
        tgrant="select"
        sgrant="select"
        ;;
    esac
    grants["$user"]="$grant"
done

read -a users <<<"$FE_USER: ${FE_USERS//
/ }"

set_privileges=""
for user in "${users[@]}"; do
    user="${user%%:*}"
    tgrant="${tgrants[$user]:-select}"
    sgrant="${sgrants[$user]:-select}"
    set_privileges="$set_privileges
alter default privileges in schema public grant $tgrant on tables to $user;
alter default privileges in schema public grant $sgrant on sequences to $user;
-- grant $tgrant on all tables in schema public to $user;
-- grant $sgrant on all sequences in schema public to $user;"
done

if [ -n "$PDBNAME" ]; then
    unset PGDATABASE
    psql <<EOF
create database $PDBNAME;

\connect $PDBNAME

$set_privileges
EOF
fi
