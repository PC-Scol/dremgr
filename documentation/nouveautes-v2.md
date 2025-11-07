# Nouveautés de dremgr 2.0

Ce document liste toutes les nouveautés de la version 2.0 de dremgr

La mise à jour nécessite une trentaire de minutes, le temps de reconstruire les
images. Avec le mode avancé, la mise à jour se fait de cette façon:
~~~sh
# arrêter les services
./dremgr -k

# mettre à jour le dépôt
git pull

# forcer la reconstruction de toutes les images
./build -rU

# démarrer les services
./dremgr
~~~

Avec le mode simple, la mise à jour demande un peu plus de travail
~~~sh
# arrêter les services
./dbinst -k

# mettre à jour le dépôt
git pull

# forcer la reconstruction de toutes les images
./build -rU

# IMPORTANT: reporter les paramètres de prod_profile.env dans dremgr.env
...

# démarrer les services
./dremgr
~~~
Vous pouvez aussi reprendre l'installation à zéro si vous n'avez pas envie de
vous prendre la tête.

## Migration des paramètres

Il n'y a plus qu'un seul fichier de configuration `dremgr.env` et il est utilisé
en mode simple et en mode avancé.

Si vous avez fait l'installation en mode avancé, la migration sera transparente.
Les fichiers `<profile>_profile.env` n'étant plus utilisés, vous pouvez les
supprimer *si ce sont des liens vers dremgr.env*

Si vous avez fait l'installation en mode simple, il vous faudra reporter
manuellement les paramètres du fichier `prod_profile.env` dans `dremgr.env` puis
le supprimer. Vous pouvez aussi reprendre l'installation à zéro si vous n'avez
pas envie de vous prendre la tête :-)

Les imports sont maintenant effectués par défaut à 4h, puisque les dumps DRE
sont maintenant systématiquement générés à 2h heure locale.

Support du paramètre DATADIR qui permet de placer les données ailleurs que dans
le répertoire du projet. Fini les liens symboliques disgracieux :-)

## Mise à jour des addons

Dans les répertoires des addons, tous les scripts exécutables sont maintenant
considérés, pas seulement les fichiers .sh et .sql comme avant

Cela permet d'écrire les scripts avec d'autres langages, Python ou PHP par
exemple. Les langages suivants sont installés par défaut dans l'image:
* Bash 5.2 avec postgresql-client 15
* Python 3.11
* PHP 8.2

### Support des notifications

Les addons peuvent fournir un répertoire `notifications` qui contient des
scripts qui sont exécutés à la fin de l'import, qu'il aie échoué ou non. Cela
peut être utilisé par exemple pour envoyer un mail ou notifier des applications
dépendantes de la mise à jour de DRE.

Consulter [développement des addons](dreaddon-developpement.md) pour les détails

### Inclusion de dreaddon-local

Le projet contient un répertoire `dreaddon-local` qui est automatiquement inclus
dans la liste des addons et activé. Cela permet de développer un addon local
sans avoir besoin de créer un dépôt à part.

Ce répertoire contient en exemple:
- un script PHP qui envoie un mail à la fin de l'import quotidien (il faut
  l'activer et le configurer au préalable)
- un script bash pour notifier un webservice externe

Consulter [envoi de mails quotidiens](setup-sendmails.md) pour les détails

## Migrations techniques

L'image de base a été migrée en debian 12. Si vous installez d'autres paquets
dans les images fournies, il faudra en tenir compte.

De plus, pgAdmin a été mis à jour vers la version 9

Pour des raisons de cohérence, le script dl-dumps a été renommé en import-dumps.
Cela ne devrait pas avoir de conséquences pour vous.

## Divers

Dans le frontal web, sur la page "Dumps", afficher la taille totale nécessaire
au stockage des fichiers de dump en tenant compte des fichiers du jour et du
paramètres `CRON_MAX_AGE`.
Cela permet d'estimer l'espace disque à réserver.

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary