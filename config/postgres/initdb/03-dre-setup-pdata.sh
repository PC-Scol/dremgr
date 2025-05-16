#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

if [ -n "$PDBNAME" ]; then
    read -a users <<<"$FE_USER: ${FE_USERS//
/ }"
    create_user_mappings=""
    for user in "${users[@]}"; do
        user="${user%%:*}"
        create_user_mappings="$create_user_mappings
create user mapping if not exists
for $user
server $PDBNAME
options (password_required 'false');"
    done

    psql <<EOF
create extension if not exists postgres_fdw;

create server if not exists $PDBNAME
foreign data wrapper postgres_fdw
options (dbname '$PDBNAME');

create user mapping if not exists
for $POSTGRES_USER
server $PDBNAME;

$create_user_mappings

import foreign schema public
from server pdata
into public;
EOF
fi
