<?
class Abitu_Form_Element_Birthdate extends Zend_Form_Element_Text
{
    private $translator;
    public function init()
    {
        $this->translator = $this->getTranslator();
        $this->addFilters(array('StringTrim', 'StripTags'));
        
        if ($this->getValue() !== '')
        {
            $this->setValue($this->getValue());
            Zend_Registry::set($this->getId(), $this->getValue());
        }
        
        $this->setLabel($this->translator->translate('Birthdate'));        
        //$this->setDescription($this->translator->translate('Enter your birthdate in the format DD.MM.YYYY, for example') . ' 31.12.' . (date('Y') - 15));
        $this->setAttribs(array(
            'autocomplete' => 'off',            
            'maxlength' => '10',
            'placeholder' => $this->translator->translate('Birthdate'),
            //'title' => $this->translator->translate('Enter your birthdate in the format DD.MM.YYYY, for example') . ' 31.12.' . (date('Y') - 15)
        ));
        
        $this->setAttrib('required', 'required');
        $this->addValidator('NotEmpty', true);
        $this->getValidator('NotEmpty')->setMessage($this->translator->translate('Your birthdate is empty'));


        $this->addValidator('StringLength', true, array(10, 10));
        $this->getValidator('StringLength')->setMessage('Date must have 10 characters in format dd.mm.yyyy, for example, 30.01.2000');

        if(!empty($_POST[$this->getValue()]))
        {
            $this->addFilter('BirthdateNormalized'); // Меняет дату произвольного формата на yyyy-mm-dd
        }
    }
    
    public function setValue($value) {
        parent::setValue( date('d.m.Y',strtotime($value)) );
        return parent::setValue( date('d.m.Y',strtotime($value)) );
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
