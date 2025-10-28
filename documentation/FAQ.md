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

Consulter la [documentation du paramètre MINIMIZE_DOWNTINE](parametres.md#minimize_downtine)
pour d'autres pistes si l'utilisation de la base de données `pdata` ne convient pas.

NB: dans la configuration par défaut via pgbouncer, l'accès aux bases est
préfixé du profil, i.e il faut se connecter à `prod_pdata`, `test_pdata`, etc.

## Je voudrais que la base de données de prod soit accessible avec le nom `dre` au lieu de `prod_dre`

Dans le mode d'installation avancé, les bases de données sont accédées avec un
nom de la forme `<PROFIL>_dre`, e.g `prod_dre`, `test_dre`, etc.

Si on veut accéder à la base de prod avec le nom `dre` au lieu de `prod_dre`,
il faut faire les modifications suivantes dans `dremgr.env`
~~~sh
# ajouter cette lignes
prod_PGBOUNCER_DBS="$DBNAME:$DBNAME $PDBNAME:$PDBNAME"

# modifier cette lignes
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
suivez [la documentation dédiée](setup-users.md)

## Je voudrais que l'utilisateur `reader` soit réellement en lecture seule

Les nouvelles installations créent un utilisateur `dreadmin` qui a les droits
d'accès en lecture seule sur la base de données `dre` et en modification sur la
base de données `pdata`. Avant la version `1.5.0` cet utilisateur était appelé
`reader` et n'avait qu'un accès en lecture seule.

La conséquence est qu'après la mise à jour, on se retrouve avec un utilisateur
`reader` qui a un accès en modification à une base de données, ce qui est
légèrement perturbant si on aime que les mots aient un sens :-)

Suivez les instructions suivantes pour:
- créer l'utilisateur `dreadmin` comme pour une nouvelle installation
- rétablir l'accès en lecture seule à l'utilisateur `reader`

On part de la situation suivante
~~~sh
FE_USER=reader
FE_PASSWORD=<mdpReader>
~~~

Modifier le fichier de paramètres pour avoir
~~~sh
FE_USER=dreadmin
FE_PASSWORD=<mdpAdmin>
FE_USERS="
reader:<mdpReader>
"
FE_ACCESS="
reader:ro
"
~~~

Relancer les services
~~~sh
./dremgr -r
~~~

Créer les utilisateurs
~~~sh
./dbinst -Ax create-pgusers.sh
~~~

Puis relancer l'importation pour rétablir les accès
~~~sh
./dbinst -Ai -- -@latest
~~~

## Je voudrais changer les paramètres de lancement de PostgreSQL

Les paramètres de lancement de la base de données sont dans un fichier
`postgresql.conf` et permettent d'optimiser l'utilisation de la mémoire et
d'autres resources.

Le fichier utilisé par défaut est `config/postgres/postgresql.conf`

Pour changer les paramètres de lancement de toutes les bases de données, il
suffit de modifier ce fichier et de relancer les instances.

Pour spécifier des paramètres particuliers pour certains profils, copier le
fichier par défaut avec le nom `<PROFIL>_postgresql.conf` *à la racine du
projet* dans le même répertoire que le fichier `dremgr.env`
~~~sh
cp config/postgres/postgresql.conf prod_postgresql.conf
~~~
et relancez l'instance

Pour afficher les paramètres modifiés, utilisez l'option `--show-conf`
~~~sh
./dbinst --show-conf
~~~
Il s'agit des différences entre la configuration actuelle et la configuration
par défaut à l'installation

Pour changer la quantité de mémoire partagée disponible au conteneur pour
préparer les requêtes, il faut adapter le paramètres `shm_size` en créant un
fichier `dbinst-docker-compose.local.yml`
~~~yaml
services:
  db:
    shm_size: 2G
~~~
Dans cet exemple, on augmente la valeur par défaut de 1Go à 2Go

## Comment optimiser les performances de la base de données?

Je ne suis pas spécialiste de la chose :-(

Vous trouverez sans doute des choses intéressantes sur les sites suivants:
* https://www.postgresql.org/docs/current/performance-tips.html
* https://postgresqlco.nf/tuning-guide

La configuration par défaut livrée avec dremgr est plus ou moins taillée pour un
serveur avec 4Go de RAM, 2 coeurs et un disque SSD

Les valeurs suivantes sont modifiées par rapport aux valeurs par défaut:
* docker
  ~~~
  shm_size = 1G                      # valeur par défaut 64M
  ~~~
* postgresql
  ~~~
  shared_buffers = 1GB               # valeur par défaut 128MB
  work_mem = 16MB                    # valeur par défaut 4MB
  maintenance_work_mem = 256MB       # valeur par défaut 64MB
  min_wal_size = 1GB                 # valeur par défaut 80MB
  max_wal_size = 4GB                 # valeur par défaut 1GB
  random_page_cost = 1.1             # valeur par défaut 4.0
  ~~~

La configuration par défaut de PostgreSQL est faite pour s'assurer de la
durabilité des données (i.e pas de perte de données en cas de crash ou
d'extinction brutale de la machine)

Cependant, la base de données DRE étant principalement utilisée en lecture seule
et reconstruite tous les jours, la durabilité n'est peut-être pas une prérequis
absolu. Dans ce cas, les paramètres suivants peuvent augmenter les performances:
~~~
fsync = off
synchronous_commit = off
full_page_writes = off
~~~
A utiliser avec précaution. Un grand pouvoir implique de grandes responsabilités
:-)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary