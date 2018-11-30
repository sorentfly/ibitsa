<?
class Engine_Form_Decorator_FormCallbackTime extends Zend_Form_Decorator_Abstract
{
    protected $_placement = null;

    public function render($content)
    {
        $translate = $this->getElement()->getTranslator();
        $elementName = $this->getElement()->getName();

        $label = $this->getElement()->getLabel();
        $description = $this->getElement()->getDescription();


        if (!isset($label)) {
            $label = '&nbsp;';
        } else {
            $label = $translate->translate($label);
        }

        if ($this->getElement()->isRequired()) {
            $label .= ' <b class="asterisk">*</b>';
        }

        $element = '<div id="' . $elementName . '-wrapper" class="form-wrapper">' .
            '<div id="' . $elementName . '-label" class="form-label">' . $label . '</div>' .
            '<div id="' . $elementName . '-element" class="form-element">' . $content . '<p class="description">' . $translate->translate($description) . ', ' .
            $translate->translate('for example') . ', <span class="time_example" id="time_now">' . $translate->translate('now') . '</span>, <span class="time_example" id="time_any">' . $translate->translate('anytime') . '</span>, ' . $translate->translate('or') .
            '<span class="time_example" id="time_evening">20:00 — 22:00</span>. ' . $translate->translate("Please, don not forgot to specify <a href='//time.yandex.com/' target='_blank'>Moscow time zone</a> (UTC +3:00).") . '  </p></div>' .
            '</div>';

        $element .= '<div class="form-wrapper callback_hours_cell">' .
            '<div class="form-label">' . $translate->translate('Hours') . '</div>' .
            '<div class="form-element callback_hours" onmouseout="blurTime()"><ul>';

        for ($i = 0; $i < 24; $i++) {
            $element .= '<li onclick="time_form(this, ' . $i . ')" onmouseover="highlightTime(this)" onmouseout="blurTime()">' . $i . '</li>';
        }

        $element .= '</ul></div>';
        $element .= '<div class="hours_reset"><span onclick="hoursReset();">' . $translate->translate('Reset') . '</span></div>';
        $element .= '</div>';
        return $element;
    }
}
