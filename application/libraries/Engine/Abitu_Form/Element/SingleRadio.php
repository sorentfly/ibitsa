<?php
class Abitu_Form_Element_SingleRadio extends Zend_Form_Element_Checkbox
{
    public $helper = 'FormSingleRadio';
    protected $_nameGroup;

    public function setNameGroup($nameGroup)
    {
        $this->_nameGroup = $nameGroup;
        return $this;
    }

    public function getFullyQualifiedName()
    {
        if (null === $this->_nameGroup)
        {
            return parent::getFullyQualifiedName();
        }

        return $this->_nameGroup;
    }

    public function getId()
    {
        if (isset($this->id))
        {
            return $this->id;
        }

        $id = parent::getFullyQualifiedName();

        // Bail early if no array notation detected
        if (!strstr($id, '['))
        {
            return $id;
        }

        // Strip array notation
        if ('[]' == substr($id, -2))
        {
            $id = substr($id, 0, strlen($id) - 2);
        }
        $id = str_replace('][', '-', $id);
        $id = str_replace(array(']', '['), '-', $id);
        $id = trim($id, '-');

        return $id;
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
                ->addDecorator('FormRadio');
        }
    }
}