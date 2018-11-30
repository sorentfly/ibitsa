<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Parser.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
abstract class Engine_Package_Manifest_Parser
{
  /**
   * Factory method
   * 
   * @param string $format
   * @return Engine_Package_Manifest_Parser
   */
  static public function factory($format)
  {
    if( strpos($format, '.') !== false ) {
      $format = strtolower(ltrim(strrchr($format, '.'), '.'));
    }
    $class = 'Engine_Package_Manifest_Parser_' . ucfirst($format);
    if( !class_exists($class, false) ) {
      if( !file_exists(dirname(__FILE__) . '/Parser/' . ucfirst($format) . '.php') ) {
        throw new Engine_Package_Manifest_Exception(sprintf('Unknown source format "%s"', $format));
      }
      if( !class_exists($class) ) {
        throw new Engine_Package_Manifest_Exception(sprintf('Unknown source format "%s"', $format));
      }
    }
    if( !is_subclass_of($class, 'Engine_Package_Manifest_Parser') ) {
      throw new Engine_Package_Manifest_Exception(sprintf('Unknown source format "%s"', $format));
    }
    return new $class();
  }
  
  abstract public function toString($arr);

  abstract public function fromString($string);

  abstract public function toFile($filename, $arr);

  abstract public function fromFile($filename);
  
  abstract public function format($string);
}