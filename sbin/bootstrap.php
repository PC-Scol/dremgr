<?php

use app\config\configurator;
use nulib\app\app;
use nulib\app\config;
use nulib\os\env;

define("AUTH_CAS", env::bool("AUTH_CAS"));
define("AUTH_BASIC", env::bool("AUTH_BASIC"));

app::init([
  "projdir" => __DIR__."/..",
  "projcode" => "dremgr",
], true);
config::init_configurator(new configurator());
