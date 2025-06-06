<?php
namespace app\app;

use Exception;
use nur\A;
use nur\F;
use nur\path;
use nur\txt;
use nur\v\bs3\vc\CNavTabs;
use nur\v\http;
use nur\v\page;
use nur\v\vp\NavigablePage;

class ANavigablePage extends NavigablePage {
  const CSS = ["dre.css"];
  const NAVBAR_OPTIONS = [
    "brand" => [
      "<img src='brand.png' width='50' height='50' alt='Logo'/>",
      "<span style='margin-right: 1em;'>&nbsp;<b>DRE - Données PEGASE</b></span>",
    ],
    "show_brand" => "asis",
  ];
  const REQUIRE_AUTH = false;
  const REQUIRE_AUTHZ = false;
  const REQUIRE_PERM = "connect";

  protected function resolveProfiles() {
    $profiles = explode(" ", getenv("APP_PROFILES"));
    $this->profiles = $profiles;

    $cprofile = F::get("p");
    if (!$cprofile) $cprofile = A::first($profiles);
    $this->profile = $cprofile;

    $profileTabs = [];
    foreach ($profiles as $iprofile) {
      $profileTabs[$iprofile] = [
        txt::upper1($iprofile),
        "url" => page::self(["p" => $iprofile]),
      ];
    }
    $this->profileTabs = $profileTabs;
  }

  /** @var array */
  protected $profiles;

  protected $profile;

  /** @var array */
  protected $profileTabs;

  protected function printProfileTabs(): void {
    new CNavTabs($this->profileTabs, $this->profile);
  }

  protected $haveContent = true;
  function haveContent(): bool {
    return $this->haveContent;
  }

  function download(string $dldir, array $files): bool {
    $dl = F::get("dl");
    if (!$dl) return false;
    # télécharger un fichier
    $dl = path::filename($dl);
    if (!array_key_exists($dl, $files)) {
      throw new Exception("$dl: fichier invalide");
    }
    $dl = $files[$dl];
    if ($dl["isa_file"]) {
      error_log($dldir); #XX
      # c'est un fichier
      $file = $dl["name"];
      header("x-sendfile: $dldir/$file");
      http::content_type();
      http::download_as($file);
    } else {
      # c'est un lien
      http::redirect($dl["url"], false);
    }
    $this->haveContent = false;
    return true;
  }
}
