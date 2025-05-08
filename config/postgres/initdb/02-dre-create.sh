#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

unset PGDATABASE
psql <<EOF
create database dre;

\connect dre

alter default privileges in schema public grant select on tables to $FE_USER;
-- grant select on all tables in schema public to $FE_USER;
EOF
fi
