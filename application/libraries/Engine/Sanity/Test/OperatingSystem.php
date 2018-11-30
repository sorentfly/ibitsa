<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: OperatingSystem.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_OperatingSystem extends Engine_Sanity_Test_Abstract
{
  const TYPE_LINUX = 0;
  const TYPE_WINDOWS = 1;
  const TYPE_BSD = 2;
  const TYPE_DARWIN = 4;
  const TYPE_UNKNOWN = 8;
  const TYPE_ALL = 15;
  
  protected $_messageTemplates = array(
    'unsupportedOs' => 'Unsupported operating system',
  );

  protected $_messageVariables = array(
    'allowed' => '_allowed',
    'uname' => '_uname',
    'uversion' => '_uversion',
  );

  protected $_allowed;

  protected $_uname;

  protected $_uversion;
  
  public function setAllowed($allowed)
  {
    $this->_allowed = $allowed;
    return $this;
  }

  public function getAllowed()
  {
    return $this->_allowed;
  }

  public function execute()
  {
    $this->_uname = $uname = php_uname('s');
    $this->_uversion = $version = php_uname('r');
    $type = null;
    $allowed = (int) $this->getAllowed();

    switch( true ) {
      case ( strtoupper(substr($uname, 0, 3)) === 'WIN'  ):
        $type = self::TYPE_WINDOWS;
        break;
      case ( strtoupper(substr($uname, 0, 3)) === 'LIN'  ):
        $type = self::TYPE_LINUX;
        break;
      case ( strpos(strtoupper($uname), 'BSD') !== false ):
        $type = self::TYPE_BSD;
        break;
      case ( strtoupper(substr($uname, 0, 3)) === 'DAR'  ):
        $type = self::TYPE_DARWIN;
        break;
      default:
        $type = self::TYPE_UNKNOWN;
        break;
    }

    if( !($allowed & $type) ) {
      $this->_error('unsupportedOs');
    }
  }
}