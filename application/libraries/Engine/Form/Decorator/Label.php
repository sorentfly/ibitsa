<?php
class Engine_Form_Decorator_Label extends Zend_Form_Decorator_Label
{

    /**
     * Render a label
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view = $element->getView();
        if (null === $view)
        {
            return $content;
        }

        $label = $this->getLabel();
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag = $this->getTag();
        $id = $this->getId();
        $class = $this->getClass();
        $options = $this->getOptions();

        unset($options['tagOptions']);
        $tagOptions = $this->getOption('tagOptions', array());

        if (empty($label) && empty($tag))
        {
            return $content;
        }

        if (!empty($label))
        {
            $options['class'] = $class;
            $label = $view->formLabel($element->getFullyQualifiedName(), trim($label), $options);
            
            if($element->isRequired())
            {                
                $label .= ' <b class="asterisk">*</b>';
            }
        }
        else
        {
            $label = '&nbsp;';
        }

        if (null !== $tag)
        {
            $tagOptions['tag'] = $tag;
            if (!isset($tagOptions['id']))
            {
                $tagOptions['id'] = $this->getElement()->getName() . '-label';
                if (null !== ($belongsTo = $this->getElement()->getBelongsTo()))
                {
                    $tagOptions['id'] = $belongsTo . '-' . $tagOptions['id'];
                }
            }
            require_once 'Zend/Form/Decorator/HtmlTag.php';
            $decorator = new Zend_Form_Decorator_HtmlTag();
            $decorator->setOptions($tagOptions);

            $label = $decorator->render($label);
        }

        switch ($placement)
        {
            case self::APPEND:
                return $content . $separator . $label;
            case self::PREPEND:
                return $label . $separator . $content;
        }
    }

}
