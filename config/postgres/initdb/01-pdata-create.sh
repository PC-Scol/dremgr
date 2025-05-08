#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

if [ -n "$PDBNAME" ]; then
    unset PGDATABASE
    psql <<EOF
create database $PDBNAME;

\connect $PDBNAME

alter default privileges in schema public grant select on tables to reader;
-- grant select on all tables in schema public to reader;
EOF
fi
