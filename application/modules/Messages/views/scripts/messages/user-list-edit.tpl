<?
$this->headScript()
	->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
	->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
	->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js')
	->appendFile($this->layout()->staticBaseUrl . 'application/modules/Messages/externals/scripts/user-list-edit.js');
	
$title = trim($this->conversation->getTitle());
if(empty($title)){
	$title = "Диалог без темы";
}
?>

<h2><?=$title?></h2>
<a href="javascript: parent.Smoothbox.close();" class="fa fa-times close-link"></a>

<form action="<?= $this->url(array('action' => 'user-list-edit', 'id' => $this->conversation->getIdentity())) ?>" method="post" class="add-user-form">
	<input type="hidden" id="add_user_id" name="add_user_id" />
	<input type="text" id="adduserName" name="adduserName" placeholder="<?=$this->translate('Start typing...') ?>"/>
	<input type="submit" value="Добавить пользователя" data-loading-text="Добавление..."/>	
</form>
<ul class="user-list" data-max-recipients="<?= $this->maxRecipients ?>">
	<?foreach ($this->recipients as $recipient){
		echo $this->partial('messages/user-list-edit.list.tpl', array('conversation'  => $this->conversation, 'recipient' => $recipient)); 
	} ?>
</ul>
