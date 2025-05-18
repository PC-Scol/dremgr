# Installer la base de données persistante pdata

Depuis la version 1.5.0, les nouvelles installations bénéficient automatiquement
de la création de la base de données persistante. Pour les installations
existantes, il faut faire l'opération manuellement.

* Mettre à jour la configuration
  ~~~sh
  # rajouter
  PDBNAME=pdata

  # modifier
  PGBOUNCER_DBS="$DBNAME $PDBNAME"
  ~~~
* relancer les serveurs
  ~~~sh
  ./dbinst -AR
  ~~~
* créer la base de données `pdata` et configurer la liaison avec la base de
  données `dre`
  ~~~sh
  ./dbinst -Ax -- setup-pdb.sh --create
  ~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary