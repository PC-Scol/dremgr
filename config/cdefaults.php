<?php
namespace app\config;

use app\vp\DumpsPage;
use app\vp\IndexPage;
use nur\v\bs3\Bs3IconManager;

class cdefaults {
  const APP = [
    "debug" => false,

    "menu" => [
      "items" => [
        ["Accueil", IndexPage::class,
          "accesskey" => "h",
        ],
        ["Dumps", DumpsPage::class,
          "accesskey" => "d",
        ],
        [[Bs3IconManager::REFRESH, "&nbsp;Rafraichir"], "",
          "accesskey" => "a",
        ],
      ],
    ],
  ];
}
