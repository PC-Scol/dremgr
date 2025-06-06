Si vous n'avez pas encore construit les images, vous devez le faire au préalable.
[Construire les images](02construire-images.md)

Le mode avancé permet d'installer autant d'instances que nécessaire sur une même
machine. Elle offre aussi une interface utilisateur, mais elle demande (un peu)
plus de travail.

Si vous avez déjà installé dremgr dans le mode simple, suivez attentivement les
instructions pour ne pas perdre la configuration courante

# Installer dans le mode avancé

Le mode avancé permet de gérer plusieurs instances. Chaque instance correspond à
un profil: prod, test, etc... Le fichier `dremgr.env` définit l'ensemble des
profils qui sont pilotés par l'installation.

Commencer par copier le fichier d'exemple
~~~sh
cp documentation/dremgr.env.sample dremgr.env
~~~

*Si vous avez déjà installé dremgr dans le mode simple*, reportez les paramètres
déjà saisis dans `prod_profile.env`, notamment `POSTGRES_PASSWORD`,
`FE_PASSWORD`, `prod_DRE_URL`, `prod_DRE_PASSWORD` et `DREADDON_URLS`

La variable `APP_PROFILES` liste les profils supportés. Pour chacun de ces
profils, un ensemble de variable doit être défini plus bas dans le fichier. On
peut rajouter autant de profils que nécessaire, mais il faut définir les
variables avec le préfixe correspondant en prenant exemple sur la section "test"

*Au minimum*, modifiez les variables dont la valeur est `XXX_a_modifier`. Les
variables suivantes peuvent être configurées le cas échéant:

`<profil>_DRE_URL`
`<profil>_DRE_USER`
`<profil>_DRE_PASSWORD`
: URL, utilisateur et mot de passe permettant de télécharger les dumps DRE

`DBVIP`
: Adresse sur laquelle les instances de bases DRE sont disponibles.
  NB: avec le paramètre par défaut, la base de données n'est accessible que
  depuis l'hôte local (via Adminer ou en ligne de commande).

  Ce paramétrage est surtout approprié pour un poste de développement. *Laisser
  vide* pour écouter sur toutes les interfaces.

`POSTGRES_PASSWORD`
: mot de passe de l'utilisateur administrateur de la base de données. Dans la
  configuration par défaut, ce mot de passe est partagé par toutes les
  instances.

`FE_PASSWORD`
: mot de passe de l'utilisateur `dreadmin`. Cet utilisateur a un accès en
  lecture uniquement à la base de données DRE, et un accès en modification à la
  base de données persistante. Dans la configuration par défaut, ce mot de passe
  est partagé par toutes les instances.

`LBHTTP`
: port d'écoute du frontal web. la valeur par défaut est surtout appropriée pour
  un poste de développpement.

  pour une installation à destination des utilisateurs, la valeur standard `80`
  est plus appropriée

`ADDON_URLS`
: Liste d'URLs de dépôts git contenant des "addons" de dremgr. Par défaut, les
  deux URLs suivants sont listés:
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

Ensuite, il faut définir autant de fichiers `<profil>_profile.env` que de
profils mentionnés dans le fichier `dremgr.env`. Chaque fichier pourrait être
indépendant, mais il est plus simple de tout définir dans le fichier `dremgr.env`
et de faire des liens symboliques.

En l'occurrence, comme les profils prod et test sont définis, on fait les liens
symboliques correspondants. ATTENTION! Si le mode simple avait été utilisé
auparavant, le fichier `prod_profile.env` précédent est perdu au profit du
nouveau lien symbolique. Assurez-vous d'avoir reportés les paramètres dans
`dremgr.env` avant de lancer ces commandes
~~~sh
ln -sf dremgr.env prod_profile.env
ln -sf dremgr.env test_profile.env
~~~

Créer le réseau mentionné dans la configuration (variable `DBNET`)
~~~sh
docker network create --attachable dremgr_db
~~~

Puis démarrer toutes les instances correspondant à chaque profil défini
~~~sh
./dbinst -A
~~~
Attention! si l'instance de prod en mode simple avait déjà été démarrée, il
faut la remplacer par les nouvelles instances, i.e
~~~sh
# forcer le redémarrage
./dbinst -AR
~~~
Notez aussi que les comptes ne sont pas recréés si l'instance de prod en mode
simple avait déjà été démarrée.

## Configurer les services frontaux

Maintenant que les instances de bases de données sont configurées, il faut
configurer les services frontaux. Ces services frontaux comprennent:
* un proxy pgbouncer qui permet de servir plusieurs bases postgresql sur la même
  adresse IP.
* adminer pour accéder à la base de façon graphique
* pgAdmin, alternative pour accéder à la base de façon graphique
* une application web destinée aux utilisateurs autorisés qui affiche les
  informations de connexion à la base de données, et met à disposition de la
  documentation technique et/ou fonctionnelle

Pour la connexion à l'application web, éditez les fichiers suivants:
* `config/apache/mods-available/auth_cas.conf`
  Par défaut, l'authenfication se fait par CAS. Ce fichier sert à indiquer
  l'adresse du serveur CAS.
* `config/apache/auth_cas.conf`
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
changer cette valeur, éditer le fichier `dremgr.env` et configurer les variables
`LBHOST` et `LBHTTP`. Par exemple, avec la configuration suivante, l'adresse du
service devient <http://dremgr.univ.tld>
~~~sh
LBHOST=dremgr.univ.tld
LBHTTP=80
~~~

Ensuite, démarrer les services frontaux
~~~sh
./dbfront

./webfront
~~~
En cas de changement de configuration, utiliser l'option -R pour redémarrer les
services concernés
~~~sh
# exemple: forcer le redémarrage après la modification du paramétrage web
./webfront -R
~~~

Visiter <http://localhost:7081> pour connaitre les paramètres de connexion à
chaque instance de base DRE. par défaut, il s'agit de l'adresse locale:
~~~sh
## profil prod
# base de données DRE
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=prod_dre"

# base de données persistante
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=prod_pdata"
~~~
~~~sh
## profil test
# base de données DRE
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=test_dre"

# base de données persistante
psql -d "host=localhost port=5432 user=reader password=PASSWORD dbname=test_pdata"
~~~
NB: ces commandes servent à vérifier que la base est bien accessible sur
l'adresse configurée. Elles nécessitent bien entendu que vous ayez le client
`psql` installé.  Si ce n'est pas le cas, vous pouvez l'installer avec la
commande suivante:
~~~sh
sudo apt install postgresql-client
~~~
Vous pouvez aussi utiliser n'importe quel autre client graphique ou en ligne de
commande.

Pour le moment, les bases ne contiennent aucune donnée. On peut forcer le
téléchargement et l'importation:
~~~sh
./dbinst -Ai
~~~
Sinon, le téléchargement et l'importation se fait tous les jours à l'heure
définie dans la variable `CRON_PLAN` c'est à dire par défaut 5h30

NB: Les bases de données sont accessibles sur l'adresse IP spécifiée avec le
paramètre `DBVIP`. par défaut, il s'agit de l'adresse locale, ce qui signifie
que les bases de données ne sont pas accessibles depuis les autres machines du
réseau.

Pour que les bases de données soient accessibles sur le réseau, il faut laisser
vide le paramètre `DBVIP` (ou mettre l'adresse IP de l'interface d'écoute). Bien
entendu, il faut relancer les services en cas de changement de configuration.

## Modification du logo

Pour remplacer le logo par celui de votre université dans l'application web, il
faut remplacer le fichier `public/brand.png` par votre propre image au format
PNG (il faut garder le même nom)
~~~sh
cp ~/path/to/monlogo public/brand.png
~~~

L'image DOIT avoir une hauteur de 50 pixel. La largeur importe peu.

## Activer l'accès en https

Si vous souhaitez activer l'accès en https, il y a un certain nombre
d'opérations supplémentaires à effectuer. Le support est géré directement par
le serveur apache qui fait tourner dremgr.

Modifiez le fichier `dremgr.env` pour indiquer le port sur lequel écouter en
https (il s'agit habituellemet du port 443)
~~~sh
LBHTTPS=443
~~~
> [!IMPORTANT]
> Il ne faut pas supprimer la valeur de `LBHTTP` qui reste obligatoire. De plus,
> la redirection vers le port https est activée automatiquement

Vous devez bien entendu disposer d'un certificat. Copiez le certificat et la clé
privée dans le répertoire `config/ssl`
~~~sh
cp path/to/mycert.crt path/to/mycert.key config/ssl
~~~

Si le certificat ne contient pas la chaine autorité, vous devez aussi copier le
fichier autorité
~~~sh
cp path/to/myca.crt config/ssl
~~~
NB: vous pouvez aussi inclure directement l'autorité dans le certificat

Ensuite, il faut modifier le fichier `config/apache/certs.conf` pour mentionner les
certificats
~~~conf
SSLCertificateFile    /etc/ssl/certs/mycert.crt
SSLCertificateKeyFile /etc/ssl/private/mycert.key
~~~

Si l'autorité est dans un fichier à part, il faut le mentionner aussi
~~~conf
SSLCertificateChainFile /etc/ssl/certs/myca.crt
~~~

Puis relancez le frontal
~~~sh
./webfront -R
~~~

Le service web sera alors accessible sur `https://$LBHOST:$LBHTTPS` tels que
définis dans le fichier `dremgr.env`

Par exemple, avec les valeurs suivantes
~~~sh
LBHOST=dremgr.univ.tld
LBHTTPS=443
~~~
Le serveur devra être accédé (et le cas échéant autorisé auprès du serveur CAS)
à l'adresse https://dremgr.univ.tld

## Installer une version de développement

<a name="install-develop"></a>
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

# reconstruire les images puis redémarrer les services concernés
./dremgr -rb
~~~

La commande `git checkout develop` permet de basculer sur la branche de
développement. Dans le même ordre d'idée, `git checkout master` permet de
revenir sur la branche stable, mais **attention aux effets de bord** si la
branche `develop` contient des modifications profondes.

En effet, la branche `develop` contient toujours une version égale ou plus
récente que celle de la branche `master`. Les mises à jour des versions
anciennes vers une version plus récente sont supportées, mais les mises à jour
"dans l'autre sens" d'une version récente vers une version plus ancienne NE SONT
PAS supportées.

En définitive, basculer sur la branche `develop` ne devrait probablement pas
être effectué en production.

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary