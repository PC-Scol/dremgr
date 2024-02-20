-- -*- coding: utf-8 mode: sql -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

create database dre;

\connect dre

alter default privileges in schema public grant select on tables to reader;
-- grant select on all tables in schema public to reader;
