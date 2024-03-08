<?php
require __DIR__.'/../sbin/vendor/autoload.php';

use app\config\cdefaults;
use app\config\cprod;
use app\vp\IndexPage;
use nur\config;
use nur\config\ArrayConfig;
use nur\msg;
use nur\session;
use nur\v\bs3\Bs3Messenger;
use nur\v\bs3\Bs3PageContainer;
use nur\v\page;
use nur\v\route;
use nur\v\vp\AppHealthcheckPage;

config::set_fact("nur/v-bs3");
config::init_configurator(new class {
  const APPCODE = "dre";

  function configure__initial_config() {
    config::init_appcode(self::APPCODE);
    config::add(cdefaults::class);
    config::add(new ArrayConfig(["app" => [
      "url" => getenv("BASE_URL"),
    ]]));
    config::add(cprod::class, config::PROD);
  }

  function configure__initial_vbs3(): void {
    page::set_container_class(Bs3PageContainer::class);
    msg::set_messenger_class(Bs3Messenger::class, true);
  }

  function configure__routes() {
    route::add(["_hk.php", AppHealthcheckPage::class]);
    route::add(["index.php", IndexPage::class]);
    route::add(["", IndexPage::class, route::MODE_PACKAGE]);
  }

  function configure__initial_session() {
    # 4h de session par défaut
    # cf php/conf.d/session.ini si cette valeur est modifiée
    session::set_duration(4 * 60 * 60);
  }
});

page::render();
