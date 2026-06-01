#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

diff \
     <(cat /usr/share/postgresql/postgresql.conf.sample |
           grep -Pv '^\s*#' |
           grep -Pv '^\s*$' |
           sed -r 's/\s*#.*$//'
      ) \
     <(cat /etc/postgresql/postgresql.conf |
           grep -Pv '^\s*#' |
           grep -Pv '^\s*$' |
           sed -r 's/\s*#.*$//'
      )
