<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Json.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Parser_Json extends Engine_Package_Manifest_Parser
{
  public function toString($arr)
  {
    return $this->format(Zend_Json::encode($arr));
  }

  public function fromString($string)
  {
    return Zend_Json::decode($string);
  }

  public function toFile($filename, $arr)
  {
    if( strtolower(substr($filename, -5)) != '.json' ) {
      throw new Engine_Package_Manifest_Exception('Invalid file extension');
    }

    $data = $this->toString($arr);

    if( !file_put_contents($filename, $data) ) {
      throw new Engine_Package_Manifest_Exception('Unable to write data to file');
    }
  }

  public function fromFile($filename)
  {
    if( !file_exists($filename) ) {
      throw new Engine_Package_Manifest_Exception('Missing file');
    }

    if( strtolower(substr($filename, -5)) != '.json' ) {
      throw new Engine_Package_Manifest_Exception('Invalid file extension');
    }

    $arr = $this->fromString(file_get_contents($filename));

    if( empty($arr) ) {
      throw new Engine_Package_Manifest_Exception('Unable to load data');
    }

    return $arr;
  }

  public function format($string)
  {
    $json = $string;

    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;
    $len = strlen($json);

    for($c = 0; $c < $len; $c++)
    {
      $char = $json[$c];
      switch($char)
      {
        case '{':
        case '[':
          if(!$in_string)
          {
            $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
            $indent_level++;
          }
          else
          {
            $new_json .= $char;
          }
          break;
        case '}':
        case ']':
          if(!$in_string)
          {
            $indent_level--;
            $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
          }
          else
          {
            $new_json .= $char;
          }
          break;
        case ',':
          if(!$in_string)
          {
            $new_json .= ",\n" . str_repeat($tab, $indent_level);
          }
          else
          {
            $new_json .= $char;
          }
          break;
        case ':':
          if(!$in_string)
          {
            $new_json .= ": ";
          }
          else
          {
            $new_json .= $char;
          }
          break;
        case '"':
          if($c > 0 && $json[$c-1] != '\\')
          {
            $in_string = !$in_string;
          }
        default:
          $new_json .= $char;
          break;
      }
    }

    return $new_json;
  }
}