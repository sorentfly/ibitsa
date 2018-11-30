<?

class Engine_Form_Element_Hr extends Engine_Form_Element_Dummy
{
    protected $_content = '<hr>';

    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }
    }
}