<?php
namespace app\pages;

use nulib\web\authz\pages\ALoginPage;
use nulib\web\bs\cards\CardBuilder;
use nulib\web\ly;
use nulib\web\page;
use nulib\web\vo;

class LoginPage extends ALoginPage {
  const TITLE = "Connexion DREmgr";

  function print(): void {
    page::no_cache();
    ly::row(["class" => "justify-content-center"]);
    vo::h1([
      "class" => "text-center",
      self::TITLE,
    ]);
    ly::col(["sm" => 6]);
    vo::start($card = new CardBuilder([
      "header" => ["Connexion"],
      "body" => ["Vous devez vous connecter pour accéder à cette application"],
    ]));
    $this->component->print();
    vo::end($card);
  }
}
