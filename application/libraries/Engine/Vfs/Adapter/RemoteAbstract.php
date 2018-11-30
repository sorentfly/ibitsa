<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: RemoteAbstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

//require_once 'Engine/Vfs/Adapter/Interface.php';
//require_once 'Engine/Vfs/Adapter/Abstract.php';
//require_once 'Engine/Vfs/Adapter/Exception.php';

/**
 * @category   Engine
 * @package    Engine_Vfs
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Vfs_Adapter_RemoteAbstract extends Engine_Vfs_Adapter_Abstract
{
  protected $_host;

  protected $_port;

  protected $_timeout = 90;

  protected $_username;

  protected $_password;

  public function __destruct()
  {
    $this->disconnect();
  }

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_host', '_port', '_timeout', '_useSsl', '_username', '_password'
    ));
  }

  public function getResource()
  {
    if( null === $this->_resource ) {
      $this->connect();
      $this->login();
    }

    return $this->_resource;
  }



  // Config
  
  public function setHost($host)
  {
    if( strpos($host, ':') !== false ) {
      $urlInfo = parse_url($host);
      if( !empty($urlInfo['host']) ) {
        $host = $urlInfo['host'];
      }
      if( !empty($urlInfo['port']) ) {
        $this->setPort($urlInfo['port']);
      }
      if( !empty($urlInfo['user']) ) {
        $this->setUsername($urlInfo['user']);
      }
      if( !empty($urlInfo['pass']) ) {
        $this->setPassword($urlInfo['pass']);
      }
    }
    $this->_host = $host;
    return $this;
  }

  public function getHost()
  {
    return $this->_host;
  }

  public function setPort($port)
  {
    $this->_port = $port;
    return $this;
  }

  public function getPort()
  {
    return $this->_port;
  }

  public function setTimeout($timeout)
  {
    $this->_timeout = (int) $timeout;
    return $this;
  }

  public function getTimeout()
  {
    return $this->_timeout;
  }

  public function setUsername($username)
  {
    $this->_username = $username;
    return $this;
  }

  public function getUsername()
  {
    return $this->_username;
  }

  public function setPassword($password)
  {
    $this->_password = $password;
    return $this;
  }

  public function getPassword()
  {
    return $this->_password;
  }



  // Abstract

  abstract public function connect();

  abstract public function disconnect();

  abstract public function login();
}