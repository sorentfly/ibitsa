<?
/* @var $conversation Messages_Model_Conversation */
$conversation = $this->conversation;
$anotherUser = null;
/* @var $userApi User_Api_Core  */
$userModel = Engine_Api::_()->user();
/* @var $user User_Model_User */

$recipients = $conversation->getRecipientsInfo();
if ($conversation->hasResource()) {
	$resource = $conversation->getResource();
}
$i = 0;
$thumbs = '';
/* @var $recipient Messages_Model_Recipient */
foreach ($recipients as $recipient){
	$user = $recipient->getUser();
	if($user && $user->getIdentity() && $user->getIdentity() != $this->viewer()->getIdentity() && $recipient->deleted==null){
		$anotherUser = $user;
		$thumb = $this->itemPhoto($user, 'thumb.icon', $user->getTitle(), array('title' => $user->getTitle()));
		$thumbs .= $thumb;
		$link = $this->htmlLink($user->getHref(), $thumb);
		$i++;
		if($i == 4) break;
	}    		
}
if ($i){
	if($i > 1){ ?>
	<div class="messages_list_photo group">
		<?= $thumbs ?>
	</div>
	<?} else {?>
	<div class="messages_list_photo">
		<?= $link ?>
	</div>
	<? }
}?>
<div class="messages_list_from">
		<p class="messages_list_from_name">
              <? if( !empty($resource) ){
                echo $resource->toString(); 
              } elseif($anotherUser && $conversation->recipients == 1 ) {
              	if(		in_array($anotherUser->academyStatus(), ['pupil_intramural','pupil_extramural'])
              		&& 	in_array($this->viewer()->academyStatus(), ['teacher_new', 'teacher_approved', 'methodist','admin'])){
              		
              		echo $this->htmlLink($anotherUser->getHref(), $anotherUser->last_name. ' '. $anotherUser->first_name);
              		if(!empty(Engine_Api::_()->core()->getNowDomainSettings()['zftshDefaults']) &&
						!empty($anotherUser->getZftshMemberData('member_code'))
					){
              			echo '<div class="member-code">'.$anotherUser->getZftshMemberData('member_code').'</div>';
              		}
              		
              	} else {
              		echo $this->htmlLink($anotherUser->getHref(), $anotherUser->getTitle());
              	}                 
              }else {
                echo $this->translate(array ('%s person','%s people',$conversation->recipients-1 ), $this->locale()->toNumber($conversation->recipients-1)) ;
              }
              ?>
		</p>	
</div>