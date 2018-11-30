<?
class Bitsa_Form_Decorator_FormErrors extends Zend_Form_Decorator_Abstract
{
    protected $_defaults = array(
        'ignoreSubForms'          => false,
        'markupElementLabelEnd'   => '', //'</b>',
        'markupElementLabelStart' => '', //'<b>',
        'markupListEnd'           => '</ul>',
        'markupListItemEnd'       => '</li>',
        'markupListItemStart'     => '<li>',
        'markupListStart'         => '<ul class="form-errors">',
    );

    protected $_skipLabels = false;
    protected $_ignoreSubForms;
    protected $_markupElementLabelEnd;
    protected $_markupElementLabelStart;
    protected $_markupListEnd;
    protected $_markupListItemEnd;
    protected $_markupListItemStart;
    protected $_markupListStart;

    public function render($content)
    {
        $form = $this->getElement();
        if (!$form instanceof Zend_Form) {
            return $content;
        }

        $view = $form->getView();
        if (null === $view) {
            return $content;
        }

        $this->initOptions();
        $markup = $this->_recurseForm($form, $view);
        
        if (empty($markup)) {
            return $content;
        }

        $markup = $this->getMarkupListStart()
                . $markup
                . $this->getMarkupListEnd();

        switch ($this->getPlacement()) {
            case self::APPEND:
                return $content . $this->getSeparator() . $markup;
            case self::PREPEND:
                return $markup . $this->getSeparator() . $content;
        }
    }

    public function initOptions()
    {
        $this->getMarkupElementLabelEnd();
        $this->getMarkupElementLabelStart();
        $this->getMarkupListEnd();
        $this->getMarkupListItemEnd();
        $this->getMarkupListItemStart();
        $this->getMarkupListStart();
        $this->getPlacement();
        $this->getSeparator();
        $this->ignoreSubForms();
    }

    public function getMarkupElementLabelStart()
    {
        if (null === $this->_markupElementLabelStart) {
            if (null === ($markupElementLabelStart = $this->getOption('markupElementLabelStart'))) {
                $this->setMarkupElementLabelStart($this->_defaults['markupElementLabelStart']);
            } else {
                $this->setMarkupElementLabelStart($markupElementLabelStart);
                $this->removeOption('markupElementLabelStart');
            }
        }

        return $this->_markupElementLabelStart;
    }
    
    public function setMarkupElementLabelStart($markupElementLabelStart)
    {
        $this->_markupElementLabelStart = $markupElementLabelStart;
        return $this;
    }

    public function getMarkupElementLabelEnd()
    {
        if (null === $this->_markupElementLabelEnd) {
            if (null === ($markupElementLabelEnd = $this->getOption('markupElementLabelEnd'))) {
                $this->setMarkupElementLabelEnd($this->_defaults['markupElementLabelEnd']);
            } else {
                $this->setMarkupElementLabelEnd($markupElementLabelEnd);
                $this->removeOption('markupElementLabelEnd');
            }
        }

        return $this->_markupElementLabelEnd;
    }

    public function setMarkupElementLabelEnd($markupElementLabelEnd)
    {
        $this->_markupElementLabelEnd = $markupElementLabelEnd;
        return $this;
    }

    public function getMarkupListStart()
    {
        if (null === $this->_markupListStart) {
            if (null === ($markupListStart = $this->getOption('markupListStart'))) {
                $this->setMarkupListStart($this->_defaults['markupListStart']);
            } else {
                $this->setMarkupListStart($markupListStart);
                $this->removeOption('markupListStart');
            }
        }

        return $this->_markupListStart;
    }

    public function setMarkupListStart($markupListStart)
    {
        $this->_markupListStart = $markupListStart;
        return $this;
    }

    public function getMarkupListEnd()
    {
        if (null === $this->_markupListEnd) {
            if (null === ($markupListEnd = $this->getOption('markupListEnd'))) {
                $this->setMarkupListEnd($this->_defaults['markupListEnd']);
            } else {
                $this->setMarkupListEnd($markupListEnd);
                $this->removeOption('markupListEnd');
            }
        }

        return $this->_markupListEnd;
    }

    public function setMarkupListEnd($markupListEnd)
    {
        $this->_markupListEnd = $markupListEnd;
        return $this;
    }

    public function getMarkupListItemStart()
    {
        if (null === $this->_markupListItemStart) {
            if (null === ($markupListItemStart = $this->getOption('markupListItemStart'))) {
                $this->setMarkupListItemStart($this->_defaults['markupListItemStart']);
            } else {
                $this->setMarkupListItemStart($markupListItemStart);
                $this->removeOption('markupListItemStart');
            }
        }

        return $this->_markupListItemStart;
    }

    public function setMarkupListItemStart($markupListItemStart)
    {
        $this->_markupListItemStart = $markupListItemStart;
        return $this;
    }

    public function getMarkupListItemEnd()
    {
        if (null === $this->_markupListItemEnd) {
            if (null === ($markupListItemEnd = $this->getOption('markupListItemEnd'))) {
                $this->setMarkupListItemEnd($this->_defaults['markupListItemEnd']);
            } else {
                $this->setMarkupListItemEnd($markupListItemEnd);
                $this->removeOption('markupListItemEnd');
            }
        }

        return $this->_markupListItemEnd;
    }

    public function setMarkupListItemEnd($markupListItemEnd)
    {
        $this->_markupListItemEnd = $markupListItemEnd;
        return $this;
    }

    public function ignoreSubForms()
    {
        if (null === $this->_ignoreSubForms) {
            if (null === ($ignoreSubForms = $this->getOption('ignoreSubForms'))) {
                $this->setIgnoreSubForms($this->_defaults['ignoreSubForms']);
            } else {
                $this->setIgnoreSubForms($ignoreSubForms);
                $this->removeOption('ignoreSubForms');
            }
        }

        return $this->_ignoreSubForms;
    }

    public function setIgnoreSubForms($ignoreSubForms)
    {
        $this->_ignoreSubForms = (bool) $ignoreSubForms;
        return $this;
    }

    public function setSkipLabels($skipLabels)
    {
        $this->_skipLabels = (bool) $skipLabels;
        return $this;
    }

    public function renderLabel(Zend_Form_Element $element, Zend_View_Interface $view)
    {
        $label = $element->getLabel();
        if (empty($label)) {
            $label = $element->getName();
        }

        if( null !== ($translate = $element->getTranslator()) ) {
          $label = $translate->translate($label);
        }

        return $this->getMarkupElementLabelStart()
             . $view->escape($label)
             . $this->getMarkupElementLabelEnd();
    }

    protected function _recurseForm(Zend_Form $form, Zend_View_Interface $view)
    {
        $content = '';
        $errors  = $form->getMessages();
        if ($form instanceof Zend_Form_SubForm || @$form->isSubForm) {
            $name = $form->getName();
            if ((1 == count($errors)) && array_key_exists($name, $errors)) {
                $errors = $errors[$name];
            }
        }
        if (empty($errors)) {
            return $content;
        }
        foreach ($errors as $name => $list) {
            $element = $form->$name;
            if( null === $element && is_numeric($name) )
            {
                $content .= $this->getMarkupListItemStart()
                         //.  $this->renderLabel($element, $view)
                         .  $view->formErrors((array)$list, $this->getOptions())
                         .  $this->getMarkupListItemEnd();
            }
            else if ($element instanceof Zend_Form_Element) {
                $element->setView($view);
                $content .= $this->getMarkupListItemStart()
                         // @todo this is bad, find better way?
                         // Note: this was commented out before
                         .  ( $this->_skipLabels ? '' : $this->renderLabel($element, $view) )
                         .  $view->formErrors($list, $this->getOptions())
                         .  $this->getMarkupListItemEnd();
            } elseif (!$this->ignoreSubForms() && ($element instanceof Zend_Form)) {
                $content .= $this->_recurseForm($element, $view);
            }
        }

        return $content;
    }
}