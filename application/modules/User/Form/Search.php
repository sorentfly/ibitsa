<?
class User_Form_Search extends Fields_Form_Search
{
    public function init()
    {
        // Add custom elements
        $this->getMemberTypeElement();
        $this->getDisplayNameElement();
        $this->getAdditionalOptionsElement();

        parent::init();

        $this->loadDefaultDecorators();

        $this->getDecorator('HtmlTag')->setOption('class', 'browsemembers_criteria');
    }

    public function getMemberTypeElement()
    {
        $multiOptions = array('' => ' ');
        $profileTypeFields = Engine_Api::_()->fields()->getFieldsObjectsByAlias($this->_fieldType, 'profile_type');
        if (count($profileTypeFields) !== 1 || !isset($profileTypeFields['profile_type'])) return;
        $profileTypeField = $profileTypeFields['profile_type'];

        $options = $profileTypeField->getOptions();

        if (count($options) <= 1) {
            if (count($options) == 1) {
                $this->_topLevelId = $profileTypeField->field_id;
                $this->_topLevelValue = $options[0]->option_id;
            }
            return;
        }

        foreach ($options as $option) {
            $multiOptions[$option->option_id] = $option->label;
        }

        $this->addElement('Select', 'profile_type', array(
            'label' => 'Member Type',
            'order' => -1000001,
            'class' =>
                'field_toggle' . ' ' .
                'parent_' . 0 . ' ' .
                'option_' . 0 . ' ' .
                'field_' . $profileTypeField->field_id . ' ',
            'onchange' => 'changeFields($(this));',
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => 'span')),
                array('HtmlTag', array('tag' => 'li'))
            ),
            'multiOptions' => $multiOptions,
        ));
        return $this->profile_type;
    }

    public function getDisplayNameElement()
    {
        $this->addElement('Text', 'displayname', array(
            'label' => 'Name',
            'order' => -1000000,
            'decorators' => array(
                'ViewHelper',
                array('Label', array('tag' => 'span')),
                array('HtmlTag', array('tag' => 'li'))
            ),
            //'onkeypress' => 'return submitEnter(event)',
        ));
        return $this->displayname;
    }

    public function getAdditionalOptionsElement()
    {
        $subform = new Zend_Form_SubForm(array(
            'name' => 'extra',
            'order' => 1000000,
            'decorators' => array(
                'FormElements',
            )
        ));
        Engine_Form::enableForm($subform);

        $subform->addElement('Checkbox', 'has_photo', array(
            'label' => 'Only Members With Photos',
            'decorators' => array(
                'ViewHelper',
                array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
                array('HtmlTag', array('tag' => 'li'))
            ),
        ));

        $subform->addElement('Checkbox', 'is_online', array(
            'label' => 'Only Online Members',
            'decorators' => array(
                'ViewHelper',
                array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
                array('HtmlTag', array('tag' => 'li'))
            ),
        ));

        $subform->addElement('Button', 'done', array(
            'label' => 'Search',
            'onclick' => 'javascript:searchMembers()',
            'ignore' => true,
        ));

        $this->addSubForm($subform, $subform->getName());

        return $this;
    }
}