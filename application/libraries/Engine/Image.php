<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Image.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Engine_Image
{
  /**
   * The image resource
   * 
   * @var resource
   */
  protected $_resource;
  
  /**
   * Quality (0-100)
   *
   * @var integer
   */
  protected $_quality;

  /**
   *
   * @var <type> 
   */
  protected $_operations = array();

  /**
   * Factory method. Automatically picks available adapter
   * 
   * @param string $adapter Force this adapter
   * @param array $options
   * @return Engine_Image
   */
  static public function factory($options = array(), $adapter = 'gd')
  {
    $hasGd = function_exists('gd_info');
    $hasImagick = class_exists('Imagick', false);
    if( !$hasGd && !$hasImagick ) {
      throw new Engine_Image_Exception('No available adapter for image operations');
    }
    if( !($adapter == 'gd' && $hasGd) &&
        !($adapter == 'imagick' && $hasImagick) ) {
      if( $hasGd ) {
        $adapter = 'gd';
      } else if( $hasImagick ) {
        $adapter = 'imagick';
      }
    }

    $class = 'Engine_Image_Adapter_' . ucfirst($adapter);
    Engine_Loader::loadClass($class);
    if( !class_exists($class, false) ) {
      throw new Engine_Image_Exception(sprintf('Missing class for adapter "%s"', $adapter));
    }
    
    return new $class($options);
  }

  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct(array $options = array())
  {
    // Set quality
    if( !empty($options['quality']) &&
        is_numeric($options['quality']) &&
        $options['quality'] > 0 &&
        $options['quality'] <= 100 ) {
      $this->_quality = (int) $options['quality'];
    }
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->destroy();
  }

  /**
   * Magic getter for image info
   *
   * @param string $key
   * @return mixed
   */
  public function __get($key)
  {
    if( ($method = 'get' . ucfirst($key)) &&
        method_exists($this, $method) ) {
      return $this->$method();
    } else if( isset($this->$key) ) {
      return $this->$key;
    } else if( isset($this->{'_' . $key}) ) {
      return $this->{'_' . $key};
    } else {
      return null;
    }
  }

  /**
   * Get the image resource
   * 
   * @return resource
   */
  public function getResource()
  {
    return $this->_resource;
  }



  // Options

  public function getQuality()
  {
    return $this->_quality;
  }

  public function setQuality($quality)
  {
    $this->_quality = $quality;
    return $this;
  }

  abstract public function getFile();

  abstract public function setFile($file);
  
  abstract public function getFormat();
  
  abstract public function setFormat($format);
  
  abstract public function getHeight();

  abstract public function getWidth();



  // Actions

  /**
   * @return Engine_Image
   */
  abstract public function create($width, $height);

  /**
   * @return Engine_Image
   */
  abstract public function open($file);

  /**
   * @return Engine_Image
   */
  abstract public function destroy();

  /**
   * @return Engine_Image
   */
  abstract public function write($file = null);

  /**
   * @return Engine_Image
   */
  abstract public function output();

  /**
   * @return Engine_Image
   */
  abstract public function resize($w, $h, $aspect = true);

  /**
   * @return Engine_Image
   */
  abstract public function crop($x, $y, $w, $h);

  /**
   * @return Engine_Image
   */
  abstract public function resample($srcX, $srcY, $srcW, $srcH, $dstW, $dstH);

  /**
   * @return Engine_Image
   */
  abstract public function rotate($angle);

  /**
   * @return Engine_Image
   */
  abstract public function flip($horizontal = true);



  // Utility

  abstract protected function _checkOpenImage($throw = true);


  
  // Static

  static public function image_type_to_extension($type, $dot = true)
  {
    return image_type_to_extension($type, $dot);
  }

  static public function image_type_to_mime_type($type)
  {
    return image_type_to_mime_type($type);
  }

  /**
   * Fits a square within another square!
   *
   * @param integer $dstW
   * @param integer $dstH
   * @param integer $maxW
   * @param integer $maxH
   * @param unknown $method No idea what this was for
   * @return array
   */
  protected static function _fitImage($dstW, $dstH, $maxW, $maxH, $allowUpscale = false)
  {
    if( $allowUpscale ) {
      $multiplier = min($maxW / $dstW, $maxH / $dstH);
      if( $multiplier > 1 ) {
        $dstH *= $multiplier;
        $dstW *= $multiplier;
      }
    }
    if( ($delta = $maxW / $dstW) < 1 ) {
      $dstH = round($dstH * $delta);
      $dstW = round($dstW * $delta);
    }
    if( ($delta = $maxH / $dstH) < 1 ) {
      $dstH = round($dstH * $delta);
      $dstW = round($dstW * $delta);
    }
    return array($dstW, $dstH);
  }
}


// Backwards compatibility
if( !function_exists('image_type_to_extension') ) {
  function image_type_to_extension($type, $dot = true) {
    $e = array(1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp',
      'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc',
      'aiff', 'wbmp', 'xbm');

    // We are expecting an integer.
    $type = (int)$type;
    if( !$type ) {
      trigger_error( 'type must be an integer', E_USER_NOTICE );
      return null;
    }

    if( !isset($e[$type]) ) {
      trigger_error( 'No corresponding image type', E_USER_NOTICE );
      return null;
    }

    return ($dot ? '.' : '') . $e[$type];
  }
}

if( !function_exists('image_type_to_mime_type') ) {
  function image_type_to_mime_type($type) {
    $m = array(1 => 'image/gif', 'image/jpeg', 'image/png',
      'application/x-shockwave-flash', 'image/psd', 'image/bmp',
      'image/tiff', 'image/tiff', 'application/octet-stream','image/jp2',
      'application/octet-stream', 'application/octet-stream',
      'application/x-shockwave-flash', 'image/iff', 'image/vnd.wap.wbmp',
      'image/xbm');

    // We are expecting an integer.
    $type = (int)$type;
    if( !$type ) {
      trigger_error( 'type must be an integer', E_USER_NOTICE );
      return null;
    }

    if( !isset($m[$type]) ) {
      trigger_error( 'No corresponding image type', E_USER_NOTICE );
      return null;
    }

    return $m[$type];
  }
}
