<? 
$this->headScript()->appendFile('/externals/simpleselect/jquery.simpleselect.js');
$this->headLink()->appendStylesheet('/externals/simpleselect/jquery.simpleselect.css');

$this->headScript()->appendFile('/application/modules/Messages/externals/scripts/inbox.js');
$this->headLink()->appendStylesheet('/application/modules/Messages/externals/styles/inbox.css');
?>
<? if ($this->hasAcademyMenu ){ ?>
    <?=$this->content()->renderWidget('zftsh.profile-tabs')?>
<? } ?>

<h2 class="navigation_prev_node">
  <?= $this->translate( ['You have %1$s new message, %2$s total', $this->unreadCount, $this->wholeCount],
  			$this->locale()->toNumber($this->unreadCount),
  			$this->locale()->toNumber($this->wholeCount)
  )?>
    &nbsp;&nbsp;
    <a href="<?=$this->url(array('action' => 'compose')); ?>" title="Новый диалог"> <i class="fa fa-weixin"></i> </a>
</h2>
<br/>

<form class="global_form" action="<?= $this->url(['action' => 'inbox'],'messages_general', true) ?>" id="filterForm">
	
	<? if(is_array($this->classSelectOptions) && !empty($this->classSelectOptions)){ ?>
	<div class="form-element">
		Класс ученика:		
		<? 
		foreach ($this->classSelectOptions as $classKey => $classOption){
			if($classKey == null){				
				$this->classSelectOptions = ['other' => 'Без класса ('.$classOption.' шт.)'] + $this->classSelectOptions;
				unset($this->classSelectOptions[$classKey]);
			} else {
				$this->classSelectOptions[$classKey] = $classKey.' класс ('.$classOption.' шт.)';
			}		
		} 
		$this->classSelectOptions = ['all' => 'Все сообщения'] + $this->classSelectOptions;
		?>
		<?=$this->formSelect('pupil_class', $this->filter['pupil_class'], ['title' => 'Класс обучения', 'autocomplete' => 'off'], $this->classSelectOptions); ?> 
	</div>
	<? } ?>

	<div class="form-element">
		<label for="only_unreaded">
			<input type="checkbox" value="1" id="only_unreaded" name="only_unreaded"<?=$this->filter['only_unreaded']?' checked':''?> autocomplete="off">
			<span>только непрочитанные</span>
		</label>
	</div>
	<input type="hidden" name="page" value="<?=$this->page?>" />
</form>

<div class="messages_list">
	<?= $this->partial('messages/inbox.list.tpl',  ['paginator' => $this->paginator, 'filter' => $this->filter, 'page' => $this->page])?>
</div>

