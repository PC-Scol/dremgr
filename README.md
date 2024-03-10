# dremgr

dremgr est un environnement pour la gestion d'une ou plusieurs instances de base
DRE

## Pré-requis

dremgr est développé et testé sur Debian 11. Il devrait fonctionner sur
n'importe quel système Linux, pourvu que les pré-requis soient respectés.

Le document [pré-requis rddmgr](documentation/prerequis.md) liste tous les
pré-requis et donne les instructions pour les installer sur Debian 11

## Démarrage rapide

Ouvrez un terminal et clonez le dépôt
~~~sh
git clone https://github.com/PC-Scol/dremgr.git

cd dremgr
~~~

Il faut d'abord construire les images utilisées par l'application
~~~sh
./build
~~~
La première invocation crée le fichier d'exemple `build.env`

Il FAUT consulter ce fichier et l'éditer AVANT de continuer. Notamment, les
variables suivantes doivent être configurées le cas échéant:

`APT_PROXY`
: proxy pour l'installation des paquets Debian

`APT_MIRROR`
`SEC_MIRROR`
: miroirs à utiliser. Il n'est généralement pas nécessaire de modifier ces
  valeurs

`TIMEZONE`
: Fuseau horaire, si vous n'êtes pas en France métropolitaine

`PRIVAREG`
: nom d'un registry docker interne vers lequel les images pourraient être
  poussées. Il n'est pas nécessaire de modifier ce paramètre.

Une fois le fichier configuré, les images peuvent être construites
~~~sh
./build
~~~

Une fois que les images sont construites, il faut décider du mode de
fonctionnement:
* dans le mode le plus simple, une seule instance de la base DRE est
  configurée. c'est le mode de fonctionnement par défaut
* dans un autre mode de fonctionnement qui demande un peu plus de travail de
  configuration, plusieurs instances de DRE peuvent être gérées depuis le même
  répertoire (prod, test, etc.). Dans ce cas, il faut activer les services
  frontaux qui comprennent:
  * proxy pgbouncer qui permet de servir plusieurs bases postgresql sur la même
    adresse IP. ce service est obligatoire.
  * pgAdmin pour accéder à la base de façon graphique
  * adminer, en tant qu'alternative pour accéder à la base de façon graphique
  * une application web destinée aux utilisateurs autorisés qui affiche les
    informations de connexion à la base de données, et met à disposition de la
    documentation technique et/ou fonctionnelle

## Mode simple

Une fois les images construites, il faut préparer le démarrage de l'instance DRE
~~~sh
./inst
~~~
La première invocation crée le fichier d'exemple `prod_profile.env`

Il FAUT consulter ce fichier et l'éditer AVANT de continuer. Notamment, les
variables suivantes doivent être configurées le cas échéant:

`DRE_URL`
`DRE_USER`
`DRE_PASSWORD`
: URL, utilisateur et mot de passe permettant de télécharger les dumps DRE

`DBVIP`
: Adresse sur laquelle l'instance de la base DRE est disponible. Laisser vide
  pour écouter sur toutes les interfaces.

`POSTGRES_PASSWORD`
: mot de passe de l'utilisateur administrateur de la base de données

`FE_PASSWORD`
: mot de passe de l'utilisateur en lecture seule de la base de données

`ADDON_URLS`
: Liste d'URLs de dépôts git contenant des "add-ons" de dremgr. Par défaut, les
  deux URLs suivants sont listés:
  * `https://github.com/PC-Scol/dreaddon-documentation.git`
    documentation technique et fonctionnelle de DRE
  * `https://github.com/PC-Scol/dreaddon-pilotage.git`
    schéma "pilotage" développé par l'UPHF, base de l'univers BO livré aussi par
    l'UPHF
  Si ces URLs ne sont pas en accès public, il suffit de spécifier un compte
  autorisé et son mot de passe e.g
  `https://LOGIN:PASSWORD@github.com/PC-Scol/dreaddon-pilotage.git`

  D'autres add-ons peuvent être spécifiés au fur et à mesure qu'ils sont rendus
  disponibles.

Il y a d'autres paramètres, consulter le fichier pour la liste complète

Une fois le fichier configuré, l'instance peut être démarrée
~~~sh
./inst
~~~

La base de données est accessible sur l'adresse IP spécifiée dans le
fichier. par défaut, il s'agit de l'adresse locale:
~~~sh
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=dre"
~~~

Bien entendu, pour le moment elle ne contient aucune donnée. On peut forcer le
téléchargement et l'importation:
~~~sh
docker exec prod-dreinst-cron-1 dl-dumps
~~~
Sinon, le téléchargement et l'importation se fait tous les jours à l'heure
définie dans la variable `CRON_PLAN` c'est à dire par défaut 5h45

## Mode avancé

Dans ce mode, un fichier `front.env` définit l'ensemble des profils qui sont
pilotés par l'installation.

Commencer par copier le fichier d'exemple
~~~sh
cp documentation/front.env.sample front.env
~~~

Reporter le cas échéant les paramètres déjà saisis dans `prod_profile.env`,
notamment `POSTGRES_PASSWORD`, `FE_PASSWORD`, `prod_DRE_URL`,
`prod_DRE_PASSWORD` et `DREADDON_URLS`

La variable `APP_PROFILES` liste les profils supportés. Pour chacun de ces
profils, un ensemble de variable doit être défini plus bas dans le fichier. On
peut rajouter autant de profils que nécessaire, mais il faut définir les
variables avec le préfixe correspondant en prenant exemple sur la section "test"

Ensuite, il faut définir autant de fichiers `PROFIL_profile.env` que de profils
mentionnés dans le fichier `front.env`. En l'occurrence, comme les profils prod
et test sont définis, on fait les liens symboliques correspondants:
~~~sh
ln -sf front.env prod_profile.env
ln -sf front.env test_profile.env
~~~
Attention! le fichier prod_profile.env précédent est perdu au profit du nouveau
lien symbolique.

Créer le réseau mentionné dans la configuration (variable `DBNET`)
~~~sh
docker network create --attachable dremgr_db
~~~

Puis démarrer toutes les instances correspondant à chaque profil défini
~~~sh
./inst -A
~~~
Attention! si l'instance en mode simple avait déjà été démarrée, il faut la
remplacer par les nouvelles instances, i.e
~~~sh
# forcer le redémarrage
./inst -Ar
~~~
Notez aussi que si l'instance existait déjà, les comptes ne sont pas récréés

Maintenant, il faut configurer les services frontaux.

Pour la connexion au service web, éditez les fichiers suivants:
* `config/apache/mods-available/auth_cas.conf`
  Par défaut, l'authenfication se fait par CAS. Ce fichier sert à indiquer
  l'adresse du serveur CAS.
* `config/apache/authnz.conf`
  Ce fichier détaille les utilisateurs autorisés. Par défaut, seul l'utilisateur
  hypothétique `dreuser` est autorisé. Lister les utilisateurs de cette façon:
  ~~~conf
  Require user bob alice
  ~~~
  Consulter la documentation apache pour savoir comment autoriser sur la base
  par exemple d'attributs fournis par le serveur CAS

Par défaut, le service web sera accessible sur <http://localhost:7081>. Pour
changer cette valeur, éditer le fichier `front.env` et configurer les variables
`LBHOST` et `LBHTTP`

Pour configurer l'accès en HTTPS, envoyer un message sur le forum pour avoir la
procédure exacte.

Ensuite, démarrer les services frontaux
~~~sh
./front
~~~
En cas de changement de configuration, utiliser l'option -r pour redémarrer les
services concernés
~~~sh
# forcer le redémarrage
./front -r
~~~

Visiter <http://localhost:7081> pour connaitre les paramètres de connexion à
chaque instance de base DRE. par défaut, il s'agit de l'adresse locale:
~~~sh
# prod
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=dre"
# test
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=test_dre"
~~~

Bien entendu, pour le moment, les bases ne contiennent aucune donnée. On peut
forcer le téléchargement et l'importation:
~~~sh
# prod
docker exec prod-dreinst-cron-1 dl-dumps
# test
docker exec test-dreinst-cron-1 dl-dumps
~~~
Sinon, le téléchargement et l'importation se fait tous les jours à l'heure
définie dans la variable `CRON_PLAN` c'est à dire par défaut 5h45

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary