<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Xml.php 10206 2014-05-08 18:59:10Z lucas $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest_Parser_Xml extends Engine_Package_Manifest_Parser
{
  public function toString($arr)
  {
    return $this->format($this->_toString($arr));
  }

  public function fromString($string)
  {
    if( is_string($string) ) {
      $obj = new SimpleXMLElement($string);
    }

    if( !$obj instanceof SimpleXMLElement ) {
      throw new Engine_Package_Manifest_Exception('Must be string or SimpleXMLElement');
    }

    $arr = array();
    $children = $obj->children();
    foreach( $children as $elementName => $node )
    {
      $content = $this->fromString($node);
      if( empty($content) ) {
        $content = (string) $node; // @todo check if this works when __toString() not added to PHP yet
      }

      if( $elementName === 'numericNode' ) {
        $arr[] = $content;
      } else {
        $arr[$elementName] = $content;
      }
    }

    return $arr;
  }

  public function toFile($filename, $arr)
  {
    if( strtolower(substr($filename, -4)) != '.xml' ) {
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
    
    if( strtolower(substr($filename, -4)) != '.xml' ) {
      throw new Engine_Package_Manifest_Exception('Invalid file extension');
    }

    $arr = $this->fromString(simplexml_load_file($filename));

    if( empty($arr) ) {
      throw new Engine_Package_Manifest_Exception('Unable to load data');
    }

    return $arr;
  }

  public function format($string)
  {
    $dom = new DOMDocument('1.0');
    $dom->loadXML($string);
    $dom->formatOutput = true;
    return $dom->saveXML();
  }

  protected function _toString($arr, SimpleXMLElement $xml = null)
  {
    if( null === $xml ) {
      $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><package />");
    }
    $i = 0;
    foreach( $arr as $key => $value ) {
      // Make name
      $childName = null;
      if( is_numeric($key) ) {
        $childName = 'numericNode'; //sprintf('numeric_node_%d', $key);
      } else {
        $childName = $key;
      }

      // Make value
      $childValue = null;
      if( is_array($value) ) {
        // This is a special case
        $childObject = $xml->addChild($childName);
        $childObject = $this->_toString($value, $childObject);
        continue;
      } else if( is_scalar($value) ) {
        if( is_bool($value) ) {
          $childValue = sprintf('%d', $value);
        } else {
          $childValue = $value;
        }
      } else {
        throw new Engine_Package_Manifest_Exception('Unknown data type passed to Engine_Package_Manifest::arrayToXml() - ' . gettype($value));
      }

      // Add child
      $xml->addChild($childName, $childValue);
    }
    return $xml;
  }
}