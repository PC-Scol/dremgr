#!/usr/bin/php
<?php
require __DIR__.'/../../sbin/vendor/autoload.php';

use nulib\app\cli\Application;
use nulib\app\config;
use nulib\app\config\YamlConfig;
use nulib\cl;
use nulib\mail\mailer;
use nulib\mail\MailTemplate;
use nulib\php\time\Elapsed;
use nulib\php\types\vbool;
use nulib\php\time\DateTime;

Application::run(new class extends Application {
  private static function get_string($name): ?string {
    $value = trim(strval(getenv($name)));
    if ($value === "") $value = null;
    return $value;
  }

  private static function get_bool($name): bool {
    return vbool::with(self::get_string($name));
  }

  private static function get_datetime($name): ?DateTime {
    return DateTime::withn(self::get_string($name));
  }

  function main() {
    config::add(new YamlConfig(__DIR__.'/sendmails.yml'));

    $disabled = vbool::with(config::k("disabled"));
    if ($disabled) return;

    $requireCron = vbool::with(config::k("require_cron"));
    $isCron = self::get_bool("TEM_CRON");
    if ($requireCron && !$isCron) return;

    $dateDeb = self::get_datetime("DATE_DEB");
    $dateFin = self::get_datetime("DATE_FIN");
    if ($dateDeb !== null && $dateFin !== null) {
      $duree = Elapsed::format_delay($dateDeb, $dateFin);
    } else {
      $duree = null;
    }
    $isDownload = self::get_bool("TEM_DOWNLOAD");
    $isImport = self::get_bool("TEM_IMPORT");
    $isAddons = self::get_bool("TEM_ADDONS");
    $criticalError = self::get_bool("CRITICAL_ERROR");
    $haveErrors = self::get_bool("HAVE_ERRORS");
    $importLog = self::get_string("IMPORT_LOG");

    $from = config::k("from");
    $allTo = cl::withn(config::k("to"));
    $errorTo = cl::withn(config::k("to_error"));
    if ($criticalError) {
      $template = config::l("critical");
      $to = cl::merge($allTo, $errorTo);
    } elseif ($haveErrors) {
      $template = config::l("error");
      $to = cl::merge($allTo, $errorTo);
    } else {
      $template = config::l("success");
      $to = $allTo;
    }
    $cc = cl::withn(config::k("cc"));

    $mail = (new MailTemplate($template))->eval([
      "date_debut" => $dateDeb,
      "date_fin" => $dateFin,
      "duree" => $duree,
      "is_download" => $isDownload,
      "is_import" => $isImport,
      "is_addons" => $isAddons,
      "critical_error" => $criticalError,
      "have_errors" => $haveErrors,
    ]);
    $mailer = mailer::build($to, $mail["subject"], $mail["body"], $cc, null, $from);
    if ($importLog !== null) {
      $mailer->addAttachment($importLog, "import.log");
    }
    mailer::_send($mailer);
  }
});
