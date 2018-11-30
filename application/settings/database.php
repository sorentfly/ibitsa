<?
defined('_ENGINE') or die(__BROAD_ERROR__ENGINE);


defined('_ENGINE_DATABASE_CORE_CONF')   ||
define('_ENGINE_DATABASE_CORE_CONF',
[
    'adapter' => 'pdo_mysql',
    'params' => [
        'host'              => _DEVELOPER__DB_HOST,
        'username'          => _DEVELOPER__DB_USER,
        'password'          => _DEVELOPER__DB_PASSWORD,
        'dbname'            => _DEVELOPER__DB_SCHEME,
        'charset'           => _DEVELOPER__DB_CHARSET,
        'adapterNamespace'  => _DEVELOPER__DB_NS_ADAPTER,
    ],
    'isDefaultTableAdapter' => TRUE,
    'tablePrefix'           => _DEVELOPER__DB_PREFIX,
    'tableAdapterClass'     => _DEVELOPER__DB_CLASS_ADAPTER,
]);

return _ENGINE_DATABASE_CORE_CONF;