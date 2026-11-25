<?php
namespace app\pages;

use app\init\NavigablePage;
use app\init\tools;
use nulib\cl;
use nulib\cv;
use nulib\os\path;
use nulib\os\sh;
use nulib\php\coll\Cursor;
use nulib\php\time\DateTime;
use nulib\php\types\vfloat;
use nulib\web\bs\tables\Table;
use nulib\web\contag\v;
use nulib\web\fo;
use nulib\web\page;
use nulib\web\params\ArrayData;
use nulib\web\vo;

class DumpsPage extends NavigablePage {
  const CRON_LOG_MAX_SIZE = 2 * 1024 * 1024;

  function setup(): void {
    $this->resolveProfiles();
    $appdir = path::join("/data", $this->profile);
    $this->dldir = $dldir = path::join($appdir, "downloads");

    $afilenames = sh::ls_files($dldir);
    usort($afilenames, function ($afilename, $bfilename) use ($dldir) {
      if (preg_match('/\d{8}/', $afilename, $ms)) $amod = $ms[0];
      else $amod = strftime("%Y%m%d", filemtime(path::join($dldir, $afilename)));
      if (preg_match('/\d{8}/', $bfilename, $ms)) $bmod = $ms[0];
      else $bmod = strftime("%Y%m%d", filemtime(path::join($dldir, $bfilename)));
      if (($c = cv::compare($amod, $bmod)) !== 0) return -$c;
      return cv::compare($afilename, $bfilename);
    });

    $files = [];
    $ydates = [];
    $yfiles = [];
    foreach ($afilenames as $filename) {
      $file = "$dldir/$filename";
      $files[$filename] = [
        "filename" => $filename,
        "file" => $file,
        "isa_file" => true,
      ];
      if (tools::isa_ts($filename, $ms)) {
        $date = tools::ts2date($filename, $ymd);
        if (!array_key_exists($ymd, $ydates)) $ydates[$ymd] = $date;
        $yfiles[$ymd][] = [
          "filename" => $filename,
          "file" => $file,
          "size" => filesize($file),
          "mtime" => new DateTime(filemtime($file)),
        ];
      }
    }
    foreach ($ydates as $ymd => &$date) {
      $date = [$ymd, $date];
    }; unset($date);
    $this->yfiles = $yfiles;

    $this->data = $data = new ArrayData([
      "p" => ["string", $this->profile,
        "form_control" => "hidden",
      ],
      "ymd" => ["string", cl::first_key($ydates),
        "form_control" => "select",
        "form_params" => [
          "label" => "Date",
          "items" => $ydates,
        ],
      ],
    ]);
    if ($this->download($files)) return;

    $dre = tools::get_profile_vars([
      "url" => "DRE_URL",
      "max_age" => "CRON_MAX_AGE",
    ], $this->profile);
    $this->dreUrl = $dre["url"];
    $this->maxAge = $dre["max_age"];
    $this->ymd = $data["ymd"];

    $cronlog = path::join($appdir, "cron.log");
    if (file_exists($cronlog)) {
      $size = filesize($cronlog);
      $inf = fopen($cronlog, "rb");
      if ($size > self::CRON_LOG_MAX_SIZE) {
        fseek($inf, -self::CRON_LOG_MAX_SIZE, SEEK_END);
      }
      $croncontent = stream_get_contents($inf);
      fclose($inf);
    } else {
      $croncontent = null;
    }
    $this->croncontent = $croncontent;
    $importlog = path::join($appdir, "import.log");
    if (file_exists($importlog)) {
      $importcontent = file_get_contents($importlog);
    } else {
      $importcontent = null;
    }
    $this->importcontent = $importcontent;
  }

  protected $dldir, $ymd, $yfiles;

  protected $dreUrl;

  protected $maxAge;

  protected ArrayData $data;

  protected $croncontent, $importcontent;

  const HAVE_JQUERY = true;

  function printJquery(): void {
    ?>
<script type="text/javascript">
jQuery.noConflict()(function($) {
  $("#ymd").on("change", function (e) {
    this.form.submit();
    return false;
  });

  $("#cron-content").scrollTop(function () { return this.scrollHeight; });
});
</script>
<?php
  }

  function print(): void {
    $this->printProfileTabs();

    $dreUrl = $this->dreUrl;
    vo::h1("Fichiers de dumps");
    vo::p([
      "Source: ",
      v::a([
        "href" => $dreUrl,
        "target" => "_blank",
        $dreUrl,
      ]),
    ]);
    fo::print($this->data, [
      "type" => "inline",
      "feedback" => false,
      "autoadd_submit" => false,
    ]);

    $yfiles = $this->yfiles[$this->ymd] ?? [];
    $totalSize = 0;
    foreach ($yfiles as $yfile) {
      $totalSize += $yfile["size"];
    }
    new Table($yfiles, [
      "layout" => [
        ["Nom", function(Cursor $yfile) {
          $filename = $yfile["filename"];
          return v::a([
            "href" => page::bu("", [
              "p" => $this->profile,
              "dl" => $filename
            ]),
            $filename,
          ]);
        }],
        ["Taille", static function(Cursor $yfile) {
          return vfloat::format_size($yfile["size"]);
        }],
        ["Date", static function(Cursor $yfile) {
          return $yfile["mtime"]->format();
        }],
      ],
      "suffix" => v::div([
        "class" => "alert alert-info",
        "Taille totale: ", vfloat::format_size($totalSize),
        "<br/>En tenant compte de CRON_MAX_AGE, il faut prévoir ",
        vfloat::format_size($totalSize * $this->maxAge),
        " pour garder une copie de tous les fichiers de dumps",
      ]),
    ]);

    if ($this->croncontent || $this->importcontent) {
      vo::h2("Logs d'importation");
      if ($this->croncontent) {
        vo::p("cron.log");
        vo::pre(["id" => "cron-content", $this->croncontent]);
      }
      if ($this->importcontent) {
        vo::p("import.log");
        vo::pre(["id" => "import-content", $this->importcontent]);
      }
    }
  }
}
