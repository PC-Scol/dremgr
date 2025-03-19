Si vous n'avez pas encore construit les images, vous devez le faire au préalable.
[Construire les images](02construire-images.md)

Le mode simple n'installe qu'une seule instance de la base de données ainsi que
du mécanisme pour la mettre à jour quotidiennement. Ce mode n'offre aucune
interface utilisateur.

Si vous souhaitez offrir une interface aux utilisateurs, ou gérer facilement
plusieurs instances sur la même machine, il faut sélectionner le mode avancé.
[Installer dremgr dans le mode avancé](03installation-avancee.md)

# Installer dans le mode simple

Une fois les images construites, il faut préparer le démarrage de l'instance DRE
~~~sh
./dbinst
~~~
La *première* invocation crée le fichier d'exemple `prod_profile.env`

Il FAUT consulter ce fichier et l'éditer AVANT de continuer. *Au minimum*,
modifiez les variables dont la valeur est `XXX_a_modifier`. Les variables
suivantes peuvent être configurées le cas échéant:

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
: Liste d'URLs de dépôts git contenant des "addons" de dremgr. Par défaut, les
  trois URLs de github suivants sont listés:
  * `PC-Scol/dreaddon-documentation.git`
    documentation technique et fonctionnelle de DRE
  * `PC-Scol/dreaddon-pilotage.git`
    schéma "pilotage" développé par l'UPHF, base de l'univers BO livré aussi par
    l'UPHF

  D'autres add-ons peuvent être spécifiés au fur et à mesure qu'ils sont rendus
  disponibles.

  Cf [la documentation de dreaddons](dreaddons.md) pour les détails

Il y a d'autres paramètres configurables.
[Consulter la liste complète des paramètres](parametres.md)

Une fois le fichier configuré, l'instance peut être démarrée
~~~sh
./dbinst
~~~

La base de données est accessible sur l'adresse IP spécifiée dans le
fichier. par défaut, il s'agit de l'adresse locale:
~~~sh
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=dre"
~~~
NB: cette commande sert à vérifier que la base est bien accessible sur l'adresse
configurée. Elle nécessite bien entendu que vous ayez le client `psql` installé.
Si ce n'est pas le cas, vous pouvez l'installer avec la commande suivante:
~~~sh
sudo apt install postgresql-client
~~~
Vous pouvez aussi utiliser n'importe quel autre client graphique ou en ligne de
commande.

Pour le moment, la base ne contient aucune donnée. On peut forcer le
téléchargement et l'importation:
~~~sh
./dbinst -i
~~~
Sinon, le téléchargement et l'importation se fait tous les jours à l'heure
définie dans la variable `CRON_PLAN` c'est à dire par défaut 5h30

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary