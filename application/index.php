<?
# Rewrite detection
if (!defined('_ENGINE_R_REWRITE') && 'cli' !== PHP_SAPI) {
    $target = null;
    if (empty($_GET['rewrite']) && 0 !== strpos($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'])) {
        # |- Redirect to index if rewrite not enabled
        $target = $_SERVER['PHP_SELF'];
        $params = $_GET;
        unset($params['rewrite']);
        if (!empty($params)) {
            $target .= '?' . http_build_query($params);
        }
    } else if (isset($_GET['rewrite']) && $_GET['rewrite'] == 2) {
        # |- Redirect to virtual index if rewrite enabled
        $target = str_replace($_SERVER['PHP_SELF'], dirname($_SERVER['PHP_SELF']), $_SERVER['REQUEST_URI']);
    }
    if (null !== $target) {
        header('Location: ' . $target);
        exit();
    }
}

# Update functions if not found
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        }
        else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}
if (!function_exists('is_countable')) {
    function is_countable($var) {
        return is_array($var) || $var instanceof Countable;
    }
}

# |- Setup required include paths; optimized for Zend usage.
# |- Most other includes will use an absolute path
set_include_path(
    APPLICATION_PATH_LIB     . PS .
    APPLICATION_PATH_LIB . DS . 'PEAR'      . PS .
    APPLICATION_COMPOSER                    . PS .
    APPLICATION_PATH_PRV_EXT                . PS .
    '.' # |- get_include_path()
);

if (!_ENGINE_CONSOLE_APP){
    # CROSS DOMAIN AUTH
    # DO NOT REMOVE THIS BLOCK - otherwise http-recursion on login happens.
    if (isset($_GET['crossdomainCookiesAuth']) || !empty($_REQUEST['sessionIdDrag']))
    {
        # |- Allow to get/set cookies from all domains by config
        $domains = include APPLICATION_PATH_SET. DS . 'domains.php';
        $accessControlOrigin = null;
        if (empty($_SERVER['HTTP_REFERER'])){
            $_SERVER['HTTP_REFERER'] = 'http://'.$_SERVER['HTTP_HOST'];
        }
        foreach($domains as $domain)
        {
            $isSSL = false;
            if (mb_strpos($_SERVER['HTTP_REFERER'], 'http://' . $domain['domain'])===0 || ($isSSL = mb_strpos($_SERVER['HTTP_REFERER'], 'https://' . $domain['domain'])===0) )
            {
                $accessControlOrigin = ($isSSL ? 'https://' : 'http://').$domain['domain'];
                break;
            }
        }
        /* Send php session ID to parent window, or rewrite session ID.
         * Warning! It is not secure, for not trusted domains - for IFRAME browser request.
         * Thats why exists check: if ($accessControlOrigin)*/
        if ($accessControlOrigin){
            # |- Application
            header("Access-Control-Allow-Methods: GET, POST");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Origin: ".$accessControlOrigin);
            $sessionIdNew = isset($_GET['crossdomainCookiesAuth']) ? $_GET['crossdomainCookiesAuth'] : $_REQUEST['sessionIdDrag'];
            if (isset($_GET['crossdomainCookiesAuth']) && !$_GET['crossdomainCookiesAuth']){
                session_start();
                ?><SCRIPT type="text/javascript">
                    parent.postMessage({setPhpSesId:'<?=session_id()?>'}, '*');
                    setTimeout(function(){
                        parent.postMessage({setPhpSesId:'<?=session_id()?>'}, '*');
                    }, 150);
                    setTimeout(function(){
                        parent.postMessage({setPhpSesId:'<?=session_id()?>'}, '*');
                    }, 500);
                </SCRIPT><?
            }else if (mb_strlen($sessionIdNew) > 5 && mb_strlen($sessionIdNew) < 64){
                # |- set a new session id
                session_id($sessionIdNew);
                if ( isset($_GET['crossdomainCookiesAuth']) )
                {/*session_start - updates PHPSESSID cookie after set with session_id . If sessionIdDrag defined itll do Zend_Auth later in Bootstrap */
                    session_start();
                }
            }
        }
        if ( isset($_GET['crossdomainCookiesAuth']) )
        {
            die();
        }
    }

    # |- https <-> http requests enable, cross origin between self domains
    if (!empty($_SERVER['HTTP_ORIGIN'])){
        $originExpl = explode('//', $_SERVER['HTTP_ORIGIN']);
        $domain = end($originExpl);
        header("Access-Control-Allow-Credentials: true");
        if ($domain == $_SERVER['HTTP_HOST']){
            header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
        }else{
            $domains = include APPLICATION_PATH_SET. DS . 'domains.php';
            foreach($domains as $selfDomain){
                if ($domain == $selfDomain['domain']){
                    header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
                    break;
                }
            }
        }
    }

    # |- basic CSRF PROTECTION
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){

        if (empty($_SERVER['HTTP_REFERER'])){
            $maybeCSRF = true;
        }else{
            $maybeCSRF = true;
            $domains = include APPLICATION_PATH_SET. DS . 'domains.php';
            foreach($domains as $domain)
            {
                if (mb_strpos($_SERVER['HTTP_REFERER'], 'http://' . $domain['domain'])===0 || mb_strpos($_SERVER['HTTP_REFERER'], 'https://' . $domain['domain'])===0)
                {
                    $maybeCSRF = false;
                    break;
                }
            }
        }
        if ($maybeCSRF){
            # |- reset of post data
            $_SERVER['REQUEST_METHOD'] = 'GET';
            foreach($_REQUEST as $key=>$value){
                if (isset($_POST[$key])) unset($_REQUEST[$key]);
            }
            $_POST = $_FILES = $HTTP_POST_FILES = $HTTP_POST_VARS = [];
        }
    }
}


# |- Gzipping OutputBuffering
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')!==false)
    ob_start("ob_gzhandler");
else
    ob_start();



defined('APPLICATION_ENV')              ||
define('APPLICATION_ENV',                   strtolower(_DEVELOPER__ENV_MODE));
$mail       = include APPLICATION_PATH_SET . DS . 'mail.php';
$cache      = include APPLICATION_PATH_SET . DS . 'cache.php';
$database   = include APPLICATION_PATH_SET . DS . 'database.php';


# |- Sub apps
if (!defined('_ENGINE_R_MAIN') && !defined('_ENGINE_R_INIT')) {
    if (@$_GET['m'] == 'css') {
        define('_ENGINE_R_MAIN', 'css.php');
        define('_ENGINE_R_INIT', false);
    } else if (@$_GET['m'] == 'lite') {
        define('_ENGINE_R_MAIN', 'lite.php');
        define('_ENGINE_R_INIT', true);
    } else {
        define('_ENGINE_R_MAIN', false);
        define('_ENGINE_R_INIT', true);
    }
}

# |- Boot
if (_ENGINE_R_INIT) {

    # |- Application
    require_once 'Engine/Loader.php';
    require_once 'Engine/Application.php';

    # |- Create application, bootstrap it, and run
    $application = new Engine_Application(
        [
            'environment' => APPLICATION_ENV,
            'bootstrap' => [
                'path' => APPLICATION_PATH_COR . DS . 'modules' . DS . APPLICATION_NAME . DS . 'Bootstrap.php',
                'class' => ucfirst(APPLICATION_NAME) . '_Bootstrap',
            ],
            'autoloaderNamespaces' => [
                'Zend'          => APPLICATION_PATH_LIB . DS . 'Zend',
                'Engine'        => APPLICATION_PATH_LIB . DS . 'Engine',
                'Facebook'      => APPLICATION_PATH_LIB . DS . 'Facebook',
                'Bootstrap'     => APPLICATION_PATH_BTS,
                'Plugin'        => APPLICATION_PATH_PLU,
                'Composer'      => APPLICATION_COMPOSER,
                'External'      => APPLICATION_PATH_PRV_EXT
            ],
        ]
    );
    Engine_Application::setInstance($application);
    Engine_Api::getInstance()->setApplication($application);
}

if( _ENGINE_CONSOLE_APP ) {
    Zend_Registry::set('tasks_log', $tasksLog =  APPLICATION_PATH_LOG . DS . 'tasks.log');
    Zend_Registry::set('task_mca', $taskMCA = (@$request['module'] . ' / ' . @$request['controller'] . ' / ' . @$request['action']));
    file_put_contents($tasksLog, "\n\n " . date("Y-m-d H:i:s.") . gettimeofday()["usec"] ."  Task RUN routed to " . $taskMCA . " ( " . implode(' ', $argv) . " )" . "\n", FILE_APPEND);
}

# |- config mode
if (defined('_ENGINE_R_CONF') && _ENGINE_R_CONF) {
    return;
}



if (_ENGINE_R_MAIN) {                                               # |- Sub apps
    require dirname(__FILE__) . DS . _ENGINE_R_MAIN;
    exit();
} elseif (
    isset($application)
    && $application instanceof Engine_Application
    && file_exists(ENGINE_FATAL_HANDLER)
) {                                                                 # |- Main app
    # |- Bootstrap required module
    $application->bootstrap();
    # |- Fatal catching
    include ENGINE_FATAL_HANDLER;
    # |- Run an application
    $application->run();
}