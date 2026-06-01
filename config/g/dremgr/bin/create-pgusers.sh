#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

unset PGDATABASE
pg_create_users | psql

export PGDATABASE="$DBNAME"
pg_grant_privileges -rdp | psql -a

if [ -n "$PDBNAME" ]; then
    export PGDATABASE="$PDBNAME"
    pg_grant_privileges -dp | psql -a
fi
