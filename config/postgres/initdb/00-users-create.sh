#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

read -a users <<<"$FE_USER:$FE_PASSWORD ${FE_USERS//
/ }"
create_roles=""
for user in "${users[@]}"; do
    password="${user#*:}"
    user="${user%%:*}"
    create_roles="$create_roles
create user $user with password '$password';"
done

unset PGDATABASE
psql <<EOF
$create_roles
EOF
