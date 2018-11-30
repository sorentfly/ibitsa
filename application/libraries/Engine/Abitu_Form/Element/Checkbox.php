<?php
class Abitu_Form_Element_Checkbox extends Zend_Form_Element_Checkbox
{

    protected $_title;
    public $options = array(
        'checkedValue' => '1',
        'uncheckedValue' => '',
    );
    protected $_checkedValue = '1';
    protected $_uncheckedValue = '';
    protected $_value = '';

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getDescription()
    {
        if (empty($this->_description))
        {
            $this->getDecorator('Description')->setOption('escape', false);
            return '&nbsp;';
        }
        return $this->_description;
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
            //$fqName = $this->getName();
            $this->addDecorator('ViewHelper')
                    ->addDecorator('FormCheckbox');
        }
    }

}