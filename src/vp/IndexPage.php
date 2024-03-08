<?php
namespace app\vp;

use app\app\ANavigablePage;
use nur\A;
use nur\F;
use nur\shutils;
use nur\txt;
use nur\v\bs3\vc\CListGroup;
use nur\v\bs3\vc\CNavTabs;
use nur\v\bs3\vc\CVerticalTable;
use nur\v\page;
use nur\v\v;
use nur\v\vo;

class IndexPage extends ANavigablePage {
  const TITLE = "DRE - Données PEGASE";

  const VARS = [
    "host" => "FE_HOST",
    "port" => "FE_PORT",
    "dbname" => "FE_DBNAME",
    "user" => "FE_USER",
    "password" => "FE_PASSWORD",
  ];

  function setup(): void {
    $profiles = explode(" ", getenv("APP_PROFILES"));
    $this->profiles = $profiles;

    $cprofile = F::get("p");
    if (!$cprofile) $cprofile = A::first($profiles);
    $this->cprofile = $cprofile;

    $profileTabs = [];
    foreach ($profiles as $iprofile) {
      $profileTabs[$iprofile] = [
        txt::upper1($iprofile),
        "url" => page::self(["p" => $iprofile]),
      ];
    }
    $this->profileTabs = $profileTabs;

    $conninfo = [];
    foreach (self::VARS as $key => $var) {
      $pvar = "${cprofile}_$var";
      $avar = "__ALL__$var";
      if (($value = getenv($pvar)) === false) {
        if (($value = getenv($avar)) === false) {
          $value = getenv($var);
        }
      }
      $conninfo[$key] = $value;
    }
    $this->conninfo = $conninfo;

    $pubdir = __DIR__.'/../../public';
    $this->files = shutils::ls_files("$pubdir/doc", null, SCANDIR_SORT_DESCENDING);
  }

  /** @var array */
  protected $profiles;

  protected $cprofile;

  /** @var array */
  protected $profileTabs;

  protected $conninfo;

  protected $files;

  function print(): void {
    new CNavTabs($this->profileTabs, $this->cprofile);

    vo::h1("Documentation");
    vo::p([
      "Une documentation technique et fonctionnelle est disponible. ",
      "Vous y trouverez notamment le schéma de la base de données",
    ]);
    new CListGroup($this->files, [
      "container" => "div",
      "map_func" => function ($file) {
        return [
          "href" => "doc/$file",
          $file,
        ];
      },
      "autoprint" => true,
    ]);

    vo::h1("Accès à la base de données");
    vo::p([
      "Vous pouvez vous connecter directement à la base de données avec les informations suivantes",
    ]);
    $conninfo = $this->conninfo;
    new CVerticalTable([[
      "Type de base de données" => "PostgreSQL",
      "Nom d'hôte" => $conninfo["host"],
      "Port" => $conninfo["port"],
      "Nom de la base de données" => $conninfo["dbname"],
      "Compte utilisateur" => $conninfo["user"],
      "Mot de passe" => [
        v::span([
          "class" => "hpc",
          v::span([
            "class" => "hp password",
            $conninfo["password"]
          ]),
        ]),
      ],
    ]], [
      "autoprint" => true,
    ]);
    $connstring = [];
    $first = true;
    foreach ($conninfo as $name => $value) {
      if ($first) $first = false;
      else $connstring[] = " ";
      if ($name == "password") {
        $connstring[] = [
          "$name=",
          v::span(["class" => "hp password", "$value"]),
        ];
      } else {
        $connstring[] = "$name=$value";
      }
    }
    vo::p([
      "Si une chaine de connexion est demandée, vous pouvez utiliser la valeur suivante:",
      "<br/>",
      v::span([
        "class" => "hpc connstring",
        $connstring,
      ]),
    ]);

    vo::h2("Accès en ligne");
    vo::p([
      "Vous avez aussi la possibilité de vous connecter avec un outil en ligne",
    ]);
    new CListGroup([
      "pgAdmin" => ["/pgadmin/", "Un outil simple et ergonomique"],
      "Adminer" => ["/adminer/", "Une alternative préférée par certains informaticiens"],
    ], [
      "container" => "div",
      "map_func" => function ($item, $title) {
        [$url, $desc] = $item;
        return [
          "href" => $url,
          $title,
          " -- ",
          $desc,
        ];
      },
      "autoprint" => true,
    ]);
  }
}
