<?
	/*@var $recipient Messages_Model_Recipient */
	if(empty($this->recipient)) return ;
	$recipient = $this->recipient;
	$user = $recipient->getUser();	
?>
<li <?=!empty($recipient->deleted)? ' class="deleted"': ""?> data-user-id = "<?= $recipient->user_id?>">
	<a href="<?= $user->getHref() ?>"  target="_blank"><?= $this->itemPhoto($user, 'thumb.icon'); ?></a>
	<a href="<?= $user->getHref() ?>" class="user-list-user-name" target="_blank"><?=$user->getTitle(); ?></a>
	
	<div class="user-list-user-right">
	<? if($user->getIdentity() == $this->conversation->user_id){?>
		<div class="user-list-user-extra">Создатель диалога</div>
	<? } else if (!empty($recipient->deleted)){?>
		<a href="<?= $this->url(array('action' => 'user-list-edit', 'id' => $this->conversation->getIdentity(), 'add_user_id' => $user->getIdentity())) ?>" 
			class="fa fa-undo user-undo-link" title="Восстановить пользователя"></a>
		<div class="user-list-user-extra user-removed-label">Пользователь удален
			<?= $this->timestamp($recipient->deleted); ?>
		</div>
	<? } else { ?>			
		<a href="<?= $this->url(array('action' => 'user-list-edit', 'id' => $this->conversation->getIdentity(), 'remove_user_id' => $user->getIdentity())) ?>"
		 	class="fa fa-remove user-remove-link" title="Удалить пользователя из диалога"></a>
		 	<? if(!empty($recipient->date)){ ?>
			<p class="user-list-user-extra user-removed-label">Пользователь добавлен
				<?= $this->timestamp($recipient->date); ?>
			</p>	
			<?} ?>
	<? } ?>
	</div>
	<div class="clr"></div>
</li>