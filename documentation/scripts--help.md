# Afficher l'aide

Chaque outil `build`, `dremgr`, `dbinst`, `dbfront` et `webfront` possède une
aide intégrée affichée avec l'option `--help`

## build

build sert à construire les images nécessaires à dremgr

Aide standard
~~~sh
./build --help
~~~
~~~
build: Construire les images pour DRE

USAGE
    build [options]

OPTIONS
    --help++
        Afficher l'aide avancée
    -r, --rebuild
        Forcer la (re)construction de l'image
    -p, --push
        Pousser les images vers le registry après construction
~~~

Aide avancée
~~~sh
./build --help++
~~~
~~~
build: Construire les images pour DRE

USAGE
    build [options]

OPTIONS
    --help++
    --check-only
        Ne faire que la vérification de l'environnement
    -r, --rebuild
    -U, --pull
        Forcer le re-téléchargement des images dépendantes
    -j, --no-cache
        Construire l'image en invalidant le cache
    -D, --plain-output
        Afficher le détail du build
    -p, --push
~~~

## dremgr

dremgr sert à piloter en une seule commandes les frontaux (via `webfront` et
`dbfront`) et les instances DRE (via `dbinst`)

Aide standard
~~~sh
./dremgr --help
~~~
~~~
dremgr: Gérer les frontaux et les instances de DRE configurées

USAGE
    dremgr [options]

OPTIONS
    --help++
        Afficher l'aide avancée
    -s, --start
        Démarrer les services
    -k, --stop
        Arrêter les services
    -r, --refresh
        (Re)démarrer les services si nécessaire
    -R, --restart
        Forcer le (re)démarrage des services
~~~

Aide avancée
~~~sh
./dremgr --help++
~~~
~~~
dremgr: Gérer les frontaux et les instances de DRE configurées

USAGE
    dremgr [options]

OPTIONS
    --help++
    -s, --start
    -k, --stop
    -r, --refresh
    -R, --restart
    -b, --rebuild
        Forcer le rebuild de l'image avant le démarrage
~~~

## dbinst

dbinst sert à gérer les instances des bases de données

Aide standard
~~~sh
./dbinst --help
~~~
~~~
dbinst: Gérer cette instance de DRE

USAGE
    dbinst [options]

OPTIONS
    --help++
        Afficher l'aide avancée
    -s, --start
        Démarrer DRE
    -k, --stop
        Arrêter DRE
    -r, --refresh
        (Re)démarrer DRE si nécessaire
    -R, --restart
        Forcer le (re)démarrage de DRE
    -i, --import-all
        Lancer l'import complet maintenant, sans attendre la planification
    -q, --psql
        Lancer une invite psql connecté avec le compte administrateur
~~~

Aide avancée
~~~sh
./dbinst --help++
~~~
~~~
dbinst: Gérer cette instance de DRE

USAGE
    dbinst [options]

OPTIONS
    --help++
    --check-only
        Ne faire que la vérification de l'environnement
    -g, --profile PROFILE
        Spécifier le profil. Un fichier de configuration PROFILE_profile.env doit exister.
        Si cette option n'est pas spécifiée, le profil sélectionné par défaut est prod
    -A, --all-profiles
        Faire l'opération pour tous les profils définis dans dremgr.env
    -P, --prod
        alias pour --profile prod
    -T, --test
        alias pour --profile test
    -s, --start
    -k, --stop
    -r, --refresh
    -R, --restart
    -b, --rebuild
        Forcer le rebuild de l'image avant le démarrage
    -i, --import-all
        Lancer l'import complet maintenant, sans attendre la planification
    -I, --import-one
        Lancer l'import d'un unique addon sans attendre la planification
        - soit la commande suivante:
            dbinst -I DREADDON
        - l'addon est importé comme avec les options suivantes:
            dbinst -i -- --runao -o DREADDON "$@"
        cf la documentation pour les détails
    -J, --import-one-devel
        Importer un addon en mode développement:
        - Soit la commande suivante:
            dbinst -J path/to/DREADDON
        - le contenu du répertoire de l'addon est synchronisé vers le répertoire correspondant du conteneur
        - puis l'addon est importé comme avec les options suivantes:
            dbinst -i -- --no-updateao --runao -o DREADDON "$@"
          cf la documentation pour les détails
    -q, --psql
    --shell-db
        Lancer une invite bash dans le container db
    -x, --shell-cron
        Lancer une invite bash dans le container cron
~~~

## dbfront

dbfront sert à gérer les services frontaux postgresql

Aide standard
~~~sh
./dbfront --help
~~~
~~~
dbfront: Gérer le frontal postgresql de DRE

USAGE
    dbfront [options]

OPTIONS
    --help++
        Afficher l'aide avancée
    -s, --start
        Démarrer le frontal postgresql
    -k, --stop
        Arrêter le frontal postgresql
    -r, --refresh
        (Re)démarrer le frontal postgresql si nécessaire
    -R, --restart
        Forcer le (re)démarrage du frontal postgresql
~~~

Aide avancée
~~~sh
./dbfront --help++
~~~
~~~
dbfront: Gérer le frontal postgresql de DRE

USAGE
    dbfront [options]

OPTIONS
    --help++
    --check-only
        Ne faire que la vérification de l'environnement
    -s, --start
    -k, --stop
    -r, --refresh
    -R, --restart
    -b, --rebuild
        Forcer le rebuild de l'image avant le démarrage
    -q, --pgbouncer
        Lancer une invite psql pour piloter pgbouncer
~~~

## webfront

webfront sert à gérer les services frontaux web

Aide standard
~~~sh
./webfront --help
~~~
~~~
webfront: Gérer le frontal web de DRE

USAGE
    webfront [options]

OPTIONS
    --help++
        Afficher l'aide avancée
    -s, --start
        Démarrer le frontal web
    -k, --stop
        Arrêter le frontal web
    -r, --refresh
        (Re)démarrer le frontal web si nécessaire
    -R, --restart
        Forcer le (re)démarrage du frontal web
~~~

Aide avancée
~~~sh
./webfront --help++
~~~
~~~
webfront: Gérer le frontal web de DRE

USAGE
    webfront [options]

OPTIONS
    --help++
    --check-only
        Ne faire que la vérification de l'environnement
    -s, --start
    -k, --stop
    -r, --refresh
    -R, --restart
    -b, --rebuild
        Forcer le rebuild de l'image avant le démarrage
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary