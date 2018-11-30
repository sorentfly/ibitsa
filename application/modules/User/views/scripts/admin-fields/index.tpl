<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php
  // Render the admin js
  echo $this->render('_jsAdmin.tpl')
?>

<h2>Profile Questions</h2>
<p>
  Your members will be asked to provide some information about themselves when joining the community or editing their profile. Create some profile questions that allow them to describe themselves in a way that relates to the theme of your community. To reorder the profile questions, click on their names and drag them up or down. If you want to show different sets of questions to different types of members, you can create multiple "profile types". This is useful, for example, if you want your community to have "fans" and "musicians", each with a different set of profile questions.
</p>
<?php
$settings = Engine_Api::_()->getApi('settings', 'core');
if( $settings->getSetting('user.support.links', 0) == 1 ) {
	echo 'More info: <a href="http://support.socialengine.com/questions/156/Admin-Panel-Settings-Profile-Questions" target="_blank">See KB article</a>';	
} 
?>	
<br />
<br />	

<div class="admin_fields_type">
  <h3>Editing Profile Type:</h3>
  <?php echo $this->formSelect('profileType', $this->topLevelOption->option_id, array(), $this->topLevelOptions) ?>
</div>

<br />

<div class="admin_fields_options">
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addquestion">Add Question</a>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addheading">Add Heading</a>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_renametype">Rename Profile Type</a>
  <?php if( count($this->topLevelOptions) > 1 ): ?>
    <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_deletetype">Delete Profile Type</a>
  <?php endif; ?>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addtype">Create New Profile Type</a>
  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_saveorder" style="display:none;">Save Order</a>
</div>

<br />


<ul class="admin_fields">
  <?php foreach( $this->secondLevelMaps as $map ): ?>
    <?php echo $this->adminFieldMeta($map) ?>
  <?php endforeach; ?>
</ul>

<br />
<br />


