<?php
class Abitu_Form_Element_Integer extends Zend_Form_Element_Text
{
    //private $translate;
    public function init()
    {
        //$this->translate = Zend_Registry::get('Zend_Translate');
        
        $this->addFilters(array('StringTrim', 'StripTags', 'Digits'));
                
        $this->setOptions(array('inputType' => 'text'));
        
        $this->setAttrib('pattern', '[0-9]+')
        	->setAttrib('title', 'допускаются только цифры');
        
        switch ($this->getId())
        {
            case 'university_course':
            {
                $this->setAttribs(array('min' => '1',
                'max' => '6',
                'data-maxlength' => '1'));                
                $this->addValidator('GreaterThan', true, array(0));
                $this->addValidator('LessThan', true, array(7));                                
            }
                break;
            case 'school_class':
            {                
                $this->setAttribs(array('min' => '1',
                'max' => '11',
                'data-maxlength' => '2'));
                $this->addValidator('GreaterThan', true, array(0));
                $this->addValidator('LessThan', true, array(12));
                $this->setClassValue();                
            }
                break;
            case 'university_group_number':
            case 'university_duration':
            case 'house_number':
            case 'room_apartment':
            {
                $this->setAttribs(array('min' => '1', 'data-maxlength' => '5'));                
                $this->addValidator('GreaterThan', true, array(0));
            }
                break;                        
        }
        
        $this->addValidator('Int', true);
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
                ->addDecorator('FormText');
        }
    }
    
    private function setClassValue()
    {
        if((String)$this->getValue() !== '')
        {
            return;
        }
        
        if (Zend_Registry::isRegistered('birthdate'))
        {
            $school_class = 11 - (17 - ((int)date('Y', time()) - (int)date('Y', strtotime(Zend_Registry::get('birthdate')))));
            if($school_class >= 1 && $school_class <= 11)
            {
                $this->setValue($school_class);
            }
            else
            {
                $this->setValue(11);
            }
        }
    }
    
}