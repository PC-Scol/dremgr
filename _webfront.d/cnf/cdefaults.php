<?php
namespace app\config;

use app\pages\DumpsPage;
use app\pages\IndexPage;
use nulib\web\bs\bsicon;

class cdefaults {
  const APP = [
    "debug" => false,

    "menu" => [
      "items" => [
        ["Accueil", IndexPage::class, "accesskey" => "h"],
        ["Dumps", DumpsPage::class, "accesskey" => "d"],
        [[bsicon::REFRESH, "&nbsp;Actualiser"], "", "accesskey" => "a"],
      ],
    ],
  ];
}
