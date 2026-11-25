<?php
require __DIR__.'/../../sbin/vendor/autoload.php';

use nulib\os\env;

echo env::get("PATH");
