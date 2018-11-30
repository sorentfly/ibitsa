<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Manager.php 10189 2014-04-30 18:51:06Z andres $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manager
{
  const PATH_BASE = 'base';
  const PATH_TEMP = 'temporary';
  const PATH_ARCHIVES = 'archives';
  const PATH_MANIFESTS = 'manifests';
  const PATH_PACKAGES = 'packages';
  const PATH_REPOSITORIES = 'repositories';
  const PATH_INSTALLED = 'installed';
  const PATH_SETTINGS = 'settings';
  const PATH_SETTINGS_NODE = 'nodesettings';
  const PATH_SETTINGS_REPOSITORIES = 'repositoriessettings';

  protected $_basePath;

  protected $_paths = array(
    self::PATH_BASE => null,
    self::PATH_TEMP => 'temporary',
    self::PATH_ARCHIVES => 'temporary/package/archives',
    self::PATH_MANIFESTS => 'temporary/package/manifests',
    self::PATH_PACKAGES => 'temporary/package/packages',
    self::PATH_REPOSITORIES => 'temporary/package/repositories',
    self::PATH_INSTALLED => 'application/packages',
    self::PATH_SETTINGS => 'application/settings',
    self::PATH_SETTINGS_NODE => 'application/settings/node.php',
    self::PATH_SETTINGS_REPOSITORIES => 'application/settings/repositories.php',
  );

  protected $_temporaryPaths = array(
    self::PATH_TEMP,
    self::PATH_ARCHIVES,
    self::PATH_MANIFESTS,
    self::PATH_PACKAGES,
    self::PATH_REPOSITORIES,
  );

  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_db;

  /**
   * @var Engine_Vfs_Adapter_Abstract
   */
  protected $_vfs;

  /**
   * @var Zend_Cache_Core
   */
  protected $_cache;

  protected $_repositories;

  protected $_installers;

  protected $_installedPackages;

  protected $_extractedPackages;

  protected $_availablePackages;



  // General
  
  public function __construct($options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }
  }

  public function __sleep()
  {
    return array('_basePath', '_paths', '_db', '_vfs', '_cache',
      '_repositories');
  }

  public function __wakeup()
  {

  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }

    return $this;
  }



  // Paths

  public function setPath($path, $type = self::PATH_BASE) {
    if( !array_key_exists($type, $this->_paths) ) {
      throw new Engine_Package_Manager_Exception('Invalid path type');
    }
    $this->_paths[$type] = $path;
    if( $type == self::PATH_BASE ) {
      $this->_basePath = $path; // B/c
    }
    return $this;
  }

  public function setPaths(array $paths)
  {
    foreach( $paths as $type => $path ) {
      $this->setPath($path, $type);
    }
    return $this;
  }

  public function getAbsPath($type)
  {
    $path = $this->getPath($type);
    if( $type !== self::PATH_BASE && @$path[0] != DIRECTORY_SEPARATOR && @$path[1] != ':' ) {
      $path = $this->_basePath . DS . $path;
    }
    return $path;
  }

  public function getPath($type = self::PATH_BASE)
  {
    if( !isset($this->_paths[$type]) ) {
      throw new Engine_Package_Manager_Exception('Invalid path type');
    }
    if( $type == self::PATH_BASE && null === $this->_paths[$type] ) {
      $this->_paths[$type] = $this->_basePath = APPLICATION_PATH; // B/c
    }
    return $this->_paths[$type];
  }

  public function getPaths()
  {
    return $this->_paths;
  }

  public function setBasePath($path)
  {
    return $this->setPath($path, self::PATH_BASE);
  }

  public function getBasePath()
  {
    return $this->getPath(self::PATH_BASE);
  }

  public function getTemporaryPath($type)
  {
    $partPath = $this->getPath($type);
    $path = $this->getAbsPath($type);
    $code = 0;

    // Change umask
    if( function_exists('umask') ) {
      $oldUmask = umask();
      umask(0);
    }

    // Check
    if( is_dir($path) ) {
      if( !is_writable($path) ) {
        if( !@chmod($path, 0777) ) {
          $code = 1;
        }
      }
    } else {
      if( !@mkdir($path, 0777, true) ) {
        $code = 2;
      } else {
        @chmod($path, 0777);
      }
    }

    // Revert umask
    if( function_exists('umask') ) {
      umask($oldUmask);
    }

    // Respond
    if( 1 == $code ) {
      throw new Engine_Package_Manager_Exception(sprintf('The temporary ' .
        'directory "%s" is not writable and permissions could not be ' .
        'changed. Please log in over FTP and set CHMOD 0777 on this ' .
        'directory.', $partPath));
    } else if( 2 == $code ) {
      throw new Engine_Package_Manager_Exception(sprintf('The temporary ' .
        'directory "%s" is not writable and could not be created. ' .
        'Please log in over FTP and set CHMOD 0777 on the ' .
        'parent directory, and create this directory.', $partPath));
    } else {
      return $path;
    }
  }

  public function checkTemporaryPaths()
  {
    foreach( $this->_temporaryPaths as $temporaryPathType ) {
      $this->getTemporaryPath($temporaryPathType);
    }
    return $this;
  }



  // FTP
  
  public function setVfs(Engine_Vfs_Adapter_Abstract $vfs)
  {
    $this->_vfs = $vfs;
    return $this;
  }

  /**
   *
   * @return Engine_Vfs_Adapter_Abstract
   */
  public function getVfs()
  {
    return $this->_vfs;
  }

  public function setDb(Zend_Db_Adapter_Abstract $db)
  {
    $this->_db = $db;
    return $this;
  }

  /**
   * Get the db adapter
   * 
   * @return Zend_Db_Adapter_Abstract
   */
  public function getDb()
  {
    return $this->_db;
  }

  public function setCache($cache)
  {
    if( $cache instanceof Zend_Cache_Core ) {
      $this->_cache = $cache;
    } else if( $cache instanceof Zend_Cache_Backend_Interface ) {
      $this->_cache = new Zend_Cache_Core(array(
        'cache_id_prefix' => get_class($this),
      ));
      $this->_cache->setBackend($cache);
    } else {
      throw new Engine_Package_Manager_Exception('Invalid argument');
    }
    
    return $this;
  }

  /**
   *
   * @return Zend_Cache_Core
   */
  public function getCache()
  {
    //if( null === $this->_cache ) {
    //  throw new Engine_Package_Manager_Exception('No cache registered');
    //}
    return $this->_cache;
  }



  // Repositories

  public function setRepository($spec, array $options = array())
  {
    $repository = null;
    if( !($spec instanceof Engine_Package_Manager_Repository) ) {
      if( is_string($spec) ) {
        $options['name'] = $spec;
      } else if( is_array($spec) ) {
        $options = array_merge($options, $spec);
      }

      $options['basePath'] = $this->getBasePath();

      $repository = new Engine_Package_Manager_Repository($options);
    }

    $repository->setManager($this);
    $this->_repositories[$repository->getName()] = $repository;

    return $this;
  }

  public function setRepositories(array $repositories)
  {
    foreach( $repositories as $key => $value ) {
      $this->setRepository($key, $value);
    }

    return $this;
  }

  public function getRepositories()
  {
    if( null === $this->_repositories ) {
      $configFile = $this->getAbsPath(self::PATH_SETTINGS_REPOSITORIES);
      $config = include $configFile;
      if( empty($config) || !is_array($config) ) {
        $this->_repositories = array();
      } else {
        $this->setRepositories($config);
      }
    }

    return $this->_repositories;
  }

  /**
   * Gets a repository by name or host
   * 
   * @param string $repository
   * @return Engine_Package_Manager_Repository
   */
  public function getRepository($repository)
  {
    foreach( $this->getRepositories() as $repositoryObject ) {
      if( $repositoryObject->getHost() == $repository || $repositoryObject->getName() == $repository ) {
        return $repositoryObject;
      }
    }
    return null;
  }



  // Actions

  public function decide(array $packages, array $actions = null)
  {
    // Strip keys
    $packages = array_values($packages);
    
    if( !is_array($packages) || count($packages) <= 0 ) {
      return false;
    }

    // Check action mode
    $actionMode = null;
    if( is_array($actions) ) {
      if( count($actions) == count($packages) && !is_string(key($actions)) ) {
        $actionMode = 'index';
        $actions = array_values($actions);
      } else if( !empty($actions) ) {
        $actionMode = 'key';
      }
    }
    
    // Get package objects
    $extractedPackages = $this->listExtractedPackages();
    $installedPackages = $this->listInstalledPackages();

    // Get object for the ones we want
    $selectedPackages = new Engine_Package_Manager_PackageCollection($this);
    foreach( $packages as $packageKey ) {
      if( !is_string($packageKey) ) {
        continue;
      }
      $packageKey = str_replace(':', '-', $packageKey);

      // Check extracted
      if( $extractedPackages->offsetExists($packageKey) ) {
        $package = $extractedPackages->offsetGet($packageKey);
      }
      // Check installed
      else if( $installedPackages->offsetExists($packageKey) ) {
        $package = $installedPackages->offsetGet($packageKey);
      }
      // Missing?
      else {
        continue;
      }

      // Check selected (for duplicates)
      if( $selectedPackages->hasGuid($package->getGuid()) ) {
        continue;
      }
      $selectedPackages->append($package);
      unset($package);
    }


    // Remove duplicates
    $cleanPackages = array();
    $cleanActions = array();
    foreach( $packages as $index => $package ) {
      $guid = substr($package, 0, strrpos($package, '-'));
      $version = substr($package, strrpos($package, '-') + 1);
      if( !isset($cleanPackages[$guid]) || version_compare($version, $cleanPackages[$guid], '>') ) {
        $cleanPackages[$guid] = $version;
        if( is_array($actions) && isset($actions[$index]) ) {
          $cleanActions[$guid] = $actions[$index];
        } else {
          $cleanActions[$guid] = null;
        }
      }
    }
    
    // Check packages array against installed packages and create transaction
    $i = 0;
    $transaction = new Engine_Package_Manager_Transaction($this);
    foreach( $selectedPackages as $package ) {
      if( !($package instanceof Engine_Package_Manifest) ) {
        continue;
      }
      
      if( $actionMode == 'key' ) {
        $key = $package->getKey();
      } else if( $actionMode == 'index' ) {
        $key = $i;
      } else if( isset($actions[$package->getKey()]) ) {
        $key = $package->getKey();
      } else if( isset($actions[$i]) ) {
        $key = $i;
      } else {
        $key = null;
      }
      
      $action = null;
      if( null !== $key && isset($actions[$key]) ) {
        $action = $actions[$key];
      }

      $targetPackage = $package; // Alias
      
      // Check installed packages
      if( $installedPackages->hasGuid($package->getGuid()) ) {
        $currentPackage = $installedPackages->offsetGet($package->getGuid());
      } else {
        $currentPackage = null;
      }
      
      if( null === $action ) {
        if( null === $currentPackage ) {
          $action = 'install';
        } else {
          switch( version_compare($currentPackage->getVersion(), $targetPackage->getVersion()) ) {
            case 1:
              $action = 'downgrade';
              break;
            case 0:
              //$action = 'ignore';
              $action = 'refresh';
              break;
            case -1:
              $action = 'upgrade';
              break;
            default:
              throw new Engine_Exception('wth happened here?');
              break;
          }
        }
      }

      switch( $action ) {
        case 'install':
          $class = 'Engine_Package_Manager_Operation_' . ucfirst($action);
          $operation = new $class($this, $targetPackage);
          break;
        case 'remove':
        case 'refresh':
        case 'ignore':
        case 'downgrade':
        case 'upgrade':
          $class = 'Engine_Package_Manager_Operation_' . ucfirst($action);
          $operation = new $class($this, $targetPackage, $currentPackage);
          break;
        default:
          throw new Engine_Exception('wth happened here?');
          break;
      }
      $i++;
      $transaction->append($operation);
    }

    return $transaction;
  }

  public function depend($transaction = null)
  {
    if( !empty($transaction) ) {
      // Verify transaction
      $transaction = $this->_verifyTransaction($transaction);
    }

    // Resultant Packages
    $resultantPackages = new Engine_Package_Manager_PackageCollection($this);
    $resultantDependencies = array();

    // Merge in installed packages
    $installedPackages = $this->listInstalledPackages();
    foreach( $installedPackages as $targetPackage ) {
      $resultantPackages->append($targetPackage);
      
      // Make dependencies
      $dependencies = $targetPackage->getDependencies();
      if( !empty($dependencies) ) {
        $targetDependencies = new Engine_Package_Manager_Dependencies($targetPackage, false);
        $targetDependencies->addDependencies($dependencies);
        $resultantDependencies[$targetPackage->getGuid()] = $targetDependencies;
      }
    }

    // Merge in target packages
    if( !empty($transaction) ) {
      foreach( $transaction as $operation ) {
        $targetPackage = $operation->getTargetPackage();
        if( !$targetPackage ) {
          $resultantPackages->offsetUnset($operation->getGuid());
          unset($resultantDependencies[$operation->getGuid()]);
        } else {
          $resultantPackages->offsetUnset($targetPackage->getGuid());
          $resultantPackages->offsetSet($targetPackage->getGuid(), $targetPackage);

          // Make dependencies
          $dependencies = $targetPackage->getDependencies();
          if( !empty($dependencies) ) {
            $targetDependencies = new Engine_Package_Manager_Dependencies($targetPackage, true);
            $targetDependencies->addDependencies($dependencies);
            $resultantDependencies[$targetPackage->getGuid()] = $targetDependencies;
          } else {
            unset($resultantDependencies[$operation->getGuid()]);
          }
        }
      }
    }

    // Now let's compare them all
    foreach( $resultantPackages as $targetPackage ) {
      foreach( $resultantDependencies as $resultantDependency ) {
        $selected = ( $transaction ? $transaction->hasGuid($targetPackage->getGuid()) : false );
        $resultantDependency->compare($targetPackage, $selected);
      }
    }

    return $resultantDependencies;
  }
  
  public function test($transaction)
  {
    // Verify transaction
    $transaction = $this->_verifyTransaction($transaction);

    
    // Check registry for db adapter
    if( Zend_Registry::isRegistered('Zend_Db') && ($db = Zend_Registry::get('Zend_Db')) instanceof Zend_Db_Adapter_Abstract ) {
      Engine_Sanity::setDefaultDbAdapter($db);
    }

    // Make tests
    $batteries = new Engine_Sanity();
    foreach( $transaction as $operation ) {
      $battery = $operation->getTests();
      if( $battery ) {
        $batteries->addTest($battery);
      }
      unset($operation);
    }

    $batteries->run();

    return $batteries;
  }

  public function diff($transaction)
  {
    throw new Engine_Package_Manager_Exception('This function is deprecated.');
  }

  public function callback($transaction, $type, array $params = null)
  {
    // Verify transaction
    $transaction = $this->_verifyTransaction($transaction);

    
    // Index by priority
    $priorityIndex = array();
    $callbackIndex = array();
    $operationIndex = array();
    
    foreach( $transaction as $operation ) {
      $package = $operation->getPrimaryPackage();
      $callback = $package->getCallback();
      if( empty($callback) || !($callback instanceof Engine_Package_Manifest_Entity_Callback) || !$callback->getClass() ) {
        continue;
      }
      
      $index = count($callbackIndex);
      $callbackIndex[$index] = $callback;
      $priorityIndex[$index] = $callback->getPriority();
      $operationIndex[$index] = $operation->getKey();
      unset($operation);
    }

    arsort($priorityIndex);

    $results = array();
    
    foreach( $priorityIndex as $index => $priorityIndex ) {
      $callback = $callbackIndex[$index];
      $operation = $transaction->__get($operationIndex[$index]);

      $result = $this->execute($operation, $type, $params);
      $primaryPackage = $operation->getPrimaryPackage();
      $result['type'] = $type;
      $result['key'] = $primaryPackage->getKey();
      $result['title'] = $primaryPackage->getTitle();
      $result['version'] = $primaryPackage->getVersion();
      //$result['operation'] = $operation;
      $results[] = $result;
      unset($operation);
    }

    return $results;
  }

  public function execute(Engine_Package_Manager_Operation_Abstract $operation, $type, array $params = null)
  {
    $package = $operation->getPrimaryPackage();
    $callback = $package->getCallback();
    if( !$callback || !($callback instanceof Engine_Package_Manifest_Entity_Callback) || !$callback->getClass() ) {
      return false;
    }

    // Include the path, if set
    if( $callback->getPath() ) {
      include_once $package->getBasePath() . '/' . $callback->getPath();
    }
    
    try {
      $instance = $this->getInstaller($callback->getClass(), $operation, $params);
      $instance->notify($type);
      $errors = $instance->getErrors();
      $messages = $instance->getMessages();
      $instance->clearErrors()->clearMessages();
    } catch( Exception $e ) {
      $errors = array($e->getMessage());
      $messages = array();
    }

    return array(
      'errors' => $errors,
      'messages' => $messages,
    );
  }

  public function cleanup($transaction)
  {
    // Verify transaction
    $transaction = $this->_verifyTransaction($transaction);


    // Cleanup
    foreach( $transaction as $operation ) {
      $operation->cleanup();
    }

    return $this;
  }



  // Informational

  /**
   * @param array $options
   * @return Engine_Package_Manager_PackageCollection
   */
  public function listInstalledPackages($options = array())
  {
    if( null !== $this->_installedPackages ) {
      return $this->_installedPackages;
    }

    // Generate
    $installedPackages = new Engine_Package_Manager_PackageCollection($this, array(), $options);
    $it = new DirectoryIterator($this->getAbsPath(self::PATH_INSTALLED));

    // List installed packages
    foreach( $it as $file ) {
      if( $file->isDot() || $file->isDir() || $file->getFilename() === 'index.html' ) continue;
      try {

        $packageFile = new Engine_Package_Manifest($file->getPathname());
        // Reset base path
        $packageFile->setBasePath($this->_basePath);

        // Check for package files for two versions of the package
        $guid = $packageFile->getGuid();
        if( $installedPackages->hasGuid($guid) ) {
          /*
          $otherPackageKey = $installedPackages->getKeyByGuid($guid);
          $otherPackageVersion = trim(str_replace($guid, '', $otherPackageKey), '.:-');
          if( version_compare($packageFile->getVersion(), $otherPackageVersion, '>') ) {
            $installedPackages->append($packageFile);
          }
           *
           */
          $otherPackage = $installedPackages->offsetGet($packageFile->getGuid());
          if( version_compare($packageFile->getVersion(), $otherPackage->getVersion(), '>') ) {
            $installedPackages->append($packageFile);
          }
        } else {
          $installedPackages->append($packageFile);
        }
        unset($packageFile);
      } catch( Exception $e ) {
        // Silence?
        if( APPLICATION_ENV == 'development' ) {
          throw $e;
        }
      }
    }

    // Order? -- @todo probably should switch to priority later
    //$installedPackages->ksort();
    $installedPackages->uasort(array('Engine_Package_Manager', 'cmpOrder'));
    
    return $this->_installedPackages = $installedPackages;;
  }

  /**
   * @param array $options
   * @return Engine_Package_Manager_PackageCollection
   */
  public function listAvailablePackages()
  {
    if( null !== $this->_availablePackages ) {
      return $this->_availablePackages;
    }

    // Generate
    $availablePackages = new Engine_Package_Manager_PackageCollection($this);
    $extractedPath = $this->getAbsPath(self::PATH_PACKAGES);
    $archivesPath = $this->getAbsPath(self::PATH_ARCHIVES);
    if( !is_dir($archivesPath) ) {
      return $availablePackages;
    }
    $it = new DirectoryIterator();
    foreach( $it as $file ) {
      if( $file->isDot() || $file->isDir() || $file->getFilename() === 'index.html' ) continue;
      // Already extracted
      if( is_dir($extractedPath . DIRECTORY_SEPARATOR . substr($file->getFilename(), 0, strrpos($file->getFilename(), '.'))) ) continue;
      try {
        $packageFile = Engine_Package_Archive::readPackageFile($file->getPathname());
        $availablePackages->append($packageFile);
        unset($packageFile);
      } catch( Exception $e ) {
        // Silence?
        //if( APPLICATION_ENV == 'development' ) {
        //  throw $e;
        //}
      }
    }

    // Order? -- @todo probably should switch to priority later
    $availablePackages->ksort();

    return $this->_availablePackages = $availablePackages;
  }

  /**
   * @param array $options
   * @return Engine_Package_Manager_PackageCollection
   */
  public function listExtractedPackages()
  {
    if( null !== $this->_extractedPackages ) {
      return $this->_extractedPackages;
    }

    // Generate
    $extractedPackages = new Engine_Package_Manager_PackageCollection($this);
    $extractedPath = $this->getAbsPath(self::PATH_PACKAGES);
    if( !is_dir($extractedPath) ) {
      return $extractedPackages;
    }
    $it = new DirectoryIterator($this->getAbsPath(self::PATH_PACKAGES));
    foreach( $it as $file ) {
      if( $file->isDot() || !$file->isDir() || $file->getFilename() == '.svn' ) continue;
      try {
        $packageFile = new Engine_Package_Manifest($file->getPathname(), array(
          'basePath' => $file->getPathname(),
        ));
        $extractedPackages->append($packageFile);
        unset($packageFile);
      } catch( Exception $e ) {
        // Silence?
        //if( APPLICATION_ENV == 'development' ) {
        //  throw $e;
        //}
      }
    }

    // Order? -- @todo probably should switch to priority later
    $extractedPackages->ksort();

    return $this->_extractedPackages = $extractedPackages;
  }

  /**
   * @param array $options
   * @return Engine_Package_Manager_PackageCollection
   */
  public function listUpgradeablePackages()
  {
    return; // Not yet implemented

    $installedPackages = $this->listInstalledPackages();
    $repositories = $this->getRepositories();

    // Index installed packages
    $repoIndex = array();
    foreach( $installedPackages as $installedPackage ) {
      $repositoryName = $installedPackage->getRepository();
      // No repo
      if( empty($repositoryName) ) continue;
      // No configured repo
      if( empty($repositories[$repositoryName]) ) continue;
      // Add to queue
      $repoIndex[$repositoryName][] = $installedPackage;
    }

    // Check for updates
    foreach( $repoIndex as $repositoryName => $packages ) {
      $repository = $repositories[$repositoryName];
      if( empty($repository) ) continue; // Sanity

      //$repository->
    }
  }



  // Utility

  public function getInstaller($class, Engine_Package_Manager_Operation_Abstract $operation,
    array $params = null)
  {
    $key = $operation->getKey();
    if( !isset($this->_installers[$key]) ) {
      if( !class_exists($class) ) { // Forces autoload
        throw new Engine_Package_Installer_Exception(sprintf('Unable to load installer class %s', $class));
      }
      $params['db'] = $this->getDb();
      $params['vfs'] = $this->getVfs();
      $this->_installers[$key] = new $class($operation, $params);
    }
    return $this->_installers[$key];
  }
  
  protected function _verifyTransaction($transaction)
  {
    if( is_array($transaction) ) {
      foreach( $transaction as $operation ) {
        if( !($operation instanceof Engine_Package_Manager_Operation_Abstract) ) {
          throw new Engine_Package_Manager_Exception('Not an operation');
        }
      }
      $transaction = new Engine_Package_Manager_Transaction($this, $transaction);
    } else if( $transaction instanceof Engine_Package_Manager_Operation_Abstract ) {
      $transaction = new Engine_Package_Manager_Transaction($this, array($transaction));
    } else if( !($transaction instanceof Engine_Package_Manager_Transaction) ) {
      throw new Engine_Package_Manager_Exception('Not a transaction');
    } else {
      $transaction->setManager($this);
    }

    return $transaction;
  }
  
  static public function cmpOrder($a, $b)
  {
    if( !($a instanceof Engine_Package_Manifest_Entity_Package) || 
        !($b instanceof Engine_Package_Manifest_Entity_Package) ) {
      return 0;
    }
    $ac = $a->getCallback();
    $bc = $b->getCallback();
    if( !($ac instanceof Engine_Package_Manifest_Entity_Callback) ||
        !($bc instanceof Engine_Package_Manifest_Entity_Callback)) {
      return 0;
    }
    $acp = $ac->getPriority();
    $bcp = $bc->getPriority();
    if( $acp == $bcp ) {
       return strcasecmp($a->getTitle(), $b->getTitle());
    } else {
      return $acp < $bcp;
    }
  }
}