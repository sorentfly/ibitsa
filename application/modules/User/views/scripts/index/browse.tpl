<h2><?=$this->translate('Browse Members');?></h2>

<div class='layout_left'>
  <?=$this->form->render($this) ?>
</div>

<div class='layout_middle'>
  <div class='browsemembers_results' id='browsemembers_results'>
      <?=$this->render('_browseUsers.tpl') ?>
  </div>
</div>

<?
  /* Include the common user-end field switching javascript */
  echo $this->partial('_jsSwitch.tpl', 'fields', array(
    'topLevelId' => (int) @$this->topLevelId,
    'topLevelValue' => (int) @$this->topLevelValue
  ))
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    var url = '<?=$this->url() ?>';
    var requestActive = false;
    var browseContainer, formElement, page, totalUsers, userCount, currentSearchParams;

    formElement = $$('.field_search_criteria')[0];
    browseContainer = $('browsemembers_results');

    // On search
    formElement.addEvent('submit', function(event) {
      event.stop();
      searchMembers();
    });

    var searchMembers = window.searchMembers = function() {
      if( requestActive ) return;
      requestActive = true;

      currentSearchParams = formElement.toQueryString();

      var param = (currentSearchParams ? currentSearchParams + '&' : '') + 'ajax=1&format=html';

      var request = new Request.HTML({
        url: url,
        onComplete: function(requestTree, requestHTML) {
          requestTree = $$(requestTree);
          browseContainer.empty();
          requestTree.inject(browseContainer);
          requestActive = false;
          Smoothbox.bind();
        }
      });
      request.send(param);
    }

    var browseMembersViewMore = window.browseMembersViewMore = function() {
      if( requestActive ) return;
      $('browsemembers_loading').setStyle('display', '');
      $('browsemembers_viewmore').setStyle('display', 'none');

      var param = (currentSearchParams ? currentSearchParams + '&' : '') + 'ajax=1&format=html&page=' + (parseInt(page) + 1);

      var request = new Request.HTML({
        url: url,
        onComplete: function(requestTree, requestHTML) {
          requestTree = $$(requestTree);
          browseContainer.empty();
          requestTree.inject(browseContainer);
          requestActive = false;
          Smoothbox.bind();
        }
      });
      request.send(param);
    }

    window.addEvent('onChangeFields', function() {
      var firstSep = $$('li.browse-separator-wrapper')[0];
      var lastSep;
      var nextEl = firstSep;
      var allHidden = true;
      do {
        nextEl = nextEl.getNext();
        if( nextEl.get('class') == 'browse-separator-wrapper' ) {
          lastSep = nextEl;
          nextEl = false;
        } else {
          allHidden = allHidden && ( nextEl.getStyle('display') == 'none' );
        }
      } while( nextEl );
      if( lastSep ) {
        lastSep.setStyle('display', (allHidden ? 'none' : ''));
      }
    });
  });
</script>
