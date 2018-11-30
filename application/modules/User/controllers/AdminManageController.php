<?

class User_AdminManageController extends Core_Controller_Action_Admin
{
    public $db;
    public $tb_prefix;
    
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->db = Engine_Db_Table::getDefaultAdapter();
        $this->tb_prefix = Engine_Db_Table::getTablePrefix();
        $this->base_href = rtrim((constant('_ENGINE_SSL') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/');
        $this->translate = Zend_Registry::get('Zend_Translate');
        
        parent::__construct($request, $response, $invokeArgs);
    }
    
    public function indexAction()
    {
        $this->view->formFilter = $formFilter = new User_Form_Admin_Manage_Filter();
        $page = $this->_getParam('page', 1);

        $table = Engine_Api::_()->getDbtable('users', 'user');
        $select = $table->select();

        // Process form
        $values = array();
        if($formFilter->isValid($this->_getAllParams()))
        {
            $values = $formFilter->getValues();
        }

        foreach($values as $key => $value)
        {
            if(null === $value)
            {
                unset($values[$key]);
            }
        }

        $values = array_merge(array(
            'order' => 'user_id',
            'order_direction' => 'DESC',
                ), $values);

        $this->view->assign($values);

        // Set up select info
        $select->order((!empty($values['order']) ? $values['order'] : 'user_id' ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

        if(!empty($values['displayname']))
        {
            $select->where('displayname LIKE ?', '%' . $values['displayname'] . '%');
        }
        if(!empty($values['username']))
        {
            $select->where('username LIKE ?', '%' . $values['username'] . '%');
        }
        if(!empty($values['email']))
        {
            $select->where('email LIKE ?', '%' . $values['email'] . '%');
        }
        if(!empty($values['level_id']))
        {
            $select->where('level_id = ?', $values['level_id']);
        }
        if(isset($values['enabled']) && $values['enabled'] != -1)
        {
            $select->where('enabled = ?', $values['enabled']);
        }
        if(!empty($values['user_id']))
        {
            $select->where('user_id = ?', (int) $values['user_id']);
        }

        // Filter out junk
        $valuesCopy = array_filter($values);
        // Reset enabled bit
        if(isset($values['enabled']) && $values['enabled'] == 0)
        {
            $valuesCopy['enabled'] = 0;
        }

        // Make paginator
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        $this->view->paginator = $paginator->setCurrentPageNumber($page);
        $this->view->formValues = $valuesCopy;

        $this->view->superAdminCount = count(Engine_Api::_()->user()->getSuperAdmins());
        $this->view->hideEmails = _ENGINE_ADMIN_NEUTER;
        //$this->view->formDelete = new User_Form_Admin_Manage_Delete();

        $this->view->openUser = (bool) ( $this->_getParam('open') && $paginator->getTotalItemCount() == 1 );
    }

    public function multiModifyAction()
    {
        if($this->getRequest()->isPost())
        {
            $values = $this->getRequest()->getPost();
            foreach($values as $key => $value)
            {
                if($key == 'modify_' . $value)
                {
                    $user = Engine_Api::_()->getItem('user', (int) $value);
                    if($values['submit_button'] === 'delete')
                    {
                        try
                        {
                            $user->delete();

                            $this->db->delete($this->tb_prefix . 'users_social', 'user_id = ' . $value); /* Удаляем привязки к соцсетям */
                            $this->db->delete($this->tb_prefix . 'cadastre', 'user_id = ' . $value); /* Удаляем запись в кадастре */
                            $this->db->delete($this->tb_prefix . 'olympic_user_has_task', 'iduser = ' . $value); /* Удаляем решённые задачи */

                            $this->db->delete($this->tb_prefix . 'user_referrals', 'idmaster = ' . $value); /* Записи о пригласителях  */
                            $this->db->delete($this->tb_prefix . 'user_referrals', 'idslave = ' . $value); /* Записи о приглашённых */

                            $this->db->delete($this->tb_prefix . 'user_online', 'user_id = ' . $value); /* Записи об онлайн юзерах */
                            $this->db->delete($this->tb_prefix . 'user_logins', 'user_id = ' . $value); /* Записи об авторизациях юзеров */

                            $this->db->commit();
                        } 
                        catch (Exception $e)
                        {
                            $this->db->rollBack();
                            throw $e;
                        }
                    } 
                    else if($values['submit_button'] == 'approve')
                    {
                        $old_status = $user->enabled;
                        $user->enabled = 1;
                        $user->approved = 1;
                        $user->save();

                        // Send a notification that the account was not approved previously
                        if($old_status == 0)
                        {
                            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'user_account_approved', array(
                                'host' => $_SERVER['HTTP_HOST'],
                                'email' => $user->email,
                                'date' => time(),
                                'recipient_title' => $user->getTitle(),
                                'recipient_link' => $user->getHref(),
                                'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
                                'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
                            ));
                        }
                    }
                }
            }
        }

        return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    public function editAction()
    {
        $id = $this->_getParam('id', null);
        $user = Engine_Api::_()->getItem('user', $id);
        $userLevel = Engine_Api::_()->getItem('authorization_level', $user->level_id);
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerLevel = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
        $superAdminLevels = Engine_Api::_()->getItemTable('authorization_level')->fetchAll(array(
            'flag = ?' => 'superadmin',
        ));

        if(!$user || !$userLevel || !$viewer || !$viewerLevel)
        {
            return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
        }

        $this->view->user = $user;
        $this->view->form = $form = new User_Form_Admin_Manage_Edit(array(
            'userIdentity' => $id,
        ));

        // Do not allow editing level if the last superadmin
        if($userLevel->flag == 'superadmin' && count(Engine_Api::_()->user()->getSuperAdmins()) == 1)
        {
            $form->removeElement('level_id');
        }

        // Do not allow admins to change to super admin
        if($viewerLevel->flag != 'superadmin' && $form->getElement('level_id'))
        {
            if($userLevel->flag == 'superadmin')
            {
                $form->removeElement('level_id');
            } else
            {
                foreach($superAdminLevels as $superAdminLevel)
                {
                    unset($form->getElement('level_id')->options[$superAdminLevel->level_id]);
                }
            }
        }

        // Get values
        $values = $user->toArray();
        unset($values['password']);
        if(_ENGINE_ADMIN_NEUTER)
        {
            unset($values['email']);
        }

        // Populate form
        $form->populate($values);

        // Check method/valid
        if(!$this->getRequest()->isPost())
        {
            return;
        }
        if(!$form->isValid($this->getRequest()->getPost()))
        {
            return;
        }

        $values = $form->getValues();

        // Check password validity
        if(empty($values['password']) && empty($values['password_conf']))
        {
            unset($values['password']);
            unset($values['password_conf']);
        } else if($values['password'] != $values['password_conf'])
        {
            return $form->getElement('password')->addError('Passwords do not match.');
        } else
        {
            unset($values['password_conf']);
        }

        // Process
        $oldValues = $user->toArray();

        // Check for null usernames
        if(empty($values['username']) )
        {
            // If value is "NULL", then set to zend Null
            $values['username'] = new Zend_Db_Expr("NULL");
        }
        $user->setFromArray($values);
        $user->save();
        // Send a notification that the account has been approved
        if(!$oldValues['enabled'] && $values['enabled'])
        {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'user_account_approved', array(
                'host' => $_SERVER['HTTP_HOST'],
                'email' => $user->email,
                'date' => time(),
                'recipient_title' => $user->getTitle(),
                'recipient_link' => $user->getHref(),
                'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
                'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
            // Send hook to add activity
            Engine_Hooks_Dispatcher::getInstance()
                    ->callEvent('onUserEnable', $user);
        } else if($oldValues['enabled'] && !$values['enabled'])
        {
            // @todo ?
        }


        // Forward
        return $this->forward('success', 'utility', 'core', array(
                    'smoothboxClose' => true,
                    'parentRefresh' => true,
                    'format' => 'smoothbox',
                    'messages' => array('Your changes have been saved.')
        ));
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id', null);
        $this->view->user = $user = Engine_Api::_()->getItem('user', $id);
        $this->view->form = $form = new User_Form_Admin_Manage_Delete();
        // deleting user
        if($this->getRequest()->isPost())
        {
            try
            {
                $user->deleted = 1;
                $user->save();
            } 
            catch (Exception $e)
            {
                throw $e;
            }

            return $this->forward('success', 'utility', 'core', array(
                        'smoothboxClose' => true,
                        'parentRefresh' => true,
                        'format' => 'smoothbox',
                        'messages' => array('This member has been successfully deleted.')
            ));
        }
    }

    public function loginAction()
    {
        if (Engine_Api::_()->user()->getViewer()->level_id != 1){
            return $this->forward('requireauth', 'error', 'core');
        }
        $this->_helper->contextSwitch->setAutoJsonSerialization(true);
        $callContext = $this->_helper->contextSwitch->getCurrentContext();
        $id = $this->_getParam('id');
        if(!$id)
        {
            if(null === $callContext)
            {
                return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null));
            }
            else
            {
                $this->view->status = false;
                $this->view->error = true;
                return;
            }
        }
        $user = Engine_Api::_()->getItem('user', $id);

        // @todo change this to look up actual superadmin level

                
        // Login
        Zend_Auth::getInstance()->getStorage()->write($user->getIdentity());

        // Redirect
        if ($callContext == 'smoothbox'){
            return $this->forward('success', 'utility', 'core', array(
                'parentRefresh'=> 3000,
                'messages' => ['Вы авторизировались под <B>'. $user->getTitle(). '</B>, обновление страницы...']
            ));
        }else if(null === $callContext)
        {
            return $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } 
        else
        {
            $this->view->status = true;
            return;
        }
    }

    public function statsAction()
    {
        $id = $this->_getParam('id', null);
        $this->view->user = $user = Engine_Api::_()->getItem('user', $id);

        $fieldsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($user);

        if(!empty($fieldsByAlias['profile_type']))
        {
            $optionId = $fieldsByAlias['profile_type']->getValue($user);
            if($optionId)
            {
                $optionObj = Engine_Api::_()->fields()
                        ->getFieldsOptions($user)
                        ->getRowMatching('option_id', $optionId->value);
                if($optionObj)
                {
                    $this->view->memberType = $optionObj->label;
                }
            }
        }

        // Friend count
        $this->view->friendCount = $user->membership()->getMemberCount($user);
    }

}
