# Nouveautés de DREmgr 3.0

Ce document liste toutes les nouveautés de la version 3.0 de DREmgr

La mise à jour nécessite d'une trentaire de minutes à une heure, le temps de
reconstruire les images. La mise à jour se fait de cette façon:
~~~sh
# Faire une sauvegarde de la base de données pdata
./dbinst -A --backup

# arrêter les services
./dremgr -k

# mettre à jour le dépôt
git pull

# forcer la reconstruction de toutes les images
./build -rU

# préparer la migration
./sbin/migrate-pg18

# démarrer les services
./dremgr

# restaurer la base de données pdata
./sbin/migrate-pg18 -r

# réimporter les données
./dbinst -Ai -- -@latest
~~~

le répertoire contenant les logos personnalisés a changé. vous pouvez déplacer
le logo actuel vers le nouvel emplacement
~~~sh
mv public/brand.png _webfront.d/res/logo.png
~~~

Une fois que toutes les importations ont été effectuées, vérifiez que tout
fonctionne correctement et que toutes les données sont là

Une fois cela confirmé, vous pouvez supprimer les fichiers de la base de données
postgresql 15
~~~sh
# supprimer les fichiers de sauvegarde
./sbin/migrate-pg18 -k

# le cas échéant, vous pouvez supprimer les sauvegardes de la base pdata
# utilisées pour la migration
rm *_pdata-*.sql.gz
~~~

## Migrations techniques

PostgreSQL passe en version 18. Cette version est compatible avec les dumps au
format Postgresql 15 qui sont livrés avant PEGASE V35

L'image de base a été migrée en debian 13. Si vous installez d'autres paquets
dans les images fournies, il faudra en tenir compte.

Les langages disponibles pour les addons sont les suivants:
* Bash 5.2 avec postgresql-client 18
* Python 3.13
* PHP 8.4

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary