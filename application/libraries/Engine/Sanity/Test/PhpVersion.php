<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: PhpVersion.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_PhpVersion extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'tooLowVersion' => 'Requires at least version %min_version%',
    'tooHighVersion' => 'Requires no greater than %max_version%',
  );

  protected $_messageVariables = array(
    'min_version' => '_minVersion',
    'max_version' => '_maxVersion',
    'actual_version' => '_actualVersion',
  );
  
  protected $_minVersion;

  protected $_maxVersion;

  protected $_actualVersion;

  public function setMinVersion($minVersion)
  {
    $this->_minVersion = $minVersion;
    return $this;
  }

  public function getMinVersion()
  {
    return $this->_minVersion;
  }

  public function setMaxVersion($maxVersion)
  {
    $this->_maxVersion = $maxVersion;
    return $this;
  }

  public function getMaxVersion()
  {
    return $this->_maxVersion;
  }

  public function execute()
  {
    $minVersion = $this->getMinVersion();
    $maxVersion = $this->getMaxVersion();
    $this->_actualVersion = $actualVersion = PHP_VERSION;

    // Tests
    if( !empty($minVersion) && version_compare($actualVersion, $minVersion, '<') ) {
      $this->_error('tooLowVersion');
    }

    if( !empty($maxVersion) && version_compare($actualVersion, $maxVersion, '>') ) {
      $this->_error('tooHighVersion');
    }

    return $this;
  }
}
