<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormComposite.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Decorator_FormComposite extends Zend_Form_Decorator_Abstract
{
    /**
     * Merges given two belongsTo (array notation) strings
     *
     * @param  string $baseBelongsTo
     * @param  string $belongsTo
     * @return string
     */
    public function mergeBelongsTo($baseBelongsTo, $belongsTo)
    {
        $endOfArrayName = strpos($belongsTo, '[');

        if ($endOfArrayName === false) {
            return $baseBelongsTo . '[' . $belongsTo . ']';
        }

        $arrayName = substr($belongsTo, 0, $endOfArrayName);

        return $baseBelongsTo . '[' . $arrayName . ']' . substr($belongsTo, $endOfArrayName);
    }

    /**
     * Render form elements
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $form    = $this->getElement();
        if( (!$form instanceof Engine_Form_Element_Composite) )
        {
            return $content;
        }

        $belongsTo      = $form->getElementsBelongTo();
        $elementContent = '';
        $separator      = $this->getSeparator();
        $translator     = $form->getTranslator();
        $items          = array();
        $view           = $form->getView();
        foreach ($form as $item) {
            $item->setView($view)
                 ->setTranslator($translator);
            if ($item instanceof Zend_Form_Element) {
                $item->setBelongsTo($belongsTo);
            } elseif (!empty($belongsTo) && ($item instanceof Zend_Form)) {
                if ($item->isArray()) {
                    $name = $this->mergeBelongsTo($belongsTo, $item->getElementsBelongTo());
                    $item->setElementsBelongTo($name, true);
                } else {
                    $item->setElementsBelongTo($belongsTo, true);
                }
            } elseif (!empty($belongsTo) && ($item instanceof Zend_Form_DisplayGroup)) {
                foreach ($item as $element) {
                    $element->setBelongsTo($belongsTo);
                }
            }

            $items[] = $item->render();

            if (($item instanceof Zend_Form_Element_File)
                || (($item instanceof Zend_Form) 
                    && (Zend_Form::ENCTYPE_MULTIPART == $item->getEnctype()))
                || (($item instanceof Zend_Form_DisplayGroup)
                    && (Zend_Form::ENCTYPE_MULTIPART == $item->getAttrib('enctype')))
            ) {
                if ($form instanceof Zend_Form) {
                    $form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
                } elseif ($form instanceof Zend_Form_DisplayGroup) {
                    $form->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
                }
            }
        }
        $elementContent = implode($separator, $items);

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $elementContent . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $elementContent;
        }
    }
}
