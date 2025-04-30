# Installer une mise à jour

En cas de livraison d'une nouvelle version de l'application, prenez le temps de
lire ce fichier AVANT de commencer à faire quoi que ce soit.

Le fichier [CHANGES](CHANGES.md) contient parfois des instructions pour la mise
à jour vers une version particulière.

Si vous ne savez pas quelle est la version actuellement instalée, vous pouvez
consulter le fichier `VERSION.txt` (à faire *avant* de lancer `git pull`)
~~~sh
cat VERSION.txt
~~~

Il vous suffit ensuite de suivre les instructions pour les versions ultérieures
listées dans le fichier [CHANGES](CHANGES.md)

S'il n'y a pas d'instructions particulière, il suffit de suivre les instructions
suivantes:
~~~sh
cd dremgr

# mettre à jour le dépôt
git pull

# reconstruire les images si nécessaire puis redémarrer les services le cas échéant
./dremgr -rb
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary