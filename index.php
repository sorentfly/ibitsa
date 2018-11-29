<?php
# |- Read config file #to build an environment
$config_php = dirname(__FILE__).'/config.php';
if (file_exists($config_php))
    include $config_php;
else
    throw new Exception('Configuration file does not exists.
    Link it with command \'ln {your-name}.config.php config.php\'.');

# |- Require composer dependencies
include APPLICATION_COMPOSER;

header('Last-Modified: ' . date('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT');


# |- Redirect from sub-domain www.*
if (strpos($_SERVER['HTTP_HOST'], 'www.')===0){
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    header('Location: '.$protocol.'://'.str_replace('www.', '',$_SERVER['HTTP_HOST']) .$_SERVER['REQUEST_URI']);
    die();
}

# |- Start trace
# |---- xdebug
if (    !empty($_SERVER['_ENGINE_TRACE_ALLOW'])
    &&  extension_loaded('xdebug')
) {
    xdebug_start_trace();
}
# |---- xhprof
if (    !empty($_SERVER['_ENGINE_XHPROF_ALLOW'])
    &&  extension_loaded('xhprof')
) {
    xhprof_enable();
}

include APPLICATION_PATH_COR . DS . ENGINE_ABSTRACT_INDEX;
