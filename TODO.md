# TODO / ROADMAP

Sans ordre particulier, fonctionnalités qui seront implémentées dans le futur:
* Support d'autant de comptes "reader" que nécessaire
  * Gestion des comptes: création, mise à jour des mots de passe
  * Mapping entre les comptes reader et les comptes utilisateurs: l'utilisateur
    ne se voit afficher que les credentials qui le concernent
    Cela permet de révoquer l'accès d'un utilisateur sans devoir changer le mot
    de passe de l'utilisateur unique
* Transmettre dynamiquement à pgbouncer les modifications telles que:
  * création d'une nouvelle base de données
  * création/modification/suppresion d'un compte
* Possibilité d'historiser les imports en base de données: une base de données
  par jour, pour une certaine quantité maximale de jours
* Même si pas d'historisation, possibilité de restaurer une base de données
  correspondant à une date spécifique dans une base temporaire
* Envoi journalier des logs par mail, notamment en cas d'erreur
* `dremgr` ne redémarre jamais pgbouncer (sauf si explicitement demandé avec
  par exemple une option --all)
* `dbfront` offre quelques options courantes pour piloter pgbouncer (e.g
  rolling restart si plusieurs instances sont configurées -- à voir si ça se
  justifie)
* prendre par défaut le fichier `dremgr.env` pour la configuration. n'utiliser
  `<PROFIL>_profile.env` que si le fichier existe. de cette façon,
  l'installation et la documentation sont simplifiés puisqu'il n'y a toujours
  qu'un seul fichier de configuration, quelle que soit la méthode d'installation

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary