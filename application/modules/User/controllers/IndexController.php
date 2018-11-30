<?php
class User_IndexController extends Core_Controller_Action_Standard
{
  public function apicadastreAction()
  {
  
	$email = $this->_getParam('e', null);
	$password = $this->_getParam('p', null);
	
	$user_table = Engine_Api::_()->getDbtable('users', 'user');
    $user_select = $user_table->select()
      ->where('email = ?', $email);
    $user = $user_table->fetchRow($user_select);
	
	if( empty($user) ) {
		echo json_encode(array("ERROR" => "You aren't login!"));
		exit;
	}
	
    $authResult = Engine_Api::_()->user()->authenticate($email, $password);
    $authCode = $authResult->getCode();
    Engine_Api::_()->user()->setViewer();

    if( $authCode != Zend_Auth_Result::SUCCESS ) {
		echo json_encode(array("ERROR" => "You aren't login!"));
		exit;
	}
	
	$viewer = Engine_Api::_()->user()->getViewer();
	
	$query = $this->_getParam('q', null);
	$user = explode(" ",$query);
	
	$user[3] = str_replace("-0","-",$user[3]);
	
	if(trim($query) == '' || trim($email) == '' || trim($password) == '') {
		echo json_encode(array("ERROR" => "Empty parameter!"));
		exit;
	}
   
	$TablePrefix = Engine_Db_Table::getTablePrefix();
	$t1 = $TablePrefix.'event_events';
	$t2 = $TablePrefix.'event_membership';
	$t3 = $TablePrefix.'volimpic_results';
	$t4 = $TablePrefix.'olimpic_diplom';
	$t5 = $TablePrefix.'cadastre';
	$t6 = $TablePrefix.'cadastre_settings';
	$t7 = $TablePrefix.'eventgroups';
	$t8 = $TablePrefix.'user_fields_values';
	$t9 = $TablePrefix.'group_membership';

	$db = Engine_Db_Table::getDefaultAdapter();
	
	$group = $db->select()
		->from($t9)
		->where("resource_id = ?", 12) // Группа доступа к API Кадастра
		->where("user_id = ?", $viewer->user_id)
		->query()->fetchAll();
	if(!is_array($group[0])) {
		echo json_encode(array("ERROR" => "You haven't access!"));
		exit;
	}
   
	$user_meta = $db->query("SELECT t5.`item_id` FROM `".$t8."` AS t1
LEFT JOIN `".$t8."` AS t2 ON t1.`item_id` = t2.`item_id`
LEFT JOIN `".$t8."` AS t3 ON t2.`item_id` = t3.`item_id`
LEFT JOIN `".$t8."` AS t4 ON t3.`item_id` = t4.`item_id`
LEFT JOIN `".$t8."` AS t5 ON t4.`item_id` = t5.`item_id`
WHERE t1.`field_id`=3 AND t1.`value`='".$user[1]."'
  AND t2.`field_id`=4 AND t2.`value`='".$user[0]."'
  AND t3.`field_id`=14 AND t3.`value`='".$user[2]."'
  AND t4.`field_id`=18 AND t4.`value`='".$user[3]."'
LIMIT 1;")->fetchAll();

	$user_id = $user_meta[0]['item_id'];
	
	if($user_id=="") {
		echo json_encode(array("ERROR" => "User isn't found!"));
		exit;
	}
	
//----------------------------------------------------------

	$query = 'SELECT t1.`event_id`, t1.`title` AS title, t1.`description`, t1.`cadastre`, t1.`cadastre1`, t1.`cadastre2`, t1.`cadastre3`, t1.`cadastre4`, t1.`endtime`, t1.`category_id`, t3.`diplom` AS d1, t4.`diplom` AS d2, t7.`group_id` 
		FROM `'.$t1.'` AS t1 
		LEFT JOIN `'.$t2.'` AS t2 
		ON t1.`event_id` = t2.`resource_id`
		LEFT JOIN `'.$t3.'` AS t3 
		ON t1.`event_id` = t3.`resource_id` AND t3.`user_id` = '.$user_id.'
		LEFT JOIN `'.$t4.'` AS t4 
		ON t1.`event_id` = t4.`resource_id` AND t4.`user_id` = '.$user_id.'
		LEFT JOIN `'.$t7.'` AS t7
		ON t7.`event_id` = t1.`event_id`
		WHERE ( t1.`cadastre` = 1 OR t7.`group_id` IS NOT NULL ) AND t2.`user_id` = '.$user_id.' AND t1.`endtime` < \''.date("Y-m-d H:i:s").'\'
		GROUP BY t1.`event_id`
		ORDER BY t1.`endtime` DESC;';

	$ol_arr = $db->query($query)->fetchAll();

	// Events
	
	foreach($ol_arr as $key => $arr) {
		$ol_id[$arr['event_id']] = $arr;
	}

	// Groups
	
	foreach($ol_arr as $key => $arr) {
		if($arr['cadastre']!='1' && $arr['group_id']!='') {
			$ol_gr[$arr['group_id']] = $arr;
		}
	}

	$query = 'SELECT t1.`event_id`, t1.`title` AS title, t1.`description`, t1.`cadastre`, t1.`cadastre1`, t1.`cadastre2`, t1.`cadastre3`, t1.`cadastre4`, t1.`endtime`, t1.`category_id` 
		FROM `'.$t1.'` AS t1 
		WHERE t1.`category_id` = 38 AND t1.`cadastre` = 1 AND t1.`endtime` < \''.date("Y-m-d H:i:s").'\' AND t1.`event_id` IN(';

	foreach($ol_gr as $key => $arr) {
		$query .= $key.',';
	}
	
	$query .= '0);';
	$gr_arr = $db->query($query)->fetchAll();

	foreach($gr_arr as $key => $arr) {
		if(!is_array($ol_id[$arr['event_id']])) {
			$ol_arr[] = $arr;
		}
	}

	//
	
	foreach($ol_arr as $key => $arr) {
		if(is_array($ol_gr[$arr['event_id']])) {
			if($ol_arr[$key]['d1'] == '' && $ol_arr[$key]['d2']=='' && ($ol_gr[$arr['event_id']]['d1']!='' || $ol_gr[$arr['event_id']]['d2']!='')) {
				$ol_arr[$key]['d1'] = $ol_gr[$arr['event_id']]['d1'];
				$ol_arr[$key]['d2'] = $ol_gr[$arr['event_id']]['d2'];
			}
		}
	}
	
	foreach($ol_arr as $key => $arr) {
		if($arr['cadastre']!='1' && $arr['group_id']!='') {
			unset($ol_arr[$key]);
		}
	}

    $result = 0;
	foreach($ol_arr as $key => $arr) {
		if($arr['d1']!='') {
			$ol_arr[$key]['ball'] = $arr['cadastre'.$arr['d1']];
			$result += $arr['cadastre'.$arr['d1']];
		} elseif($arr['d2']!='') {
			$ol_arr[$key]['ball'] = $arr['cadastre'.$arr['d2']];
			$result += $arr['cadastre'.$arr['d2']];
		} else {
			$ol_arr[$key]['ball'] = $arr['cadastre4'];
			$result += $arr['cadastre4'];
		}
	}

	$cad_arr = $db->query('SELECT * FROM `'.$t5.'` WHERE `user_id`='.$user_id.';')->fetchAll();
	if( count($cad_arr)==0 OR $cad_arr[0]['result']!=$result ) {
		$query = 'INSERT INTO `'.$t5.'` VALUES('.$user_id.','.$result.') ON DUPLICATE KEY UPDATE result = '.$result.';';
		$db->query($query)->execute();
	}
	
	$cad_set = $db->query('SELECT * FROM `'.$t6.'` ORDER BY `rate` DESC;')->fetchAll();
	
	for($i=0;$i<count($cad_set);$i++) {
		if($result>=$cad_set[$i]['rate']) {
			$medal_i = $cad_set[$i]['name'];
			break;
		}
	}
	
//----------------------------------------------------------

    $jarr = array();
	$jarr['ID'] = $user_id;
	$jarr['Surname'] = $user[0];
	$jarr['Name'] = $user[1];
	$jarr['Patronymic'] = $user[2];
	$jarr['Birthday'] = $user[3];
	$jarr['Rate'] = $result;
	$jarr['Medal'] = $medal_i;

//	$str = '';
//	$str .= '{"ID":"'.$user_id.'","Surname":"'.$user[0].'","Name":"'.$user[1].'","Patronymic":"'.$user[2].'","Birthday":"'.$user[3].'","Rate":"'.$result.'","Medal":"'.$medal_i.'","Events":[';
	
	$k = 0;
	foreach($ol_arr as $key => $arr) {

		//$str .= '["Name":"'.$arr['title'].'",';
		//$str .= '"URL":"/event/'.$arr['event_id'].'",';
		
		$d = 0;
		if($arr['d1']!='') { $d = $arr['d1']; }
		elseif($arr['d2']!='') { $d = $arr['d2']; }

		//$str .= '"Place":"'.$d.'"],';
		
		$jarr['Events'][$k]['Name'] = $arr['title'];
		$jarr['Events'][$k]['URL'] = '/event/'.$arr['event_id'];
                $place_title=array(
                    '0'=>'Participant',
                    '1'=>'First Place',
                    '2'=>'Second Place',
                    '3'=>'Third Place',
                    '4'=>'Encouragement'
                );
		$jarr['Events'][$k]['Place'] = Zend_Registry::get('Zend_Translate')->_($place_title[$d]);
		
		$k += 1;
	}
	
//	$str .= ']}';
//	echo str_replace('],]',']]',$str);
	
	echo json_encode($jarr);
	exit;
  }
  
//==========================================================
  
  public function homeAction()
  {
    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.portal', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }else{
      return $this->_helper->redirector->gotoUrl($viewer->getHref(), array('prependBase' => false));
    }
  }

  public function browseAction()
  {
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.browse', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }
    if( !$this->_executeSearch() ) {
      // throw new Exception('error');
    }

    if( $this->_getParam('ajax') ) {
      $this->renderScript('_browseUsers.tpl');
    }
  }
  
  protected function _executeSearch()
  {
    // Check form
    $form = new User_Form_Search(array(
      'type' => 'user'
    ));

    if( !$form->isValid($this->_getAllParams()) ) {
      $this->view->error = true;
      $this->view->totalUsers = 0; 
      $this->view->userCount = 0; 
      $this->view->page = 1;
      return false;
    }

    $this->view->form = $form;

    // Get search params
    $page = (int)  $this->_getParam('page', 1);
    $ajax = (bool) $this->_getParam('ajax', false);
    $options = $form->getValues();
    
    // Process options
    $tmp = array();
    $originalOptions = $options;
    foreach( $options as $k => $v ) {
      if( null == $v || '' == $v || (is_array($v) && count(array_filter($v)) == 0) ) {
        continue;
      } else if( false !== strpos($k, '_field_') ) {
        list($null, $field) = explode('_field_', $k);
        $tmp['field_' . $field] = $v;
      } else if( false !== strpos($k, '_alias_') ) {
        list($null, $alias) = explode('_alias_', $k);
        $tmp[$alias] = $v;
      } else {
        $tmp[$k] = $v;
      }
    }
    $options = $tmp;

    // Get table info
    $table = Engine_Api::_()->getItemTable('user');
    $userTableName = $table->info('name');

    $searchTable = Engine_Api::_()->fields()->getTable('user', 'search');
    $searchTableName = $searchTable->info('name');

    //extract($options); // displayname
    $profile_type = @$options['profile_type'];
    $displayname = @$options['displayname'];
    if (!empty($options['extra'])) {
      extract($options['extra']); // is_online, has_photo, submit
    }

    // Contruct query
    $select = $table->select()
      //->setIntegrityCheck(false)
      ->from($userTableName)
      ->joinLeft($searchTableName, "`{$searchTableName}`.`item_id` = `{$userTableName}`.`user_id`", null)
      //->group("{$userTableName}.user_id")
      ->where("{$userTableName}.search = ?", 1)
      ->where("{$userTableName}.enabled = ?", 1)
      ->order("{$userTableName}.displayname ASC");

    // Build the photo and is online part of query
    if( isset($has_photo) && !empty($has_photo) ) {
      $select->where($userTableName.'.photo_id != ?', "0");
    }

    if( isset($is_online) && !empty($is_online) ) {
      $select
        ->joinRight("engine4_user_online", "engine4_user_online.user_id = `{$userTableName}`.user_id", null)
        ->group("engine4_user_online.user_id")
        ->where($userTableName.'.user_id != ?', "0");
    }

    // Add displayname
    if( !empty($displayname) ) {
      $select->where("(`{$userTableName}`.`username` LIKE ? || `{$userTableName}`.`displayname` LIKE ?)", "%{$displayname}%");
    }

    // Build search part of query
    $searchParts = Engine_Api::_()->fields()->getSearchQuery('user', $options);
    foreach( $searchParts as $k => $v ) {
      $select->where("`{$searchTableName}`.{$k}", $v);
    }

    // Build paginator
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($page);
    
    $this->view->page = $page;
    $this->view->ajax = $ajax;
    $this->view->users = $paginator;
    $this->view->totalUsers = $paginator->getTotalItemCount();
    $this->view->userCount = $paginator->getCurrentItemCount();
    $this->view->topLevelId = $form->getTopLevelId();
    $this->view->topLevelValue = $form->getTopLevelValue();
    $this->view->formValues = array_filter($originalOptions);

    return true;
  }
}