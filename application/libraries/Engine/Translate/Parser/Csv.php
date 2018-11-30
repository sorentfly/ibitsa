<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Csv.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Translate_Parser_Csv implements Engine_Translate_Parser_Interface
{
  public static function parse($file, $locale = null, array $options = array())
  {
    if( !isset($options['length']) )
    {
      $options['length'] = 0;
    }

    if( !isset($options['delimiter']) )
    {
      $options['delimiter'] = ";";
    }

    if( !isset($options['enclosure']) )
    {
      $options['enclosure'] = '"';
    }

    $handle = @fopen($file, 'rb');
    if( !$handle )
    {
      require_once 'Zend/Translate/Exception.php';
      throw new Zend_Translate_Exception('Error opening translation file \'' . $file . '\'.');
    }
    
    $data = array();
    while( ($tmp = fgetcsv($handle, $options['length'], $options['delimiter'], $options['enclosure'])) !== false )
    {
      if (substr($tmp[0], 0, 1) === '#') {
        continue;
      }

      if (!isset($tmp[1])) {
        continue;
      }

      if (count($tmp) == 2) {
        $data[$locale][$tmp[0]] = $tmp[1];
      } else {
        $singular = array_shift($tmp);
        $data[$locale][$singular] = $tmp;
      }
    }

    fclose($handle);

    return $data;
  }
}
