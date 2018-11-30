<?
defined('_ENGINE') or die(__BROAD_ERROR__ENGINE);


defined('_ENGINE_MAIL_CORE_CONF')   ||
define('_ENGINE_MAIL_CORE_CONF',
[
    'class' => 'Zend_Mail_Transport_Smtp',
    'args'  => [
        '',
        [
            'ssl' => 'tls',
            'port' => 25,
            'auth' => 'login',
            'username' => '__',
            'password' => '__'
        ]
    ]
]);

return _ENGINE_MAIL_CORE_CONF;