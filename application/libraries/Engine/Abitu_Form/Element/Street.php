<?
class Abitu_Form_Element_Street extends Zend_Form_Element_Text
{
    public function init()
    {
        $translate = Zend_Registry::get('Zend_Translate');
        $this->addFilter('StringTrim');     
        $this->addFilter('StripTags');
        
    }
    
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled())
        {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators))
        {
            $this->addDecorator('ViewHelper')
                ->addDecorator('FormStreet');
        }
        
        $this->setAttribs(array('class' => 'street'));
    }
}