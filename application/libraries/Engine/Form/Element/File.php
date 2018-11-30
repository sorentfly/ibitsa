<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: File.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Element_File extends Zend_Form_Element_File
{
  /**
   * Load default decorators
   *
   * @return void
   */
  public function loadDefaultDecorators()
  {
    $this->getView()->headScript()->appendFile('/application/modules/Core/externals/scripts/file-element.js');
    if( $this->loadDefaultDecoratorsIsDisabled() )
    {
      return;
    }

    $decorators = $this->getDecorators();
    if( empty($decorators) )
    {
      $this->addDecorator('File');
      Engine_Form::addDefaultDecorators($this);
    }
  }

    public function setValue($value)
    {
        if (is_numeric($value) && ($file = Engine_Api::_()->storage()->get($value))){
            $this->setAttrib('data-path', $file->map());
            $this->setAttrib('data-name', $file->name);
        }
        return parent::setValue($value);
    }
}
