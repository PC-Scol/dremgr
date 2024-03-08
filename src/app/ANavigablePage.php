<?php
namespace app\app;

use nur\A;
use nur\F;
use nur\txt;
use nur\v\bs3\vc\CNavTabs;
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
}
