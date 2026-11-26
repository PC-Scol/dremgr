<?php
require __DIR__.'/../../sbin/vendor/autoload.php';

use nulib\app\config;
use nulib\app\app;

config::configure(config::CONFIGURE_INITIAL_ONLY);
if (app::is_debug() || app::is_devel()) phpinfo();
