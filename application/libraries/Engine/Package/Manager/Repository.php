<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Repository.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manager_Repository
{
  protected $_manager;
  
  protected $_name;
  
  protected $_host;

  protected $_path;

  protected $_client;

  protected $_basePath;

  protected $_temporaryPath = 'application/temporary/package';

  protected $_cachePath = 'application/temporary/repositories';

  protected $_throwExceptions = false;

  protected $_cacheLifetime = 86400;

  protected $_auth;
  
  public function __construct(array $options = null)
  {
    if( is_array($options) ) {
      $this->setOptions($options);
    }

    if( null === $this->_host ) {
      throw new Engine_Package_Exception('No uri supplied');
    }

    if( null === $this->_basePath ) {
      $this->_basePath = APPLICATION_PATH;
    }
  }

  public function setOptions(array $options)
  {
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( method_exists($this, $method) ) {
        $this->$method($value);
      }
    }
  }

  public function setManager(Engine_Package_Manager $manager = null)
  {
    $this->_manager = $manager;
    return $this;
  }

  /**
   * Get manager instance
   * 
   * @return Engine_Package_Manager
   */
  public function getManager()
  {
    if( null === $this->_manager ) {
      throw new Engine_Package_Exception('No manager registered');
    }
    return $this->_manager;
  }

  public function setName($name)
  {
    $this->_name = (string) $name;
    return $this;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function setHost($host)
  {
    $this->_host = $host;
    if( null !== $this->_client ) {
      $this->_client->setUri('http://' . $this->getHost() . $this->getPath());
    }
    return $this;
  }

  public function getHost()
  {
    if( null === $this->_host ) {
      throw new Engine_Package_Exception('No host supplied');
    }
    return $this->_host;
  }

  public function setPath($path)
  {
    if( !is_string($path) || $path[0] !== '/' ) {
      throw new Engine_Package_Exception('Path must be a string and must begin with a slash (/).');
    }
    $this->_path = $path;
    if( null !== $this->_client ) {
      $this->_client->setUri('http://' . $this->getHost() . $this->getPath());
    }
    return $this;
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function setBasePath($path)
  {
    if( !is_dir($path) ) {
      throw new Engine_Package_Exception('Base path is not valid');
    }
    $this->_basePath = $path;
    return $this;
  }

  public function getBasePath()
  {
    if( null === $this->_basePath ) {
      throw new Engine_Package_Exception('No base path supplied');
    }
    return $this->_basePath;
  }

  public function setTemporaryPath($path)
  {
    $this->_temporaryPath = $path;
  }

  public function getTemporaryPath()
  {
    if( null === $this->_temporaryPath ) {
      throw new Engine_Package_Exception('No temporary path supplied');
    }
    return $this->_temporaryPath;
  }

  public function setCachePath($path)
  {
    $this->_cachePath = $path;
  }

  public function getCachePath()
  {
    if( null === $this->_cachePath ) {
      throw new Engine_Package_Exception('No cache path supplied');
    }
    return $this->_cachePath;
  }

  /**
   * Get current client
   * 
   * @return Zend_Http_Client
   */
  public function getClient()
  {
    if( null === $this->_client ) {
      $this->_client = new Zend_Http_Client();
      $this->_client->setUri('http://' . $this->getHost() . $this->getPath());
      $this->_client->setCookieJar(true);
      $this->_client->setAdapter('Zend_Http_Client_Adapter_Curl');
    }

    return $this->_client;
  }



  // Stuff

  public function queryInfo($package)
  {
    if( $package instanceof Engine_Package_Manifest_Entity_Package ) {
      $package = $package->getGuid();
    } else if( !is_string($package) ) {
      return false;
    }

    $client = $this->getClient();
    $client
      ->resetParameters()
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet(array(
        'action' => 'info',
        'guid' => $package,
      ))
      ;

    $response = $client->request();
    $result = $response->getBody();
    $responseData = $this->_processResponse($result);

    return $responseData;
  }

  public function queryList()
  {
    $client = $this->getClient();
    $client
      ->resetParameters()
      ->setMethod(Zend_Http_Client::GET)
      ->setParameterGet(array(
        'action' => 'list',
      ))
      ;

    $response = $client->request();
    $result = $response->getBody();
    $responseData = $this->_processResponse($result);

    return $responseData;
  }






  
  // Auth

  /*
  public function getAuth()
  {
    if( null === $this->_auth ) {
      // Try to pull pre-configured info
      $nodeFile = $this->getManager()->getAbsPath(Engine_Package_Manager::PATH_SETTINGS_NODE);
      $nodeConfig = array();
      if( file_exists($nodeFile) ) {
        $nodeConfig = include $nodeFile;
      }
      
      // Check to make sure it's up to date
      $db = $this->getManager()->getDb();
      $key = $db->select()
        ->from('engine4_core_settings')
        ->where('name = ?', 'core.license.key')
        ->limit(1)
        ->query()
        ->fetchObject()
        ->value;

      $host = $_SERVER['HTTP_HOST'];
      $path = dirname($_SERVER['SCRIPT_NAME']);
      $base = Zend_Controller_Front::getInstance()->getBaseUrl();

      // Remove install
      $path = str_replace('/install', '', $path);
      $base = str_replace('/install', '', $base);

      // Compare to stored config
      $failed = false;
      if( empty($nodeConfig) ) {
        $failed = true;
      } else if( empty($nodeConfig['identity']) || strlen($nodeConfig['identity']) != 40 ) {
        $failed = true;
      } else if( $host != @$nodeConfig['host'] ) {
        $failed = true;
      } else if( $path != @$nodeConfig['path'] ) {
        $failed = true;
      } else if( $base != @$nodeConfig['base'] ) {
        $failed = true;
      } else if( $key != @$nodeConfig['key'] ) {
        $failed = true;
      }

      // Issue new auth token
      if( $failed ) {
        $identity = $this->_getAuthIdentity();
        if( !$identity || strlen($identity) != 40 ) {
          throw new Core_Model_Exception('Unable to generate node identity.');
        }
        $nodeConfig = array(
          'identity' => $identity,
          'host' => $host,
          'path' => $path,
          'base' => $base,
          'key'  => $key,
        );

        @file_put_contents($nodeFile, '<?php return ' . var_export($nodeConfig, true) . '; ?>');

        if( !file_exists($nodeFile) ) {
          throw new Engine_Package_Exception('Unable to verify node config.');
        }

        $nodeConfig = include $nodeFile;
        if( !isset($nodeConfig['identity']) || strlen($nodeConfig['identity']) != 40 ) {
          throw new Engine_Package_Exception('Unable to verify node identity.');
        }
      }

      $this->_auth = $nodeConfig;
    }

    return $this->_auth;
  }

  protected function _getAuthIdentity()
  {
    // Generate token
    $token = sha1(uniqid(serialize($_SERVER), true));

    // Save token temporarily
    $db = $this->getManager()->getDb();
    try {
      $db->insert('engine4_core_settings', array(
        'name' => 'core.license.token',
        'value' => $token,
      ));
    } catch( Exception $e ) {
      $db->update('engine4_core_settings', array(
        'value' => $token,
      ), array(
        'name = ?' => 'core.license.token',
      ));
    }

    // Prepare info
    $key = $db->select()
      ->from('engine4_core_settings')
      ->where('name = ?', 'core.license.key')
      ->limit(1)
      ->query()
      ->fetchObject()
      ->value;

    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $base = Zend_Controller_Front::getInstance()->getBaseUrl();
    
    $path = str_replace('/install', '', $path);
    $base = str_replace('/install', '', $base);
    
    // Send request
    $error = null;
    try {
      $client = $this->getClient();
      $client
        ->resetParameters()
        ->setMethod(Zend_Http_Client::POST)
        ->setParameterGet(array(
          'action' => 'nodes',
        ))
        ->setParameterPost(array(
          'host' => $host,
          'path' => $path,
          'base' => $base,
          'key' => $key,
          'token' => $token,
          'format' => 'json'
        ))
        ;
      $response = $client->request();
      $result = $response->getBody();
    } catch( Exception $e ) {
      $error = $e;
    }
    
    // Remove tempkey
    $db->delete('engine4_core_settings', array(
      'name = ?' => 'core.license.token',
    ));

    if( $error ) {
      throw $error;
    }
    
    // Save node id
    $return = Zend_Json::decode($result);
    if( !is_array($return) || !isset($return['responseStatus']) || !isset($return['responseData']) ) {
      throw new Engine_Package_Exception('Unable to generate node identity (2).');
    }
    if( @$return['responseData']['code'] == 5 ) {
      if( APPLICATION_ENV == 'development' ) {
        throw new Engine_Package_Exception(Zend_Json::encode($return), 5);
      }
      return false;
    }
    if( $return['responseStatus'] != 200 ) {
      throw new Engine_Package_Exception('Unable to generate node identity (3).');
    }
    if( $return['responseData']['code'] != 12 && $return['responseData']['code'] != 18 ) {
      throw new Engine_Package_Exception('Unable to generate node identity (4).');
    }
    if( empty($return['responseData']['key']) || strlen($return['responseData']['key']) != 40 ) {
      throw new Engine_Package_Exception('Unable to generate node identity (5).');
    }

    $nodeIdentity = $return['responseData']['key'];

    return $nodeIdentity;
  }
   * 
   */

  protected function _processResponse($body)
  {
    $body = Zend_Json::decode($body);
    if( !is_array($body) ) {
      throw new Engine_Package_Manager_Exception(sprintf('Unable to decode response body: %s', $body));
    }
    if( !isset($body['responseStatus']) || !isset($body['responseData']) ) {
      throw new Engine_Package_Manager_Exception(sprintf('Unable to decode response body: %s', var_export($body, true)));
    }
    if( $body['responseStatus'] !== 200 ) {
      throw new Engine_Package_Manager_Exception(sprintf('Response returned error code: %d', $body['responseStatus']));
    }
    return $body['responseData'];
  }
}