<?
class Engine_Form_Decorator_FormTelephone extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;
    private $translate;

    public function render($content)
    {
        $this->translate = Zend_Registry::get('Zend_Translate');
        $style = '';
        if ($this->getElement()->getAttrib('class') === 'hidden') {
            $style = ' style="display:none;"';
        }

        $elementName = $this->getElement()->getName();
        $elementId = $this->getElement()->getId();
        $label = $this->getElement()->getLabel();
        if ($this->getElement()->isRequired()) {
            $label .= ' <b class="asterisk">*</b>';
        }
        
        $description = $this->getElement()->getDescription();
        
        $wrapper = '<div class="form-wrapper" ' . $style . '>' . 
            '<div class="form-label"><label for="' . $elementName . '">' . $label . '</label></div>' .
            '<div class="form-element"><div class="telephone"><div class="telephone-flag ru"></div>' . $content . '</div>'
            . '<ul class="telephone-code-list" style="display: none;">' .
                '<li class="telephone-country-code ru" data-code="+7">' . $this->translate->_('Russia') . '</li>' .
                '<li class="telephone-country-code ua" data-code="+380">' . $this->translate->_('Ukraine') . '</li>' .
                '<li class="telephone-country-code by" data-code="+375">' . $this->translate->_('Belarus') . '</li>' .
                '<li class="telephone-country-code kz" data-code="+7">' . $this->translate->_('Kazakhstan') . '</li>' .
                '<li class="telephone-country-code az" data-code="+994">' . $this->translate->_('Azerbaijan') . '</li>' .
                '<li class="telephone-country-code am" data-code="+374">' . $this->translate->_('Armenia') . '</li>' .
                '<li class="telephone-country-code ge" data-code="+995">' . $this->translate->_('Georgia') . '</li>' .
                '<li class="telephone-country-code il" data-code="+972">' . $this->translate->_('Israel') . '</li>' .
                '<li class="telephone-country-code in" data-code="+91">' . $this->translate->_('India') . '</li>' .
                '<li class="telephone-country-code cn" data-code="+86">' . $this->translate->_('China') . '</li>' .
                '<li class="telephone-country-code us" data-code="+1">' . $this->translate->_('USA') . '</li>' .
                '<li class="telephone-country-code de" data-code="+49">' . $this->translate->_('Germany') . '</li>' .
                '<li class="telephone-country-code kg" data-code="+996">' . $this->translate->_('Kyrgyzstan') . '</li>' .
                '<li class="telephone-country-code lv" data-code="+371">' . $this->translate->_('Latvia') . '</li>' .
                '<li class="telephone-country-code lt" data-code="+370">' . $this->translate->_('Lithuania') . '</li>' .
                '<li class="telephone-country-code ee" data-code="+372">' . $this->translate->_('Estonia') . '</li>' .
                '<li class="telephone-country-code md" data-code="+373">' . $this->translate->_('Moldova') . '</li>' .
                '<li class="telephone-country-code tj" data-code="+992">' . $this->translate->_('Tajikistan') . '</li>' .
                '<li class="telephone-country-code uz" data-code="+998">' . $this->translate->_('Uzbekistan') . '</li>' .
            '</ul>';

        $wrapper .= '<div class="field_correct" id="' . $elementId . '_correct"></div>';

        if ($description != null) {
            $wrapper .= '<p class="description">' . $description . '</p>';
        }

        $wrapper .= '</div></div>';

        return $wrapper;
    }
}