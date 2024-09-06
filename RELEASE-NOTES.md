# Release Notes

## Version 0.19.0

Certains paramètres ont été modifiés dans le fichier `dremgr.env` (ou
`prod_profile.env` si le mode simple est utilisé). Les modifications suivantes
sont à apporter manuellement:

* Rajouter les paramètres `PGBOUNCER_ADMIN_PASS` et `MINIMIZE_DOWNTIME`
  ~~~sh
  PGBOUNCER_ADMIN_PASS=XXX_a_modifier
  MINIMIZE_DOWNTIME=1
  ~~~
* Enlever les définitions de `PGDATABASE` et `APP_PROFILE_VARS`. Les lignes
  suivantes doivent être supprimées:
  ~~~sh
  PGDATABASE=dre

  APP_PROFILE_VARS="
  HOST_MAPPINGS
  DRE_URL DRE_USER DRE_PASSWORD
  POSTGRES_HOST POSTGRES_USER POSTGRES_PASSWORD
  INST_VIP INST_PORT
  FE_HOST FE_PORT FE_DBNAME FE_USER FE_PASSWORD
  PGADMIN_USER PGADMIN_PASSWORD
  ADMINER_DBHOSTS ADMINER_DBCONNS
  ADDON_URLS
  CRON_PLAN CRON_DISABLE CRON_MAX_AGE
  "
  ~~~

ATTENTION! bien lire la documentation concernant le nouveau paramètre
`MINIMIZE_DOWNTIME`, notamment si votre base DRE est provisionnée autrement que
par des addons.

## Version 0.18.0 du 16/07/2024

Les options suivantes ont été renommées:
* `-i, --import-dumps` devient `-i, --import-all`
* `-I, --import-dreaddon` devient `-J, --import-one-devel`

Nouvelles options
* `-I, --import-one` pour réimporter uniquement un addon

## Version 0.17.0 du 15/07/2024

ATTENTION! Changement cassant: le fichier `front.env` est renommé `dremgr.env`

Pour migrer depuis une version inférieur à 0.17.0, utiliser les commandes
suivantes:
~~~sh
if [ -f front.env ]; then
  mv front.env dremgr.env
  for i in *_profile.env; do
    [ -L "$i" ] || continue
    if [ "$(readlink "$i")" == front.env ]; then
      ln -sf dremgr.env "$i"
    fi
  done
fi
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary