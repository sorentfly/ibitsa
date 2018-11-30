<ul>
    <? if(!empty($this->memberType)): ?>
        <li>
            <?=$this->translate('Member Type:') ?>
            <?=$this->translate($this->memberType) ?>
        </li>
    <? endif; ?>
    <? if(!empty($this->networks) && count($this->networks) > 0): ?>
        <li>
            <?=$this->translate('Networks:') ?>
            <?=$this->fluentList($this->networks) ?>
        </li>
    <? endif; ?>
    <li>
        <?=$this->translate('Profile Views:') ?>
        <?=$this->translate(array('%s view', '%s views', $this->subject->view_count), $this->locale()->toNumber($this->subject->view_count)); ?>
    </li>
    <li>
        <? $direction = Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction'); ?>
        <? if($direction == 0): ?>
            <?=$this->translate('Followers:') ?>  
            <?=$this->translate(array('%s follower', '%s followers', $this->subject->member_count), $this->locale()->toNumber($this->subject->member_count)); ?>      
        <? else: ?>  
            <?=$this->translate('Friends:') ?>
            <?=$this->translate(array('%s friend', '%s friends', $this->subject->member_count), $this->locale()->toNumber($this->subject->member_count)); ?>
        <? endif; ?>
    </li>
    <li>
        <?=$this->translate('Last Update:'); ?>
        <?=$this->timestamp($this->subject->modified_date) ?>
    </li>
    <li>
        <?=$this->translate('Joined:') ?>
        <?=$this->timestamp($this->subject->creation_date) ?>
    </li>
    <? if(!$this->subject->enabled && $this->viewer->isAdmin()): ?>
        <li><em><?=$this->translate('Enabled:') ?> <?=$this->translate('No') ?></em></li>
    <? endif; ?>
</ul>