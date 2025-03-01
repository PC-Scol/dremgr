# TODO / ROADMAP

Sans ordre particulier, fonctionnalités qui seront implémentées dans le futur:
* Transmettre dynamiquement à pgbouncer les modifications telles que:
  * création d'une nouvelle base de données
  * création/modification/suppresion d'un compte
* Possibilité d'historiser les imports en base de données: une base de données
  par jour, pour une certaine quantité maximale de jours
* Même si pas d'historisation, possibilité de restaurer une base de données
  correspondant à une date spécifique dans une base temporaire
* Support d'autant de comptes "reader" que nécessaire
  * Gestion des comptes: création, mise à jour des mots de passe
  * Mapping entre les comptes reader et les comptes utilisateurs: l'utilisateur
    ne se voit afficher que les credentials qui le concernent
    Cela permet de révoquer l'accès d'un utilisateur sans devoir changer le mot
    de passe de l'utilisateur unique
* Possibilité de copier le mot de passe et/ou la chaine de connexion dans le
  presse-papier
* Envoi journalier des logs par mail

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary