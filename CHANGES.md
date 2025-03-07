# Release Notes

Si vous ne savez pas quelle est la version actuellement instalée, vous pouvez
consulter le fichier `VERSION.txt` (à faire *avant* de lancer `git pull`)
~~~sh
cat VERSION.txt
~~~

Il vous suffit ensuite de suivre les instructions pour les versions ultérieures
listées ci-dessous.

----

## Release 1.1.2 du 07/03/2025-12:32

* `0144514` maj doc

## Release 1.1.1 du 07/03/2025-11:49

* `dbcff31` granularité fine pour la désactivation de l'écoute
* `cd807e3` support proxy externe: possibilité de désactiver l'écoute

## Release 1.1.0 du 07/03/2025-10:40

Modifications techniques pour support l'authentification basique, utilisée
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
