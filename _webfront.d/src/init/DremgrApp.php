<?php
namespace app\init;

use app\pages\IndexPage;
use app\pages\LoginPage;
use nulib\app\web\Application;
use nulib\web\authz\pages\ACasauthPage;
use nulib\web\authz\pages\AExtauthPage;
use nulib\web\authz\pages\ALogoutPage;
use nulib\web\base\AHealthcheckPage;

class DremgrApp extends Application {
  const ROUTES = [
    ["_hk.php", AHealthcheckPage::class],
    ["_casauth.php", ACasauthPage::class],
    ["_extauth.php", AExtauthPage::class],
    ["_logout.php", ALogoutPage::class],
    ["login.php", LoginPage::class],
    ["", IndexPage::class, self::MODE_PACKAGE2_PI],
  ];
}
