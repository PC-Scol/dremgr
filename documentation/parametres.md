# Paramètres dremgr

`build` a besoin de certains paramètres pour pouvoir construire les images
nécessaires à dremgr

`dbinst`, `dbfront` et `webfront` ont besoin de certains paramètres pour
démarrer les services associés

Ces paramètres sont cherchés dans des fichiers dont le nom est normalisés

## Paramètres communs

Chaque instance correspond à un profil nommé. Les paramètres de chaque instance
sont lus dans un fichier de la forme `<profil>_profile.env`

Dans le mode simple, le nom du profil est fixé: c'est `prod`, ainsi les
paramètres sont lus dans le fichier `prod_profile.env`

**Récupération des données chez PC-SCOL**

`DRE_URL`
: Adresse du serveur des dumps DRE e.g https://dre-dump.DOMAINEETAB.pc-scol.fr

`DRE_USER`
`DRE_PASSWORD`
: utilisateur et mot de passe pour accéder au serveur des dumps DRE

`DRE_PREFIX`
: Préfixe des fichiers à télécharger. Normalement, la liste des fichiers à
  télécharger est calculée à partir du contenu du fichier `checksums`. Dans tous
  les cas, seuls les fichiers dont le nom commence par ce préfixe sont
  considérés.

  La valeur par défaut est `prod-DOMAINEETAB` pour le profil `prod` et
  `DOMAINEETAB` pour les autres profils

  Attention! si vous utilisez le mode simple pour attaquer une instance qui
  n'est PAS de production, il faut alors absolument définir ce paramètre, parce
  que dans le mode simple, le nom du profil est fixé à `prod`

`DRE_FILES_FROM`
: Indiquer que le répertoire de téléchargement est celui du profil
  spécifié. Cela permet de partager le répertoire de téléchargement pour des
  profils qui attaquent la même instance PEGASE. Il faut probablement aussi
  spécifier le paramètre `DRE_PREFIX` pour sélectionner les bons fichiers.

  Dans l'exemple suivant, le profil `devel` utilise autant que possible la
  configuration du profil `prod`, ainsi que les fichier déjà téléchargés, mais a
  une liste d'addons différente (c'est la branche develop de l'addon
  dreaddon-local qui est installée)
  ~~~sh
  APP_PROFILES="prod devel"
  ...
  prod_DRE_URL=https://dre-dump.univ-ville.pc-scol.fr
  prod_DRE_USER=dre
  prod_DRE_PASSWORD=z3Pass
  ...
  ADDON_URLS="
  # installer la branche main
  https://git.univ-ville.fr/dreaddon-local.git
  "
  ...
  devel_DRE_URL="$prod_DRE_URL"
  devel_DRE_USER="$prod_DRE_USER"
  devel_DRE_PASSWORD="$prod_DRE_PASSWORD"
  devel_DRE_PREFIX=prod-univ-ville
  devel_DRE_FILES_FROM=prod
  devel_ADDON_URLS="
  # installer la branche develop sur l'instance devel
  https://git.univ-ville.fr/dreaddon-local.git#develop
  "
  ...
  ~~~

**Configuration du service postgresql**

`POSTGRES_HOST`
: nom d'hôte du serveur BDD (dans le réseau interne docker). ne pas modifier

`POSTGRES_USER`
: utilisateur administrateur de la BDD

`POSTGRES_PASSWORD`
: mot de passe administrateur de la BDD. Ce paramètre doit être modifié *avant*
  le premier démarrage de l'instance de la base de données. Si l'instance a déjà
  démarré, modifier ce paramètre n'a plus aucun effet.

**Adresse et port d'écoute de l'instance**

`INST_VIP`
`INST_PORT`
: Adresse et port d'écoute de l'instance.

  Si `INST_VIP` est vide ou contient une adresse telle que `0.0.0.0`, l'instance
  est accessible sur toutes les interfaces réseaux. La valeur par défaut
  `127.0.0.1` n'autorise la connexion que depuis l'hôte local.

  Si `INST_PORT` est vide, l'écoute directe est désactivée (utilisé uniquement
  dans le mode avancé)

**Informations à afficher à l'utilisateur**

Ces paramètres sont utilisés par l'application web pour afficher des
informations à l'utilisateur.

`FE_HOST`
`FE_PORT`
: Nom d'hôte sur lequel est accessible la BDD (sur le réseau externe), et port
  d'écoute.

`FE_DBNAME`
: nom de la BDD créée pour contenir les données DRE

`FE_USER`
: utilisateur avec accès en lecture universel (il a accès en lecture à TOUTES
  les données). S'il faut créer des utilisateurs supplémentaires, il faut passer
  par un addon

`FE_PASSWORD`
: mot de passe de l'utilisateur avec accès en lecture universel. Ce paramètre
  doit être modifié *avant* le premier démarrage de l'instance de la base de
  données. Si l'instance a déjà démarré, modifier ce paramètre n'a plus aucun
  effet.

**Autres données**

`ADDON_URLS`
: Liste d'addons à installer et/ou mettre à jour lors de chaque import
  journalier, un par ligne. Le préfixe https://github.com/ est rajouté
  automatiquement le cas échéant
  
  Ajouter un suffixe '#branch' pour spécifier une branche ou '^rev' pour
  spécifier un tag ou un id de commit, e.g PC-Scol/dreaddon.git#develop pour
  utiliser la branche develop

  Cf [Installation d'addons](dreaddons.md) pour les détails

`CRON_PLAN`
: Planification cron pour le script d'import

  Les fichiers sont générés à 4h GMT, soit 5h heure de Paris. on planifie à 5h30
  par défaut pour laisser le temps à l'export de se terminer.

  Bien entendu, si vous n'êtes pas en métropole, il faudra modifier ce paramètre
  en fonction du fuseau horaire.

`CRON_DISABLE`
: Indiquer une valeur quelconque pour désactiver les imports automatiques. La
  base de données est laisée en l'état et plus aucune mise à jour n'est lancée
  automatiquement

`CRON_MAX_AGE`
: Nombre de jours au terme duquel un fichier téléchargé est supprimé. Par
  défaut, ne garder que les 15 derniers jours

<a name="minimize_downtine"></a>
`MINIMIZE_DOWNTINE`
: Si ce paramètre est activé, l'importation des dumps et des addons se fait de
  façon à minimiser le temps d'indisponibilité de la base de données DRE:
  l'importation se fait dans une base temporaire vide, et ensuite, cette base
  temporaire remplace la base actuelle.

  De cette façon, le temps pendant lequel la base DRE n'est pas disponible à
  cause de l'import quotidien est réduit à 1 ou 2 secondes. Bien entendu, les
  connexion en cours sont "sauvagement" arrêtées lors de la bascule sur la
  nouvelle base.

  Cependant, à cause de ce mode opératoire, toutes les tables et données qui ont
  été créées "manuellement" dans la base de données DRE sont perdues, puisqu'on
  repart toujours d'une base vide. Il y a plusieurs solutions, par ordre de
  préférence:
  * Créer les tables dans le schéma `public` de la base de données `pdata` :
    Elles sont importées automatiquement dans le schéma `public` de la base de
    données `dre` à chaque fois.
    C'est sans doute la méthode la plus simple et la plus efficace.
  * Créer les tables supplémentaires via un addon. Ce n'est pas forcément
    possible, surtout si ce sont des données créées manuellement.
  * Désactiver cette fonctionnalité (i.e `MINIMIZE_DOWNTINE=`) mais le temps
    d'indisponibilité va de 10 à 15 minutes voire plus en fonction du nombre
    d'addons et de la quantité de données.

`FORCE_CREATE_SCHEMAS`
: Liste des dumps qui ne contiennent pas la commande de création de schéma. En
  effet, les dumps pour les schémas standards contiennent une commande
  permettant de créer le schéma. Mais il arrive que certains dumps ne
  contiennent pas cette commande. Il faut donc forcer la création du schéma
  avant d'importer le dump.

  Par exemple, si les logs d'importation de dump contiennent cette erreur:
  ~~~text
  pg_restore: erreur : could not execute query: ERROR:  schema "schema_keycloak" does not exist
  ~~~
  Cela signifie que le dump `DOMAIN-YYYYmmdd-keycloak.bin` ne contient pas la
  commande de création du schéma `schema_keycloak`

  Pour pallier ce problème, il faut définir `FORCE_CREATE_SCHEMA=keycloak` et
  redémarrer les services afin que le schéma soit créé avant l'importation du
  dump.

`HOST_MAPPINGS`
: Liste de mappings d'hôte à installer dans le container, un par ligne

  Les mappings sont au format docker, i.e `cas.univ.tld:10.50.20.30`

**Paramètres partagés**

Le fichier livré contient des définitions permettant de changer en une seule
fois certains paramètres:
~~~sh
DBNET=
DBVIP=127.0.0.1
DBHOST=localhost
DBPORT=5432
DBNAME=dre
LBNET=
LBHTTP=7081
LBHTTPS=
~~~

Les valeurs partagées sont ensuite réutilisées ailleurs dans la configuration.
En temps normal, il n'est pas nécessaire de modifier ces valeurs:
~~~sh
LBVIP="$DBVIP"
LBHOST="$DBHOST"
INST_VIP="$DBVIP"
INST_PORT="$DBPORT"
FE_HOST="$DBHOST"
FE_PORT="$DBPORT"
FE_DBNAME="$DBNAME"
~~~

`DBNET`
: nom d'un réseau docker dans lequel doit tourner l'instance de la base de
  données

  en mode simple, c'est une valeur vide parce que c'est inutile. cf ci-dessous
  pour la valeur par défaut pour le mode avancé

`DBVIP`
: adresse d'écoute de la base de données

  la valeur par défaut ne permet l'accès que depuis l'hôte local, ce qui est
  approprié pour une utilisation personnelle à des fins de développement. pour
  un serveur de production, il faut laisser le champ vide pour écouter sur
  toutes les interfaces, ou indiquer une adresse IP spécifique
  
`DBHOST`
`DBPORT`
`DBNAME`
: nom d'hôte, port, et nom de la base de données pour la connexion. ces
  informations sont affichées à l'utilisateur par l'application web frontal

`LBNET`
: nom d'un réseau docker dans lequel doit tourner l'application web frontal

  en mode simple, c'est une valeur vide parce que ce n'est pas utilisé. cf
  ci-dessous pour la valeur par défaut pour le mode avancé

`LBVIP`
: adresse d'écoute de l'application web frontal

  la valeur par défaut est `$DBVIP`, mais on peut décider de mettre une valeur
  différente pour par exemple ne rendre accessible *que* l'application web

`LBHOST`
: nom d'hôte pour le serveur web frontal. c'est important surtout pour
  l'authentication CAS qui a besoin de connaitre l'adresse officielle du serveur
  web.

  la valeur par défaut `$DBHOST` est généralement un choix approprié

`LBHTTP`
: port d'écoute pour le serveur web frontal, pour l'accès en clair en http://

  la valeur par défaut est 7081, mais en production, on peut choisir de mettre
  une valeur standard comme 80

`LBHTTPS`
: port d'écoute pour le serveur web frontal, pour l'accès sécurisé en https://

  si on met une valeur, comme la valeur standard 443, il y a un certain nombre
  d'actions supplémentaires à faire pour configurer l'accès. consultez à cet
  effet la [documentation d'installation avancée](03installation-avancee.md)

**Paramètres privés non documentés**

`PGDATABASE`
`PGUSER`
`APP_PROFILES`
`APP_PROFILE_VARS`
`POSTGRES_PROFILES`
: Dans le mode simple, ne pas modifier ni supprimer la valeur de ces paramètres

## Paramètres du mode avancé

Dans le mode avancé, le fichier `dremgr.env` contient le paramètrage des
profils. Ce même fichier peut regrouper les configurations de tous les profils,
et ainsi les fichiers de profils peuvent être des liens symboliques vers
`dremgr.env`

Dans l'exemple suivant, `dremgr.env` contient la configuration pour les profils
`prod` et `test`:
~~~sh
ln -s dremgr.env prod_profile.env
ln -s dremgr.env test_profile.env
~~~

**Paramètres courants**

`APP_PROFILES`
: Liste des profils supportés. Chaque profil permet de piloter une instance de
  base de données

  pour chacune des variables listées dans `APP_PROFILE_VARS`, et pour chaque
  profil, un calcul est effectuée pour déterminer la valeur effective.

  prenons par exemple une variable nommé `MAVAR`, et le profil `prod`
  * si la variable `prod_MAVAR` existe, c'est cette valeur qui est sélectionnée
  * sinon, c'est la valeur de la variable `MAVAR` qui est sélectionnée

`APP_PROFILES_AUTO`
: Liste des profils à considérer en mode "automatique"

  Si ce paramètre est défini, `dbinst -A` effectue l'opération demandée sur tous
  les profils spécifiés pour les actions suivantes: import, invite sql, invite
  shell. Sinon, faire l'opération sur tous les profils listés dans
  `APP_PROFILES`

**Paramètres partagés**

Le fichier livré contient des définitions permettant de changer en une seule
fois certains paramètres:
~~~sh
DBNET=dremgr_db
DBVIP=127.0.0.1
DBHOST=localhost
DBPORT=5432
DBNAME=dre
PDBNAME=pdata
LBNET=
LBHTTP=7081
LBHTTPS=
POSTGRES_USER=postgres
POSTGRES_PASSWORD=XXX_a_modifier
FE_USER=dreadmin
FE_PASSWORD=XXX_a_modifier
~~~

Les valeurs partagées sont ensuite réutilisées ailleurs dans la configuration.
En temps normal, il n'est pas nécessaire de modifier ces valeurs:
~~~sh
LBVIP="$DBVIP"
LBHOST="$DBHOST"
<profil>_INST_VIP="$DBVIP"
<profil>_INST_PORT=
<profil>_FE_HOST="$DBHOST"
<profil>_FE_PORT="$DBPORT"
<profil>_FE_DBNAME="<profil>_$DBNAME"
PGBOUNCER_DBS="$DBNAME $PDBNAME"
PGBOUNCER_USERS="${FE_USER}:${FE_PASSWORD}"
PGADMIN_USER="$FE_USER"
PGADMIN_PASSWORD="$FE_PASSWORD"
ADMINER_USER="$FE_USER"
ADMINER_PASSWORD="$FE_PASSWORD"
ADMINER_DB="$DBNAME"
~~~

Les descriptions sont les mêmes que dans la section ci-dessus, avec les
précisions suivantes:

`DBNET`
: nom du réseau docker dans lequel doivent tourner les instances des bases de
  données. la valeur par défaut est dremgr_db

  l'application web est aussi placée dans ce réseau afin de pouvoir afficher la
  version et la date de la dernière importation de la base de données

`LBNET`
: nom d'un réseau docker dans lequel doit tourner l'application web frontal

  il n'y a généralement pas de raison de mettre l'application dans un réseau
  spécifique, sauf si elle doit être placée derrière un frontal tel que traefik

`<profil>_INST_PORT`
: Cette variable est vide parce que les instances sont accédées via le proxy
  pgbouncer. Il n'y a donc généralement pas de raison pour activer un accès
  direct à l'instance.

`POSTGRES_USER`
`POSTGRES_PASSWORD`
`FE_USER`
`FE_PASSWORD`
: Dans la configuration par défaut, ces valeurs sont partagées par toutes les
  instances. La raison est que le proxy pgbouncer requière que le même mot de
  passe soit utilisé par tous les backend pour un même nom d'utilisateur.

  S'il faut définir un mot de passe différent suivant le profil, il faudra aussi
  définir des noms différents pour les comptes, e.g
  ~~~sh
  prod_POSTGRES_USER=prod_postgres
  prod_POSTGRES_PASSWORD=XXX_a_modifier
  prod_FE_USER=prod_dreadmin
  prod_FE_PASSWORD=XXX_a_modifier
  test_POSTGRES_USER=test_postgres
  test_POSTGRES_PASSWORD=XXX_a_modifier
  test_FE_USER=test_dreadmin
  test_FE_PASSWORD=XXX_a_modifier
  ~~~
  Bien entendu, il faut aussi redéfinir le paramètre `PGBOUNCER_USERS` en
  conséquence
  ~~~sh
  PGBOUNCER_USERS="$prod_FE_USER:$prod_FE_PASSWORD $test_FE_USER:$test_FE_PASSWORD"
  ~~~

**Paramètres privés non documentés**

`PGDATABASE`
`PGUSER`
`APP_PROFILE_VARS`
`POSTGRES_PROFILES`
: Dans le mode avancé, ne pas modifier ni supprimer la valeur de ces paramètres

## Paramètres pour la construction des images

Ces paramètres sont lus à partir du fichier `build.env` qui est créé en copiant
le fichier `.build.env.dist`
~~~sh
cp .build.env.dist build.env
~~~
Il FAUT consulter `build.env` et l'éditer AVANT de chercher à construire les
images.

`APT_PROXY`
: proxy pour l'installation des paquets Debian, e.g `http://monproxy.tld:3142`

`APT_MIRROR`
`SEC_MIRROR`
: miroirs à utiliser. Il n'est généralement pas nécessaire de modifier ces
  valeurs

`TIMEZONE`
: Fuseau horaire, si vous n'êtes pas en France métropolitaine, e.g
  `Indian/Reunion`

`PRIVAREG`
: nom d'un registry docker interne vers lequel les images pourraient être
  poussées. Il n'est pas nécessaire de modifier ce paramètre.

  Si PRIVAREG est utilisé, `build -p` pousse les images vers ce dépôt après les
  avoir construites. Ensuite, si un autre poste est configuré avec la bonne
  valeur de PRIVAREG, les images seront téléchargées et n'ont pas besoin d'être
  reconstruites

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary