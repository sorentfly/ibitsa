<?
class User_Form_Edit_RemovePhoto extends Engine_Form
{
    public function init()
    {
        $this->setTitle('Remove Photo')
                ->setDescription('Do you want to remove your profile photo?  Doing so will set your photo back to the default photo.')
                ->setMethod('POST')
                ->setAction($_SERVER['REQUEST_URI'])
                ->setAttrib('class', 'global_form_popup');

        //$this->addElement('Hash', 'token');

        // Buttons
        $this->addElement('Button', 'submit', array(
            'label' => 'Remove Photo',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'href' => '',
            'onclick' => 'parent.Smoothbox.close();',
            'decorators' => array(
                'ViewHelper'
            )
        ));
        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
        $button_group = $this->getDisplayGroup('buttons');
    }
}