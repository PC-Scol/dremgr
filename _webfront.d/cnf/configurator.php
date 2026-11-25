<?php
namespace app\config;

use nulib\app\config;
use nulib\app\config\EnvConfig;
use nulib\app\config\ref_profiles;
use nulib\web\authz;
use nulib\web\authz\CasAuthzManager;
use nulib\web\authz\ExtAuthzManager;
use nulib\web\authz\FormAuthzManager;
use nulib\web\session;

class configurator {
  const AUTH_ANY = \AUTH_CAS || \AUTH_BASIC;

  function configure__initial_config() {
    config::add(cdefaults::class);
    config::add(new EnvConfig());
    config::add(cprod::class, ref_profiles::PROD);
    #config::add(ctest::class, ref_profiles::TEST);
  }

  function configure__initial_authnz() {
    if (\AUTH_CAS) authz::set_manager_class(CasAuthzManager::class);
    elseif (\AUTH_BASIC) authz::set_manager_class(ExtAuthzManager::class);
    else authz::set_manager_class(FormAuthzManager::class);
  }

  function configure__initial_session() {
    # 4h de session par défaut
    # cf ../web.d/php/conf.d/session.ini si cette valeur est modifiée
    session::set_duration(4 * 60 * 60);
  }
}
