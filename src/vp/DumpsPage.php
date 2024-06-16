<?php
namespace app\vp;

use app\app\ANavigablePage;
use app\q\tools;
use Exception;
use nur\b\date\Datetime;
use nur\config;
use nur\F;
use nur\num;
use nur\path;
use nur\shutils;
use nur\SV;
use nur\v\bs3\fo\ControlHidden;
use nur\v\bs3\fo\ControlSelect;
use nur\v\bs3\fo\FormInline;
use nur\v\bs3\vc\CNavTabs;
use nur\v\bs3\vc\CTable;
use nur\v\http;
use nur\v\js;
use nur\v\page;
use nur\v\plugins\showmorePlugin;
use nur\v\v;
use nur\v\vo;

class DumpsPage extends ANavigablePage {
  const CRON_LOG_MAX_SIZE = 2 * 1024 * 1024;

  function setup(): void {
    $this->resolveProfiles();
    $appdir = path::join("/data", $this->profile);
    $this->dldir = $dldir = path::join($appdir, "downloads");

    $afilenames = shutils::ls_files($dldir);
    usort($afilenames, function ($afilename, $bfilename) use ($dldir) {
      if (preg_match('/\d{8}/', $afilename, $ms)) $amod = $ms[0];
      else $amod = strftime("%Y%m%d", filemtime(path::join($dldir, $afilename)));
      if (preg_match('/\d{8}/', $bfilename, $ms)) $bmod = $ms[0];
      else $bmod = strftime("%Y%m%d", filemtime(path::join($dldir, $bfilename)));
      if (($c = SV::compare($amod, $bmod)) !== 0) return -$c;
      return SV::compare($afilename, $bfilename);
    });

    if ($this->download($dldir, $afilenames)) return;

    $ydates = [];
    $yfilenames = [];
    foreach ($afilenames as $filename) {
      if (tools::isa_ts($filename, $ms)) {
        $date = tools::ts2date($filename, $ymd);
        if (!array_key_exists($ymd, $ydates)) $ydates[$ymd] = $date;
        $yfilenames[$ymd][] = $filename;
      }
    }
    foreach ($ydates as $ymd => &$date) {
      $date = [$ymd, $date];
    }; unset($date);
    $this->yfilenames = $yfilenames;

    $dre = tools::get_profile_vars(["url" => "DRE_URL"], $this->profile);
    $this->dreUrl = $dre["url"];

    $this->fo = $fo = new FormInline([
      "params" => [
        "p" => [
          "control" => ControlHidden::class,
          "value" => $this->profile,
        ],
        "ymd" => [
          "control" => ControlSelect::class,
          "label" => "Date",
          "items" => $ydates,
        ],
      ],
      "autoload_params" => true,
      "autoadd_submit" => false,
    ]);
    $ymd = $fo["ymd"];
    if (!$ymd) $ymd = array_key_first($ydates);
    $this->ymd = $ymd;

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

  protected $dldir, $ymd, $yfilenames;

  protected $dreUrl;

  /** @var FormInline */
  protected $fo;

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

    $dldir = $this->dldir;
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
    $this->fo->print();

    new CTable($this->yfilenames[$this->ymd], [
      "map_func" => function($filename) use ($dldir) {
        $file = "$dldir/$filename";
        return [
          "Nom" => v::a([
            "href" => page::bu("", [
              "p" => $this->profile,
              "dl" => $filename
            ]),
            $filename,
          ]),
          "Taille" => num::format_size(filesize($file)),
          "Date" => (new Datetime(filemtime($file)))->format(),
        ];
      },
      "autoprint" => true,
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
