<?
$this->headScript()
	->appendFile($this->layout()->staticBaseUrl . 'externals/tinymce4/tinymce.min.js')
	->appendFile($this->layout()->staticBaseUrl . 'application/modules/Messages/externals/scripts/view.js');

	$contactInfo = null;
	if( $this->resource ) {
		// Resource
		$subTitle = $this->resource->toString();
		$description = $this->translate('To members of %1$s', $this->resource->toString());
	} else {
		 // Recipients
		$them = array();		
		/* @var $recipient Messages_Model_Recipient */
		foreach ($this->recipients as $recipient) {
			/**/
			$user = $recipient->getUser();			
			if (!$user->isSelf($this->viewer())) {
				$oneOfThem = $this->htmlLink($user->getHref(), $user->getTitle());
				if($recipient == $this->blocker){
					$oneOfThem = "<s>".$oneOfThem."</s>";
				}
				if(!empty($recipient->deleted)){
					$oneOfThem = '<i class="deleted">'.$oneOfThem.'</i>';
				}
				$them[] = $user;
				$subTitle = $user->getTitle();
				if ($this->viewer()->getIdentity()
					&& $this->viewer()->hasMethodistRights()
					&& !empty(Engine_Api::_()->core()->getNowDomainSettings()['zftshDefaults'])
					&& ($member_code = $user->getZftshMemberData('member_code'))
				){
					$subTitle .= ' <span class="member_code">'.$member_code.'</span>';
				}
			} else {				
				$you  = $this->htmlLink($user->getHref(), $this->translate('You'));
			}
		}
		if (count($this->recipients) == 2 && (Engine_Api::_()->core()->getNowDomainSettings()['key'] == 'zftsh') ){
			$contactInfo = $them[0]->getZftshMemberData('contact_information');
		}
		if (count($them)) {
			$description = $this->translate('Between %1$s and %2$s', $you, $this->fluentList($them));
		} else{
			$description = 'Conversation with a deleted member.';
		}
	}
	$title = trim($this->conversation->getTitle());
	if(!empty($title)){ 	
		$subTitle = $title;
	} else if(is_countable($this->recipients) && count($this->recipients) > 2){
		$subTitle = "Диалог без темы";
	}
	
?>
<? if ($this->hasAcademyMenu ){ ?>
    <?=$this->content()->renderWidget('zftsh.profile-tabs')?>
<? } ?>
<H2><a href="<?= $this->url(array('action' => 'inbox')) ?>" class="navigation_prev_node">Все диалоги <i class="fa fa-chevron-right"></i></a> <?= $subTitle ?></H2>
<div class="message_view_header">
  <div class="message_view_description">
    <?= $description ?>
  </div>  
  <? if (is_countable($this->recipients) && count($this->recipients) > 2){ ?>
  <a href="<?= $this->url(array('action' => 'user-list-edit', 'id' => $this->conversation->getIdentity())) ?>" 
  	class="fa fa-edit list-edit-link"></a>
  <? } ?>
  <?  
  if(!empty($this->lastOnlineDate)){
  	echo '<div class="last_visit_info">Был на сайте: '.$this->timestamp($this->lastOnlineDate).'</div>';
  }  
  ?>
  <div class="clr"></div>
</div>


<ul class="message_view" data-viewed-url="<?= $this->url(array('action' => 'setviewed', 'id' => $this->conversation->getIdentity())) ?>">
	<?= $this->partial('messages/view.list.tpl', $this->getVars()); ?>  
</ul>

<? if( !$this->locked ): ?>
<div class='message_quick_entry'>     
    <div class='message_view_info'>
    <? if( (!$this->blocked && !$this->viewer_blocked) || (is_countable($this->recipients) && count($this->recipients)>1)): ?>
    <?=$this->form->setAttrib('id', 'messages_form_reply')->render($this) ?>
    <? elseif ($this->viewer_blocked):?>
    <?=$this->translate('You can no longer respond to this message because you have blocked %1$s.', $this->viewer_blocker->getTitle())?>
    <? else:?>
    <?=$this->translate('You can no longer respond to this message because %1$s has blocked you.', $this->blocker->getTitle())?>
    <? endif; ?>
	</div>
 </div>
 <? endif ?>
<? if ($contactInfo){ ?>
	<div id="messages_contact_info">
		<? require_once('application/libraries/NameCaseLib/Library/NCL.NameCase.ru.php'); $nameCases = new NCLNameCaseRu(); ?>
		<i class="fa fa-phone" style="opacity: 0.1;font-size: 32px;margin-right: 25px;"></i>
		<div>Контактная информация <?=$nameCases->q($subTitle, NCL::$GENETIVE)?>: <BR> <?=$contactInfo?></div>
	</div>
<? } ?>


