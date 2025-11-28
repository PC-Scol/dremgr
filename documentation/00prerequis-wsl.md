# Pré-requis sous WSL

DREmgr est développé et testé sur Debian 12. Il est cependant possible de le
faire fonctionner sous WSL en faisant attention à certains points.

Installer tout d'abord WSL en suivant les instructions sur <https://aka.ms/wslinstall>
~~~powershell
wsl --install -d debian
~~~

Puis installer Docker Desktop <https://www.docker.com/products/docker-desktop/>

Lancer Docker Desktop pour s'assurer de la présence des services nécessaires. Il
faut garder Docker Desktop ouvert à chaque utilisation de DREmgr

Lancer Debian, puis installer les outils nécessaires
~~~sh
sudo apt update && sudo apt install git curl rsync tar unzip python3 gawk
~~~
NB: si vous avez installé Ubuntu à la place de Debian, c'est bon aussi, pas
d'inquiétude

---

Une fois que vous avez installé les pré-requis, vous pouvez passer à l'étape
suivante, [>> cloner le dépôt](01cloner-depot.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary