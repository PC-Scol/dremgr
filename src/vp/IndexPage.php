<?php
namespace app\vp;

use app\app\ANavigablePage;
use app\q\tools;
use Exception;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use nulib\ext\yaml;
use nulib\file;
use nur\b\date\Hour;
use nur\json;
use nur\m\pgsql\PgsqlConn;
use nur\md;
use nur\path;
use nulib\A;
use nulib\php\time\Date;
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
    "name" => "?string",
    "isa_file" => "bool",
    "url" => "?string",
    "title" => "?string",
    "target" => "?string",
    "desc" => "?string",
  ];

  function setup(): void {
    $this->resolveProfiles();
    $profile = $this->profile;

    $this->docdir = $docdir = path::join("/data", $profile, "documentation");
    $metadata = file::try_ext("$docdir/metadata.yml", ".yaml");
    if ($metadata !== null) $metadata = yaml::load($metadata);
    $files = shutils::ls_files($docdir, null, SCANDIR_SORT_DESCENDING);
    $docs = [];
    foreach ($files as $file) {
      if ($file === "metadata.yml" || $file === "metadata.yaml") continue;
      if (fnmatch("*.url", $file)) {
        try {
          $data = json::load(path::join($docdir, $file));
          md::ensure_schema($data, self::url_SCHEMA);
          $url = $data["url"];
          $target = $data["target"] ?? "_blank";
          $title = $data["title"] ?? $file;
          $desc = $data["desc"] ?? null;
          $file = [
            "name" => $file,
            "isa_file" => false,
            "url" => $url,
            "target" => $target,
            "title" => $title,
            "desc" => $desc,
          ];
        } catch (Exception $e) {
        }
      } else {
        $data = $metadata["files"][$file] ?? null;
        if ($data !== null) {
          md::ensure_schema($data, self::url_SCHEMA);
          $data["name"] = $file;
          $data["isa_file"] = true;
          $data["url"] = null;
          $data["title"] ??= $file;
          $file = $data;
        }
      }
      if (is_array($file)) {
        $docs[$file["name"]] = $file;
      } else {
        $docs[$file] = [
          "name" => $file,
          "isa_file" => true,
          "url" => null,
          "title" => $file,
          "target" => null,
          "desc" => null,
        ];
      }
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

  const HAVE_JQUERY = true;

  function printJquery(): void {
    ?>
    <script type="text/javascript">
      jQuery.noConflict()(function($) {
        if (navigator.clipboard) {
          $(".copy-btn").click(function() {
            var $action = $(this);
            var $data = $action.closest(".copy-container").find(".copy-data");
            navigator.clipboard.writeText($data.text());
            $action.addClass("btn-success").text("Copié!");
            window.setTimeout(function() {
              $action.removeClass("btn-success").text("Copier");
            }, 1000);
            return false;
          });
        } else {
          $(".copy-btn").addClass("hidden");
        }
      });
    </script>
    <?php
  }

  function print(): void {
    $this->printProfileTabs();

    $importDisabled = $this->importDisabled;
    $importPlan = $this->importPlan;
    $importHour = $this->importHour;
    if ($importDisabled) {
      $planDesc = "L'import journalier de la base DRE est <b>désactivé</b>";
    } elseif ($importHour !== null) {
      $planDesc = "Selon la planification configurée, la base DRE doit être importée tous les jours à <code>$importHour</code>";
    } else {
      $planDesc = "La base DRE doit être importée tous les jours selon la planification suivante: <code>$importPlan</code>";
    }

    $version = $this->version;
    if ($version["valid"]) {
      $today = new Date();
      $dateDesc = ["<br/>La dernière importation date "];
      if ($version["date"] == $today) {
        A::merge($dateDesc, [
          v::b("d'aujourd'hui"),
        ]);
      } else {
        A::merge($dateDesc, [
          " du ",
          v::b($version["date"]),
        ]);
      }
      A::merge($dateDesc, [
        " et sa version est ",
        v::b($version["version"]),
      ]);

      vo::p([
        "class" => "alert alert-info",
        $planDesc,
        $dateDesc,
      ]);
    } else {
      vo::p([
        "class" => "alert alert-info",
        $planDesc,
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
      "Adminer" => ["/adminer/", "Un outil simple et pragmatique. Le choix du chef!"],
      "pgAdmin" => ["/pgadmin/", "Si vous en avez l'habitude, le même outil que les RDD-Tools"],
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
      $markdown = new GithubFlavoredMarkdownConverter([
        'html_input' => 'strip',
        'allow_unsafe_links' => false,
      ]);
      vo::p([
        "Une documentation technique et fonctionnelle est disponible. ",
        "Vous y trouverez notamment le schéma de la base de données",
      ]);
      new CListGroup($docs, [
        "container" => "ul",
        "map_func" => function ($file) use ($markdown) {
          $title = $file["title"];
          if (!$file["isa_file"]) $title = icon::new_window($title);
          $desc = $file["desc"];
          if ($desc !== null) {
            return [
              v::p([
                v::b($title),
                " : ",
                v::a([
                  "href" => page::bu("", [
                    "p" => $this->profile,
                    "dl" => $file["name"],
                  ]),
                  "target" => $file["target"],
                  icon::download($file["name"]),
                ]),
              ]),
              $markdown->convert($desc),
            ];
          } else {
            return v::a([
              "href" => page::bu("", [
                "p" => $this->profile,
                "dl" => $file["name"],
              ]),
              "target" => $file["target"],
              $title,
            ]);
          }
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
    $sm->printInvite([
      "accesskey" => "s",
      "Afficher les informations de connexion à postgresql...",
    ]);

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
        "class" => "copy-container",
        v::span([
          "class" => "hpc",
          v::span([
            "class" => "hp password copy-data",
            $conninfo["password"],
          ]),
        ]),
        "&nbsp;&nbsp;",
        v::a([
          "class" => "btn btn-default btn-sm copy-btn",
          "href" => "#",
          icon::copy("Copier"),
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
      "class" => "copy-container",
      "Si une chaine de connexion est demandée, vous pouvez utiliser la valeur suivante:",
      "<br/>",
      v::span([
        "class" => "hpc connstring",
        v::span([
          "class" => "copy-data",
          $connstring,
        ]),
      ]),
      v::a([
        "class" => "btn btn-default btn-sm copy-btn",
        "href" => "#",
        icon::copy("Copier"),
      ]),
    ]);
    $sm->printEnd();
  }
}
