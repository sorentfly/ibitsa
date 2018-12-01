<?

# |--- General config defines
if (!defined('_ENGINE_GENERAL')) {
    define('_ENGINE_GENERAL',               TRUE); # |>--- Self-stub


    defined('_ENGINE')                  ||
    define('_ENGINE',                       TRUE);

    defined('_ENGINE_REQUEST_START')    ||
    define('_ENGINE_REQUEST_START',         microtime(true));                                               # The time, end user has sent current request

    defined('_ENGINE_CONF__GLOBAL')     ||
    define('_ENGINE_CONF__GLOBAL',          realpath(dirname(__FILE__)) . '/application/settings/global.php');    # Path to the global configuration file

    if (file_exists(_ENGINE_CONF__GLOBAL)) {
        include_once _ENGINE_CONF__GLOBAL;
    } else {
        die("CONFIG FILE ERROR.");
    }
}

# |---
if (!defined('_ENGINE_APPLICATION')) {
    define('_ENGINE_APPLICATION',              TRUE); # |>--- Self-stub

    # |---- Personal information

    # |>---- Personal info
    defined('_DEVELOPER_EMAIL')            ||
    define('_DEVELOPER_EMAIL',                 'TO_CHANGE');                                                            # Email of a developer

    # |>---- Backend configuration
    # |>---- Cache
    # |>---- _ENGINE_CACHE_MODE_FILE or _ENGINE_CACHE_MODE_MEMCACHED
    defined('_DEVELOPER__CACHE')           ||
    define('_DEVELOPER__CACHE',                 'TO_CHANGE');                                                           # Way to cache an application. May be any of [File/Memcached]

    # |>---- Environment
    defined('_DEVELOPER__ENV_MODE')        ||
    define('_DEVELOPER__ENV_MODE',              _ENGINE_ENVIRONMENT_MODE_DEVELOPMENT);                                  # Environment mode of an application. May be any of [Development/Production]
    # |>---- Database configuration
    defined('_DEVELOPER__DB_HOST')         ||
    define('_DEVELOPER__DB_HOST',              'TO_CHANGE');                                                            # Host a DB locates
    defined('_DEVELOPER__DB_USER')         ||
    define('_DEVELOPER__DB_USER',              'TO_CHANGE');                                                            # Username of DB account
    defined('_DEVELOPER__DB_PASSWORD')     ||
    define('_DEVELOPER__DB_PASSWORD',          'TO_CHANGE');                                                            # Password of DB account
    defined('_DEVELOPER__DB_SCHEME')       ||
    define('_DEVELOPER__DB_SCHEME',            'TO_CHANGE');                                                            # Scheme of DB account
    defined('_DEVELOPER__DB_CHARSET')      ||
    define('_DEVELOPER__DB_CHARSET',           _ENGINE__DB_CHARSET);                                                    # Charset, DB is used to work with
    defined('_DEVELOPER__DB_NS_ADAPTER')   ||
    define('_DEVELOPER__DB_NS_ADAPTER',        _ENGINE__DB_NS_ADAPTER);                                                 # Adapter for php namespace to use query builder
    defined('_DEVELOPER__DB_CLASS_ADAPTER')||
    define('_DEVELOPER__DB_CLASS_ADAPTER',     _ENGINE__DB_CLASS_ADAPTER);                                              # Adapter for php class to use query builder
    defined('_DEVELOPER__DB_PREFIX')      ||
    define('_DEVELOPER__DB_PREFIX',            '');                                                                     # Table prefix each query prepends


    # |>---- Frontend configuration

    # |---- Personal information [END]
}