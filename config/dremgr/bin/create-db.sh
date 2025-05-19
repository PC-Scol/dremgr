#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

unset PGDATABASE
psql <<EOF
create database $DBNAME;

\connect $DBNAME

$(pg_grant_default_privileges)
EOF
