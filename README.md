# dremgr

dremgr est un environnement pour la gestion d'une ou plusieurs instances de base
DRE

## Pré-requis

dremgr est développé et testé sur Debian 11. Il devrait fonctionner sur
n'importe quel système Linux, pourvu que les pré-requis soient respectés.

* Installation des [pré-requis pour Debian](documentation/prerequis-linux.md)
  et autres distributions Linux.
* Installation des [pré-requis pour WSL](documentation/prerequis-wsl.md)

## Démarrage rapide

Ouvrez un terminal et clonez le dépôt
~~~sh
git clone https://github.com/PC-Scol/dremgr.git
~~~
~~~sh
cd dremgr
~~~

Il faut d'abord construire les images utilisées par l'application. Commencer en
faisant une copie de `build.env` depuis `.build.env.dist`
~~~sh
cp .build.env.dist build.env
~~~
Il FAUT consulter `build.env` et l'éditer AVANT de continuer. Notamment, les
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
  * adminer, alternative pour accéder à la base de façon graphique
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
./inst -i
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
Attention! Si le mode simple avait été utilisé auparavant, le fichier
`prod_profile.env` précédent est perdu au profit du nouveau lien symbolique.

Créer le réseau mentionné dans la configuration (variable `DBNET`)
~~~sh
docker network create --attachable dremgr_db
~~~

Puis démarrer toutes les instances correspondant à chaque profil défini
~~~sh
./inst -A
~~~
Attention! si l'instance de prod en mode simple avait déjà été démarrée, il
faut la remplacer par les nouvelles instances, i.e
~~~sh
# forcer le redémarrage
./inst -Ar
~~~
Notez aussi que les comptes ne sont pas recréés si l'instance de prod en mode
simple avait déjà été démarrée.

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

  Il est aussi possible d'autoriser sur la base d'attributs fournis par le
  serveur CAS, cf <https://github.com/apereo/mod_auth_cas>. Par exemple, pour
  autoriser tous les comptes dont l'attribut `authzApp` vaut `dremgr`, il
  faudrait une configuration de ce type:
  ~~~conf
  Require cas-attribute authzApp:dremgr
  ~~~
  Typiquement, on autorisera sur l'appartenance à un groupe via l'attribut
  `memberOf`. Bien entendu, il faut configurer le serveur CAS pour servir les
  attributs nécessaires.

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
# redémarrge si nécessaire
./front -r
# ou forcer le redémarrage
./front -R
~~~

Visiter <http://localhost:7081> pour connaitre les paramètres de connexion à
chaque instance de base DRE. par défaut, il s'agit de l'adresse locale:
~~~sh
# prod
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=dre"
~~~
~~~sh
# test
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=test_dre"
~~~

Bien entendu, pour le moment, les bases ne contiennent aucune donnée. On peut
forcer le téléchargement et l'importation:
~~~sh
./inst -Ai
~~~
Sinon, le téléchargement et l'importation se fait tous les jours à l'heure
définie dans la variable `CRON_PLAN` c'est à dire par défaut 5h45

# Exploitation

## Mise à jour

En cas de mise à jour, cette suite d'opération permet de s'assurer que tout est
correctement installé:
~~~sh
cd dremgr
# mettre à jour le dépôt
git pull
# reconstruire les images
./build -r
# redémarrer les bases de données
./inst -Ar
# redémarrer le frontal
./front -r
~~~

## Branche de développement

La release courante est sur la branche `master`. Pour tester des fonctionnalités
qui ne sont pas encore stabilisées, il faut basculer sur la branche `develop` ou
une autre branche qui vous est indiquée.

Par exemple, pour basculer sur la branche `develop`, il faut le considérer comme
une sorte de mise à jour
~~~sh
cd dremgr
# mettre à jour le dépôt
git pull
# s'assurer qu'on est bien sur la bonne branche
git checkout develop
# reconstruire les images
./build -r
# redémarrer les bases de données
./inst -Ar
# redémarrer le frontal
./front -r
~~~

La commande `git checkout develop` permet de basculer sur la branche de
développement. Dans le même ordre d'idée, `git checkout master` permet de
revenir sur la branche stable, mais attention aux effets de bord si la branche
`develop` contient des modifications profondes.

En effet, la branche `develop` contient toujours une version égale ou plus
récente que celle de la branche `master`. Les mises à jour des versions
anciennes vers une version plus récente sont supportées, mais les mises à jour
"dans l'autre sens" d'une version récente vers une version plus ancienne ne sont
à priori pas supportées.

En définitive, basculer sur la branche `develop` ne devrait probablement pas
être effectué en production.

## Développement d'addons

Lorsqu'on développe un addon (cf <https://github.com/PC-Scol/dreaddon-template>),
il faut pouvoir tester les scripts avant de les envoyer en production. Une façon
de faire est d'avoir une installation de dremgr sur son poste (via WSL ou autre
méthode) et lancer l'importation à chaque fois.

* Installer dremgr sur son poste avec la même configuration qu'en prod
* Il n'est pas forcément possible de télécharger les fichiers depuis le poste du
  développeur à cause de la restriction sur l'adresse IP. il faut donc récupérer
  les fichiers depuis le serveur de prod
  ~~~sh
  # ici, on récupére les fichiers depuis le serveur monserveur.univ.tld dans le
  # profil prod, en partant du principe que dremgr est installé dans le
  # répertoire d'origine de root, et on les copie dans le répertoire courant
  # dans le profil prod
  src_dremgr=root@monserveur.univ.tld:dremgr
  src_profile=prod
  dest_profile=prod

  rsync -avP "${src_dremgr}/var/${src_profile}-dredata/downloads/" "var/${dest_profile}-dredata/downloads/"
  ~~~
* Ensuite, on peut lancer l'imporation des fichiers du jour
  ~~~sh
  ./inst -i
  ~~~

  NB: il est possible de spécifier la date des fichiers à importer avec l'option
  `-@ YYYYMMDD`. Par exemple, pour importer les fichiers du 04/05/2024, on peut
  faire ceci (le `--` entre `-i` et `-@` est requis):
  ~~~sh
  ./inst -i -- -@ 20240504
  ~~~

La méthode ci-dessus réimporte TOUS les dumps et TOUS les addons, ce qui permet
de vérifier que l'import quotidien fonctionnera correctement une fois en
production, mais ça peut prendre un certain temps en fonction du nombre
d'addons.

Si on veut uniquement réimporter l'addon sur lequel on travaille, il est
possible de le faire avec les options `--runao` et `-o`, e.g:
~~~sh
./inst -i -- --runao -o documentation
~~~
Dans cet exemple, seul l'addon `dreaddon-documentation` est réimporté, ce qui
permet de vérifier que les fichiers de documentation proposés sont bien ceux
attendus.

## Modification du logo

Pour remplacer le logo par celui de votre université, il faut *supprimer* le
fichier `public/brand.png` puis copier une nouvelle image au format PNG avec le
nom `brand.png` (la raison pour laquelle il faut supprimer d'abord est que par
défaut il s'agit d'un lien symbolique. donc, pour être certain de copier un
nouveau fichier et non de remplacer la cible du lien symbolique, on supprime
d'abord)

L'image DOIT avoir une hauteur de 50 pixel. la largeur importe peu.

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary