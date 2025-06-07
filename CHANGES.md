## Release 1.7.0 du 07/06/2025-22:39

* `d13fd16` support des configuration postgresql personnalisées

## Release 1.6.2 du 04/06/2025-11:02

## Release 1.6.1 du 04/06/2025-10:50

* `967e998` corriger les conditions d'utilisations de APP_PROFILES_AUTO
* `5f69d49` corriger sélection des addons

## Release 1.6.0 du 03/06/2025-11:38

* `6648dc4` maj doc
* `8c69f59` bug avec le calcul des répertoires de doc
* `678f7d7` améliorer la prise en charge de la documentation
* `38eff89` migration vers nulib/base

## Release 1.5.5 du 23/05/2025-10:51

* `e4dc2ac` bug dans la prise en compte de APP_PROFILES_AUTO

## Release 1.5.4 du 22/05/2025-17:58

* `f9b3103` transporter les informations de proxy dans le cron
* `1c62781` ajout du paramètre APP_PROFILES_AUTO

## Release 1.5.3 du 19/05/2025-21:46

* `5e134c1` changer le répertoire courant avant de lancer un script

## Release 1.5.2 du 19/05/2025-05:37

Cette release contient de nombreuses modifications, notamment:
- création d'une base de données persistante
- possibilité de créer des utilisateurs supplémentaires

IMPORTANT: après la mise à jour, il faut suivre les instructions de
[setup-pdata.md](documentation/setup-pdata.md) pour créer la base de données
persistante

Notez que l'utilisateur `\\\$FE_USER` a par défaut les droits d'accès en
modification sur la base de données persistante. si vous souhaitez un accès en
lecture uniquement, il faut créer un utilisateur supplémentaire

* `06b16bb` bouton pour copier le mot de passe ou la chaine de connexion
* `1875152` réorganisation des fichiers
* `e083d57` faciliter le lancement des commandes
* `9e91b44` support création plusieurs utilisateurs
* `255e3cb` possiblité d'ignorer les erreurs. afficher s'il y a des erreurs
* `24feaf2` rajouter une indication sur le profil courant dans la base de données
* `a769880` support des installations sans profil prod
* `a4a6d67` l'option -R recrée les container au lieu de les redémarrer
* `2f59500` accepter l'option -B pour --rebuild
* `a967089` importer les tables de pdata automatiquement
* `2f13888` ajouter l'option --klone à dl-dumps
* `ba49754` support base de données persistante

## Release 1.4.3 du 05/05/2025-11:19

* `76dd9be` tenir compte de FORCE_CREATE_SCHEMAS dans le cron
* `9b8d38f` maj doc

## Release 1.4.2 du 28/04/2025-15:34

Cette release contient des modifications supplémentaires pour éviter la
surutilisation de l'espace disque en cas de problème d'import ou de
téléchargement

* `8f11acc` supprimer les fichiers de dumps obsolètes juste après un téléchargement réussi

## Release 1.4.1 du 28/04/2025-14:41

* `33046c0` maj doc

## Release 1.4.0 du 28/04/2025-13:57

Cette release contient un palliatif pour les dumps qui sont livrés sans la
commande de création de schéma.

~~Au 28/04/2025, tant qu'un correctif n'a pas été livré par PC-SCOL, il faut rajouter la ligne suivante dans `dremgr.env` AVANT de faire la mise à jour~~
Edit: au 06/05/2025, cette modification n'est plus nécessaire
~~~sh
FORCE_CREATE_SCHEMAS=keycloak
~~~

De plus, une modification a été intégrée pour s'assurer que les bases de
données temporaires sont nettoyées en cas d'échec d'import

* `9eb0ee2` support des dumps sans schéma

## Release 1.3.7 du 16/04/2025-10:33

* `fd1f38d` maj doc
* `66fa118` maj doc

## Release 1.3.6 du 15/04/2025-14:37

* `6635b7d` maj doc

## Release 1.3.5 du 11/04/2025-14:48

## Release 1.3.4 du 11/04/2025-14:35

Maj de la doc pour indiquer qu'il ne faut pas désactiver l'écoute en http

Si l'accès en https est activé, une redirection automatique est mise en place.

* `425a9bc` regénérer systématiquement le fichier setup.conf

## Release 1.3.3 du 11/04/2025-11:09

* `dd726af` documenter la configuration de https

## Release 1.3.2 du 25/03/2025-09:02

* `f16d5f4` documenter la configuration du proxy
* `7dad869` corriger la prise en compte du proxy

## Release 1.3.1 du 19/03/2025-19:25

* `781fe0f` maj doc
* `2ecfdcd` maj todo
* `b3cb2f5` support de la fusion avec des fichiers compose locaux

## Release 1.3.0 du 17/03/2025-13:05

Correction de la prise en compte du mode d'authentification: la mise à jour
vers cette version est obligatoire pour rétablir l'authentification CAS

> [!IMPORTANT]
> La mise à jour nécessite la suppression d'un fichier et le redémarrage forcé
> du frontal web
~~~sh
# mettre à jour le dépôt
git pull

# forcer la regénération de la configuration apache
rm -f config/apache/setup.conf

# reconstruire les images si nécessaire
./build -r

# forcer le redémarrage du frontal web
./webfront -R

# redémarrer les autres services le cas échéant
./dremgr -r
~~~

* `1bff360` corriger la prise en compte des variables

## Release 1.2.0 du 14/03/2025-16:00

* `94a0b41` maj src/php

## Release 1.1.3 du 14/03/2025-14:35

* `c5955e6` augmenter la liste des erreurs non fatales
* `c536881` ajout de la licence

## Release 1.1.2 du 07/03/2025-12:32

* `0144514` maj doc

## Release 1.1.1 du 07/03/2025-11:49

* `dbcff31` granularité fine pour la désactivation de l'écoute
* `cd807e3` support proxy externe: possibilité de désactiver l'écoute

## Release 1.1.0 du 07/03/2025-10:40

Modifications techniques pour support de l'authentification basique, utilisée
principalement par l'instance de démo

> [!IMPORTANT]
> La mise à jour nécessite le renommage manuel d'un fichier de configuration
~~~sh
# mettre à jour le dépôt
git pull

# renommer le fichier de configuration
mv config/apache/authnz.conf config/apache/auth_cas.conf

# reconstruire les images si nécessaire puis redémarrer les services le cas échéant
./dremgr -rb
~~~

* `ab8a9be` support authentification basique
* `6a83389` maj doc

## Release 1.0.2 du 01/03/2025-16:31

Cette version est la première livraison publique de dremgr. Elle utilise un
canal de mise à jour pérenne.
