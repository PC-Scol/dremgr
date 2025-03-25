# Pré-requis sous Linux

dremgr est développé et testé sur Debian 11. Il devrait fonctionner sur
n'importe quel système Linux, pourvu que les pré-requis soient respectés.

Les programmes suivants sont nécessaires:
* git
* curl
* rsync
* sudo
* tar
* unzip
* Python 3
* GNU awk (gawk)
* docker
  (podman n'a pas été testé, je ne sais pas si ça marche avec)

Les instructions suivantes permettent d'installer ce qui est nécessaire sous
Debian Linux:

Ouvrir un terminal, et vérifier que l'utilisateur courant est sudoer
~~~sh
sudo -v
~~~
~~~console
Désolé, l'utilisateur jclain ne peut pas utiliser sudo sur <MACHINE>
~~~
Dans cet exemple, l'utilisateur jclain n'est pas sudoer, il faut donc faire la
configuration

D'abord se connecter en root
~~~sh
su -
~~~

Puis créer le fichier sudoer pour l'utilisateur jclain, connecté en root
~~~sh
user=jclain
~~~
~~~sh
echo "$user ALL=(ALL:ALL) ALL" >/etc/sudoers.d/$user &&
chmod 440 /etc/sudoers.d/$user
~~~
~~~sh
exit
~~~

Après ces opérations, ou si l'utilisateur était déjà sudoer, 'sudo -v' demande
son mot de passe à l'utilisateur
~~~sh
sudo -v
~~~
~~~console
[sudo] Mot de passe de jclain :
~~~

Ensuite, il faut installer les programmes requis
~~~sh
sudo apt update && sudo apt install git curl rsync tar unzip python3 gawk
~~~

Puis installer docker
~~~sh
curl -fsSL https://get.docker.com | sudo sh
~~~
~~~sh
[ -n "$(getent group docker)" ] || sudo groupadd docker
sudo usermod -aG docker $USER
~~~
Il faut se déconnecter et se reconnecter pour activer le changement dans la
configuration des groupes

Pour les autres systèmes, vous devez vous reporter à votre manuel utilisateur

## Configuration du proxy

Si vous utilisez un proxy, vous avez sûrement des variables `http_proxy`,
`https_proxy` et/ou `no_proxy` qui sont définies dans votre environnement
~~~sh
$ declare -p http_proxy https_proxy no_proxy
declare -x http_proxy="http://myproxy.tld:3128"
declare -x https_proxy="http://myproxy.tld:3128"
-bash: declare: no_proxy : non trouvé
~~~
Dans cet exemple, seules les variables `http_proxy` et `https_proxy` sont
configurées, mais ce n'est pas gênant

Si aucune de ces variable n'est définie, vous pouvez vous rapprocher de notre
administrateur système pour avoir les inforamtions nécessaires (il s'agit
habituellement de renseigner le fichier `/etc/environment`)

Cette configuration présente dans votre environnement est automatiquement
utilisée par l'application `dremgr`. Par contre il faut explicitement configurer
le daemon docker pour utiliser votre proxy (ce n'est pas automatique)

Sur Debian Linux, vous pouvez définir un override sur l'unité systemd qui
démarre docker
~~~sh
sudo mkdir -p /etc/systemd/system/docker.service.d

sudo nano /etc/systemd/system/docker.service.d/proxy.conf
~~~
~~~ini
[Service]
Environment=http_proxy=http://myproxy.tld:3128
Environment=https_proxy=http://myproxy.tld:3128
Environment=no_proxy=int.univ.tld
~~~
~~~sh
sudo systemctl daemon-reload

sudo systemctl restart docker.service
~~~

Une alternative est de définir le proxy directement dans la configuration du
daemon docker
~~~sh
sudo mkdir -p /etc/docker

sudo nano /etc/docker/daemon.json
~~~
~~~json
{
  "proxies": {
    "http-proxy": "http://proxy.tld:3128",
    "https-proxy": "http://proxy.tld:3128",
    "no-proxy": "int.univ.tld"
  }
}
~~~
~~~sh
sudo systemctl restart docker.service
~~~

---

Une fois que vous avez installé les pré-requis, vous pouvez passer à l'étape
suivante, [>> cloner le dépôt](01cloner-depot.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary