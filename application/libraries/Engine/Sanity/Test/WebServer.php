<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: WebServer.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_WebServer extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'badWebServer' => 'Unsupported web server type.',
    'basWebServerVersion' => 'Unsupported web server version.',
  );

  protected $_messageVariables = array(
    'allowed' => '_allowed',
    'versions' => '_versions',
    'server_type' => '_serverType',
    'server_version' => '_serverVersion',
    'server_software' => '_serverSoftware',
  );
  
  const TYPE_APACHE = 0;
  const TYPE_IIS = 1;
  const TYPE_LIGHTTPD = 2;
  const TYPE_NGINX = 4;
  const TYPE_UNKNOWN = 8;
  const TYPE_ALL = 15;

  protected $_allowed;

  protected $_versions;

  protected $_serverType;

  protected $_serverVersion;

  protected $_serverSoftware;

  public function setAllowed($allowed)
  {
    $this->_allowed = $allowed;
    return $this;
  }

  public function getAllowed()
  {
    return $this->_allowed;
  }

  public function setVersions(array $versions)
  {
    $this->_versions = $versions;
    return $this;
  }

  public function getVersions()
  {
    return $this->_versions;
  }

  public function execute()
  {
    $this->_serverSoftware = $wserv = $_SERVER['SERVER_SOFTWARE'];
    $type = null;
    $version = null;
    if( preg_match('/([^\s]+)\/([^\s]+)/', $wserv, $matches) ) {
      $this->_serverType = $type = @$matches[1];
      $this->_serverVersion = $version = @$matches[2];
    }
    $intType = null;
    
    $allowed = (int) $this->getAllowed();
    $versions = $this->getVersions();

    switch( true ) {
      case ( strpos(strtoupper($type), 'APACHE') !== false ):
        $intType = self::TYPE_APACHE;
        break;
      case ( strpos(strtoupper($type), 'IIS') !== false ):
        $intType = self::TYPE_IIS;
        break;
      case ( strpos(strtoupper($type), 'LIGHTTPD') !== false ):
        $intType = self::TYPE_LIGHTTPD;
        break;
      case ( strpos(strtoupper($type), 'NGINX') !== false ):
        $intType = self::TYPE_NGINX;
        break;
      default:
        $intType = self::TYPE_UNKNOWN;
        break;
    }

    if( !($allowed & $intType) ) {
      return $this->_error('badWebServer');
    }

    if( !empty($version) && isset($versions[$intType]) && version_compare($version, $versions[$intType], '<') ) {
      return $this->_error('badWebServerVersion');
    }
  }
}