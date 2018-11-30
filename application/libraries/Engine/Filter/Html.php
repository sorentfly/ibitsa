<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Html.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Filter_Html implements Zend_Filter_Interface
{
  protected $_useDefaultLists = true;
  protected $_notFilterToAdmin = true;
  protected $_allowedTags;
  protected $_forbiddenTags;
  protected $_allowedAttributes;
  protected $_forbiddenAttributes;
  protected $_forbiddenAttributeValues;
  protected $_decode = true;

  // Static
  public static function process($text, $options = array())
  {
    $instance = new InputFilter($options);
    return $instance->execute($text);
  }

  // Constructor
  function __construct($options = array())
  {
    foreach( $options as $key => $value )
    {
      $method = 'set'.ucfirst($key);
      if( method_exists($this, $method) )
      {
        $this->$method($value);
      }
    }

    if( $this->_useDefaultLists )
    {
      $this->loadDefaultLists();
    }
  }

  public function filter($value)
  {
    return $this->execute($value);
  }

  public function loadDefaultLists()
  {
    $this->_notFilterToAdmin = true;
            
    $this->_allowedAttributes = null;

    $this->_forbiddenTags = array_unique(array_merge((array) $this->_forbiddenTags,
      array('applet', 'body', 'bgsound', 'base', 'basefont', 'frame', 'frameset', 'head', 'html', 'id', 'ilayer', 'layer', 'link', 'meta', 'name', 'script', 'style', 'title', 'xml')
    ));

    $this->_forbiddenAttributes = array_unique(array_merge((array) $this->_forbiddenAttributes,
      array('action', 'background', 'codebase', 'dynsrc', 'lowsrc', 'on*')
    ));

    $this->_forbiddenAttributeValues = array_unique(array_merge((array) $this->_forbiddenAttributeValues,
      array('*expression*', 'javascript:*', 'behaviour:*', 'vbscript:*', 'mocha:*', 'livescript:*')
    ));
  }



  // Options

  public function setDecode($flag = true)
  {
    $this->_decode = $flag;
  }

  public function setUseDefaultLists($flag = true)
  {
    $this->_useDefaultLists = (bool) $flag;
  }

  public function setAllowedTags($allowedTags = null)
  {
    if( is_string($allowedTags) )
    {
      $allowedTagsArray = explode(',', $allowedTags);
      foreach($allowedTagsArray as $index => $tag)
        $allowedTagsArray[$index] = trim($tag);

      $this->_allowedTags = $allowedTagsArray;
    }
    else if( is_array($allowedTags) )
    {
      $this->_allowedTags = $allowedTags;
    }
    else
    {
      $this->_allowedTags = null;
    }
  }

  public function setForbiddenTags($forbiddenTags = null)
  {
    if( is_string($forbiddenTags) )
    {
      $this->_forbiddenTags = explode(',', $forbiddenTags);
    }
    else if( is_array($forbiddenTags) )
    {
      $this->_forbiddenTags = $forbiddenTags;
    }
    else
    {
      $this->_forbiddenTags = null;
    }
  }

  public function setAllowedAttributes($allowedAttributes = null)
  {
    if( is_string($allowedAttributes) )
    {
      $this->_allowedAttributes = explode(',', $allowedAttributes);
    }
    else if( is_array($allowedAttributes) )
    {
      $this->_allowedAttributes = $allowedAttributes;
    }
    else
    {
      $this->_allowedAttributes = null;
    }
  }

  public function setForbiddenAttributes($forbiddenAttributes = null)
  {
    if( is_string($forbiddenAttributes) )
    {
      $this->_forbiddenAttributes = explode(',', $forbiddenAttributes);
    }
    else if( is_array($forbiddenAttributes) )
    {
      $this->_forbiddenAttributes = $forbiddenAttributes;
    }
    else
    {
      $this->_forbiddenAttributes = null;
    }
  }

  // Main
  public function execute($text)
  {
    //FIX bug with mspace tag
    $text = preg_replace('@<\s*mspace[^>/]*>@i', '<mspace linebreak="newline"/>', $text);
    $text = preg_replace('@</mn><mo>,</mo><mn>@i', ',', $text);
    //FIX bitsa: tinymce workaround
    $text = str_replace('<p></p>', '<p><br/></p>', $text);
    //not filter text for admins
    if ($this->_notFilterToAdmin &&  class_exists('Engine_Api') && ($viewer = Engine_Api::_()->user()->getViewer()) && $viewer->getIdentity() && ($viewer->level_id == 1 || $viewer->level_id == 2 ) )
    {
        return $text;
    }
    // Process tags

    // Decode
    if( $this->_decode )
    {
      $text = htmlspecialchars_decode($text, ENT_QUOTES);
    }
    // Nothing was specified? Just strip everything
    if( !$this->_allowedTags && !$this->_forbiddenTags )
    {
      return strip_tags($text);
    }

    // Close any open-ended tags, escape all non-html lt and gt
    $text = str_replace(array('[TO]', '[TC]'), array('', ''), $text);
    $text = preg_replace('/<(\/?[a-zA-Z][^<>]*?)((<)|>)/', '<$1>$3', $text);
    $text = preg_replace('/<(\/?[a-zA-Z][^<>]*?)>/', '[TO]$1[TC]', $text);
    $text = str_replace(array('<', '>'), array('&lt;', '&gt;'), $text);
    $text = str_replace(array('[TO]', '[TC]'), array('<', '>'), $text);
    
    // Strip everything but allowable tags
    if( $this->_allowedTags )
    {
      $text = strip_tags($text, '<' . join('><', $this->_allowedTags) . '>');
    }

    // Strip all forbidden tags
    if( $this->_forbiddenTags )
    {
      $text = $this->stripTags($text, $this->_forbiddenTags);
    }

    // Strip all but allowed attributes
    if( $this->_allowedAttributes || $this->_forbiddenAttributes )
    {
      // </?\w+((\s+\w+(\s*=\s*(?:".*?"|'.*?'|[^'">\s]+))?)+\s*|\s*)/? >
      preg_match_all('/(<\/?\w+)\s+([^<>]*?)>/i', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
      /* preg_match_all('/<\/?(\w+)((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>/ims', $text, $matches, PREG_SET_ORDER); */

      $delta = 0;
      foreach( $matches as $match ) // Each one of these is an element with attributes
      {
        $current = $match[1][0];
        $modified = false;
        preg_match_all('/(\w+(\s*=\s*(?:".*?(?<!\\\\)"|\'.*?(?<!\\\\)\'|[^\'">\s]+))?)/', $match[0][0], $m, PREG_SET_ORDER);

        for( $i = 1; $i < count($m); $i++ )
        {
          $attribString = $m[$i][0];
          @list($attribName, $attribValue) = explode('=', $attribString, 2);

          if( !$this->checkAttribute($attribName, $attribValue) )
          {
            $modified = true;
            continue; // Throw it away!
          }

          $current .= ' ' . $attribName;
          if( !empty($attribValue) )
          {
            $current .= '=' . $attribValue;
          }
        }
        $current .= '>';

        // Here we replace the original
        $offset = $match[0][1] + $delta;
        $length = strlen($match[0][0]);
        if( $modified == true )
        {
          $currentLength = strlen($current);
          $tmp = substr($text, 0, $offset + 0)
            . $current
            . substr($text, $offset + $length);
          $delta -= ($length - $currentLength);
          $text = $tmp;
        }
      }
    }

    return $text;
  }

  public function checkAttribute($name, $value)
  {
    $name = strtolower($name);
    $value = trim($value, "\x00..\x20\x22\x27");
    $softAllow = false;

    // This attribute has been specifically whitelisted
    if( empty($this->_allowedAttributes) || is_array($this->_allowedAttributes) && in_array($name, $this->_allowedAttributes) )
    {
       $softAllow = true;
    }

    // This attribute has been specifically blacklisted
    if( is_array($this->_forbiddenAttributes) && in_array($name, $this->_forbiddenAttributes) )
    {
      return false;
    }

    // Now check for wildcards

    // Allowed Attributes
    if( is_array($this->_allowedAttributes) )
    {
      foreach( $this->_allowedAttributes as $allowedAttribute )
      {
        if( strpos($allowedAttribute, '*') === false )
        {
          continue;
        }

        // Make a regex for the wildcard
        $regex = '/^' . str_replace('*', '.+?', preg_quote($allowedAttribute)) . '$/i';
        if( preg_match($regex, $name) )
        {
          $softAllow = true;
          //return true;
        }
      }
    }


    // Forbidden Attributes
    if( is_array($this->_forbiddenAttributes) )
    {
      foreach( $this->_forbiddenAttributes as $forbiddenAttribute )
      {
        if( strpos($forbiddenAttribute, '*') === false )
        {
          continue;
        }

        // Make a regex for the wildcard
        $regex = '/^' . str_replace('*', '.+?', preg_quote($forbiddenAttribute)) . '$/i';
        if( preg_match($regex, $name) )
        {
          return false;
        }
      }
    }

    // Forbidden attribute values
    if( is_array($this->_forbiddenAttributeValues) )
    {
      foreach( $this->_forbiddenAttributeValues as $forbiddenAttributeValue )
      {
        if( strpos($forbiddenAttributeValue, '*') === false )
        {
          continue;
        }

        // Make a regex for the wildcard
        $regex = '/^' . str_replace('\*', '.+?', preg_quote($forbiddenAttributeValue)) . '$/i';
        if( preg_match($regex, $value) )
        {
          return false;
        }
      }
    }

    return $softAllow;
  }

  public function stripTags($str, $params)
  {
    for( $i = 0; $i < count($params); $i++ )
    {
      $str = preg_replace('/<' . $params[$i] . '\b[^>]*>/i', '', $str);
      $str = preg_replace('/<\/' . $params[$i] . '[^>]*>/i', '', $str);
    }
    return $str;
  }
}

