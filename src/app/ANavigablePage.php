<?php
namespace app\app;

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
}
