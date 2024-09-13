# Release Notes

## Version 0.19.0

IMPORTANT: *AVANT* de faire cette mise à jour, il faut arrêter les services, faire
la mise à jour, faire les modifications indiquées ci-dessous, puis redémarrer
les services.
~~~sh
# arrêter les services
./front -k
./inst -Ak

# mettre à jour le dépôt
git pull

# faire les modifications des paramètres indiquées ci-dessous
...

# Reconstruire les images
./build -r

# Redémarrer les services
./dremgr
~~~

Les modifications notables sont:
* introduction d'un script unique `dremgr` pour simplifier certaines actions
* le frontal a été séparé en deux entités: frontal web et frontal postgresql
* renommage des scripts `inst` en `dbinst` et `front` en `webfront`
* certains paramètres ont été modifiés dans le fichier `dremgr.env` (ou
  `prod_profile.env` si le mode simple est utilisé) et doivent faire l'objet
  d'une modification manuelle

Voici les modification à reporter manuellement dans les fichiers de
configuration:

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

IMPORTANT: bien lire la documentation concernant le nouveau paramètre
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