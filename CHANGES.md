## Version 0.23.3 du 06/02/2025-08:44

* `91b60b8` corriger la prise en compte du profil courant

## Version 0.23.2 du 06/02/2025-07:48

* `a36a81c` corriger la doc

## Version 0.23.1 du 05/02/2025-17:20

* `8e2e888` maj doc
* `55eb2a0` support de la nouvelle convention de nommage
* `6c26479` maj outils devel

## Version 0.23.0 du 11/01/2025-14:14

* `e841637` fixer la version des librairies
* `a9bb7da` cosmetic

## Version 0.22.0 du 29/11/2024-12:29

* `3eba6fc` Maj RELEASE NOTES
* `5c15ff2` remplacer le lien du logo par un fichier
* `92602bc` migration vers nulib/php et nur/ture
* `6e5c3ac` maj README à propos de la version

## Version 0.21.3 du 13/11/2024-07:23

* `527472f` maj documentation

## Version 0.21.2 du 06/11/2024-17:32

* `d30701d` suivre le fichier témoin .composer.lock.runphp
* `25175ac` n'afficher un message que si le fichier de profil n'existe pas

## Version 0.21.1 du 18/10/2024-21:39

* `342a7cb` supprimer dreaddon-ins_piste qui est obsolète de la documentation

## Version 0.21.0 du 18/10/2024-21:10

* `a14d4c1` ajouter la table mongo_piste_inscription.version_instance
* `58884ef` bug mineur
* `5b6b4be` toujours utiliser la dernière version mineure de pgadmin

## Version 0.20.0 du 20/09/2024-08:16

* `36e671b` maj release notes
* `2a6fb62` maj doc pour MINIMIZE_DOWNTIME
* `5870373` bug avec le chargement des paramètres
* `6d8a882` maj doc
* `8c3e467` maj runphp
* `6e49247` migration vers runphp

## Version 0.19.3 du 16/09/2024-14:37

* `df0b85e` bug suite à une maj de l'image adminer
* `928dbea` maj de la documentation

## Version 0.19.2 du 14/09/2024-07:28

* `0538635` bug: ne faire le rebuild qu'une seule fois

## Version 0.19.1 du 13/09/2024-13:43

* `b103c57` maj mineure de la doc

## Version 0.19.0 du 13/09/2024-13:29

* `3af572b` maj documentation
* `b0ebb83` distinguer dbfront et webfront
* `5c334a5` exposer le mot de passe administrateur de pgbouncer
* `78cfd2e` bug: les variables de .dremgr.env ne doivent pas pouvoir être surchargées
* `cb78606` bug
* `a4eeb6b` cosmetic
* `86674cf` préparation release-notes pour la v0.19.0
* `5e3d853` placer les options statiques dans un fichier non modifiable par l'utilisateur
* `efb62a6` option pour lancer un shell dans cron ou db
* `baa91e0` minimiser le temps d'indisponibilité de la base
* `eb17f2e` supprimer la clé obsolète version: dans docker-compose.yml
* `5a56ec6` ajout tbin/ pour des outils spécifiques au développeur
* `eb6ec18` ajouter dremgr qui lance front et inst en une seule commande

## Version 0.18.1 du 19/08/2024-19:40

* `a80e751` rendre paramétrable la durée de rétention des fichiers téléchargés

## Version 0.18.0 du 16/07/2024-17:15

* `3ed673a` renommage des options -i, -I, -J

## Version 0.17.2 du 16/07/2024-15:41

* `f6d64a4` bug

## Version 0.17.1 du 15/07/2024-20:06

* `ca3563a` créer un fichier RELEASE-NOTES.md pour les notes de mise à jour

## Version 0.17.0 du 15/07/2024-17:14

* `4354766` renommer front.env en dremgr.env

## Version 0.16.3 du 07/07/2024-10:17

* `c6d6677` réessayer si le fichier n'est pas trouvé
* `342e47f` bug

## Version 0.16.2 du 07/07/2024-07:34

* `bdb4a6f` afficher l'heure d'import configuré
* `a743930` cosmetic

## Version 0.16.1 du 29/06/2024-08:07

* `e9b44b5` maj documentation

## Version 0.16.0 du 29/06/2024-07:23

* `d099bc6` Simplifier la page d'accueil de l'application web
* `f8164fe` afficher les logs après l'import
* `959ba64` Mettre à jour et réorganiser la documentation
* `eca3f08` cosmetic
* `718618a` meilleur support du développement d'addons
* `9f1fbf9` maj version pgadmin
* `62d776f` corriger le grant pour les sequences: reader doit rester en lecture seule
* `13e0676` rajouter aussi l'accès aux séquences par défaut
* `8896085` identifier clairement les schéma importés depuis mongoDB
* `cfc8b1f` bug addons
* `3c1d27e` support importation collections json

## Version 0.15.1 du 23/05/2024-07:10

* `246c51b` maj doc

## Version 0.15.0 du 23/05/2024-06:56

* `2d2bda7` possibilité de désactiver la planification

## Version 0.14.1 du 07/05/2024-13:37

* `17612c3` ajouter la documentation de dreaddons

## Version 0.14.0 du 02/05/2024-23:02

* `f1da911` possiblité de désigner une branche ou un commit
* `e7c4a82` maj doc sur le développement d'addon

## Version 0.13.0 du 01/05/2024-10:09

* `1aa3b86` cosmetic
* `12ad603` support des fichiers .url dans la documentation
* `f80483c` ADDON_URLS: ignorer les commentaires
* `cb783f9` cosmetic
* `a1d3712` déplacer bin/ qui n'est pas fait pour l'utilisateur

## Version 0.12.2 du 29/04/2024-19:30

* `d635655` améliorer la synchro de la documentation

## Version 0.12.1 du 26/04/2024-12:24

* `1d18c73` bug

## Version 0.12.0 du 24/04/2024-15:56

* `c9e533a` dl-dumps: possibilité de réinstaller un addon
* `7b06641` ajouter inst --psql

## Version 0.11.4 du 18/04/2024-09:23

* `9a4b66b` faire une copie de cron-config pour éviter les interactions hasardeuses

## Version 0.11.3 du 17/04/2024-10:16

* `116c761` exit en cas d'erreur de build

## Version 0.11.2 du 17/04/2024-10:03

* `2bed522` bug connexion depuis cron
* `8ae838e` maj upstream

## Version 0.11.1 du 05/04/2024-22:16

* `c42fae7` fix pour PGDATABASE

## Version 0.11.0 du 05/04/2024-22:05

* `94d37c0` changer utilisateur admin par défaut
* `9c96510` supprimer fichier parasite

## Version 0.10.3 du 05/04/2024-20:01

* `46eb809` maj doc: authz CAS et modification du logo

## Version 0.10.2 du 05/04/2024-19:43

* `9c5e91f` maj nulib et nur-sery

## Version 0.10.1 du 05/04/2024-19:34

* `e69f0f6` mentionner la possibilité de forcer le redémarrage

## Version 0.10.0 du 05/04/2024-18:55

* `1633ff5` Monter les répertoires plutôt que copier les fichiers
* `0eb1257` Améliorer le support du développement d'addons

## Version 0.9.0 du 27/03/2024-22:42

* `3d6308b` Intégration de la branche wip/proxy
  * `cfc2987` ajouter le support du proxy
* `0e03d75` maj documentation
* `8e2bd8c` maj support rundk dans nulib
* `32adfa9` documenter l'option -i

## Version 0.8.2 du 21/03/2024-17:16

* `c734cb1` cosmetic
* `27c3b0a` front: afficher les logs d'importation
* `cf2e26d` cron: recharger systématiquement l'environnement système

## Version 0.8.1 du 21/03/2024-12:33

* `a896b34` front: afficher la source des dumps

## Version 0.8.0 du 21/03/2024-11:53

* `a176164` front: afficher la version importée courante
* `e82a452` inst: option -i pour déclencher l'import
* `372321b` cosmetic
* `1bd8836` build: honorer --no-cache pour rundk
* `73d7a29` build: support de l'option --pull

## Version 0.7.2 du 14/03/2024-09:56

* `1ed6fcf` maj nulib
* `9e9c10d` typo

## Version 0.7.1 du 13/03/2024-22:47

* `93b9100` simplifier l'exemple front.env

## Version 0.7.0 du 13/03/2024-22:35

* `ed62aec` utiliser nulib:template
* `5cd5791` cosmetic: chaque fichier dist ignoré depuis son propre répertoire
* `50460dd` cosmetic

## Version 0.6.1 du 12/03/2024-10:43

* `8550569` build fait frontal pour rundk -0

## Version 0.6.0 du 12/03/2024-09:48

* `4fbf627` l'image rundk est nommée d'après PRIVAREG si défini
* `fe2d5eb` maj doc pour build rundk
* `e6f5465` ajout doc pour installer sous WSL
* `9ab4119` support construction locale de rundk

## Version 0.5.2 du 11/03/2024-18:36

* `d6609e5` support WSL

## Version 0.5.1 du 10/03/2024-21:30

* `b68bcc6` maj doc

## Version 0.5.0 du 10/03/2024-21:15

* `12b1f3d` maj doc
* `3424234` maj configuration d'exemple

## Version 0.4.1 du 10/03/2024-18:50

* `07a462c` bug

## Version 0.4.0 du 10/03/2024-18:45

* `4d9b4d4` build: option pour pousser l'image après construction
* `8d88bd1` renommer le projet en dremgr

## Version 0.3.0 du 08/03/2024-13:44

* `1dddb82` ajouter adminer

## Version 0.2.1 du 08/03/2024-11:26

* `682d120` bug
* `d453ac8` afficher la documentation
* `b445bb3` front/web: affichage des informations de connexion

## Version 0.2.0 du 07/03/2024-15:25

* `bdfd0ad` front: ajout de pgAdmin, et préparation de web
* `ff12f86` maj doc

## Version 0.1.1 du 05/03/2024-16:51

* `8fabfd2` distinguer config FE et INST

## Version 0.1.0 du 05/03/2024-16:08

* release initiale
