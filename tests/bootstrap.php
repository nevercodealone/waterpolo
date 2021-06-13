<?php
define('BASE_PATH', realpath(dirname(__DIR__)));
$_SERVER['DOCUMENT_ROOT'] = BASE_PATH;

use Symfony\Component\Dotenv\Dotenv;

require '/var/www/html/vendor/autoload.php';

if (file_exists('/var/www/html//config/bootstrap.php')) {
    require '/var/www/html/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
