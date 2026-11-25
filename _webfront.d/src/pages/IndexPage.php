<?php
namespace app\pages;

use app\init\NavigablePage;
use app\init\tools;
use Exception;
use nulib\A;
use nulib\db\pgsql\Pgsql;
use nulib\ext\json;
use nulib\ext\yaml;
use nulib\file;
use nulib\mail\mdc;
use nulib\os\path;
use nulib\os\sh;
use nulib\php\schema\Schema;
use nulib\php\time\Date;
use nulib\php\time\Hour;
use nulib\web\bs\bsicon;
use nulib\web\bs\lists\ListGroup;
use nulib\web\bs\tables\VTable;
use nulib\web\contag\v;
use nulib\web\page;
use nulib\web\plugins\showmorePlugin;
use nulib\web\vo;

class IndexPage extends NavigablePage {
  const TITLE = "DREmgr";
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
    "filename" => "?string",
    "file" => "?string",
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
    if (!is_dir($docdir)) $filenames = [];
    else $filenames = sh::ls_files($docdir, null, SCANDIR_SORT_DESCENDING);
    $docs = [];
    foreach ($filenames as $filename) {
      if ($filename === ".gitignore") continue;
      if ($filename === "metadata.yml" || $filename === "metadata.yaml") continue;
      $file = path::join($docdir, $filename);
      $fileobj = null;
      if (fnmatch("*.url", $filename)) {
        try {
          $data = json::load($file);
          $data = Schema::with($data, self::url_SCHEMA);
          $url = $data["url"] ?? null;
          $target = $data["target"] ?? "_blank";
          $title = $data["title"] ?? $filename;
          $desc = $data["desc"] ?? null;
          if ($url !== null) {
            $fileobj = [
              "filename" => $filename,
              "file" => "$file",
              "isa_file" => false,
              "url" => $url,
              "target" => $target,
              "title" => $title,
              "desc" => $desc,
            ];
          }
        } catch (Exception $e) {
        }
      } else {
        $fileobj = $metadata["files"][$filename] ?? null;
        if ($fileobj !== null) {
          $fileobj = Schema::with($fileobj, self::url_SCHEMA);
          $fileobj["filename"] = $filename;
          $fileobj["file"] = $file;
          $fileobj["isa_file"] = true;
          $fileobj["url"] = null;
          $fileobj["title"] ??= $filename;
        }
      }
      $fileobj ??= [
        "filename" => $filename,
        "file" => $file,
        "isa_file" => true,
        "url" => null,
        "title" => $filename,
        "target" => null,
        "desc" => null,
      ];
      $docs[$filename] = $fileobj;
    }
    if ($this->download($docs)) return;
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
    # le port est toujours 5432 puisqu'on est en direct
    $inst["port"] = 5432;
    $version = ["valid" => false];
    try {
      $conn = new Pgsql("host=$inst[host] port=$inst[port] dbname=$inst[dbname] user=$inst[user] password=$inst[password]");
      $version = $conn->one("select * from version");
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
    $(".copy-btn").addClass("d-none");
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
    new ListGroup([
      "Adminer" => ["/adminer/", "Un outil simple et pragmatique. Le choix du chef!"],
      "pgAdmin" => ["/pgadmin/", "Si vous en avez l'habitude, le même outil que les RDD-Tools"],
    ], [
      "type" => "links",
      "item" => static function ($item, $title) {
        [$url, $desc] = $item;
        return [
          "href" => $url,
          $title,
          " -- ",
          $desc,
        ];
      },
    ]);

    vo::h1("Documentation");
    $docs = $this->docs;
    if ($docs) {
      $mdc = mdc::mdc();
      vo::p([
        "Une documentation technique et fonctionnelle est disponible. ",
        "Vous y trouverez notamment le schéma de la base de données",
      ]);
      new ListGroup($docs, [
        "type" => "items",
        "item" => function ($file) use ($mdc) {
          $name = $file["filename"];
          $title = $file["title"];
          if ($file["isa_file"]) {
            if ($title !== $name) {
              $link = [
                v::b($title),
                " : ",
                v::a([
                  "href" => page::bu("", [
                    "p" => $this->profile,
                    "dl" => $name,
                  ]),
                  "target" => $file["target"],
                  bsicon::download($name),
                ]),
              ];
            } else {
              $link = v::a([
                "href" => page::bu("", [
                  "p" => $this->profile,
                  "dl" => $name,
                ]),
                "target" => $file["target"],
                $title,
              ]);
            }
          } else {
            $link = v::a([
              "href" => page::bu("", [
                "p" => $this->profile,
                "dl" => $name,
              ]),
              "target" => $file["target"],
              bsicon::new_window($title),
            ]);
          }
          $desc = $file["desc"];
          if ($desc !== null) {
            return [
              v::p($link),
              $mdc->convert($desc),
            ];
          } else {
            return $link;
          }
        },
      ]);
    } else {
      vo::p([
        "Aucune documentation n'est actuellement disponible pour cette version",
      ]);
    }

    vo::h2("Connexion postgresql");
    $sm = new showmorePlugin();
    $sm->invite([
      "accesskey" => "s",
      "Afficher les informations de connexion à postgresql...",
    ]);
    $sm->more();
    vo::p([
      "Pour vous connecter directement à la base de données, utilisez les informations suivantes",
    ]);
    $conninfo = $this->conninfo;
    new VTable([[
      "Type de base de données" => "PostgreSQL",
      "Nom d'hôte" => $conninfo["host"],
      "Port" => $conninfo["port"],
      "Nom de la base de données" => $conninfo["dbname"],
      "URL JDBC" => [
        "class" => "copy-container",
        v::a([
          "class" => "btn btn-default btn-sm copy-btn",
          "href" => "#",
          bsicon::copy("Copier"),
        ]),
        "&nbsp;&nbsp;",
        v::span([
          "class" => "copy-data",
          "jdbc:postgresql://{$conninfo["host"]}:{$conninfo["port"]}/{$conninfo["dbname"]}",
        ]),
      ],
      "Compte utilisateur" => [
        "class" => "copy-container",
        v::a([
          "class" => "btn btn-default btn-sm copy-btn",
          "href" => "#",
          bsicon::copy("Copier"),
        ]),
        "&nbsp;&nbsp;",
        v::span([
          "class" => "copy-data",
          $conninfo["user"],
        ]),
      ],
      "Mot de passe" => [
        "class" => "copy-container",
        v::a([
          "class" => "btn btn-default btn-sm copy-btn",
          "href" => "#",
          bsicon::copy("Copier"),
        ]),
        "&nbsp;&nbsp;",
        v::span([
          "class" => "crdmkc",
          v::span([
            "class" => "credential crdmk copy-data",
            $conninfo["password"],
          ]),
        ]),
      ],
    ]], [
      "class" => "table-auto",
    ]);
    $connstring = [];
    $first = true;
    foreach ($conninfo as $name => $value) {
      if ($first) $first = false;
      else $connstring[] = " ";
      if ($name == "password") {
        $connstring[] = [
          "$name=",
          v::span(["class" => "credential crdmk", "$value"]),
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
        "class" => "crdmkc connstring",
        v::span([
          "class" => "copy-data",
          $connstring,
        ]),
      ]),
      v::a([
        "class" => "btn btn-default btn-sm copy-btn",
        "href" => "#",
        bsicon::copy("Copier"),
      ]),
    ]);
    $sm->print();
  }
}
