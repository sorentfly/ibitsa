<?php
class Bitsa_Form_Element_File extends Zend_Form_Element_File
{
  /**
   * Load default decorators
   *
   * @return void
   */
  public function loadDefaultDecorators()
  {
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
}
