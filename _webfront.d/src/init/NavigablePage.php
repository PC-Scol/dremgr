<?php
namespace app\init;

use app\config\configurator;
use Exception;
use nulib\cl;
use nulib\os\env;
use nulib\os\path;
use nulib\txt;
use nulib\web\base\ANavigablePage;
use nulib\web\bs\BsRenderer;
use nulib\web\bs\lists\CNav;
use nulib\web\http;
use nulib\web\page;
use nulib\web\params\F;
use nulib\web\vo;

class NavigablePage extends ANavigablePage {
  const RENDERER_CLASS = BsRenderer::class;
  const FAVICON_URL = "favicon.ico";
  const APPICON_URL = "appicon.png";

  # il faut TOUJOURS avoir une session même si pas d'authentification
  const USE_SESSION = true;
  const REQUIRE_AUTH = configurator::AUTH_ANY;

  const CSS = [
    "nulib-base/bs-compact.css",
    "dremgr.css?1"
  ];
  const NAVBAR_PARAMS = [
    //"container" => "fluid",
    "brand" => [
      "<img class='logo' src='logo.png' alt='DREmgr'/>",
      "<span class='ms-2' style='font-variant: small-caps;'>DREmgr</span>",
    ],
  ];
  const CONTAINER_PARAMS = [
    //"type" => "fluid",
  ];

  protected ?array $profiles = null;

  protected ?string $profile = null;

  protected ?array $profileTabs = null;

  protected function resolveProfiles() {
    $profiles = explode(" ", env::get("APP_PROFILES"));
    $this->profiles = $profiles;

    $cprofile = F::get("p");
    if (!$cprofile) $cprofile = cl::first($profiles);
    $this->profile = $cprofile;

    $profileTabs = [];
    foreach ($profiles as $iprofile) {
      $profileTabs[$iprofile] = [
        txt::upper1($iprofile),
        "href" => page::self(["p" => $iprofile]),
      ];
    }
    $this->profileTabs = $profileTabs;
  }

  protected function printProfileTabs(): void {
    vo::print(new CNav($this->profileTabs, [
      "select" => $this->profile,
    ]));
  }

  function download(array $files): bool {
    $dl = F::get("dl");
    if (!$dl) return false;
    # télécharger un fichier
    $dl = path::filename($dl);
    if (!array_key_exists($dl, $files)) {
      throw new Exception("$dl: fichier invalide");
    }
    $dl = $files[$dl];
    if ($dl["isa_file"]) {
      # c'est un fichier
      header("x-sendfile: {$dl["file"]}");
      http::content_type();
      http::download_as($dl["filename"]);
    } else {
      # c'est un lien
      http::redirect($dl["url"], false);
    }
    return true;
  }
}
