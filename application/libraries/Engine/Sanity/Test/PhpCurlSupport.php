<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PhpCurlSupport.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_PhpCurlSupport extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'noCurl' => 'cURL is not installed.',
    'noIpv6' => 'IPv6 support is not available.',
    'noKerberos4' => 'Kerberos4 support is not available.',
    'noLibz' => 'Libz support is not available.',
    'noSsl' => 'SSL support is not available.',
    'tooLowVersion' => 'Requires at least version %min_version%',
    'tooHighVersion' => 'Requires no greater than %max_version%',
    'tooLowSslVersion' => 'Requires at least version %min_version% SSL support',
    'tooHighSslVersion' => 'Requires no greater than %max_version% SSL support',
  );

  protected $_messageVariables = array(
    //'ipv6' => '_ipv6',
    //'libz' => '_libz',
    'minSslVersion' => '_minSslVersion',
    'maxSslVersion' => '_maxSslVersion',
    'minVersion' => '_minVersion',
    'maxVersion' => '_maxVersion',
    //'protocols' => '_protocols',
    //'ssl' => '_ssl',
  );
  
  protected $_ipv6;

  protected $_kerberos4;
  
  protected $_libz;
  
  protected $_minSslVersion;

  protected $_maxSslVersion;

  protected $_minVersion;

  protected $_maxVersion;
  
  protected $_protocols;

  protected $_ssl;

  public function setIpv6($flag)
  {
    $this->_ipv6 = (bool) $flag;
    return $this;
  }

  public function setKerberos4($flag)
  {
    $this->_kerberos4 = (bool) $flag;
    return $this;
  }

  public function setLibz($flag)
  {
    $this->_libz = (bool) $flag;
    return $this;
  }

  public function setProtocols($protocols)
  {
    $this->_protocols = (array) $protocols;
    return $this;
  }

  public function setMinSslVersion($minSslVersion)
  {
    $this->_minSslVersion = $minSslVersion;
    return $this;
  }

  public function setMaxSslVersion($maxSslVersion)
  {
    $this->_maxSslVersion = $maxSslVersion;
    return $this;
  }

  public function setMinVersion($minVersion)
  {
    $this->_minVersion = $minVersion;
    return $this;
  }

  public function setMaxVersion($maxVersion)
  {
    $this->_maxVersion = $maxVersion;
    return $this;
  }

  public function setSsl($flag)
  {
    $this->_ssl = (bool) $flag;
    return $this;
  }

  public function execute()
  {
    // Check if we have curl
    if( !extension_loaded('curl') || !function_exists('curl_version') ) {
      return $this->_error('noCurl');
    }

    // Get curl info
    $info = curl_version();

    // Check features
    if( $this->_ipv6 && !($info['features'] & CURL_VERSION_IPV6) ) {
      $this->_error('noIpv6');
    }
    if( $this->_kerberos4 && !($info['features'] & CURL_VERSION_KERBEROS4) ) {
      $this->_error('noKerberos4');
    }
    if( $this->_libz && !($info['features'] & CURL_VERSION_LIBZ) ) {
      $this->_error('noLibz');
    }
    if( $this->_ssl && !($info['features'] & CURL_VERSION_SSL) ) {
      $this->_error('noSsl');
    }

    // Check ssl version
    if( ($this->_minSslVersion || $this->_maxSslVersion) ) {
      if( empty($info['ssl_version']) ||
          !($info['features'] & CURL_VERSION_SSL) ||
          !preg_match('/[\d.]+/', $info['ssl_version'], $matches) ||
          !($sslVersion = $matches[0]) ) {
        $this->_error('noSsl');
      } else {
        if( $this->_minSslVersion && version_compare($sslVersion, $this->_minSslVersion, '<') ) {
          $this->_error('tooLowSslVersion');
        }
        if( $this->_maxSslVersion && version_compare($sslVersion, $this->_maxSslVersion, '>') ) {
          $this->_error('tooHighSslVersion');
        }
      }
    }

    // Check curl lib version
    if( $this->_minVersion && version_compater($info['version'], $this->_minVersion, '<') ) {
      $this->_error('tooLowVersion');
    }
    if( $this->_maxVersion && version_compare($info['version'], $this->_maxVersion, '>') ) {
      $this->_error('tooHighVersion');
    }

    // Check protocols
    if( is_array($this->_protocols) && !empty($this->_protocols) ) {
      $missingProtocols = array_diff($this->_protocols, $info['protocols']);
      if( !empty($missingProtocols) ) {
        $this->_error('missingProtocols');
      }
    }

    return $this;
  }
}