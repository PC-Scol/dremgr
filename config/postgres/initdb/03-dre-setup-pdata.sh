#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

if [ -n "$PDBNAME" ]; then
    psql <<EOF
create extension if not exists postgres_fdw;

create server if not exists $PDBNAME
foreign data wrapper postgres_fdw
options (dbname '$PDBNAME');

create user mapping if not exists
for $POSTGRES_USER
server $PDBNAME;

create user mapping if not exists
for $FE_USER
server $PDBNAME
options (password_required 'false');
EOF
fi
