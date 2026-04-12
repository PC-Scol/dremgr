#!/usr/bin/php
<?php
require __DIR__.'/../../sbin/vendor/autoload.php';

use nulib\app\args\_exceptions;
use nulib\app\cli\Application;
use nulib\app\config;
use nulib\cl;
use nulib\mail\mailer;
use nulib\mail\MailTemplate;
use nulib\output\msg;
use nulib\php\time\DateTime;
use nulib\php\time\Elapsed;
use nulib\php\types\vbool;

Application::run(new class extends Application {
  const ARGS = [
    "merge" => parent::ARGS,
    "purpose" => "Envoyer un mail de notification à la fin de l'importation",

    ["-t::", "--test", "value" => "success",
      "help" => <<<EOT
Envoyer un mail de test. Par défaut, le type sélectionné est 'success'
Il est possible de sélectionner un autre type avec un argument optionnel e.g --test=error ou --test=critical
EOT,
    ],
  ];

  const TESTS = [
    "s" => "success",
    "e" => "error",
    "c" => "critical",
  ];

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

  private function sendmail(array $mail, ?string $importLog, ?array $to, ?array $cc, ?string $from): void {
    $mailer = mailer::build($to, $mail["subject"], $mail["body"], $cc, null, $from);
    if ($this->test && $mailer->SMTPDebug == 0) $mailer->SMTPDebug = 4;
    if ($importLog !== null) $mailer->addAttachment($importLog, "import.log");
    mailer::_send($mailer);
  }

  private ?string $test = null;

  function main() {
    $disabled = vbool::with(config::k("disabled"));
    $requireCron = vbool::with(config::k("require_cron"));

    $test = $this->test;
    if ($test !== null) {
      $profile = "prod";
      $dateDeb = $dateFin = new DateTime();
      $isCron = $isDownload = $isImport = $isAddons = true;
      $criticalError = $haveErrors = false;
      $importLog = null;
      switch (cl::get(self::TESTS, $test, $test)) {
      case "critical":
        $criticalError = true;
        break;
      case "error":
        $haveErrors = true;
        break;
      case "success":
        break;
      default:
        throw _exceptions::forbidden_value("--test=$test", null, ["success", "error", "critical"]);
      }
      msg::set_verbosity("debug");

      if ($disabled) {
        msg::warning("Avec le paramètre disabled:true, aucun mail ne sera envoyé après les imports");
        msg::note("si vous souhaitez activer l'envoi des mails, supprimez ou commentez la ligne disabled:true");
      } else {
        if ($requireCron) {
          msg::info("Avec le paramètre require_cron:true, les mails ne seront envoyés que lors de l'import de la planification quotidienne");
        } else {
          msg::info("Avec le paramètre require_cron:false, les mails seront systématiquement envoyés après chaque import");
        }
      }
      msg::section("Envoi du mail de test");
    } else {
      # ne pas envoyer de mail si c'est désactivé
      if ($disabled) return;

      # ne pas envoyer de mail si on n'est pas dans le cadre de la planification
      # et que require_cron==true
      $isCron = self::get_bool("TEM_CRON");
      if ($requireCron && !$isCron) return;

      $profile = self::get_string("APP_PROFILE");
      $dateDeb = self::get_datetime("DATE_DEB");
      $dateFin = self::get_datetime("DATE_FIN");
      $isDownload = self::get_bool("TEM_DOWNLOAD");
      $isImport = self::get_bool("TEM_IMPORT");
      $isAddons = self::get_bool("TEM_ADDONS");
      $criticalError = self::get_bool("CRITICAL_ERROR");
      $haveErrors = self::get_bool("HAVE_ERRORS");
      $importLog = self::get_string("IMPORT_LOG");
    }
    if ($dateDeb !== null && $dateFin !== null) {
      $duree = Elapsed::format_delay($dateDeb, $dateFin);
    } else {
      $duree = null;
    }

    if ($criticalError) $template = config::l("critical");
    elseif ($haveErrors) $template = config::l("error");
    else $template = config::l("success");
    $mail = (new MailTemplate($template))->eval([
      "profile" => $profile,
      "date_debut" => $dateDeb,
      "date_fin" => $dateFin,
      "duree" => $duree,
      "is_cron" => $isCron,
      "is_download" => $isDownload,
      "is_import" => $isImport,
      "is_addons" => $isAddons,
      "critical_error" => $criticalError,
      "have_errors" => $haveErrors,
    ]);

    $from = config::k("from");
    $cc = cl::withn(config::k("cc"));
    # faire deux mails différents pour to_error et to: les règles de classement
    # ne sont sans doute pas les mêmes pour ces destinataires
    $errorTo = cl::withn(config::k("to_error"));
    if ($errorTo && ($criticalError || $haveErrors)) {
      $this->sendmail($mail, $importLog, $errorTo, $cc, $from);
    }
    $allTo = cl::withn(config::k("to"));
    if ($allTo) {
      $this->sendmail($mail, $importLog, $allTo, $cc, $from);
    }
  }
});
