<?

/**
 * @category Application_Core
 * @package Core
 * @copyright Copyright 2006-2010 Bitsa team
 * @author Vlad Smith
 *
 */
class Core_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
    public function __construct($application)
    {
        parent::__construct($application);

        date_default_timezone_set('UTC');

        if( function_exists('mb_internal_encoding') ) {
            mb_internal_encoding("UTF-8");
        }

        if( function_exists('iconv_set_encoding') && PHP_VERSION_ID < 50600 ) {
            // Not sure if we want to do all of these
            iconv_set_encoding("input_encoding", "UTF-8");
            iconv_set_encoding("output_encoding", "UTF-8");
            iconv_set_encoding("internal_encoding", "UTF-8");
        }else{
            ini_set('default_charset', 'UTF-8');
        }

        // Production
        error_reporting(
            ( APPLICATION_ENV === 'production' )
                ? 0
                : E_ALL & ~E_STRICT & ~E_DEPRECATED
        );
    }

    public function run(Zend_Controller_Request_Http $request = null)
    {
        if (!isset($_SERVER['HTTP_HOST'])){
            $_SERVER['HTTP_HOST'] = BITSA_SITE;
        }
        if (!isset($_SESSION['appear'])){
            $_SESSION['appear'] = time();
        }

        /* @var Zend_Controller_Front $front */
        $front = $this->getContainer()->frontcontroller;

        if( !$request && (null === ($request = $front->getRequest())) ) {
            $request = new Zend_Controller_Request_Http();
        }
        $front->setRequest($request);
        die('snow');

        /*Bitsa - rewrite URLs*/
        if (!_ENGINE_CONSOLE_APP && ($rewriteRule = Engine_Api::_()->getItemTable('urlrewrite')->matchRewrite($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) ){
            $request->setRequestUri( $rewriteRule instanceof Core_Model_Urlrewrite ?  $rewriteRule->apply($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']) : $rewriteRule  );
            $front->setRequest($request);
            $front->getRouter()->route($request);
        }
        die('fire');

        // Start main

        $front->setParam('bootstrap', $this);
                            # DIRTY PART GOES HERE <----
        $front->dispatch();
        // End main

        if (Zend_Registry::isRegistered('tasks_log')){
            $tasksLog = Zend_Registry::get('tasks_log');
            file_put_contents($tasksLog, "\n ". date("Y-m-d H:i:s.").gettimeofday()["usec"] ." Task ".Zend_Registry::get('task_mca')." DONE in ". (microtime(true) - _ENGINE_REQUEST_START)."s\n", FILE_APPEND);
        }

        Zend_Session::writeClose();
    }


    /**
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _initDb()
    {
        $file = APPLICATION_PATH_SET . DS . 'database.php';
        $options = include $file;

        $db = Zend_Db::factory($options['adapter'], $options['params']);
        Engine_Db_Table::setDefaultAdapter($db);
        Engine_Db_Table::setTablePrefix($options['tablePrefix']);

        // Non-production
        if( APPLICATION_ENV !== 'production' ) {
            $db->setProfiler(array(
                'class' => 'Zend_Db_Profiler_Firebug',
                'enabled' => true
            ));
        }

        // set DB to UTC timezone for this session
        switch( $options['adapter'] ) {
            case 'mysqli':
            case 'mysql':
            case 'pdo_mysql': {
                $db->query("SET time_zone = '+0:00'");
                break;
            }

            case 'postgresql': {
                $db->query("SET time_zone = '+0:00'");
                break;
            }

            default: {
                // do nothing
            }
        }

        // attempt to disable strict mode
        try {
            $db->query("SET SQL_MODE = ''");
        } catch (Exception $e) {}

        return $db;
    }

    /**
     *
     */
    protected function _initNode()
    {
        // @todo revisit this for cloud hosting
        return;
        try {
            $db = Engine_Db_Table::getDefaultAdapter();

            // Check for signature
            $signatureFile = APPLICATION_PATH . '/application/settings/node.php';
            if( file_exists($signatureFile) ) {
                $signature = file_get_contents($signatureFile);
                $writeable = is_writable($signatureFile);
            } else {
                $signature = null;
                $writeable = is_writable(dirname($signatureFile));
            }

            // Verify signature exists
            $node_id = null;
            if( $signature ) {
                $node_id = $db->select()
                    ->from('engine4_core_nodes', 'node_id')
                    ->where('signature = ?', $signature)
                    ->query()
                    ->fetchColumn();
                if( !$node_id ) {
                    $signature = null;
                }
            }

            // Update signature
            if( $signature ) {
                $db->update('engine4_core_nodes', array(
                    'last_seen' => new Zend_Db_Expr('NOW()'),
                ), array(
                    'node_id = ?' => $node_id,
                ));
                Zend_Registry::set('Engine_Node', $node_id);
            }

            // Create signature
            else if( $writeable ) {
                $signature = sha1((function_exists('php_uname') ? php_uname() : '')
                    . $_SERVER['SERVER_ADDR']
                    . time());

                $db->insert('engine4_core_nodes', array(
                    'signature' => $signature,
                    'host' => $_SERVER['HTTP_HOST'],
                    'ip' => ip2long($_SERVER['SERVER_ADDR']),
                    'first_seen' => new Zend_Db_Expr('NOW()'),
                    'last_seen' => new Zend_Db_Expr('NOW()'),
                ));

                $node_id = $db->lastInsertId();

                file_put_contents($signatureFile, $signature);

                Zend_Registry::set('Engine_Node', $node_id);
            }

            // Failure
            else {
                Zend_Registry::set('Engine_Node', false);
            }
        } catch( Exception $e ) {
            // Silence?
        }
    }

    /**
     * @return Zend_Controller_Front
     */
    protected function _initFrontController()
    {
        Zend_Controller_Action_HelperBroker::addPath("Engine/Controller/Action/Helper/", 'Engine_Controller_Action_Helper');

        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setRouter(new Engine_Controller_Router_Rewrite());
        $frontController
            //->addModuleDirectory(APPLICATION_PATH . "/application/modules/")
            ->setDefaultModule('core')
            ->setParam('viewSuffix', 'tpl')
            ->setParam('prefixDefaultModule', 'true');

        // Add our special path for action helpers
        $this->initActionHelperPath();

        // Our virtual index hack confuses the request class, this other hack will
        // make it think it's in the root folder
        $request = new Zend_Controller_Request_Http();
        $script = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = str_replace('/application/', '/', $script);
        $frontController->setBaseUrl($request->getBaseUrl());
        $_SERVER['SCRIPT_NAME'] = $script;

        // Save to registy and local container
        Zend_Registry::set('Zend_Controller_Front', $frontController);
        return $frontController;
    }

    /**
     * @return null|Zend_Cache_Core
     */
    protected function _initCache()
    {
        // Get configurations
        $file = APPLICATION_PATH_SET . DS . 'cache.php';

        // @todo cache config in database

        if( file_exists($file) ) {
            // Manual config
            $options = include $file;
        } else if( is_writable(APPLICATION_PATH_CACHE) || (
                !@is_dir(APPLICATION_PATH_CACHE) &&
                @mkdir(APPLICATION_PATH_CACHE, 0777, true)
            ) ) {
            // Auto default config
            $options = array(
                'default_backend' => 'File',
                'frontend' => array (
                    'core' => array (
                        'automatic_serialization' => true,
                        'cache_id_prefix' => 'Engine4_',
                        'lifetime' => '7200',
                        'caching' => true,
                    ),
                ),
                'backend' => array(
                    'File' => array(
                        'cache_dir' => APPLICATION_PATH_CACHE,
                    ),
                ),
            );
        } else {
            // Failure
            return null;
        }

        // Create cache
        $frontend = key($options['frontend']);
        $backend = key($options['backend']);
        Engine_Cache::setConfig($options);
        $cache = Engine_Cache::factory($frontend, $backend);

        // Disable caching in development mode
        if( APPLICATION_ENV == 'development' ) {
            $cache->setOption('caching', false);
        }

        // Save in registry
        Zend_Registry::set('Zend_Cache', $cache);

        // Use cache helper?
        Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-1, new Engine_Controller_Action_Helper_Cache());

        // Add cache to database
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

        // Save in bootstrap
        return $cache;
    }

    /**
     * @return Zend_Log
     */
    protected function _initLog()
    {
        $log = new Zend_Log();
        $log->setEventItem('domain', 'error');

        // Non-production
        if( APPLICATION_ENV !== 'production' ) {
            $log->addWriter(new Zend_Log_Writer_Firebug());
        }

        // Get log config
        $db = Engine_Db_Table::getDefaultAdapter();
        $logAdapter = $db->select()
            ->from('Settings', 'value')
            ->where('`name` = ?', 'core.log.adapter')
            ->query()
            ->fetchColumn();

        // Set up log
        switch( $logAdapter ) {
            case 'database': {
                try {
                    $log->addWriter(new Zend_Log_Writer_Db($db, 'engine4_core_log'));
                } catch( Exception $e ) {
                    // Make sure logging doesn't cause exceptions
                    $log->addWriter(new Zend_Log_Writer_Null());
                }
                break;
            }
            default:
            case 'file': {
                try {
                    $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH_LOG . DS . 'main.log'));
                } catch( Exception $e ) {
                    // Check directory
                    if( !@is_dir(APPLICATION_PATH_LOG) &&
                        @mkdir(APPLICATION_PATH_LOG, 0777, true) ) {
                        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH_LOG . DS . 'main.log'));
                    } else {
                        // Silence ...
                        if( APPLICATION_ENV !== 'production' ) {
                            $log->log($e->__toString(), Zend_Log::CRIT);
                        } else {
                            // Make sure logging doesn't cause exceptions
                            $log->addWriter(new Zend_Log_Writer_Null());
                        }
                    }
                }
                break;
            }
            case 'none': {
                $log->addWriter(new Zend_Log_Writer_Null());
                break;
            }
        }

        // Save to registry
        Zend_Registry::set('Zend_Log', $log);

        // Register error handlers
        Engine_Api::registerErrorHandlers();

        if( 'production' != APPLICATION_ENV ) {
            Engine_Exception::setLog($log);
        }

        return $log;
    }

    /**
     * @throws Engine_Exception
     */
    protected function _initFrontControllerModules()
    {
        die('front-controller');
        $frontController = Zend_Controller_Front::getInstance();
        $path = APPLICATION_PATH_MOD;

        /* @var Core_Model_DbTable_Modules $modulesTable */
        $modulesTable = Engine_Api::_()->getDbtable('modules', 'core');
        $enabledModuleNames = $modulesTable->getEnabledModuleNames();
        foreach( $enabledModuleNames as $module ) {
            $moduleInflected = Engine_Api::inflect($module);
            $moduleDir = $path . DS . $moduleInflected;
            if( is_dir($moduleDir) ) {
                $moduleDir .= DS . $frontController->getModuleControllerDirectoryName();
                $frontController->addControllerDirectory($moduleDir, $module);
            } else {
                // Maybe we should log modules that fail to load?
                if( APPLICATION_ENV == 'development' ) {
                    throw new Engine_Exception('failed to load module "' . $module . '"');
                }
            }
        }

        $frontController
            ->setDefaultModule('core');
    }

    protected function _initManifest()
    {
        // Load from cache
        $cached = false;

        if( isset($this->getContainer()->cache) ) {
            $data = $this->getContainer()->cache->load('Engine_Manifest');
            if( is_array($data) )
            {
                $manifest = $data;
                //$manifest = new Zend_Config($data);
                $cached = true;
            }
        }

        // Load manually
        if( !$cached ) {
            $data = array();
            foreach( $this->getContainer()->frontcontroller->getControllerDirectory() as $name => $path ) {
                $file = dirname($path) . '/settings/manifest.php';
                if( file_exists($file) ) {
                    $data[$name] = include($file);
                } else {
                    $data[$name] = array();
                }
            }
            $manifest = $data;
            //$manifest = new Zend_Config($data);
        }

        Zend_Registry::set('Engine_Manifest', $manifest);

        // Save to cache
        if( !$cached && isset($this->getContainer()->cache) ) {
            $this->getContainer()->cache->save(serialize($manifest), 'Engine_Manifest');
            //$this->getContainer()->cache->save(serialize($manifest->toArray()), 'Engine_Manifest');
        }

        return $data;
    }

    protected function _initSession()
    {
        // Get session configuration
        $file = APPLICATION_PATH . '/application/settings/session.php';
        $config = array();
        if( file_exists($file) ) {
            $config = include $file;
        }

        // Get default session configuration
        if( empty($config) ) {
            $config = array(
                'options' => array(
                    'save_path' => 'session',
                    'use_only_cookies' => true,
                    'remember_me_seconds' => 15*86400,
                    'gc_maxlifetime' => 86400,
                    'cookie_httponly' => false,
                ),
                'saveHandler' => array(
                    'class' => 'Core_Model_DbTable_Session',
                    'params' => array(
                        'lifetime' => 15*86400,
                    ),
                ),
            );
        }

        // Remove httponly unless forced in config
        if( !isset($config['options']['cookie_httponly']) ) {
            $config['options']['cookie_httponly'] = false;
        }

        // Set session options
        Zend_Session::setOptions($config['options']);

        $saveHandler = $config['saveHandler']['class'];
        Zend_Session::setSaveHandler(new $saveHandler($config['saveHandler']['params']));

        // Session hack for fancy upload
        //if( !isset($_COOKIE[session_name()]) )
        //{
        $sessionName = Zend_Session::getOptions('name');
        if( isset($_POST[$sessionName]) ) {
            Zend_Session::setId($_POST[$sessionName]);
        } else if( isset($_POST['PHPSESSID']) ) {
            Zend_Session::setId($_POST['PHPSESSID']);
        }
        //}

        //Zend_Session::start();
    }

    protected function _initRouter()
    {
        $router = $this->getContainer()->frontcontroller->getRouter();

        $defaultAdminRoute = Engine_Controller_Router_Route_ControllerPrefix::getInstance(new Zend_Config(array()));
        $router->addRoute('admin_default', $defaultAdminRoute);

        // Add module-configured routes
        $manifest = Zend_Registry::get('Engine_Manifest');
        foreach( $manifest as $module => $config ) {
            if( !isset($config['routes']) ) {
                continue;
            }
            $router->addConfig(new Zend_Config($config['routes']));
        }

        // Add default routes
        $router->addDefaultRoutes();

        return $router;
    }

    protected function _initView()
    {
        // Create view
        $view = new Zend_View();

        // Set encoding (@todo maybe use configuration?)
        $view->setEncoding('utf-8');

        $view->addScriptPath(APPLICATION_PATH);

        // Setup and register viewRenderer
        // @todo we may not need to override zend's
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        //$viewRenderer = new Engine_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewSuffix('tpl');
        Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, $viewRenderer);

        // Initialize contextSwitch helper
        Zend_Controller_Action_HelperBroker::addHelper(new Core_Controller_Action_Helper_ContextSwitch());

        // Add default helper paths
        $view->addHelperPath('Engine/View/Helper/', 'Engine_View_Helper_');
        $this->initViewHelperPath();

        // Set doctype
        Engine_Loader::loadClass('Zend_View_Helper_Doctype');
        $doctypeHelper = new Zend_View_Helper_Doctype();
        $doctypeHelper->doctype(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.doctype', 'HTML4_LOOSE'));

        // Add to local container and registry
        Zend_Registry::set('Zend_View', $view);
        return $view;
    }

    protected function _initLayout()
    {
        // Create layout
        $layout = Zend_Layout::startMvc();

        // Set options
        $layout->setViewBasePath(APPLICATION_PATH . "/application/modules/Core/layouts", 'Core_Layout_View')
            ->setViewSuffix('tpl')
            ->setLayout(null);

        // Add themes
        $theme = null;
        $themes = array();
        $themesInfo = array();

        $themeTable = Engine_Api::_()->getDbtable('themes', 'core');
        if( !empty($_COOKIE['theme']) && is_numeric($_COOKIE['theme']) ) {
            $theme = $themeTable->find((int) $_COOKIE['theme'])->current();
        } else if( !empty($_SESSION['theme']) && is_numeric($_SESSION['theme']) ) {
            $theme = $themeTable->find((int) $_SESSION['theme'])->current();
        }
        if( !$theme ) {
            $themeSelect = $themeTable->select()
                ->where('active = ?', 1)
                ->limit(1);
            $theme = $themeTable->fetchRow($themeSelect);
        }

        if( $theme ) {
            $themes[] = $theme->name;
            $themesInfo[$theme->name] = include APPLICATION_PATH_COR . DS
                . 'themes' . DS . $theme->name . DS . 'manifest.php';
        }

        $layout->themes = $themes;
        $layout->themesInfo = $themesInfo;
        Zend_Registry::set('Themes', $themesInfo);

        // Add global site title etc
        $siteinfo = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site', array());
        $siteinfo = array_filter($siteinfo);
        $siteinfo = array_merge(array(
            'title' => 'Social Network',
            'description' => '',
            'keywords' => '',
        ), $siteinfo);
        $layout->siteinfo = $siteinfo;

        // Get global site revision counter
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $counter = $settings->core_site_counter;
        if( !$counter ) {
            $settings->core_site_counter = $counter = 1;
        }
        $layout->counter = $counter;

        // Get baseUrl for static content
        $view = Zend_Registry::get('Zend_View');
        $staticBaseUrl = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.static.baseurl');
        if( !$staticBaseUrl ) {
            $staticBaseUrl = $view->baseUrl();
        }
        $staticBaseUrl = rtrim($staticBaseUrl, '/') . '/';
        $layout->staticBaseUrl = $staticBaseUrl;
        Zend_Registry::set('StaticBaseUrl', $staticBaseUrl);

        // Get includes
        $front = $this->getContainer()->frontcontroller;
        $request = $front->getRequest();
        if ( $request === null ) {
            $request = new Zend_Controller_Request_Http();
        }
        if ( $request->getParam('format') !== 'smoothbox' ) {
            $layout->headIncludes = (string) Engine_Api::_()->getApi('settings', 'core')
                ->getSetting('core.general.includes', '');
        }

        $layout->getViewport = function() use ($layout){

            $layout->viewport  = isset($layout->viewport) ? $layout->viewport  : null;
            $content = '';

            if ($layout->viewport !== false){
                $content =  '<meta name="viewport" content="'. ($layout->viewport ? $layout->viewport  : 'width=device-width') .'" />';
                $width = MIN_ADAPTIVE_WIDTH;
                if ($layout->viewport && preg_match('@width=(\d+)@i', $layout->viewport, $matches)){
                    $width = (int)$matches[1];
                }
                $content .= "\n<style type='text/css'>"
                    . "body,html { min-width: ".$width."px;}"
                    . "</style>";
            }
            return $content;
        };
        return $layout;
    }
    /**
     * Initializes translator
     *
     * @return Zend_Translate_Adapter
     */
    public function _initTranslate()
    {
        // Set cache
        if( isset($this->getContainer()->cache) ) {
            Zend_Translate::setCache($this->getContainer()->cache);
        }

        // Get list of supported languages
        /*
        $languages = array();
        $it = new DirectoryIterator(APPLICATION_PATH_COR . DS . 'languages');
        foreach( $it as $item ) {
          if( $item->isDot() || !$item->isDir() ) {
            continue;
          }
          $name = $item->getBasename();
          if( !Zend_Locale::isLocale($name) ) {
            continue;
          }
          $languages[] = $name;
        }
        */

        // If in development, log untranslated messages
        $params = array(
            'scan' => Zend_Translate_Adapter::LOCALE_DIRECTORY,
            'logUntranslated' => true
        );

        $log = new Zend_Log();
        if( APPLICATION_ENV == 'development' ) {
            $log = new Zend_Log();
            $log->addWriter(new Zend_Log_Writer_Firebug());
            $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/translate.log'));
        } else {
            $log->addWriter(new Zend_Log_Writer_Null());
        }
        $params['log'] = $log;

        // Check Locale
        $locale = Zend_Locale::findLocale();
        // Make Sure Language Folder Exist
        $languageFolder = is_dir(APPLICATION_PATH . '/application/languages/' . $locale);
        if($languageFolder === false) {
            $locale = substr($locale, 0, 2);
            $languageFolder = is_dir(APPLICATION_PATH . '/application/languages/' . $locale);
            if($languageFolder == false) {
                $locale = 'en';
            }
        }

        // Check which Translation Adapter has been selected
        $db = Engine_Db_Table::getDefaultAdapter();
        $translationAdapter = $db->select()
            ->from('engine4_core_settings', 'value')
            ->where('`name` = ?', 'core.translate.adapter')
            ->query()
            ->fetchColumn();

        // If adapter is 'array', Make sure array files exist
        /*
        if( $translationAdapter == 'array'){
          // Check if Language File Exists
          if( !file_exists(APPLICATION_PATH . '/application/languages/' . $locale . '/' . $locale . '.php')){
            //echo 'Locale does not exist ' . APPLICATION_PATH . '/application/languages/' . $locale . '/' . $locale . '_array.php<br />';
            // Try looking elsewhere
            $newLocale = substr($locale, 0, 2);
            //echo 'Attempting to Look for ' . $newLocale . '<br />';
            if( file_exists(APPLICATION_PATH . '/application/languages/' . $newLocale . '/' . $newLocale . '.php')){
              $locale = $newLocale;
              //echo 'New Locale Found ' . APPLICATION_PATH . '/application/languages/' . $newLocale . '/' . $newLocale . '_array.php<br />';
            } else { $translationAdapter = 'csv'; $locale = 'en'; }
          }
        }
        */

        // Use Array Translation Adapter, Loop through all Availible Translations
        if($translationAdapter == 'array'){
            // Find all Valid Language Arrays
            // Check For Array Files
            $languagePath = APPLICATION_PATH.'/application/languages';
            // Get List of Folders
            $languageFolders = array_filter(glob( $languagePath . DS . '*' ), 'is_dir');
            // Look inside Folders for PHP array
            $locale_array = array();
            foreach( $languageFolders as $folder){
                // Get Locale code
                $locale_code = str_replace($languagePath . DS, "", $folder);
                $locale_array[] = $locale_code;
                if (!file_exists($folder . DS . $locale_code . '.php')) {
                    // If Array files do not exist, switch to CSV
                    $translationAdapter = 'csv';
                }
            }

            $language_count = count($locale_array);
            // Add the First One
            $translate = new Zend_Translate(
                array(
                    'adapter' => 'Engine_Translate_Adapter_Array',
                    'content' => $languagePath . DS . $locale_array[0] . DS . $locale_array[0] . '.php',
                    'locale'  => $locale_array[0] )
            );
            if( $language_count > 1) {
                for( $i = 1; $i < $language_count; $i++){
                    $translate->addTranslation(
                        array(
                            'content' => $languagePath . DS . $locale_array[$i] . DS . $locale_array[$i] . '.php',
                            'locale' => $locale_array[$i] )
                    );
                }
            }

            /*
            if( $language_count > 1) {
              for( $i = 1; $i < $language_count; $i++ ) {
                $translate->addTranslation(
                        array(
                            'content' => $languageFolders[$i] . DS . $locale_array[$i] . '.php',
                            'locale' => $locale_array[$i] )
                                      );
                  echo $locale_array[$i] . ' Translation Added<br />';
                }

              }
             * */
        }

        // Use CSV Translation Adapter
        else {
            $translate = new Zend_Translate(
                'Csv',
                APPLICATION_PATH.'/application/languages',
                null,
                $params
            );
        }

        /* Use Zend_Registry::get('Zend_Translate')->getLocale() to get locale */
        Zend_Registry::set('Zend_Translate', $translate);
        if(Zend_Registry::isRegistered('Zend_View')){
            Zend_Registry::get('Zend_View')->headTranslate(array(
                'now', 'in a few seconds', 'a few seconds ago', '%s minute ago',
                'in %s minute', '%s hour ago', 'in %s hour', '%s at %s',
            ));
        }
        Zend_Validate_Abstract::setDefaultTranslator($translate);
        Zend_Form::setDefaultTranslator($translate);
        Zend_Controller_Router_Route::setDefaultTranslator($translate);

        return $translate;
    }

    protected function _initContent()
    {
        $content = Engine_Content::getInstance();

        // Set storage
        $contentTable = Engine_Api::_()->getDbtable('pages', 'core');
        $content->setStorage($contentTable);

        // Load content helper
        $contentRenderer = new Engine_Content_Controller_Action_Helper_Content();
        $contentRenderer->setContent($content);
        Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-85, $contentRenderer);

        // Set cache object
        if( isset($this->getContainer()->cache) ) {
            $content->setCache($this->getContainer()->cache);
        }

        // Set translator
        if( isset($this->getContainer()->translate) ) {
            $content->setTranslator($this->getContainer()->translate);
        }

        // Save to registry
        Zend_Registry::set('Engine_Content', $content);

        return $content;
    }

    protected function _initPaginator()
    {
        // Set up default paginator options
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_Paginator::setDefaultPageRange(10);
        Zend_View_Helper_PaginationControl::setDefaultViewPartial(array(
            'pagination/search.tpl',
            'core'
        ));
    }

    protected function _initHooks()
    {
        $hooks = Engine_Hooks_Dispatcher::getInstance();

        // Add module-configured routes
        $manifest = Zend_Registry::get('Engine_Manifest');
        foreach( $manifest as $module => $config ) {
            if( !isset($config['hooks']) ) {
                continue;
            }
            $hooks->addEvents($config['hooks']);
        }

        return $hooks;
    }

    protected function _initApi()
    {
        return Engine_Api::_();
    }

    protected function _initModules()
    {
        /* @var Zend_Controller_Dispatcher_Interface $front */
        $front = $this->getContainer()->frontcontroller;
        $default = $front->getDefaultModule();

        $bootstraps = new ArrayObject();

        // Prepare data
        $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
        $baseDir = APPLICATION_PATH;
        //$baseUrl = preg_replace('/[\/]*index\.php[\/]*/', '/', $front->getBaseUrl());

        foreach( $enabledModuleNames as $module ) {
            $moduleInflected = Engine_Api::inflect($module);
            $moduleDir = $baseDir . DS . 'application'
                . DS . 'modules' . DS . $moduleInflected;

            // Default module is already bootstrapped, but bootstrap others
            if( strtolower($module) === strtolower($default) ) {
                continue;
            }

            $bootstrapClass = $moduleInflected . '_Bootstrap';
            if( !class_exists($bootstrapClass, false) ) {
                $bootstrapPath  = $moduleDir . '/Bootstrap.php';
                if( file_exists($bootstrapPath) ) {
                    include_once $bootstrapPath;
                    if( !class_exists($bootstrapClass, false) ) {
                        throw new Zend_Application_Resource_Exception('Bootstrap file found for module "' . $module . '" but bootstrap class "' . $bootstrapClass . '" not found');
                    }
                } else {
                    continue;
                }
            }

            $moduleBootstrap = new $bootstrapClass($this);
            $moduleBootstrap->bootstrap();
            $bootstraps[$module] = $moduleBootstrap;
        }

        return $bootstraps;
    }

    protected function _initLocale()
    {
        // Translate needs to be initialized before Modules, so _initTranslate() could
        // not load the "User" couldn't be initialized then.  Thus, we must assign
        // the language over here if it is a user.

        // Try to pull from various sources
        $viewer   = Engine_Api::_()->user()->getViewer();
        $timezone = 'UTC/GMT+3';
        if( $viewer->getIdentity() ) {
            $locale = $viewer->locale;
            $language = $viewer->language;
            $timezone = $viewer->timezone;
        } else if( !empty($_COOKIE['en4_language']) && !empty($_COOKIE['en4_locale']) ) {
            $locale = $_COOKIE['en4_locale'];
            $language = $_COOKIE['en4_language'];
        } else {
            $locale = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'auto');
            $language = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'auto');
        }
        //пока что так - отображение всех времён в одном час-поясе
        Zend_Registry::set('timezone', 'Etc/GMT-3');

        // Make sure it's valid
        try {
            $locale = Zend_Locale::findLocale($locale);
        } catch( Exception $e ) {
            $locale = 'en_US';
        }

        $localeObject = new Zend_Locale($locale);
        Zend_Registry::set('Locale', $localeObject);

        // Set in locale and language
        $translate = $this->getContainer()->translate;
        $defaultLanguage  = Engine_Api::_()->getApi('settings', 'core')->core_locale_locale;
        $localeLanguage = $localeObject->getLanguage();

        $ls = array($locale, $language, $localeLanguage, $defaultLanguage, 'en');
        foreach ($ls as $l)
            if ( $translate->isAvailable($l) ) {
                $translate->setLocale($l);
                break;
            }

        if( !$viewer->getIdentity() ) {
            if( empty($_COOKIE['en4_language']) ) {
                setcookie('en4_language', $translate->getLocale(), time() + (86400*365), '/');
            }

            if( empty($_COOKIE['en4_locale']) ) {
                setcookie('en4_locale', $locale, time() + (86400*365), '/');
            }
        }

        // Set cache
        Zend_Locale_Data::setCache($this->getContainer()->cache);

        // Get orientation
        $localeData = Zend_Locale_Data::getList($localeObject->__toString(), 'layout');
        $this->getContainer()->layout->orientation = $localeData['characters'];

        return $localeObject;
    }

    protected function _initCensor()
    {
        $bannedWords = null;

        // caching
        $cache = $this->getContainer()->cache;
        if( $cache instanceof Zend_Cache_Core &&
            ($data = $cache->load('bannedwords')) &&
            is_string($data) ) {
            $bannedWords = $data;
        } else {
            $bannedWords = Engine_Api::_()->getApi('settings', 'core')->core_spam_censor;

            $db = $this->getContainer()->db;
            if( $db instanceof Zend_Db_Adapter_Abstract ) {
                $dbBannedWords = $db->select()
                    ->from('engine4_core_bannedwords', 'word')
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
                $bannedWords .= ',' . join(',', $dbBannedWords);
            }

            $bannedWords = trim($bannedWords, ' ,');

            // save
            $cache->save($bannedWords, 'bannedwords');
        }

        Engine_Filter_Censor::setDefaultForbiddenWords($bannedWords);
    }

    protected function _initBannedIps()
    {
        // No CLI
        if( 'cli' === PHP_SAPI ) {
            return;
        }

        // Check if visitor is banned by IP
        $addressObject = new Engine_IP();
        $addressBinary = $addressObject->toBinary();

        // Load banned IPs
        $db = $this->getContainer()->db;
        $bannedIps = $db->select()
            ->from('engine4_core_bannedips')
            ->query()
            ->fetchAll();

        $isBanned = false;
        foreach( $bannedIps as $bannedIp ) {
            // @todo ipv4->ipv6 transformations
            if( strlen($addressBinary) == strlen($bannedIp['start']) ) {
                if( strcmp($addressBinary, $bannedIp['start']) >= 0 &&
                    strcmp($addressBinary, $bannedIp['stop']) <= 0 ) {
                    $isBanned = true;
                    break;
                }
            }
        }

        // tell them they're banned
        if( $isBanned ) {
            //@todo give appropriate forbidden page
            if( !headers_sent() ) {
                header('HTTP/1.0 403 Forbidden');
            }
            die('banned');
        }
    }
}