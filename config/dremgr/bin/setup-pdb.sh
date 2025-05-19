#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

if [ "$1" == --create ]; then
    "$(dirname -- "$0")/create-pdb.sh"
fi

if [ -n "$PDBNAME" ]; then
    psql <<EOF
create extension if not exists postgres_fdw;

create server if not exists $PDBNAME
foreign data wrapper postgres_fdw
options (dbname '$PDBNAME');

create user mapping if not exists
for $POSTGRES_USER
server $PDBNAME;

$(pg_create_user_mappings)

import foreign schema public
from server $PDBNAME
into public;
EOF
fi
