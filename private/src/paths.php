<?php

$DS = DIRECTORY_SEPARATOR;
define('PRIVATE_PATH', realpath(__DIR__ . "$DS..") . $DS);
define('PUBLIC_PATH', realpath(PRIVATE_PATH . "$DS..") . $DS . "htdocs" . $DS);
define('VENDOR_PATH', PRIVATE_PATH . "vendor$DS");
define('SRC_PATH', PRIVATE_PATH . "src$DS");
