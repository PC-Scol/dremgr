# -*- coding: utf-8 mode: dockerfile -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
ARG REGISTRY=pubdocker.univ-reunion.fr
FROM $REGISTRY/src/base as base
FROM $REGISTRY/src/postgres as postgres

FROM postgres:15-bookworm
COPY --from=base /g/ /g/
COPY --from=postgres /g/ /g/

ARG APT_MIRROR SEC_MIRROR APT_PROXY TIMEZONE
ENV APT_MIRROR=$APT_MIRROR SEC_MIRROR=$SEC_MIRROR APT_PROXY=$APT_PROXY TIMEZONE=$TIMEZONE

RUN /g/build -a @default postgres
RUN /g/pkg i @ssl @git

ENTRYPOINT ["/g/entrypoint"]
