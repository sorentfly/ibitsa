 <?php

class Engine_Form_Decorator_FormAddressText extends Zend_Form_Decorator_Abstract
{
    /**
     * Default placement: surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * Render
     *
     * Renders as the following:
     * <dt></dt>
     * <dd>$content</dd>
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $elementName = $this->getElement()->getName();

        $options = $this->getOptions();
        if( !isset($options['label']) ) {
          $options['label'] = '&nbsp;';
        } else {
          $translate = $this->getElement()->getTranslator();
          if( $translate ) {
            $options['label'] = $translate->translate($options['label']);
          }
        }

        return
          '<input type="text" id="' . $elementName . '" value="" style=\'width: 206px; height: 22px;\' onchange="drawAddress(this.value);">' . '</input>';
    }
}  
