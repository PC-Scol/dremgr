# Release Notes

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
    if [ "$(readlink "$i")" == front.env ]; then
      ln -sf dremgr.env "$i"
    fi
  done
fi
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary