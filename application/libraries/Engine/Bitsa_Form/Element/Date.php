<?php
class Bitsa_Form_Element_Date extends Zend_Form_Element_Text
{
    public function init()
    {
        $this->addFilters(array('StringTrim', 'StripTags'));

        if ($this->getValue() !== '')
        {
            $this->setValue($this->getValue());
            Zend_Registry::set($this->getId(), $this->getValue());
        }

        $this->setAttribs(array(
            'autocomplete' => 'off',
            'maxlength' => '10',
            'placeholder' => 'DD.MM.YYYY',
        ));

        if($this->isRequired())
        {
            $this->setAttrib('required', 'required');
            $this->addValidator('NotEmpty', true);
            $this->getValidator('NotEmpty')->setMessage('Date is empty');
        }

        $this->addValidator('StringLength', true, array(10, 10));
        $this->getValidator('StringLength')->setMessage('Date must have 10 characters in format dd.mm.yyyy, for example, 30.01.2000');

        $this->addValidator('Date', true, array('d.m.Y'));
        $this->getValidator('Date')->setMessage('Incorrect date. Correct format dd.mm.yyyy, for example, 30.01.2000');
    }

    public function setValue($value) {
        return parent::setValue( $value ? date('d.m.Y',strtotime($value)) : '' );
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
                ->addDecorator('FormDate');
        }
    }
}