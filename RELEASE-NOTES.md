# Release Notes

Si vous ne savez pas quelle la version actuellement instalée, vous pouvez
consulter le fichier `VERSION.txt`
~~~sh
cat VERSION.txt
~~~

Il vous suffit ensuite de suivre les instructions ci-dessous pour les versions ultérieures

## Version 0.22.0 du 29/11/2024

Cette mise à jour technique utilise des paquets sources différents. Elle demande
donc quelques manipulations.
~~~sh
# supprimer un fichier qui n'était pas suivi avant, pour éviter les conflits
rm -rf .composer.lock.runphp sbin/vendor

# mettre à jour le dépôt
git pull

# installer les paquets mis à jour
./sbin/runphp --bs --ue
./sbin/runphp ci

# Forcer le redémarrage des services
./dremgr -R
~~~

## Version 0.20.0 du 20/09/2024

Cette mise à jour nécessite que la reconstruction des images soit forcée
~~~sh
# mettre à jour le dépôt
git pull

# Forcer la reconstruction des images
./build -UR

# Forcer le redémarrage des services
./dremgr -R
~~~

## Version 0.19.0 du 13/09/2024

IMPORTANT: *AVANT* de faire cette mise à jour, il faut arrêter les services.
Prévoir un temps d'arrêt d'une trentaine de minutes, le temps de reconstruire
les images
~~~sh
# arrêter les services
./front -k
./inst -Ak

# mettre à jour le dépôt
git pull

# reconstruire les images
./build -r

# faire les modifications des paramètres indiquées ci-dessous
...

# Démarrer les services
./dremgr
~~~

Les modifications notables sont:
* introduction d'un script unique `dremgr` pour simplifier certaines actions
* le frontal a été séparé en deux entités: frontal web et frontal bdd
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