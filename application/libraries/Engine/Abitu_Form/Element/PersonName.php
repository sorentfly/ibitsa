<?
class Abitu_Form_Element_PersonName extends Zend_Form_Element_Text
{
    private $translator;
    const ERROR_TEXT = 'Поле должно состоять либо из целиком из русских букв, либо только из английских. При этом допускаются пробел и знаки "-", "\'" в качаестве разделителей';
    const ERROR_TEXT_EN = 'The field must consist entirely of Russian letters, or only English letters. In this case, a space and "-", "\'" as separators';

    const ERROR_TEXT_CYR = 'Поле должно состоять только из русских букв. При этом допускаются пробел и знаки "-", "\'" в качаестве разделителей';
    const ERROR_TEXT_CYR_EN = 'The field should consist only of Russian letters. In this case, a space and "-", "" characters are allowed in the separators';

    const PATRO_TEXT = 'Ваше отчество, например, Иванович. В случае если отчество отсутсвует, введите символ "-".';
    const PATRO_TEXT_EN = 'Your middle name, for example, Ivanovich. If the patronymic is missing, enter the "-" symbol.';

    public function init()
    {
        $this->translator = $this->getTranslator();
        $this->addFilters(array('StringTrim', 'CapitalLetter', 'StripTags'));                                
        
        switch($this->getId())
        {
            case 'first_name':
            case 'middle_name':
            case 'last_name':
            {
                $lower_template = str_replace('_name', '', $this->getId());
                $capitalized_template = mb_convert_case($lower_template, MB_CASE_TITLE);
                
                $placeholder = $this->translator->translate($capitalized_template . ' name');
                $this->setLabel($placeholder);
                $notEmptyValidationError = $this->translator->translate('Your ' . $lower_template . ' name is empty');
                $stringLengthValidationErrorTooShort = $this->translator->translate($capitalized_template . ' name must have at least %min% characters');
                $stringLengthValidationErrorTooLong = $this->translator->translate($capitalized_template . ' name must have at most %max% characters');
            }
            break;
        }
        
        $this->addValidator('NotEmpty', true);        
        $this->addValidator('StringLength', true, array($this->getId()=='middle_name' ? 0 : 2, 32));
        
        $this->getValidator('NotEmpty')->setMessage($notEmptyValidationError, 'isEmpty');
        $this->getValidator('StringLength')->setMessage($stringLengthValidationErrorTooShort, 'stringLengthTooShort');
        $this->getValidator('StringLength')->setMessage($stringLengthValidationErrorTooLong, 'stringLengthTooLong');
        $isEn = Zend_Registry::get('Zend_Translate')->getLocale() == 'en';

        if ($this->getId()=='middle_name'){
            $this->getView()->headScript()->appendFile('/application/modules/User/externals/scripts/field_middle_name.js');
            $this->getView()->headTranslate(['I have no middle name']);
        }
        if ($this->getAttrib('cyrillic_only')){
            $reValidator = new Zend_Validate_Regex($this->getId()=='middle_name' ? '#-|' . mb_substr(USERNAME_PATTERN_RU, 1) : USERNAME_PATTERN_RU);
            $reValidator->setMessage(self::ERROR_TEXT_CYR);
        }else{
            $reValidator = new Zend_Validate_Regex($this->getId()=='middle_name' ? '#-|' . mb_substr(USERNAME_PATTERN, 1) : USERNAME_PATTERN);
        }
        $reValidator->setMessage($reError = ($this->getAttrib('cyrillic_only')
            ? ($isEn ? self::ERROR_TEXT_CYR_EN : self::ERROR_TEXT_CYR)
            : ($isEn ? self::ERROR_TEXT_EN : self::ERROR_TEXT) )
        );


        $this->addValidator($reValidator);

        $attributes = array(
            'class' => 'person_name',
            'maxlength' => '32',
            'onchange' => 'userNamesChange();',
            'onkeydown' => 'userNamesTextchange(this);',
            'pattern' => $this->getAttrib('cyrillic_only')
                ? ($this->getId()=='middle_name' ? '-|'.USERNAME_PATTERN_HTML_RU: USERNAME_PATTERN_HTML_RU)
                : ($this->getId()=='middle_name' ? '-|'.USERNAME_PATTERN_HTML: USERNAME_PATTERN_HTML),
            'placeholder' => $placeholder
        );
                
        if($this->isRequired())
        {
            $attributes['required'] = 'required';
        }
        
        $this->setAttribs($attributes);
        
        $title = $this->getAttrib('title');
        $this->setAttrib('title', ($title ? ($title.'. '):'').$reError );
        if ($this->getId()=='middle_name'){
            $title = $this->setAttrib('title', ($isEn ? self::PATRO_TEXT_EN : self::PATRO_TEXT). ' '. $reError);
        }
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
                    ->addDecorator('FormText');
        }
    }
}