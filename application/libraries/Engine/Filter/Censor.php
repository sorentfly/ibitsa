<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Censor.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Filter_Censor implements Zend_Filter_Interface
{
  protected static $_defaultForbiddenWords;
  
  protected $_forbiddenWords;

  protected $_replaceString = '*';
  
  public function __construct($options = array())
  {
    if( !empty($options['forbiddenWords']) ) {
      $this->_forbiddenWords = $options['forbiddenWords'];
    } else {
      $this->_forbiddenWords = self::getDefaultForbiddenWords();
    }
    if( is_string($this->_forbiddenWords) ) {
      $this->_forbiddenWords = preg_split('/\s*,\s*/', $this->_forbiddenWords);
      $this->_forbiddenWords = array_map('trim', $this->_forbiddenWords);
      $this->_forbiddenWords = array_filter($this->_forbiddenWords);
    }
    if( !is_array($this->_forbiddenWords) ) {
      $this->_forbiddenWords = null;
    }

    if( !empty($options['replaceString']) ) {
      $this->_replaceString = $options['replaceString'];
    }
  }

  public function filter($value)
  {
    if( empty($value) || empty($this->_forbiddenWords) || !is_array($this->_forbiddenWords) ) {
      return $value;
    }
    
    foreach( $this->_forbiddenWords as $word ) {
      // periods and slashes need to be escaped otherwise they become
      // part of the regular expression which can result in strange errors
      $expr = preg_quote($word, '/');
      $expr = str_replace('\\*', '.*?', $expr);
      $replace = str_pad('', strlen(str_replace('*', '', $word)), $this->_replaceString);
      $value = preg_replace('/\b' . $expr. '\b/i', $replace, $value);
    }
    
    return $value;
  }

  // Static stuff
  
  public static function getDefaultForbiddenWords()
  {
    if( null === self::$_defaultForbiddenWords ) {
      if( Zend_Registry::isRegistered('Censor') ) {
        $forbiddenWords = Zend_Registry::get('Censor');
        if( is_string($forbiddenWords) || is_array($forbiddenWords) ) {
          return $forbiddenWords;
        }
      }
    } else {
      return self::$_defaultForbiddenWords;
    }
  }

  public static function setDefaultForbiddenWords($words)
  {
    self::$_defaultForbiddenWords = $words;
  }
}