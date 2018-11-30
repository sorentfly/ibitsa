<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_String
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_String
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_String
{
  static protected $_isNative;

  static public function isNative()
  {
    if( null === self::$_isNative )
    {
      self::$_isNative = function_exists('mb_strpos');
    }
    return self::$_isNative;
  }

  static public function strlen($string)
  {
    // Native
    if( self::isNative() )
    {
      return mb_strlen($string);
    }

    // Custom
    return strlen(preg_replace('/./u', ' ', $string));
  }

  static public function substr($string, $start, $length = null)
  {
    // Native
    if( self::isNative() )
    {
      return mb_substr($string, $start, $length);
    }

    // Custom
    $strlen = self::strlen($string);
    if( $start < 0 ) $start += $strlen;
    if( func_num_args() <= 2 ) $length = $strlen - $start;
    if( $length < 0 ) $length += $strlen - $start;

    $regex = '/^' . ( $start > 0 ? '.{'.$start.'}' : '' ) . '(.{0,'.$length.'})/u';
    preg_match($regex, $string, $m);

    if( !empty($m[1]) ) {
      return $m[1];
    } else {
      return $string;
    }
  }

  static public function strpos($haystack, $needle, $offset = null)
  {
    // Native
    if( self::isNative() )
    {
      return mb_strpos($haystack, $needle, $offset);
    }

    // Custom
    $regex = '/^(';
    if( $offset > 0 ) $regex .= '.{'.$offset.'}';
    $regex .= '.*?)'.preg_quote($needle).'.*$/u';
    if( !preg_match($regex, $haystack) ) return false;
    return self::strlen(preg_replace($regex, ' ', $haystack));
  }

  static public function strip_tags($str, $allowable_tags = null)
  {
    return strip_tags($str, $allowable_tags);
    
    // @todo this might not actually be necessary
    $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
    $str = strip_tags($str, $allowable_tags);
    $str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
    return $str;
  }

  static public function ucfirst($str)
  {
    if( self::isNative() ) {
      return mb_strtoupper(mb_substr($str, 0, 1), 'UTF-8') . mb_substr($str, 1);
    } else {
//      if( !preg_match('/^(.)(.*?)$/u') ) {
//        return $str;
//      } else {
//
//      }
      // No support?
      return ucfirst($str);
    }
  }
  
  static public function str_random($length = 10)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $countCharacters = self::strlen($characters);
    $randomString = '';    
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $countCharacters - 1)];
    }
    return $randomString;
  }
}

