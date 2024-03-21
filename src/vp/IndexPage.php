<?php
namespace app\vp;

use app\app\ANavigablePage;
use app\q\tools;
use Exception;
use nur\A;
use nur\F;
use nur\m\pgsql\PgsqlConn;
use nur\path;
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

  const FE_VARS = [
    "host" => "FE_HOST",
    "port" => "FE_PORT",
    "dbname" => "FE_DBNAME",
    "user" => "FE_USER",
    "password" => "FE_PASSWORD",
  ];

  const INST_VARS = [
    "host" => "POSTGRES_HOST",
    "port" => "DBPORT",
    "dbname" => "DBNAME",
    "user" => "POSTGRES_USER",
    "password" => "POSTGRES_PASSWORD",
  ];

  function setup(): void {
    $this->resolveProfiles();
    $profile = $this->profile;

    $this->docdir = $docdir = path::join("/data", $profile, "documentation");
    $this->docs = $docs = shutils::ls_files($docdir, null, SCANDIR_SORT_DESCENDING);
    if ($this->download($docdir, $docs)) return;

    $this->conninfo = tools::get_profile_vars(self::FE_VARS, $profile);

    $inst = tools::get_profile_vars(self::INST_VARS, $profile);
    $version = ["valid" => false];
    try {
      $conn = new PgsqlConn("host=$inst[host] port=$inst[port] dbname=$inst[dbname] user=$inst[user] password=$inst[password]");
      $version = $conn->first("select * from version");
      $version["valid"] = true;
      $version["version"] = "$version[majeure].$version[mineure].$version[patch]";
      if ($version["prerelease"]) $version["version"] .= "-$version[prerelease]";
      $version["date"] = tools::ts2date($version["timestamp"]);
    } catch (Exception $e) {
    }
    $this->version = $version;
  }

  protected $conninfo;

  protected $version;

  protected $docdir, $docs;

  function print(): void {
    $this->printProfileTabs();

    $version = $this->version;
    if ($version["valid"]) {
      vo::p([
        "class" => "alert alert-info",
        "La base DRE a été importée le ",
        v::b($version["date"]),
        " et sa version est ",
        v::b($version["version"]),
      ]);
    } else {
      vo::p([
        "class" => "alert alert-warning",
        "Impossible de déterminer la version actuelle de la base de données. Elle n'a peut-être pas encore été chargée",
      ]);
    }

    vo::h1("Documentation");
    $docs = $this->docs;
    if ($docs) {
      vo::p([
        "Une documentation technique et fonctionnelle est disponible. ",
        "Vous y trouverez notamment le schéma de la base de données",
      ]);
      new CListGroup($docs, [
        "container" => "div",
        "map_func" => function ($file) {
          return [
            "href" => page::bu("", [
              "p" => $this->profile,
              "dl" => $file,
            ]),
            $file,
          ];
        },
        "autoprint" => true,
      ]);
    } else {
      vo::p([
        "Aucune documentation n'est actuellement disponible pour cette version",
      ]);
    }

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
            $conninfo["password"],
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
