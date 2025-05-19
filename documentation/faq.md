# Foire Aux Questions

Voici une liste de questions que personne n'a jamais posées, mais qui si
c'était le cas, seraient sûrement les plus fréquentes :-)

## La base de données n'est pas accessible par BO / PowerBI / tout autre client

Par défaut, la base de données n'est accessible que depuis l'hôte local (via
Adminer ou en ligne de commande)

Pour que la base de données soit accessible sur le réseau par des clients
génériques, il faut laisser vide le paramètre `DBVIP` (ou mettre l'adresse IP de
l'interface d'écoute). Bien entendu, il faut relancer les services en cas de
changement de configuration.

## Je voudrais créer des schémas supplémentaires

Par défaut, pour minimiser le temps d'indisponibilité, la base est recréée à
zéro chaque jour. L'avantage est aussi que les addons sont simplifiés puisqu'ils
sont toujours exécutés sur une base vierge, et n'ont pas besoin de prendre en
compte l'existence éventuelle de précédentes données.

S'il faut gérer des données persistantes, elles peuvent être créées dans la base
de données `pdata`. A chaque fois que la base `dre` est recréée, les tables du
schéma `public` de `pdata` sont importées automatiquement en tant que "tables
étrangères" dans le schéma `public` de `dre`

Ainsi, les tables de la base de données `pdata` sont utilisables directement,
comme si elles faisaient partie de la base de données `dre`

Consulter la [documentation du paramètre MINIMIZE_DOWNTINE](documentation/parametres.md#minimize_downtine)
pour d'autres pistes si l'utilisation de la base de données `pdata` ne convient pas.

NB: dans la configuration par défaut via pgbouncer, l'accès aux bases est
préfixé du profil, i.e il faut se connecter à `prod_pdata`, `test_pdata`, etc.

## Je voudrais que la base de données de prod soit accessible avec le nom `dre` au lieu de `prod_dre`

Dans le mode d'installation avancé, les bases de données sont accédées avec un
nom de la forme `<PROFIL>_dre`, e.g `prod_dre`, `test_dre`, etc.

Si on veut accéder à la base de prod avec le nom `dre` au lieu de `prod_dre`,
il faut faire les modifications suivantes dans `dremgr.env`
~~~sh
# supprimer ces lignes
PGBOUNCER_DBS="$DBNAME $PDBNAME"

# ajouter ces lignes
__ALL__PGBOUNCER_DBS="$DBNAME $PDBNAME"
prod_PGBOUNCER_DBS="$DBNAME:$DBNAME $PDBNAME:$PDBNAME"

# modifier ces lignes
prod_FE_DBNAME="$DBNAME"
~~~

Puis relancer les services
~~~sh
./dremgr -r
~~~

## Je voudrais que la base de données soit accessible directement au lieu de passer par pgbouncer

Dans le mode d'installation avancé, pgbouncer permet d'accéder aux instances des
bases de données de chaque profil à partir d'une adresse IP partagée. Toutes ces
bases de données sont nommées `dre`, mais elles sont accédées via pgbouncer avec
un nom de la forme `<PROFIL>_dre` e.g `prod_dre`, `test_dre`, etc.

Cependant, si le serveur ne contient qu'une seule instance (par exemple, si la
politique interne impose que le serveur de prod soit sur une machine à part), il
est possible de passer outre l'utilisation de pgbouncer et d'accéder directement
à la base de données

Dans le mode d'installation simple, c'est le cas par défaut: il n'y a rien à
faire de plus.

Dans le mode d'installation avancé, il faut désactiver l'écoute pour pgbouncer
et jouer sur le paramètre `INST_PORT`
~~~sh
NO_LSN_DBFRONT=1
prod_INST_PORT="$DBPORT"
~~~

Puis relancer les services
~~~sh
./dremgr -r
~~~

## Je voudrais créer des utilisateurs supplémentaires

Cette fonctionnalité existe mais elle n'est pas documentée en détail, parce
qu'elle doit être remaniée pour être dynamique. Actuellement, tout changement
dans la liste des utilisateurs nécessite le redémarrage complet des instances.

Si vous souhaitez tout de même créer des utilisateurs supplémentaires, notamment
pour faciliter le suivi et le controle de l'accès à la base de données DRE,
suivez [la documentation dédiée](documentation/setup-users.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary