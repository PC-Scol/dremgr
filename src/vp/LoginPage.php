<?php
namespace app\vp;

use nur\v\vp\AInitAuthzPage;
use nur\v\vp\TAuthzLoginPage;

class LoginPage extends AInitAuthzPage {
  use TAuthzLoginPage;

  const TITLE = "Connexion DREmgr";
}
