<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Csv.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Translate_Writer_Csv implements Engine_Translate_Writer_Interface
{
  const STATE_ADDED = 1;
  const STATE_MODIFIED = 2;
  const STATE_REMOVED = 3;

  protected $_file;
  
  protected $_data;

  protected $_modifiedData = array();

  protected $_requiresFullRewrite = false;

  protected $_options = array(
    'delimiter' => ';',
    'enclosure' => '"',
  );



  // General
  
  public function __construct($file = null)
  {
    if( null !== $file ) {
      $this->_file = $file;
      $this->read($file);
    }
  }



  // Data accessors
  
  public function getTranslation($key)
  {
    if( !isset($this->_data[$key]) ) {
      return null;
    }

    return $key;
  }

  public function getTranslations($keys = null)
  {
    if( null === $keys ) {
      return $this->_data;
    }

    $data = array();
    foreach( $keys as $key ) {
      if( isset($this->_data[$key]) ) {
        $data[$key] = $this->_data[$key];
      } else {
        $data[$key] = null;
      }
    }

    return $data;
  }

  public function removeTranslation($key)
  {
    if( isset($this->_data[$key]) ) {
      $this->_modifiedData[$key] = self::STATE_REMOVED;
      $this->_requiresFullRewrite = true;
      unset($this->_data[$key]);
    }

    return $this;
  }

  public function setTranslation($key, $value)
  {
    if( !isset($this->_data[$key]) ) {
      $this->_modifiedData[$key] = self::STATE_ADDED;
    } else if( $value === $this->_data[$key] ) {
      // Ignore if equal
      return $this;
    } else {
      $this->_modifiedData[$key] = self::STATE_MODIFIED;
      //$this->_requiresFullRewrite = true; Do we need this here?
    }
    $this->_data[$key] = $value;
    return $this;
  }

  public function setTranslations(array $data)
  {
    foreach( $data as $key => $value ) {
      $this->setTranslation($key, $value);
    }
    return $this;
  }


  // File reading

  public function read($file = null)
  {
    if( null === $file && null === $this->_file ) {
      throw new Engine_Translate_Writer_Exception('no file to write to specified.');
    }

    if( null === $file ) {
      $file = $this->_file;
    }
    
    $this->_checkFile($file);

    $this->_file = $file;
    $tmp = Engine_Translate_Parser_Csv::parse($file, 'null', $this->_options);
    if( !empty($tmp['null']) && is_array($tmp['null']) ) {
      $this->_data = $tmp['null'];
    } else {
      $this->_data = array();
    }

    return $this;
  }

  public function write($file = null)
  {
    if( null === $file && null === $this->_file ) {
      throw new Engine_Translate_Writer_Exception('no file to write to specified.');
    }

    if( null === $file ) {
      $file = $this->_file;
    }

    if( !file_exists($file) ) {
      if( !file_exists(dirname($file)) ) {
        $path = explode(DIRECTORY_SEPARATOR, dirname($file));
        $dir  = '';
        while (!empty($path)) {
          $dir .= DIRECTORY_SEPARATOR . array_shift($path);
          if (!file_exists($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
          }
        }
      }
      @touch($file);
      @chmod($file, 0777);
    }

    $this->_checkFile($file);

    // Begin rewriting
    $fh = null;
    if( $this->_requiresFullRewrite ) {
      $fh = fopen($file, 'wb');
    } else {
      $fh = fopen($file, 'ab');
    }

    if( !$fh ) {
      throw new Engine_Translate_Writer_Exception('unable to open file');
    }

    // Full rewrite mode
    if( $this->_requiresFullRewrite ) {
      foreach( $this->_data as $key => $value ) {
        $this->_writeLine($fh, $key, $value);
      }
    }

    // Partial write mode
    else {
      foreach( $this->_modifiedData as $key => $mode ) {
        $this->_writeLine($fh, $key, $this->_data[$key]);
      }
    }

    fclose($fh);

    return $this;
  }



  // Utility
  
  protected function _checkFile($file)
  {
    if( pathinfo($file, PATHINFO_EXTENSION) != 'csv' ) {
      throw new Engine_Translate_Writer_Exception(sprintf('file "%s" not a csv file', $file));
    }

    if( !file_exists($file) ) {
      throw new Engine_Translate_Writer_Exception(sprintf('file "%s" does not exist', $file));
    }

    if( !is_writeable($file) ) {
      throw new Engine_Translate_Writer_Exception(sprintf('file "%s" not writeable', $file));
    }
  }

  protected function _writeLine(&$fh, $key, $value)
  {
    $value = (array) $value;
    array_unshift($value, $key);
    $this->_fputcsv($fh, $value, $this->_options['delimiter'], $this->_options['enclosure']);
    //fputcsv($fh, $value, $this->_options['delimiter'], $this->_options['enclosure']);
  }

  protected function _fputcsv(&$handle, $fields = array(), $delimiter = ';', $enclosure = '"')
  {
      // Sanity Check
      if (!is_resource($handle)) {
          trigger_error('fputcsv() expects parameter 1 to be resource, ' .
              gettype($handle) . ' given', E_USER_WARNING);
          return false;
      }

      if ($delimiter!=NULL) {
          if( strlen($delimiter) < 1 ) {
              trigger_error('delimiter must be a character', E_USER_WARNING);
              return false;
          } elseif( strlen($delimiter) > 1 ) {
              trigger_error('delimiter must be a single character', E_USER_NOTICE);
          }

          /* use first character from string */
          $delimiter = $delimiter[0];
      }

      if( $enclosure!=NULL ) {
          if( strlen($enclosure) < 1 ) {
              trigger_error('enclosure must be a character', E_USER_WARNING);
              return false;
          } elseif( strlen($enclosure) > 1 ) {
              trigger_error('enclosure must be a single character', E_USER_NOTICE);
          }

          /* use first character from string */
          $enclosure = $enclosure[0];
     }

      $first = true;
      $csvline = '';
      $escape_char = '\\';
      $field_cnt = count($fields);
      $enc_is_quote = in_array($enclosure, array('"',"'"));
      reset($fields);

      foreach( $fields as $field ) {

          if( !$first ) {
            $csvline .= $delimiter;
          } else {
            $first = false;
          }
          
          /* enclose a field that contains a delimiter, an enclosure character, or a newline */
          /*
          if( is_string($field) && (
              strpos($field, $delimiter)!==false ||
              strpos($field, $enclosure)!==false ||
              strpos($field, $escape_char)!==false ||
              strpos($field, "\n")!==false ||
              strpos($field, "\r")!==false ||
              strpos($field, "\t")!==false ||
              strpos($field, ' ')!==false ) ) {
              */
        /* screw it, let's enclose everything */
          if( is_string($field) ) {
              $field_len = strlen($field);
              $escaped = 0;

              $csvline .= $enclosure;
              for( $ch = 0; $ch < $field_len; $ch++ ) {
                  if( $field[$ch] == $escape_char && $field[$ch+1] == $enclosure && $enc_is_quote ) {
                      continue;
                  } elseif( $field[$ch] == $escape_char ) {
                      $escaped = 1;
                  } elseif( !$escaped && $field[$ch] == $enclosure ) {
                      $csvline .= $enclosure;
                  }else{
                      $escaped = 0;
                  }
                  $csvline .= $field[$ch];
              }
              $csvline .= $enclosure;
          } else {
              $csvline .= $field;
          }

          /*
          if( $i++ != $field_cnt ) {
              $csvline .= $delimiter;
          }
           */
      }

      $csvline .= PHP_EOL; //"\n";

      return fwrite($handle, $csvline);
  }
}