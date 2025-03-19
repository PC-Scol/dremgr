# Release Notes

Si vous ne savez pas quelle est la version actuellement instalée, vous pouvez
consulter le fichier `VERSION.txt` (à faire *avant* de lancer `git pull`)
~~~sh
cat VERSION.txt
~~~

Il vous suffit ensuite de suivre les instructions pour les versions ultérieures
listées ci-dessous.

----

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

# reconstruire les images
./build -r

# redémarrer les services concernés
./dremgr -r
~~~

* `ab8a9be` support authentification basique
* `6a83389` maj doc

## Release 1.0.2 du 01/03/2025-16:31

Cette version est la première livraison publique de dremgr. Elle utilise un
canal de mise à jour pérenne.

La mise à jour se fait "normalement", en suivant les
[instructions de la page d'accueil](README.md)
