<?php
class Bitsa_Form_Element_AddEducation extends Zend_Form_Element
{
    protected $_content;

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->removeDecorator('ViewHelper');
        if (null !== $view)
        {
            $this->setView($view);
        }

        $content = $this->getContent();
        foreach ($this->getDecorators() as $decorator)
        {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
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
                ->addDecorator('AddEducation');
        }
    }
}
