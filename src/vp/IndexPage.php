<?php
namespace app\vp;

use app\app\ANavigablePage;
use app\q\tools;
use Exception;
use nur\b\date\Hour;
use nur\json;
use nur\m\pgsql\PgsqlConn;
use nur\md;
use nur\path;
use nur\shutils;
use nur\v\bs3\vc\CListGroup;
use nur\v\bs3\vc\CVerticalTable;
use nur\v\icon;
use nur\v\page;
use nur\v\plugins\showmorePlugin;
use nur\v\v;
use nur\v\vo;

class IndexPage extends ANavigablePage {
  const TITLE = "DRE - Données PEGASE";
  const PLUGINS = [showmorePlugin::class];

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

  const url_SCHEMA = [
    "url" => "string",
    "title" => "?string",
    "target" => "?string",
  ];

  function setup(): void {
    $this->resolveProfiles();
    $profile = $this->profile;

    $this->docdir = $docdir = path::join("/data", $profile, "documentation");
    $tmpdocs = shutils::ls_files($docdir, null, SCANDIR_SORT_DESCENDING);
    $docs = [];
    foreach ($tmpdocs as $doc) {
      if (fnmatch("*.url", $doc)) {
        try {
          $data = json::load(path::join($docdir, $doc));
          md::ensure_schema($data, self::url_SCHEMA);
          $url = $data["url"];
          $title = $data["title"]?? $doc;
          $target = $data["target"]?? "_blank";
          $doc = [
            "name" => $doc,
            "url" => $url,
            "title" => $title,
            "target" => $target,
          ];
        } catch (Exception $e) {
        }
      }
      if (is_array($doc)) $docs[$doc["name"]] = $doc;
      else $docs[$doc] = $doc;
    }
    if ($this->download($docdir, $docs)) return;
    $this->docs = $docs;

    $this->conninfo = tools::get_profile_vars(self::FE_VARS, $profile);

    $importDisabled = boolval(tools::get_profile_var("CRON_DISABLE", $profile));
    $importPlan = trim(tools::get_profile_var("CRON_PLAN", $profile) ?? "");
    $t = preg_split('/\s+/', $importPlan);
    if (count($t) == 5 && is_numeric($t[0]) && is_numeric($t[1]) &&
      $t[2] === "*" && $t[3] === "*" && $t[4] === "*") {
      $importHour = new Hour([$t[1], $t[0], 0]);
    } else {
      $importHour = null;
    }
    $this->importDisabled = $importDisabled;
    $this->importPlan = $importPlan;
    $this->importHour = $importHour;

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

  protected $importDisabled, $importPlan, $importHour;

  protected $version;

  protected $docdir, $docs;

  function print(): void {
    $this->printProfileTabs();

    $importDisabled = $this->importDisabled;
    $importPlan = $this->importPlan;
    $importHour = $this->importHour;
    if ($importDisabled) {
      $importDesc = "L'import journalier de la base DRE est <b>désactivé</b>";
    } elseif ($importHour !== null) {
      $importDesc = "Selon la planification configurée, la base DRE doit être importée tous les jours à <code>$importHour</code>";
    } else {
      $importDesc = "La base DRE doit être importée tous les jours selon la planification suivante: <code>$importPlan</code>";
    }
    $version = $this->version;
    if ($version["valid"]) {
      vo::p([
        "class" => "alert alert-info",
        $importDesc,
        "<br/>La dernière importation date du ",
        v::b($version["date"]),
        " et sa version est ",
        v::b($version["version"]),
      ]);
    } else {
      vo::p([
        "class" => "alert alert-info",
        $importDesc,
      ]);
      vo::p([
        "class" => "alert alert-warning",
        "Impossible de déterminer la version actuelle de la base de données. Elle n'a peut-être pas encore été chargée",
      ]);
    }

    //vo::h1("Accès en ligne");
    vo::p([
      "Vous pouvez vous connecter à la base DRE avec un outil en ligne",
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
          if (is_array($file)) {
            $name = $file["name"];
            $title = icon::new_window($file["title"]);
            $target = $file["target"];
          } else {
            $name = $file;
            $title = $file;
            $target = null;
          }
          return [
            "href" => page::bu("", [
              "p" => $this->profile,
              "dl" => $name,
            ]),
            "target" => $target,
            $title,
          ];
        },
        "autoprint" => true,
      ]);
    } else {
      vo::p([
        "Aucune documentation n'est actuellement disponible pour cette version",
      ]);
    }

    $sm = new showmorePlugin();
    $sm->printStartc();
    vo::h2("Connexion postgresql");
    $sm->printInvite("Afficher les informations de connexion à postgresql...");

    $sm->printStartp();
    vo::p([
      "Pour vous connecter directement à la base de données, utilisez les informations suivantes",
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
    $sm->printEnd();
  }
}
