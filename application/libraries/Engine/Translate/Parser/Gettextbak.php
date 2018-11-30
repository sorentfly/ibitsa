<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Gettextbak.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Translate_Parser_Gettext implements Engine_Translate_Parser_Interface
{
  public static function parse($file, array $options = array())
  {
    $data      = array();
    $bigEndian = false;
    $handle     = @fopen($file, 'rb');
    if( !$handle )
    {
      require_once 'Zend/Translate/Exception.php';
      throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
    }

    if( @filesize($file) < 10 )
    {
        require_once 'Zend/Translate/Exception.php';
        throw new Zend_Translate_Exception('\'' . $filename . '\' is not a gettext file');
    }
    
    // get Endian
    $input = self::_readMOData($handle, 1, $bigEndian);
    if( strtolower(substr(dechex($input[1]), -8)) == "950412de" )
    {
      $bigEndian = false;
    }
    else if( strtolower(substr(dechex($input[1]), -8)) == "de120495" )
    {
      $bigEndian = true;
    }
    else
    {
      require_once 'Zend/Translate/Exception.php';
      throw new Zend_Translate_Exception('\'' . $filename . '\' is not a gettext file');
    }
    
    // read revision - not supported for now
    $input = self::_readMOData($handle, 1, $bigEndian);

    // number of bytes
    $input = self::_readMOData($handle, 1, $bigEndian);
    $total = $input[1];

    // number of original strings
    $input = self::_readMOData($handle, 1, $bigEndian);
    $OOffset = $input[1];

    // number of translation strings
    $input = self::_readMOData($handle, 1, $bigEndian);
    $TOffset = $input[1];

    // fill the original table
    fseek($handle, $OOffset);
    $origtemp = self::_readMOData($handle, 2 * $total, $bigEndian);
    fseek($handle, $TOffset);
    $transtemp = self::_readMOData($handle, 2 * $total, $bigEndian);

    for( $count = 0; $count < $total; ++$count )
  {
        if ($origtemp[$count * 2 + 1] != 0) {
            fseek($handle, $origtemp[$count * 2 + 2]);
            $original = @fread($handle, $origtemp[$count * 2 + 1]);
            $original = explode(chr(00), $original);
        } else {
            $original[0] = '';
        }

        if ($transtemp[$count * 2 + 1] != 0) {
            fseek($handle, $transtemp[$count * 2 + 2]);
            $translate = fread($handle, $transtemp[$count * 2 + 1]);
            $translate = explode(chr(00), $translate);
            if ((count($original) > 1) && (count($translate) > 1)) {
                $data[$original[0]] = $translate;
                array_shift($original);
                foreach ($original as $orig) {
                    $data[$orig] = '';
                }
            } else {
                $data[$original[0]] = $translate[0];
            }
        }
    }

    //$data[''] = trim($data['']);
    //if (empty($data[''])) {
    //    $this->_adapterInfo[$filename] = 'No adapter information available';
    //} else {
    //    $this->_adapterInfo[$filename] = $data[''];
    //}

    unset($data['']);
    return $data;
  }

  protected static function _readMOData($file, $bytes, $bigEndian = false)
  {
    if( $bigEndian === false )
    {
      return unpack('V' . $bytes, fread($file, 4 * $bytes));
    }
    else
    {
      return unpack('N' . $bytes, fread($file, 4 * $bytes));
    }
  }
}
