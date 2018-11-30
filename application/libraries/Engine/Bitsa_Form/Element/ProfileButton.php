<?
class Bitsa_Form_Element_ProfileButton extends Zend_Form_Element_Button
{
  public function loadDefaultDecorators()
  {
    if( $this->loadDefaultDecoratorsIsDisabled() )
    {
      return;
    }

    $decorators = $this->getDecorators();
    if( empty($decorators) )
    {
      $this->addDecorator('ViewHelper')
        ->addDecorator('FormProfileButton');
      
    }
  }
}
