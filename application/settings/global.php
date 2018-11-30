<?
defined('_ENGINE') or die('Global config can not be included.');

#
##
###
####
#   Paths of an application for comfortable access.
########################################################################################################################
if (!defined('ENGINE_CONF__PATH_CONSTANTS')) {
    define('ENGINE_CONF__PATH_CONSTANTS',   TRUE); # |>--- Self-stub




    # |---- Separators
    defined('DS')                       ||
    define('DS',                            DIRECTORY_SEPARATOR);                           # Does/This
    defined('PS')                       ||
    define('PS',                            PATH_SEPARATOR);                                # Does:This
    # |---- Separators [END]


    # |---- Helpers
    defined('DOT')                      ||
    define('DOT',                           '.');                                           # Does/This [.]
    defined('UP')                       ||
    define('UP',                            DS . DOT . DOT);                                # Does/This [/..]
    defined('DOWN')                     ||
    define('DOWN',                          DS . DOT . DOT);                                # Does/This [../]
    # |---- Helpers [END]



    # |---- Paths
    defined('APPLICATION_PATH')         ||
    define('APPLICATION_PATH',              realpath(dirname(__FILE__) . UP . UP));# |_____Root;

    defined('APPLICATION_PATH_COR')     ||
    define('APPLICATION_PATH_COR',          APPLICATION_PATH . DS . 'application');         # |_____Core;

    defined('APPLICATION_PATH_PRV')     ||
    define('APPLICATION_PATH_PRV',          APPLICATION_PATH . DS . 'private');             # |_____Private_data;

    defined('APPLICATION_PATH_PRV_EXT') ||
    define('APPLICATION_PATH_PRV_EXT',      APPLICATION_PATH_PRV . DS . 'externals');       # |_____External_solutions_used_in_an_application;

    defined('APPLICATION_PATH_PUB')     ||
    define('APPLICATION_PATH_PUB',          APPLICATION_PATH . DS . 'public');              # |_____Public_data;

    defined('APPLICATION_PATH_TMP')     ||
    define('APPLICATION_PATH_TMP',          APPLICATION_PATH . DS . 'temporary');           # |_____Temporary_data;

    defined('APPLICATION_PATH_CACHE')   ||
    define('APPLICATION_PATH_CACHE',        APPLICATION_PATH_TMP . DS . 'cache');           # |_____Application_cache;

    defined('APPLICATION_PATH_LOG')     ||
    define('APPLICATION_PATH_LOG',          APPLICATION_PATH_TMP . DS . 'log');             # |_____Application_logs;

    defined('APPLICATION_PATH_BTS')     ||
    define('APPLICATION_PATH_BTS',          APPLICATION_PATH_COR . DS . 'bootstraps');      # |_____Bootstraps_of_an_application;

    defined('APPLICATION_PATH_LIB')     ||
    define('APPLICATION_PATH_LIB',          APPLICATION_PATH_COR . DS . 'libraries');       # |_____Heavy_backend_libraries;

    defined('APPLICATION_PATH_MOD')     ||
    define('APPLICATION_PATH_MOD',          APPLICATION_PATH_COR . DS . 'modules');         # |_____Modules_of_an_application;

    defined('APPLICATION_PATH_PLU')     ||
    define('APPLICATION_PATH_PLU',          APPLICATION_PATH_COR . DS . 'plugins');         # |_____Plugins_of_an_application;

    defined('APPLICATION_PATH_SET')     ||
    define('APPLICATION_PATH_SET',          APPLICATION_PATH_COR . DS . 'settings');        # |_____Application_settings_path;

    defined('APPLICATION_COMPOSER')     ||
    define('APPLICATION_COMPOSER',          APPLICATION_PATH . DS . 'vendor');                   # |_____Composer_path;

    defined('APPLICATION_COMPOSER_AL')  ||
    define('APPLICATION_COMPOSER_AL',       APPLICATION_COMPOSER . DS . 'autoload.php');    # |_____Composer_autoload_path;
    # |---- Paths [END]
}
########################################################################################################################



#
##
###
####
#   General config
########################################################################################################################
if (!defined('ENGINE_CONF__GENERAL')) {
    define('ENGINE_CONF__GENERAL',          TRUE); # |>--- Self-stub


    defined('APPLICATION_NAME')         ||
    define('APPLICATION_NAME',              'Core');
    defined('_ENGINE_ADMIN_NEUTER')     ||
    define('_ENGINE_ADMIN_NEUTER',          FALSE);
    defined('_ENGINE_NO_AUTH')          ||
    define('_ENGINE_NO_AUTH',               FALSE);
    defined('_ENGINE_SSL')              ||
    define('_ENGINE_SSL',
                                            isset($_SERVER["REQUEST_SCHEME"])
                                            && $_SERVER["REQUEST_SCHEME"] == 'https'
                                            || !empty($_SERVER['HTTP_X_HTTPS'])
                                            || isset($_SERVER["HTTPS"])
                                            && $_SERVER["HTTPS"] == 'on'
                                            || isset($_SERVER['SERVER_PORT'])
                                            && ( '443' == $_SERVER['SERVER_PORT'] )
    );
    defined('_ENGINE_CONSOLE_APP')      ||
    define('_ENGINE_CONSOLE_APP',           FALSE);
    defined('_ENGINE_R_REWRITE')        ||
    define('_ENGINE_R_REWRITE',             FALSE);
    defined('_ENGINE_R_CONF')           ||
    define('_ENGINE_R_CONF',                FALSE);
    defined('BITSA_SOURCE')             ||
    define('BITSA_SOURCE',                  'https://github.com/sorentfly/ibitsa');
    defined('BITSA_SITE_LOCALE')        ||
    define('BITSA_SITE_LOCALE',             'bitsa.loc');
    defined('BITSA_SITE')               ||
    define('BITSA_SITE',                    BITSA_SITE_LOCALE);

    defined('OLD_FILE_LIMIT_DATE')      ||
    define('OLD_FILE_LIMIT_DATE',           '2018-11-30  00:00:00' );

    # |---- Patterns
    defined('MOSCOW_TIMEZONE_OFFSET')   ||
    define('MOSCOW_TIMEZONE_OFFSET',        10800);
    defined('NAMES_HTML_PATTERN')       ||
    define('NAMES_HTML_PATTERN',            trim(json_encode('^(([A-z]+([-\s]?[A-z]+)?){1}|([А-яЁё]+([-\s]?[А-яЁё]+)?){1})$')));
    defined('NAMES_PATTERN')            ||
    define('NAMES_PATTERN',                 '#^(([A-z]+([-\s]?[A-z]+)?){1}|([А-яЁё]+([-\s]?[А-яЁё]+)?){1})$#u');

    defined('USERNAME_PATTERN')         ||
    define('USERNAME_PATTERN',              "#^([a-zA-Z][-\s']{0,1}([a-zA-Z]+[-\s']{0,1})*[a-zA-Z])$|^([а-яА-ЯёЁ][-\s']{0,1}([а-яА-ЯёЁ]+[-\s']{0,1})*[а-яА-ЯёЁ])$#u");
    defined('USERNAME_PATTERN_HTML')    ||
    define('USERNAME_PATTERN_HTML',         trim(json_encode("^([a-zA-Z][- ']{0,1}([a-zA-Z]+[- ']{0,1})*[a-zA-Z])$|^([А-яЁё][- ']{0,1}([А-яЁё]+[- ']{0,1})*[А-яЁё])$"), '"') );
    defined('USERNAME_PATTERN_RU')      ||
    define('USERNAME_PATTERN_RU',           "#^([а-яА-ЯёЁ][-\s']{0,1}([а-яА-ЯёЁ]+[-\s']{0,1})*[а-яА-ЯёЁ])$#u");
    defined('USERNAME_PATTERN_HTML_RU') ||
    define('USERNAME_PATTERN_HTML_RU',      trim(json_encode("^([А-яЁё][- ']{0,1}([А-яЁё]+[- ']{0,1})*[А-яЁё])$"), '"') );
    # |---- Patterns [END]


    # |---- Backend
    defined('_ENGINE__DB_CHARSET')                  ||
    define('_ENGINE__DB_CHARSET',                           'utf8mb4');
    defined('_ENGINE__DB_NS_ADAPTER')               ||
    define('_ENGINE__DB_NS_ADAPTER',                        'Zend_Db_Adapter');
    defined('_ENGINE__DB_CLASS_ADAPTER')            ||
    define('_ENGINE__DB_CLASS_ADAPTER',                     'Engine_Db_Table');
    defined('_ENGINE__DB_PREFIX')                   ||
    define('_ENGINE__DB_PREFIX',                            'engine_');
    defined('_ENGINE_CACHE_MODE_FILE')              ||
    define('_ENGINE_CACHE_MODE_FILE',                       'File');
    defined('_ENGINE_CACHE_MODE_MEMCACHED')         ||
    define('_ENGINE_CACHE_MODE_MEMCACHED',                  'Memcached');
    defined('_ENGINE_ENVIRONMENT_MODE_PRODUCTION')  ||
    define('_ENGINE_ENVIRONMENT_MODE_PRODUCTION',           'Production');
    defined('_ENGINE_ENVIRONMENT_MODE_DEVELOPMENT') ||
    define('_ENGINE_ENVIRONMENT_MODE_DEVELOPMENT',          'Development');
    # |---- Backend [END]
}
########################################################################################################################



#
##
###
####
#   Objects specifications current application may rely to.
########################################################################################################################
if (!defined('ENGINE_CONF__NAMES')) {
    define('ENGINE_CONF__NAMES',            TRUE); # |>--- Self-stub


    # |---- Name constants broadly used in app
    if (!defined('__BROAD_INDEX')) {
        define('__BROAD_INDEX',             TRUE); # |>--- Self-stub

        define('__BROAD_INDEX_NAME',        'index.php');
        define('__BROAD_INDEX_FOLDER',      [
                                                APPLICATION_PATH,
                                                APPLICATION_PATH_COR
                                            ]);
    }

    if (!defined('__BROAD_DATABASE')) {
        define('__BROAD_DATABASE',          TRUE); # |>--- Self-stub

        define('__BROAD__DB_TABLE_PREFIX',        '');
    }

    if (!defined('__BROAD_IMAGE')) {
        define('__BROAD_IMAGE',             TRUE); # |>--- Self-stub

        define('__BROAD_IMAGE_FOLDER',      APPLICATION_PATH_PUB . DS . 'images');
        define('__BROAD_IMAGE_EXTENSIONS',  'png,jpg,gif,jpeg,heif,webp,exif,ppm,pgm,pbm,pnm,bmp,tiff');
    }

    if (!defined('__BROAD_AUDIO')) {
        define('__BROAD_AUDIO',             TRUE); # |>--- Self-stub

        define('__BROAD_AUDIO_FOLDER',      APPLICATION_PATH_PUB . DS . 'audios');
        define('__BROAD_AUDIO_EXTENSIONS',  'wv,wma,wav,vox,mp3');
    }

    if (!defined('__BROAD_VIDEO')) {
        define('__BROAD_VIDEO',             TRUE); # |>--- Self-stub

        define('__BROAD_VIDEO_FOLDER',      APPLICATION_PATH_PUB . DS . 'videos');
        define('__BROAD_VIDEO_EXTENSIONS',  'webm,mkv,flv,vob,ogv,ogg,gifv,avi,mov,wmv,mp4,m4p,m4v,mpg,mp2,mpeg,mpe,mpv,m4v,3gp,flv,f4v,f4p,f4a,f4b');
    }

    if (!defined('__BROAD_ERROR')) {
        define('__BROAD_ERROR',             TRUE); # |>--- Self-stub

        define('__BROAD_ERROR_FOLDER',      APPLICATION_PATH_TMP . DS . 'errors');

        define('__BROAD_ERROR__ENGINE',     'Access Denied due to the inappropriate request.');
    }
    # |---- Name constants broadly used in app [END]
}


#
##
###
####
#   Files to represent default data while there is no custom data from users
#   Files essential for current system environment
########################################################################################################################
if (!defined('ENGINE_CONF__FILES')) {
    define('ENGINE_CONF__FILES',             TRUE); # |>--- Self-stub


    defined('DEFAULT_NOPHOTO')          ||
    define('DEFAULT_NOPHOTO',               __BROAD_IMAGE_FOLDER . DS . 'user' . DS . 'avatar.png');


    defined('JQUERY_LIB')               ||
    define('JQUERY_LIB',                    APPLICATION_PATH_PRV_EXT . DS . 'jquery.min.js');
    defined('JQUERY_UI_LIB')            ||
    define('JQUERY_UI_LIB',                 APPLICATION_PATH_PRV_EXT . DS . 'jquery-ui.min.js');

    defined('ENGINE_FATAL_HANDLER')     ||
    define('ENGINE_FATAL_HANDLER',          APPLICATION_PATH_SET . DS . 'fatal.php');
}
########################################################################################################################



#
##
###
####
#   Frequently used sizes
########################################################################################################################
if (!defined('ENGINE_CONF__SIZES')) {
    define('ENGINE_CONF__SIZES',             TRUE); # |>--- Self-stub


    # |---- Dimensions
    defined('MIN_ADAPTIVE_WIDTH')        ||
    define('MIN_ADAPTIVE_WIDTH',             490);
    defined('GENERAL_CONTENT_WIDTH')     ||
    define('GENERAL_CONTENT_WIDTH',          960);
}
########################################################################################################################

