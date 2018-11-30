<?
class Abitu_Form_Element_AdvancedSelect extends Zend_Form_Element_Select
{
    public function init()
    {        
        
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
                ->addDecorator('FormAdvancedSelect');
        }
    }
}