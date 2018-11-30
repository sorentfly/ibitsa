  <? if( !empty($this->ajax) ): // Simple feed only for AJAX ?>
    <? foreach( $this->users as $user ): ?>
      <li>
        <?=$this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
        <? if( $this->viewer()->getIdentity() ): ?>
          <div class='browsemembers_results_links'>
            <?=$this->userFriendship($user) ?>
          </div>
        <? endif; ?>

          <div class='browsemembers_results_info'>
            <?=$this->htmlLink($user->getHref(), $user->getTitle()) ?>
            <?=$user->status; ?>
            <? if( $user->status != "" ): ?>
              <div>
                <?=$this->timestamp($user->status_date) ?>
              </div>
            <? endif; ?>
          </div>
      </li>
    <? endforeach; ?>

    <script type="text/javascript">
      $('form_lastrow').value = <?=$this->lastrow; ?>;
      var lastrow = <?=$this->lastrow; ?>;
      var userCount = <?=$this->userCount; ?>;
    </script>
<? return; // Do no render the rest of the script in this mode
endif; ?>

<div>
    <h3>
      <?=$this->translate(array('%s member found.', '%s members found.', $this->totalUsers),$this->locale()->toNumber($this->totalUsers)) ?>
    </h3>
  </div>
  <ul id="browsemembers_ul">
    <? foreach( $this->users as $user ): ?>
      <li>
        <?=$this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
        
        <? if( $this->viewer()->getIdentity() ): ?>
          <div class='browsemembers_results_links'>
            <?=$this->userFriendship($user) ?>
          </div>
        <? endif; ?>

          <div class='browsemembers_results_info'>
            <?=$this->htmlLink($user->getHref(), $user->getTitle()) ?>
            <?=$user->status; ?>
            <? if( $user->status != "" ): ?>
              <div>
                <?=$this->timestamp($user->status_date) ?>
              </div>
            <? endif; ?>
          </div>
      </li>
    <? endforeach; ?>
  </ul>

<script type="text/javascript">
    function disableEnterKey(e)
    {
         var key;
         if(window.event)
              key = window.event.keyCode; //IE
         else
              key = e.which; //firefox

         return (key != 13);
    }

    var requestActive = false;
    $('form_lastrow').value = <?=$this->lastrow; ?>;
    var totalusers = <?=$this->totalUsers; ?>;
    var lastrow = <?=$this->lastrow; ?>;
    var userCount = <?=$this->userCount; ?>;
    var loadNextSearchMembers = function()
    {
      $('browsemembers_viewmore').innerHTML = "<div><img src='" + en4.core.staticBaseUrl + "application/modules/Core/externals/images/loading.gif' style='float:left;margin-right: 5px;'/><?=$this->translate('Loading...');?></div>";
      $('form_ajax').value = "true";
      if( requestActive ) return;

      (new Request.HTML({
        'format': 'html',
        'url' : '<?=$this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>',
        'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
        {
          requestActive = false;
          $('browsemembers_ul').innerHTML += responseHTML;
          responseJavaScript;
          if(userCount >= 10  && lastrow < totalusers){
            $('browsemembers_viewmore').innerHTML = '<a id="more_link" class="buttonlink icon_viewmore" href="javascript:loadNextSearchMembers();"><?=$this->translate('View More');?></a>';
          }
          else{
            $('browsemembers_viewmore').innerHTML = "";
          }
          Smoothbox.bind();
        }
      })).post($('myForm'));
    }

    var searchMembers = function()
    {
      $('browsemembers_results').innerHTML = "<div><img src='" + en4.core.staticBaseUrl + "application/modules/Core/externals/images/loading.gif' style='float:left;margin-right: 5px;'/><?=$this->translate('Loading...');?></div>";
      $('form_ajax').value = "";
      $('form_lastrow').value = '0';
      if( requestActive ) return;
      (new Request.HTML({
        'format': 'html',
        'url' : '<?=$this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>',
        'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
        {
          requestActive = false;
          $('browsemembers_results').innerHTML = responseHTML;
          responseJavaScript;
          Smoothbox.bind();
        }
      })).post($('myForm'));
    }
  </script>

  <? if( $this->lastrow < $this->totalUsers ): ?>
  

  <div class='browsemembers_viewmore' id="browsemembers_viewmore">
    <a id="more_link" class="buttonlink icon_viewmore" href="javascript:loadNextSearchMembers();"><?=$this->translate('View More');?></a>
  </div>
<? endif; ?>