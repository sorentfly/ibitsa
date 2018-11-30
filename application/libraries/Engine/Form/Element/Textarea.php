<?

class Engine_Form_Element_Textarea extends Zend_Form_Element_Textarea
{
    public $cols = 45;
    public $rows = 6;

    public function init()
    {
        $this->addFilters(array('StringTrim'));

        if ($this->isRequired()) {
            $this->setAttrib('required', 'required');
        }
    }


    public function loadDefaultDecorators()
    {

        if ($this->loadDefaultDecoratorsIsDisabled()) {

            return;
        }

        $decorators = $this->getDecorators();

        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
            Engine_Form::addDefaultDecorators($this);
        }
    }
}
