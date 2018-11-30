<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Gd.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Image
 * @copyright  Copyright 2006-2010 Webligo Developments 
 * @license    http://www.socialengine.com/license/
 */
class Engine_Image_Adapter_Gd extends Engine_Image
{
  /**
   * Image format support
   * 
   * @var array
   */
  protected static $_support;

  protected $_file;

  protected $_format;
  
  protected $_height;

  protected $_width;



  // Methods

  /**
   * Constructor
   * 
   * @param string $file Image to open
   */
  public function __construct($options = array())
  {
    // Check support
    if( !function_exists('gd_info') ) {
      throw new Engine_Image_Adapter_Exception('GD library is not installed');
    }

    parent::__construct($options);
  }



  // Options
  
  public function getFile()
  {
    return $this->_file;
  }

  public function setFile($file)
  {
    $this->_file = $file;
    return $this;
  }

  public function getFormat()
  {
    return $this->_format;
  }

  public function setFormat($format)
  {
    $format = strtolower($format);
    self::_isSupported($format);
    $this->_format = $format;
    return $this;
  }

  public function getHeight()
  {
    return $this->_height;
  }

  public function getWidth()
  {
    return $this->_width;
  }



  // Actions

  public function create($width, $height)
  {
    // Check if we can create the image
    self::_isSafeToOpen($width, $height);

    // Create image
    $resource = imagecreatetruecolor($width, $height);

    if( !$resource ) {
      throw new Engine_Image_Adapter_Exception("Unable to create image");
    }

    // Assign info
    $this->_width = $width;
    $this->_height = $height;
    $this->_resource = $resource;

    return $this;
  }

  /**
   * Open an image
   * 
   * @param string $file
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to open
   */
  public function open($file)
  {
    // Set file
    $this->_file = $file;
    
    // Get image info
    $info = @getimagesize($file);
    if( !$info ) {
      throw new Engine_Image_Adapter_Exception(sprintf("File \"%s\" is not an image or does not exist", $file));
    }

    // Check if we can open the file
    self::_isSafeToOpen($info[0], $info[1]);

    // Detect type
    $type = ltrim(strrchr('.', $file), '.');
    if( !$type ) {
      $type = self::image_type_to_extension($info[2], false);
    }
    $type = strtolower($type);

    // Check support
    self::_isSupported($type);
    $function = 'imagecreatefrom'.$type;
    if( !function_exists($function) ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Image type "%s" is not supported', $type));
    }

    // Open
    $this->_resource = $function($file);
    if( !$this->_checkOpenImage(false) ) {
      throw new Engine_Image_Adapter_Exception("Unable to open image");
    }

    // Set information
    $this->_file = $file;
    $this->_format = $type;
    $this->_width = $info[0];
    $this->_height = $info[1];
    
    return $this;
  }

  /**
   * Write current image to a file
   * 
   * @param string $file (OPTIONAL) The file to write to. Default: original file
   * @param string $type (OPTIONAL) The output image type. Default: jpeg
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to write
   */
  public function write($file = null)
  {
    // If no file specified, write to existing file
    if( null === $file ) {
      if( null === $this->_file ) {
        throw new Engine_Image_Adapter_Exception("No file to write specified.");
      }
      $file = $this->_file;
    }
    
    // Get output format
    $outputFormat = $this->_format;
//    $outputFormat = null;
//    switch( $this->_format ) {
//      default:
//      case 'png';
//      case 'gif':
//      case 'bmp':
//        $outputFormat = 'png';
//        break;
//      case 'tiff':
//      case 'tif':
//      case 'jpg':
//        $outputFormat = 'jpg';
//        break;
//    }

    // Check support
    $function = 'image' . $outputFormat;
    if( !function_exists($function) ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Image type "%s" is not supported', $outputFormat));
    }

    // Apply quality
    $quality = null;
    if( is_int($this->_quality) && $this->_quality >= 0 && $this->_quality <= 100 ) {
      $quality = $this->_quality;
    }

    // Write
    if( $function == 'imagejpeg' && null !== $quality ) {
      $result = $function($this->_resource, $file, $quality);
    } else if( $function == 'imagepng' && null !== $quality ) {
      $result = $function($this->_resource, $file, round(abs(($quality - 100) / 11.111111)));
    } else {
      $result = $function($this->_resource, $file);
    }

    // Check result
    if( !$result ) {
      throw new Engine_Image_Adapter_Exception(sprintf("Unable to write image to file %s", $file));
    }

    return $this;
  }

  /**
   * Remove the current image object from memory
   */
  public function destroy()
  {
    if( is_resource($this->_resource) ) {
      imagedestroy($this->_resource);
    }
    return $this;
  }

  /**
   * Output an image to buffer or return as string
   * 
   * @param string $type Image format
   * @param boolean $buffer Output or return?
   * @return mixed
   * @throws Engine_Image_Adapter_Exception If unable to output
   */
  public function output($buffer = false)
  {
    $this->_checkOpenImage();

    // Get output format
    $outputFormat = $this->_format;
//    $outputFormat = null;
//    switch( $this->_format ) {
//      default:
//      case 'png';
//      case 'gif':
//      case 'bmp':
//        $outputFormat = 'png';
//        break;
//      case 'tiff':
//      case 'tif':
//      case 'jpg':
//        $outputFormat = 'jpg';
//        break;
//    }
    
    // Check support
    $function = 'image' . $outputFormat;
    if( !function_exists($function) ) {
      throw new Engine_Image_Adapter_Exception(sprintf('Image type "%s" is not supported', $outputFormat));
    }

    // Open buffer
    if( $buffer ) {
      ob_start();
    }
    
    // Apply quality
    $quality = null;
    if( is_int($this->_quality) && $this->_quality >= 0 && $this->_quality <= 100 ) {
      $quality = $this->_quality;
    }

    // Write
    if( $function == 'imagejpeg' && null !== $quality ) {
      $result = $function($this->_resource, null, $quality);
    } else if( $function == 'imagepng' && null !== $quality ) {
      $result = $function($this->_resource, null, round(abs(($quality - 100) / 11.111111)));
    } else {
      $result = $function($this->_resource, null);
    }

    // Check result
    if( !$result ) {
      if( $buffer ) {
        ob_end_clean();
      }
      throw new Engine_Image_Adapter_Exception("Unable to output image");
    }

    // Finish
    if( $buffer ) {
      return ob_get_clean();
    } else {
      return $this;
    }
  }

  /**
   * Resizes current image to $width and $height. If aspect is set, will fit
   * within boundaries while keeping aspect
   * 
   * @param integer $width
   * @param integer $height
   * @param boolean $aspect (OPTIONAL) Default - true
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to resize
   */
  public function resize($width, $height, $aspect = true)
  {
    $this->_checkOpenImage();
    
    $imgW = $this->_width;
    $imgH = $this->_height;

    // Keep aspect
    if( $aspect ) {
      list($width, $height) = self::_fitImage($imgW, $imgH, $width, $height);
    }

    // Create new temporary image
    self::_isSafeToOpen($width, $height);
    $dst = imagecreatetruecolor($width, $height);

    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Resize
    if( !imagecopyresampled($dst, $this->_resource, 0, 0, 0, 0, $width, $height, $imgW, $imgH) ) {
      imagedestroy($dst);
      throw new Engine_Image_Adapter_Exception('Unable to resize image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = $width;
    $this->_height = $height;
    
    return $this;
  }

  /**
   * Crop an image
   * 
   * @param integer $x
   * @param integer $y
   * @param integer $w
   * @param integer $h
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to crop
   */
  public function crop($x, $y, $w, $h)
  {
    $this->_checkOpenImage();
    
    // Create new temporary image and resize
    self::_isSafeToOpen($w, $h);
    $dst = imagecreatetruecolor($w, $h);
    
    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Crop
    if( !imagecopy($dst, $this->_resource, 0, 0, $x, $y, $w, $h) ) {
      imagedestroy($dst);
      throw new Engine_Image_Adapter_Exception('Unable to crop image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = $w;
    $this->_height = $h;
    
    return $this;
  }

  /**
   * Resample. Just crop+resize
   * 
   * @param integer $srcX
   * @param integer $srcY
   * @param integer $srcW
   * @param integer $srcH
   * @param integer $dstW
   * @param integer $dstH
   * @return Engine_Image_Adapter_Gd
   * @throws Engine_Image_Adapter_Exception If unable to crop
   */
  public function resample($srcX, $srcY, $srcW, $srcH, $dstW, $dstH)
  {
    $this->_checkOpenImage();

    // Create new temporary image
    self::_isSafeToOpen($dstW, $dstH);
    $dst = imagecreatetruecolor($dstW, $dstH);

    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Resample
    $result = imagecopyresampled($dst, $this->_resource, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
    
    if( !$result ) {
      imagedestroy($dst);
      throw new Engine_Image_Adapter_Exception('Unable to resample image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = $dstW;
    $this->_height = $dstH;
    
    return $this;
  }

  public function rotate($angle)
  {
    $this->_checkOpenImage();
    
    // Check if is safe to open (note if angle is not divisible by 90, then
    // this may not be handled correctly
    self::_isSafeToOpen($this->_width, $this->_height);

    // Rotate
    $result = imagerotate($this->_resource, $angle, 0);

    if( !$result ) {
      imagedestroy($result);
      throw new Engine_Image_Adapter_Exception('Unable to rotate image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $result;
    $this->_width = imagesx($this->_resource);
    $this->_height = imagesy($this->_resource);
    
    return $this;
  }

  public function flip($horizontal = true)
  {
    $this->_checkOpenImage();
    
    // Create new temporary image
    self::_isSafeToOpen($this->_width, $this->_height);
    $dst = imagecreatetruecolor($this->_width, $this->_height);

    // Try to preserve transparency
    self::_allocateTransparency($this->_resource, $dst, $this->_format);

    // Flip
    if( $horizontal ) {
      $result = imagecopyresampled($dst, $this->_resource,
          0, 0, ($this->_width - 1), 0,
          $this->_width, $this->_height, (0 - $this->_width), $this->_height);
    } else {
      $result = imagecopyresampled($dst, $this->_resource,
          0, 0, 0, ($this->_height - 1),
          $this->_width, $this->_height, $this->_width, (0 - $this->_height));
    }
    
    if( !$result ) {
      imagedestroy($result);
      throw new Engine_Image_Adapter_Exception('Unable to rotate image');
    }

    // Now destroy old image and overwrite with new
    imagedestroy($this->_resource);
    $this->_resource = $dst;
    $this->_width = imagesx($this->_resource);
    $this->_height = imagesy($this->_resource);
    
    return $this;
  }



  // Utility

  protected function _checkOpenImage($throw = true)
  {
    if( !is_resource($this->_resource) ) {
      if( $throw ) {
        throw new Engine_Image_Adapter_Exception('No open image to operate on.');
      } else {
        return false;
      }
    } else {
      return true;
    }
  }



  // Static

  /**
   * Check if it is safe to open an image (memory-wise)
   * 
   * @param integer $width Width in pixels
   * @param integer $height Height in pixels
   * @param integer $bpp Bytes per pixel
   */
  protected static function _isSafeToOpen($width, $height, $bpp = 4)
  {
    // "Fudge Factor"
    $fudge = 1.2;

    // Calculate used
    if( !function_exists('memory_get_usage') ) {
      $used = 15 * 1024 * 1024; // typical used
    } else {
      $used = memory_get_usage();
    }

    // Calculate limit
    $limit = false;
    if( function_exists('ini_get') ) {
      $limit = ini_get('memory_limit');
    }
    if( -1 == $limit ) {
      return true; // infinite mode
    } else if( !$limit ) {
      $limit = 32 * 1024 * 1024; // recommended default
    } else {
      $limit = self::_convertBytes($limit);
    }

    // Calculate available and required
    $available = $limit - $used;
    $required = $width * $height * $bpp * $fudge;

    // Whoops, not enough memory
    if( $required > $available ) {
      throw new Engine_Image_Exception(sprintf('Insufficient memory to open ' .
        'image: %d required > %d available (%d limit, %d used)', $required,
        $available, $limit, $used));
    }
  }

  /**
   * Get supported format info
   * 
   * @return stdClass
   */
  protected static function getSupport()
  {
    if( null === self::$_support )
    {
      $info = ( function_exists('gd_info') ? gd_info() : array() );
      $support = new stdClass();
      
      $support->freetype = !empty($info["FreeType Support"]);
      $support->t1lib = !empty($info["T1Lib Support"]);
      $support->gif = ( !empty($info["GIF Read Support"]) && !empty($info["GIF Create Support"]) );
      $support->jpg = ( !empty($info["JPG Support"]) || !empty($info["JPEG Support"]) );
      $support->jpeg = $support->jpg;
      $support->png = !empty($info["PNG Support"]);
      $support->wbmp = !empty($info["WBMP Support"]);
      $support->xbm = !empty($info["XBM Support"]);
      $support->bmp = true; // through b/c at bottom

      self::$_support = $support;
    }

    return self::$_support;
  }

  /**
   * Check if a specific image type is supported
   * 
   * @param string $type
   * @param boolean $throw
   * @return boolean
   * @throws Engine_Image_Adapter_Exception If $throw is true and not supported
   */
  protected static function _isSupported($type, $throw = true)
  {
    if( empty(self::getSupport()->$type) ) {
      if( $throw ) {
        throw new Engine_Image_Adapter_Exception(sprintf('Image type %s is not supported', $type));
      }
      return false;
    }
    return true;
  }

  /**
   * Convert short-hand bytes to integer
   * 
   * @param string $value
   * @return integer
   */
  protected static function _convertBytes($value)
  {
    if( is_numeric( $value ) )
    {
      return $value;
    }
    else
    {
      $value_length = strlen( $value );
      $qty = substr( $value, 0, $value_length - 1 );
      $unit = strtolower( substr( $value, $value_length - 1 ) );
      switch ( $unit )
      {
        case 'k':
          $qty *= 1024;
          break;
        case 'm':
          $qty *= 1048576;
          break;
        case 'g':
          $qty *= 1073741824;
          break;
      }
      return $qty;
    }
  }

  protected static function _allocateTransparency(&$img1, &$img2, $type)
  {
    // GIF
    if( $type == 'gif' ) {
      $transparent_index = imagecolortransparent($img1);
      if( $transparent_index >= 0 && $transparent_index < imagecolorstotal($img1) ) {
        $transparent_color = imagecolorsforindex($img1, $transparent_index);
        $transparent_index2 = imagecolorallocate($img2, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($img2, 0, 0, $transparent_index2);
        imagecolortransparent($img2, $transparent_index2);
      }
    }
    // PNG
    else if( $type == 'png' )
    {
      imagealphablending($img2, false);
      $transparent_color = imagecolorallocatealpha($img2, 0, 0, 0, 127);
      imagefill($img2, 0, 0, $transparent_color);
      imagesavealpha($img2, true);
    }
  }
}

if( !function_exists('imagecreatefrombmp') ) {

  function imagecreatefrombmp($filename)
  {
    if( !function_exists('imagecreatefromgd') ) {
      return false;
    }
    
    // Create tmp file
    $src = $filename;
    $dest = $tmp_name = tempnam("/tmp", "GD");

    if( !($src_f = fopen($src, "rb")) ) {
      return false;
    }

    if( !($dest_f = fopen($dest, "wb")) ) {
      return false;
    }
    
    $header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f, 14));
    $info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($src_f, 40));

    extract($info);
    extract($header);

    if( $type != 0x4D42 ) {  // signature "BM"
      @fclose($src_f);
      @fclose($dest_f);
      @unlink($tmp_name);
      return false;
    }

    $palette_size = $offset - 54;
    $ncolor = $palette_size / 4;
    $gd_header = "";
    // true-color vs. palette
    $gd_header .= ( $palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
    $gd_header .= pack("n2", $width, $height);
    $gd_header .= ( $palette_size == 0) ? "\x01" : "\x00";
    if( $palette_size ) {
      $gd_header .= pack("n", $ncolor);
    }
    // no transparency
    $gd_header .= "\xFF\xFF\xFF\xFF";

    fwrite($dest_f, $gd_header);

    if( $palette_size ) {
      $palette = fread($src_f, $palette_size);
      $gd_palette = "";
      $j = 0;
      while( $j < $palette_size ) {
        $b = $palette{$j++};
        $g = $palette{$j++};
        $r = $palette{$j++};
        $a = $palette{$j++};
        $gd_palette .= "$r$g$b$a";
      }
      $gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
      fwrite($dest_f, $gd_palette);
    }

    $scan_line_size = (($bits * $width) + 7) >> 3;
    $scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;

    for( $i = 0, $l = $height - 1; $i < $height; $i++, $l-- ) {
      // BMP stores scan lines starting from bottom
      fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
      $scan_line = fread($src_f, $scan_line_size);
      if( $bits == 24 ) {
        $gd_scan_line = "";
        $j = 0;
        while( $j < $scan_line_size ) {
          $b = $scan_line{$j++};
          $g = $scan_line{$j++};
          $r = $scan_line{$j++};
          $gd_scan_line .= "\x00$r$g$b";
        }
      } elseif( $bits == 8 ) {
        $gd_scan_line = $scan_line;
      } elseif( $bits == 4 ) {
        $gd_scan_line = "";
        $j = 0;
        while( $j < $scan_line_size ) {
          $byte = ord($scan_line{$j++});
          $p1 = chr($byte >> 4);
          $p2 = chr($byte & 0x0F);
          $gd_scan_line .= "$p1$p2";
        }
        $gd_scan_line = substr($gd_scan_line, 0, $width);
      } elseif( $bits == 1 ) {
        $gd_scan_line = "";
        $j = 0;
        while( $j < $scan_line_size ) {
          $byte = ord($scan_line{$j++});
          $p1 = chr((int) (($byte & 0x80) != 0));
          $p2 = chr((int) (($byte & 0x40) != 0));
          $p3 = chr((int) (($byte & 0x20) != 0));
          $p4 = chr((int) (($byte & 0x10) != 0));
          $p5 = chr((int) (($byte & 0x08) != 0));
          $p6 = chr((int) (($byte & 0x04) != 0));
          $p7 = chr((int) (($byte & 0x02) != 0));
          $p8 = chr((int) (($byte & 0x01) != 0));
          $gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
        }
        $gd_scan_line = substr($gd_scan_line, 0, $width);
      }

      fwrite($dest_f, $gd_scan_line);
    }

    fclose($src_f);
    fclose($dest_f);
    
    // Create from GD
    $img = imagecreatefromgd($tmp_name);
    @unlink($tmp_name);
    return $img;
  }
}
