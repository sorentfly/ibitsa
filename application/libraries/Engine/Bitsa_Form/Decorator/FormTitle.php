<?
class Bitsa_Form_Decorator_FormTitle extends Zend_Form_Decorator_Abstract
{
    protected $_placement = 'PREPEND';
    protected $_tag;

    public function setTag($tag)
    {
        if (empty($tag)) {
            $this->_tag = null;
        } else {
            $this->_tag = (string) $tag;
        }
        return $this;
    }

    public function getTag()
    {
        if (null === $this->_tag) {
            $tag = $this->getOption('tag');
            if (null !== $tag) {
                $this->removeOption('tag');
                $this->setTag($tag);
            }
            return $tag;
        }

        return $this->_tag;
    }

    public function getTitle()
    {
        if (null === ($element = $this->getElement())) {
            return '';
        }

        if( method_exists($element, 'getTitle') )
        {
          $label = $element->getTitle();
        }
        else
        {
          $label = $element->getAttrib('title');
          $element->removeAttrib('title');
        }
        $label = trim($label);

        if (empty($label)) {
            return '';
        }

        if (null !== ($translator = $element->getTranslator())) {
            $label = $translator->translate($label);
        }

        return $label;
    }

    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $label     = $this->getTitle();
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag       = $this->getTag();
        $options   = $this->getOptions();


        if (empty($label) || empty($tag)) {
            return $content;
        }

        //if (!empty($label)) {
            //$options['class'] = $class;
            //$label = $view->formLabel($element->getFullyQualifiedName(), trim($label), $options);
        //} else {
        //    $label = '&nbsp;';
        //}

        if (null !== $tag) {
            require_once 'Zend/Form/Decorator/HtmlTag.php';
            $decorator = new Zend_Form_Decorator_HtmlTag();
            $decorator->setOptions(array('tag' => $tag));

            $label = $decorator->render($label);
        }

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $label;
            case self::PREPEND:
                return $label . $separator . $content;
        }
    }
}
