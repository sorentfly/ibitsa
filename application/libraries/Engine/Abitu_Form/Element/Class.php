<?
class Abitu_Form_Element_Class extends Zend_Form_Element_Text
{
    private $translate;
    public function init()
    {
        $this->translate = Zend_Registry::get('Zend_Translate'); 
        $this->addFilter('StringTrim');
        $this->addFilter('StripTags');
        
        if($this->getValue() != '')
        {
            $this->addValidator('Int', true);
            $this->addValidator('GreaterThan', true, array(0));
            $this->addValidator('LessThan', true, array(12));
        }
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
                ->addDecorator('FormClass');
        }
    }
}