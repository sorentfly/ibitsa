<?php
if (!defined('_ENGINE_GENERAL')) {
    define('_ENGINE_GENERAL',               1); # |>--- Self-stub


    defined('_ENGINE')                  ||
    define('_ENGINE',                       true);

    # The time, end user has sent current request
    defined('_ENGINE_REQUEST_START')    ||
    define('_ENGINE_REQUEST_START',         microtime(true));
}


if (!defined('ENGINE_PATH_CONSTANTS')) {
    define('ENGINE_PATH_CONSTANTS',         1); # |>--- Self-stub


    # |---- Paths
    defined('DS')                       ||
    define('DS',                            DIRECTORY_SEPARATOR);
    defined('PS')                       ||
    define('PS',                            PATH_SEPARATOR);

    defined('APPLICATION_PATH')         ||
    define('APPLICATION_PATH',              realpath(dirname(__FILE__)));
    defined('APPLICATION_PATH_COR')     ||
    define('APPLICATION_PATH_COR',          APPLICATION_PATH . DS . 'application');
    defined('APPLICATION_PATH_PRV')     ||
    define('APPLICATION_PATH_PRV',          APPLICATION_PATH . DS . 'private');
    defined('APPLICATION_PATH_PRV_EXT') ||
    define('APPLICATION_PATH_PRV_EXT',      APPLICATION_PATH_PRV . DS . 'externals');
    defined('APPLICATION_PATH_PUB')     ||
    define('APPLICATION_PATH_PUB',          APPLICATION_PATH . DS . 'public');
    defined('APPLICATION_PATH_TMP')     ||
    define('APPLICATION_PATH_TMP',          APPLICATION_PATH . DS . 'temporary');

    defined('APPLICATION_PATH_BTS')     ||
    define('APPLICATION_PATH_BTS',          APPLICATION_PATH_COR . DS . 'bootstraps');
    defined('APPLICATION_PATH_LIB')     ||
    define('APPLICATION_PATH_LIB',          APPLICATION_PATH_COR . DS . 'libraries');
    defined('APPLICATION_PATH_MOD')     ||
    define('APPLICATION_PATH_MOD',          APPLICATION_PATH_COR . DS . 'modules');
    defined('APPLICATION_PATH_PLU')     ||
    define('APPLICATION_PATH_PLU',          APPLICATION_PATH_COR . DS . 'plugins');
    defined('APPLICATION_PATH_SET')     ||
    define('APPLICATION_PATH_SET',          APPLICATION_PATH_COR . DS . 'settings');

    defined('APPLICATION_COMPOSER')     ||
    define('APPLICATION_COMPOSER',          APPLICATION_PATH . 'vendor' . DS . 'autoload.php');
    # |---- Paths [END]



    # |---- Name constants broadly used in app
    if (!defined('__BROAD_IMAGE')) {
        define('__BROAD_IMAGE',             1); # |>--- Self-stub

        define('__BROAD_IMAGE_FOLDER',      APPLICATION_PATH_PUB . DS . 'images');
        define('__BROAD_IMAGE_EXTENSIONS',  'png,jpg,gif,jpeg,heif,webp,exif,ppm,pgm,pbm,pnm,bmp,tiff');
    }
    if (!defined('__BROAD_AUDIO')) {
        define('__BROAD_AUDIO',             1); # |>--- Self-stub

        define('__BROAD_AUDIO_FOLDER',      APPLICATION_PATH_PUB . DS . 'audios');
        define('__BROAD_AUDIO_EXTENSIONS',  'wv,wma,wav,vox,mp3');
    }
    if (!defined('__BROAD_VIDEO')) {
        define('__BROAD_VIDEO',             1); # |>--- Self-stub

        define('__BROAD_VIDEO_FOLDER',      APPLICATION_PATH_PUB . DS . 'videos');
        define('__BROAD_VIDEO_EXTENSIONS',  'webm,mkv,flv,vob,ogv,ogg,gifv,avi,mov,wmv,mp4,m4p,m4v,mpg,mp2,mpeg,mpe,mpv,m4v,3gp,flv,f4v,f4p,f4a,f4b');
    }
    if (!defined('__BROAD_ERROR')) {
        define('__BROAD_ERROR',             1); # |>--- Self-stub

        define('__BROAD_ERROR_FOLDER',      APPLICATION_PATH_TMP . DS . 'errors');

        define('__BROAD_ERROR__ENGINE',     'Access Denied due to the inappropriate request.');
    }

    # |---- Name constants broadly used in app [END]



    # |---- Most frequently used files
    defined('DEFAULT_NOPHOTO')          ||
    define('DEFAULT_NOPHOTO',               __BROAD_IMAGE_FOLDER . DS . 'user.png');
    defined('JQUERY_LIB')               ||
    define('JQUERY_LIB',                    APPLICATION_PATH_PRV_EXT . DS . 'jquery.min.js');
    defined('JQUERY_UI_LIB')            ||
    define('JQUERY_UI_LIB',                 APPLICATION_PATH_PRV_EXT . DS . 'jquery-ui.min.js');
    # |---- Most frequently used files [END]



    # |---- Most frequently used abstract file names
    defined('ENGINE_ABSTRACT_INDEX')    ||
    define('ENGINE_ABSTRACT_INDEX',         'index.php');
    # |---- Most frequently used abstract file names [END]

}
if (!defined('ENGINE_CONFIG_CONSTANTS')){
    define('ENGINE_CONFIG_CONSTANTS',       1); # |>--- Self-stub



    # |---- General
    defined('_ENGINE_CONSOLE_APP')      ||
    define('_ENGINE_CONSOLE_APP',           false);
    defined('BITSA_SOURCE')             ||
    define('BITSA_SOURCE',                  'https://github.com/sorentfly/ibitsa');
    defined('BITSA_SITE_LOCALE')        ||
    define('BITSA_SITE_LOCALE',             'bitsa.loc');
    defined('BITSA_SITE')               ||
    define('BITSA_SITE',                    'undefined');

    define('OLD_FILE_LIMIT_DATE', '2018-11-20  00:00:00' );
    # |---- General [END]


    # |---- Personal information
    defined('_LEAD_DEVELOPER_EMAIL')    ||
    define('_LEAD_DEVELOPER_EMAIL',         'stuf.developer@gmail.com');
    #define('')
    # |---- Personal information [END]
}

if (!defined('BITSA_ENGINE_APPLICATION')) {
    define('BITSA_ENGINE_APPLICATION', 1); # |>--- Self-stub



    # |---- Patterns
    define('MOSCOW_TIMEZONE_OFFSET', 10800);
    define('NAMES_HTML_PATTERN', '^(([A-z]+([-\s]?[A-z]+)?){1}|([А-яЁё]+([-\s]?[А-яЁё]+)?){1})$');
    define('NAMES_PATTERN', '/' . NAMES_HTML_PATTERN . '/');
    # |---- Patterns [END]



    # |- Basic setup
    # error_reporting(E_ALL ^ E_DEPRECATED);



    # |- BACKEND
    # |---- Get general config
    $general_tmp = APPLICATION_PATH_SET . DS . 'general.php';
    $generalConfig = file_exists($general_tmp) && !empty($general_tmp) && isset($general_tmp)
        ? include $general_tmp
        : array('environment_mode' => 'production');

    # |---- Maintenance mode
    if (!defined('_ENGINE_R_MAINTENANCE') || _ENGINE_R_MAINTENANCE) {
        if (!empty($generalConfig['maintenance']['enabled']) && !empty($generalConfig['maintenance']['code'])) {
            $code = $generalConfig['maintenance']['code'];
            if (@$_REQUEST['en4_maint_code'] == $code || @$_COOKIE['en4_maint_code'] == $code) {
                if (@$_COOKIE['en4_maint_code'] !== $code) {
                    setcookie('en4_maint_code', $code, time() + (86400 * 7), '/');
                }
            } else {
                echo file_get_contents(APPLICATION_PATH_COR . DS . 'maintenance.html');
                exit();
            }
        }
    }

    # |---- Mode
    $application_env = @$generalConfig['environment_mode'];
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', (
    !empty($_SERVER['_ENGINE_ENVIRONMENT']) ? $_SERVER['_ENGINE_ENVIRONMENT'] : (
    $application_env ? $application_env :
        'production'
    )));



    # |- Setup required include paths; optimized for Zend usage. Most other includes
    # |- will use an absolute path
    set_include_path(
        APPLICATION_PATH_LIB . PS .
        APPLICATION_PATH_LIB . DS . 'PEAR' . PS .
        '.' // get_include_path()
    );

    defined('APPLICATION_NAME') ||
    define('APPLICATION_NAME', 'Core');
    defined('_ENGINE_ADMIN_NEUTER') || define('_ENGINE_ADMIN_NEUTER', false);
    defined('_ENGINE_NO_AUTH') || define('_ENGINE_NO_AUTH', false);
    defined('_ENGINE_SSL') || define('_ENGINE_SSL',
        isset($_SERVER["REQUEST_SCHEME"]) && $_SERVER["REQUEST_SCHEME"] == 'https'
        || !empty($_SERVER['HTTP_X_HTTPS'])
        || isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on'
        || isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )
    );


    # |- FRONTEND
    # |---- Dimensions
    define('MIN_ADAPTIVE_WIDTH', 490);
    define('GENERAL_CONTENT_WIDTH', 960);
}

$cache = array (
    'default_backend' => 'File',
    'frontend' =>
        array (
            'core' =>
                array (
                    'automatic_serialization' => true,
                    'cache_id_prefix' => 'Bitsa_Engine_',
                    'lifetime' => '7200',
                    'caching' => true,
                    'gzip' => true,
                ),
        ),
    'backend' =>
        [
            'File' => [
                'cache_dir' => __DIR__. '/temporary/cache'
            ]
        ],
    'default_file_path' => '/var/www/SocialEngine/temporary/cache',
);

//$cache = array (
//    'default_backend' => 'Memcached',
//    'frontend' =>
//        array (
//            'core' =>
//                array (
//                    'automatic_serialization' => true,
//                    'cache_id_prefix' => 'Engine4_',
//                    'lifetime' => '7200',
//                    'caching' => true,
//                    'gzip' => true,
//                ),
//        ),
//    'backend' =>
//        array (
//            'Memcached' =>
//                array (
//                    'servers' =>
//                        array (
//                            0 =>
//                                array (
//                                    'host' => '127.0.0.1',
//                                    'port' => 11211,
//                                ),
//                        ),
//                    'compression' => false,
//                ),
//        ),
//    'default_file_path' => '/var/www/SocialEngine/temporary/cache',
//);

$mailTransport =  array(
    'class' => 'Zend_Mail_Transport_Smtp',
    'args' => [
        'email-smtp.eu-west-1.amazonaws.com',
        [
            'ssl' => 'tls',
            'port' => 25,
            'auth' => 'login',
            'username' => 'AKIAJYUBQBZQUJ25VISQ',
            'password' => 'AmeTAG2xx7b3LnxCqPCq+r5zQNVVdSgyP4wm7RRYXfOp'
        ]
    ]
);

$database = array (
    'adapter' => 'pdo_mysql',
    'params' =>
        array (
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname' => 'bitsa',
            'charset' => 'utf8mb4',
            'adapterNamespace' => 'Zend_Db_Adapter',
        ),
    'isDefaultTableAdapter' => true,
    'tablePrefix' => 'engine4_',
    'tableAdapterClass' => 'Engine_Db_Table',
);