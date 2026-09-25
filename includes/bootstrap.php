<?php

declare(strict_types=1);

define('HW_APP', true);
define('HW_ROOT', dirname(__DIR__));

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

require_once HW_ROOT . '/includes/config.php';
require_once HW_ROOT . '/includes/security.php';
require_once HW_ROOT . '/includes/store.php';
require_once HW_ROOT . '/includes/mailer.php';

hw_boot_session();
hw_send_security_headers();
hw_ensure_storage();
