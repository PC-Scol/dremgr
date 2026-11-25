#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
source /etc/nulib.sh || exit 1

rsync -rltpv "$MYDIR/${MYNAME%.sh}.d/" "${CONFIG_DEST:-$MYDIR/../}"
