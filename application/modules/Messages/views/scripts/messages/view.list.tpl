<? 
if($this->messageLeftCount > 0){ ?>
<li class="more-link-wrp">
	<a href="<?= $this->url(array('action' => 'view', 'start' => $this->minMessageId)) ?>" class="more-link">
	<?=$this->translate(array('More %1$s messages', null, $this->messageLeftCount), $this->messageLeftCount); ?>
	</a>
</li>
<? } ?>
<?
/* @var $message Messages_Model_Message */
/* $this->messages Zend_Db_Table_Rowset */
foreach ($this->messages as $message) {
	$sender = $this->user($message->user_id);
	if ($this->conversation->hasResource() && $this->conversation->user_id == $message->user_id){
		$sender = $this->conversation->getResource();
	}
	if (! $message->hasReadedBy($this->recipient)) {
		$liClass = ' class="message_view_new"';
	} else {
		$liClass = '';
	}
	?>
<li <?=$liClass ?>>
	<div class='message_view_leftwrapper'>
		<div class='message_view_photo'>
          <?=$this->htmlLink($sender->getHref(), $this->itemPhoto($sender, 'thumb.icon'))?>
        </div>
		<div class='message_view_from'>
			<div>
            <?=$this->htmlLink($sender->getHref(), $sender->getTitle())?>
          </div>
			<div class="message_view_date">
            <?=$this->timestamp($message->date)?>
          </div>
		</div>		
	</div>
	<div class='message_view_info'>
      	<? if($message->canBeDeletedBy($this->recipient)){ ?>
          <a
			href="<?= $this->url(array('action' => 'messagedelete', 'id' => $message->getIdentity())) ?>"
			class="fa fa-times-circle message_delete_link"
			title="Сообщение можно удалить, если оно не прочитано другими пользователями"></a>
        <? } ?>
        <?=html_entity_decode($message->body)?>
        <? if( !empty($message->attachment_type) && null !== ($attachment = $this->item($message->attachment_type, $message->attachment_id))): ?>
          <div class="message_attachment">
            <? if(null != ( $richContent = $attachment->getRichContent(false, array('message'=>$message->conversation_id)))): ?>
              <?=$richContent; ?>
            <? else: ?>
              <div class="message_attachment_photo">
                <? if( null !== $attachment->getPhotoUrl() ): ?>
                  <?=$this->itemPhoto($attachment, 'thumb.normal')?>
                <? endif; ?>
              </div>
			<div class="message_attachment_info">
				<div class="message_attachment_title">
                  <?=$this->htmlLink($attachment->getHref(array('message'=>$message->conversation_id)), $attachment->getTitle())?>
                </div>
				<div class="message_attachment_desc">
                  <?=$attachment->getDescription()?>
                </div>
			</div>
           <? endif; ?>
          </div>
        <? endif; ?>
      </div>
</li>
<? }; ?>




