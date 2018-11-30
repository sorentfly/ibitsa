<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Locale.php 10031 2013-03-28 23:11:41Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_Locale extends Zend_View_Helper_Abstract
{
  /**
   * The current locale
   * 
   * @var Zend_Locale
   */
  protected $_locale;

  /**
   * Accessor
   * 
   * @return Engine_View_Helper_Locale
   */
  public function locale()
  {
    return $this;
  }
  
  /**
   * Magic caller
   * 
   * @param string $method
   * @param array $args
   * @return mixed
   */
  public function __call($method, array $args)
  {
    $locale = $this->getLocale();
    $r = new ReflectionMethod($locale, $method);
    return $r->invokeArgs($locale, $args);
  }

  /**
   * Set the current locale
   * 
   * @param string|Zend_Locale $locale
   * @return Engine_View_Helper_Locale
   */
  public function setLocale($locale)
  {
    if( is_string($locale) )
    {
      $locale = new Zend_Locale($locale);
    }

    if( !$locale instanceof Zend_Locale )
    {
      throw new Zend_View_Exception('Not passed locale object or valid locale string');
    }

    $this->_locale = $locale;
    return $this;
  }

  /**
   * Get the current locale. Defaults to locale in registry
   * 
   * @return Zend_Locale
   */
  public function getLocale()
  {
    if( null === $this->_locale )
    {
      $this->_locale = Zend_Registry::get('Locale');
    }

    return $this->_locale;
  }

  public function getTimezone()
  {
    return Zend_Registry::get('timezone');
  }

  /**
   * Format a number according to locale and currency
   * @param  integer|float  $number
   * @return string
   * @see Zend_Currency::toCurrency()
   */
  public function toCurrency($value, $currency, $options = array())
  {
    $options = array_merge(array(
      'locale' => $this->getLocale(),
      'display' => 2,
      'precision' => 2,
    ), $options);

    // Doesn't like locales w/o regions
    if( is_object($options['locale']) ) {
      $locale = $options['locale']->__toString();
    } else {
      $locale = (string) $options['locale'];
    }
    if( strlen($locale) < 5 ) {
      $locale = Zend_Locale::getBrowser();
      if( is_array($locale) ) {
        foreach( $locale as $browserLocale => $q ) {
          if( strlen($browserLocale) >= 5 ) {
            $locale = $browserLocale;
            break;
          }
        }
      }
      if( !$locale || !is_string($locale) || strlen($locale) < 5 ) {
        $locale = 'en_US';
      }
    }
    unset($options['locale']);
    
    $currency = new Zend_Currency($currency, $locale);
    return $currency->toCurrency($value, $options);
  }
  
  /**
   * Format a number according to locale
   * @param mixed $number
   * @see Zend_Locale_Format::toNumber()
   */
  public function toNumber($number, $options = array())
  {
    $options = array_merge(array(
      'locale' => $this->getLocale()
    ), $options);

    // Convert numerals?
    $convert = false;
    if( !isset($options['convert']) || $options['convert'] ) {
      $convert = true;
      unset($options['convert']);
    }
    
    // Format
    $number = Zend_Locale_Format::toNumber($number, $options);

    // Convert numerals
    if( $convert ) {
      $number = $this->convertNumerals($number, $options['locale']);
    }

    return $number;
  }

  public function toTime($date, $options = array())
  {
    $options = array_merge(array(
      'locale' => $this->getLocale(),
      'size' => 'short',
      'type' => 'time',
      'timezone' => Zend_Registry::get('timezone'),
    ), $options);

    $date = $this->_checkDateTime($date, $options);
    if( !$date ) {
      return false;
    }

    if( empty($options['format']) ) {
      $options['format'] = Zend_Locale_Data::getContent($options['locale'], $options['type'], $options['size']);
    }
    // Hack for weird usage of L instead of M in Zend_Locale
    $options['format'] = str_replace('L', 'M', $options['format']);

    $str = $date->toString($options['format'], $options['locale']);
    $str = $this->convertNumerals($str, $options['locale']);
    return $str;
  }

  public function toDate($date, $options = array())
  {
    $options = array_merge(array(
      'locale' => $this->getLocale(),
      'size' => 'short',
      'type' => 'date',
      'timezone' => Zend_Registry::get('timezone'),
    ), $options);

    $date = $this->_checkDateTime($date, $options);
    if( !$date ) {
      return false;
    }

    if( empty($options['format']) ) {
      $options['format'] = Zend_Locale_Data::getContent($options['locale'], $options['type'], $options['size']);
    }
    // Hack for weird usage of L instead of M in Zend_Locale
    $options['format'] = str_replace('L', 'M', $options['format']);
    
    $str = $date->toString($options['format'], $options['locale']);
    $str = $this->convertNumerals($str, $options['locale']);
    return $str;
  }

  public function toDateTime($date, $options = array())
  {
    $options = array_merge(array(
      'locale' => $this->getLocale(),
      'size' => 'long',
      'type' => 'datetime',
      'timezone' => Zend_Registry::get('timezone'),
    ), $options);
    
    $date = $this->_checkDateTime($date, $options);
    if( !$date ) {
      return false;
    }

    if( empty($options['format']) ) {
      if( substr($options['locale']->__toString(), 0, 2) == 'en' && 
          $options['size'] == 'long' && 
          $options['type'] == 'datetime' ) {
        $options['format'] = 'MMMM d, y h:mm a z';
      } else {
        $options['format'] = Zend_Locale_Data::getContent($options['locale'], $options['type'], $options['size']);
      }
    }
    // Hack for weird usage of L instead of M in Zend_Locale
    $options['format'] = str_replace('L', 'M', $options['format']);

    $str = $date->toString($options['format'], $options['locale']);
    $str = $this->convertNumerals($str, $options['locale']);
    return $str;
  }

  public function toDateTimeInterval($start, $end, $options = array())
  {
    return false;
    /*
    $start = $this->_checkDateTime($start, $options);
    $end = $this->_checkDateTime($end, $options);
    if( !$start || !$end ) {
      return false;
    }
    unset($options['timezone']);

    $options = array_merge(array(
      'locale' => $this->getLocale(),
      //'size' => 'long',
      'format' => 'MEd',
    ), $options);

    $options['locale'] = 'ja';

    $format = Zend_Locale_Data::getContent($options['locale'], 'dateinterval', $options['format']);
    var_dump(Zend_Locale_Format::getDate($start, array(
      'format' => $format)));die();


    if( preg_match('/^(.+?)(\s*[–-～]\s*)(.+?)$/iu', $format, $matches) ) {
      var_dump($matches);die();
    } else {
      // Sigh
      echo 'zzz';
    }


    var_dump($format);
    var_dump($start->toString($format, $this->getLocale()));
    die();
     * 
     */
  }

  protected function _checkDateTime($datetime, $options = array())
  {
    if( is_numeric($datetime) ) {
      $datetime = new Zend_Date($datetime);
    } else if( is_string($datetime) ) {
      $datetime = new Zend_Date(strtotime($datetime));
    } else if( !($datetime instanceof Zend_Date) ) {
      return false;
    }
    
    if( !($datetime instanceof Zend_Date) ) {
      throw new Engine_Exception('Not a valid date');
    }

    if( !isset($options['timezone']) &&
        Zend_Registry::isRegistered('timezone') ) {
      $options['timezone'] = Zend_Registry::get('timezone');
    }

    if( !isset($options['locale']) ) {
      $options['locale'] = $this->getLocale();
    }

    if( $options['timezone'] ) {
      $datetime->setTimezone($options['timezone']);
    }

    if( $options['locale'] ) {
      $datetime->setLocale($options['locale']);
    }
    
    return $datetime;
  }

  public function convertNumerals($string, $locale = null)
  {
    if( !$locale ) {
      $locale = $this->getLocale();
    }

    // Get the numbering system
    $defaultNumberingSystem = null;
    try {
      $defaultNumberingSystem = Zend_Locale_Data::getContent($locale, 'defaultnumberingsystem');
    } catch( Zend_Locale_Exception $e ) {
      // Silence
    }

    // Convert now
    if( $defaultNumberingSystem &&
        'latn' != strtolower($defaultNumberingSystem) ) {
      $string = Zend_Locale_Format::convertNumerals($string, 'Latn', $defaultNumberingSystem);
    }

    return $string;
  }
}