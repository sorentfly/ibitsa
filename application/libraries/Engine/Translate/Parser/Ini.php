<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ini.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Translate_Parser_Ini implements Engine_Translate_Parser_Interface
{
  public static function parse($file, array $options = array())
  {
    $data = array();
    if( !file_exists($file) )
    {
      require_once 'Zend/Translate/Exception.php';
      throw new Zend_Translate_Exception("Ini file '".$data."' not found");
    }

    return parse_ini_file($file, false);
  }
}