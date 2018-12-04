<?
if (!defined('_ENGINE_GENERAL')) {
    define('_ENGINE_GENERAL',               1); # |>--- Self-stub

    defined('DS')                   ||
    define('DS',                            '/');

    defined('APPLICATION_PATH')     ||
    define('APPLICATION_PATH',              realpath(dirname(__FILE__)));

    defined('CONFIG__DB_HOST')      ||
    define('CONFIG__DB_HOST',               'localhost');
    defined('CONFIG__DB_SCHEME')    ||
    define('CONFIG__DB_SCHEME',             'bitsa_dev');
    defined('CONFIG__DB_USER_NAME') ||
    define('CONFIG__DB_USER_NAME',          'root');
    defined('CONFIG__DB_USER_PASS') ||
    define('CONFIG__DB_USER_PASS',          '');
    defined('CONFIG__DB_CHARSET')   ||
    define('CONFIG__DB_CHARSET',            'utf8mb4');

    defined('CONNECT__DB')          ||
    define('CONNECT__DB',                   APPLICATION_PATH . DS . 'connectDB.php');

    defined('ERRORS')               ||
    define('ERRORS',                        0);
}
ini_set('display_errors', ERRORS);