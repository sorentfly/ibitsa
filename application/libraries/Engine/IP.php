<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_IP
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Form.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_IP
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_IP
{
  // Constants

  const MASK = '::ffff:';



  // Static
  
  static protected $_preferIPv6 = false;

  static public function setPreferIPv6($flag = true)
  {
    self::$_preferIPv6 = (bool) $flag;
  }


  
  // Properties
  
  protected $_address;



  // General
  
  public function __construct($address = null)
  {
    if( null === $address ) {
      $address = self::getRealRemoteAddress();
    }
    $this->_address = self::normalizeAddress($address);
  }



  // Type
  
  public function isIPv4()
  {
    return ( false === strpos($this->_address, ':') );
  }

  public function isIPv6()
  {
    return ( false !== strpos($this->_address, ':') );
  }



  // Address

  public function isValid()
  {
    return ( false !== $this->_address );
  }
  
  public function toString()
  {
    return $this->_address;
  }

  public function toBinary()
  {
    return self::inet_pton($this->_address);
  }

  public function toLong()
  {
    if( $this->isIPv4() ) {
      return ip2long($this->_address);
    } else {
      return false;
    }
  }

  public function toHex()
  {
    return bin2hex($this->toBinary());
  }

  public function toIPv4()
  {
    return self::convertIPv6to4($this->_address);
  }

  public function toIPv6()
  {
    return self::convertIPv4to6($this->_address);
  }



  // Magic
  
  public function __toString()
  {
    return $this->toString();
  }



  // Static utility

  static public function normalizeAddress($address)
  {
    if( is_int($address) ) {
      // Probably an IPv4 address as an integer
      $address = long2ip($address);
    } else if( !is_string($address) ) {
      // Not a string?
      return false;
    } else if( preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $address) ) {
      // An IPv4 address as a string
      //$address = $address;
    } else if( strlen($address) == 4 ) {
      // This is probably a binary IPv4 address created using inet_pton
      $address = self::inet_ntop($address);
    } else if( strlen($address) == 16 ) {
      // This is probably a binary IPv6 address created using inet_pton
      $address = self::inet_ntop($address);
    } else if( is_numeric($address) && false !== ($tmp = long2ip($address)) ) {
      // An IPv4 address created using ip2long but stored as a string?
      $address = $tmp;
    } else if( false !== ($tmp = self::inet_pton($address)) ) {
      // This is simply checking if running the IP through inet_pton does not
      // return false. This should validate if it's a proper address
      //$address = $address;
    } else {
      return false;
    }

    // Convert if necessary
    if( self::$_preferIPv6 ) {
      if( false !== ($tmp = self::convertIPv4to6($address)) ) {
        $address = $tmp;
      }
    } else {
      if( false !== ($tmp = self::convertIPv6to4($address)) ) {
        $address = $tmp;
      }
    }

    return $address;
  }

  static public function normalizeAddressToBinary($address)
  {
    $address = self::normalizeAddress($address);
    if( $address ) {
      $address = self::inet_pton($address);
    }
    return $address;
  }

  static public function inet_pton($address)
  {
    if (!$address) return 0;

    $address = explode('[',$address)[0];
      $address = explode(':',$address)[0];
    if (substr($address,-1,1)=='.'){
        $address .= '0';
    }
    return mb_strpos($address, 'unk')!==0 ? inet_pton($address) : 0;
  }

  /**
   * This function converts a 32bit IPv4, or 128bit IPv6 address into an 
   * address family appropriate string representation. Currently wraps the 
   * PHP inet_ntop function, which means PHP 5.3 is required for Windows
   * users. This function was not available for PHP versions prior to 5.3
   * on Windows.
   *
   * @param string $address 
   * @return mixed
   */
  static public function inet_ntop($address)
  {
    if( function_exists('inet_ntop') ) {
      return inet_ntop($address);
    } else {
      // Compat
      if( strlen($address) == 16 ) {
        // IPv6
        // @TODO
      } else if( strlen($address) == 4 ) {
        // IPv4
        // @TODO
      } else {
        return false;
      }
    }
  }

  static public function expandIPv6Notation($address, $padGroups = true)
  {
    // From: http://stackoverflow.com/questions/444966/working-with-ipv6-addresses-in-php
    if( false !== strpos($address, '::') ) {
      $address = str_replace('::', str_repeat(':0', 8 - substr_count($address, ':')) . ':', $address);
    }
    if( 0 === strpos($address, ':') ) {
      $address = '0' . $address;
    }
    if( $padGroups ) {
      $parts = explode(':', $address);
      foreach( $parts as &$part ) {
        if( strlen($part) < 4 ) {
          $part = str_pad($part, 4, 0, STR_PAD_LEFT);
        }
      }
      $address = join(':', $parts);
    }
    return $address;
  }

  static public function convertIPv4to6($address)
  {
    // From: http://stackoverflow.com/questions/444966/working-with-ipv6-addresses-in-php
    $IPv6 = ( 0 === strpos($address, '::') );
    $IPv4 = ( 0 < strpos($address, '.') );

    if( !$IPv4 && !$IPv6 ) {
      return false;
    } else if( $IPv6 && $IPv4 ) {
      // Strip IPv4 Compatibility notation
      $address = substr($address, strrpos($address, ':') + 1); 
    } else if( !$IPv4 ) {
      return $address; // Seems to be IPv6 already?
    }
    $address = array_pad(explode('.', $address), 4, 0);
    if( count($address) > 4 ) {
      return false;
    }
    for( $i = 0; $i < 4; $i++ ) {
      if( $address[$i] > 255 ) {
        return false;
      }
    }

    $Part7 = base_convert(($address[0] * 256) + $address[1], 10, 16);
    $Part8 = base_convert(($address[2] * 256) + $address[3], 10, 16);
    return self::MASK . $Part7 . ':' . $Part8;
  }

  static public function convertIPv6to4($address)
  {
    $IPv6 = ( 0 === strpos($address, '::') );
    $IPv4 = ( 0 < strpos($address, '.') );

    if( $IPv4 && !$IPv6 ) {
      // Already IPv4?
      return $address;
    } else if( $IPv6 && self::MASK == substr($address, 0, strlen(self::MASK)) ) {
      // Strip mask
      $address = substr($address, strlen(self::MASK));
      // If in hex format, convert to IPv4 format
      if( false !== strpos($address, ':') ) {
        list($part7, $part8) = explode(':', $address);
        $part7 = hexdec($part7);
        $part8 = hexdec($part8);
        $address = sprintf('%d.%d.%d.%d',
          floor($part7 / 256),
          ($part7 % 256),
          floor($part8 / 256),
          ($part8 % 256)
        );
      }
    } else {
      return false;
    }
  }

  static public function getRealRemoteAddress($asIPv6 = null)
  {
    $address = $_SERVER['REMOTE_ADDR'];
    if( false !== ($pos = strpos($address, ',')) ) {
      $address = substr($address, 0, $pos - 1);
    }

    if( null === $asIPv6 ) {
      $asIPv6 = self::$_preferIPv6;
    }

    if( $asIPv6 ) {
      return self::convertIPv4to6($address);
    } else {
      return $address;
    }
  }
}