<?

class User_Plugin_Menus
{

    public function canDelete()
    {
        // Check subject
        if(!Engine_Api::_()->core()->hasSubject('user'))
        {
            return false;
        }
        /* @var User_Model_User $subject */
        $subject = Engine_Api::_()->core()->getSubject('user');

        // Check viewer
        $viewer = Engine_Api::_()->user()->getViewer();
        if(!$viewer || !$viewer->getIdentity())
        {
            return false;
        }

        // Check auth
        return (bool) $subject->authorization()->isAllowed($viewer, User_Model_User::PERMISSION_DELETE);
        
    }

    // core_main

    public function onMenuInitialize_CoreMainHome($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $route = array(
            'route' => 'default',
        );

        if($viewer->getIdentity())
        {
            $route['route'] = 'user_general';
            $route['params'] = array(
                'action' => 'home',
            );
            if('user' == $request->getModuleName() &&
                    'index' == $request->getControllerName() &&
                    'home' == $request->getActionName())
            {
                $route['active'] = true;
            }
        }
        return $route;
    }

    // core_mini

    public function onMenuInitialize_CoreMiniAdmin($row)
    {
        // @todo check perms
        if(Engine_Api::_()->getApi('core', 'authorization')->isAllowed('admin', null, 'view'))
        {
            return array(
                'label' => $row->label,
                'route' => 'admin_default',
                'class' => 'no-dloader',
            );
        }

        return false;
    }

    public function onMenuInitialize_CoreMiniProfile($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if($viewer->getIdentity())
        {
            return array(
                'label' => $row->label,
                'uri' => $viewer->getHref(),
            );
        }

        return false;
    }

    public function onMenuInitialize_CoreMiniSettings($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if($viewer->getIdentity())
        {
            return array(
                'label' => $row->label,
                'route' => 'user_extended',
                'params' => array(
                    'controller' => 'settings',
                    'action' => 'general',
                )
            );
        }

        return false;
    }

    public function onMenuInitialize_CoreMiniAuth($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if($viewer->getIdentity())
        {
            return array(
                'label' => 'Sign Out',
                'route' => 'user_logout',
                'class' => 'no-dloader',
            );
        }
        else
        {
            return array(
                'label' => 'Sign In',
                'route' => 'user_login',
                'params' => array(
                    // Nasty hack
                    'return_url' => '64-' . base64_encode($_SERVER['REQUEST_URI']),
                ),
            );
        }
    }

    public function onMenuInitialize_CoreMiniSignup($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if(!$viewer->getIdentity())
        {
            return array(
                'label' => 'Sign Up',
                'route' => 'user_signup'
            );
        }

        return false;
    }

    // user_edit

    public function onMenuInitialize_UserEditStyle($row)
    {
        if(Engine_Api::_()->core()->hasSubject('user'))
        {
            $user = Engine_Api::_()->core()->getSubject('user');
        }
        else
        {
            $user = Engine_Api::_()->user()->getViewer();
        }
        if(!$user->getIdentity())
        {
            return false;
        }
        return (bool) Engine_Api::_()->getDbtable('permissions', 'authorization')
                        ->getAllowed('user', $user->level_id, 'style');
    }

    // user_home

    public function onMenuInitialize_UserHomeView($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if($viewer->getIdentity())
        {
            return array(
                'label' => $row->label,
                'icon' => $row->params['icon'],
                'route' => 'user_profile',
                'params' => array(
                    'id' => $viewer->getIdentity()
                )
            );
        }
        return false;
    }

    public function onMenuInitialize_UserHomeEdit($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();

        // @todo move to authorization
        return array(
            'label' => 'Edit My Profile',
            'iconfont' => 'fa fa-pencil',
            //'icon' => 'application/modules/User/externals/images/edit.png',
            'route' => 'user_extended',
            'params' => array(
                'controller' => 'edit',
                'action' => 'profile'
            )
        );
    }

    // user_profile

    public function onMenuInitialize_UserProfileEdit($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        $label = "Edit My Profile";
        if(!$viewer->isSelf($subject))
        {
            $label = "Edit Member Profile";
        }

        if($subject->authorization()->isAllowed($viewer, 'edit'))
        {
            return array(
                'label' => $label,
                'iconfont' => 'fa fa-pencil',
                'route' => 'user_extended',
                'params' => array(
                    'controller' => 'edit',
                    'action' => 'profile',
                    'id' => ( $viewer->getGuid(false) == $subject->getGuid(false) ? null : $subject->getIdentity() ),
                )
            );
        }

        return false;
    }
    
     public function onMenuInitialize_UserProfileSettings($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        $label = "Настройки";
        if(!$viewer->isSelf($subject))
        {
            return false;
        }

        return array(
            'label' => $label,
            'iconfont' => 'fa fa-gear',
            'route' => 'user_extended',
            'params' => array(
                'controller' => 'settings',
                'action' => 'general'
            )
        );

        return false;
    }

    public function onMenuInitialize_UserProfileFriend($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        // Not logged in
        if(!$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false))
        {
            return false;
        }
        
        //по запросу - пункт с друзьями убирается для методистов
        $domainSettings = Engine_Api::_()->core()->getNowDomainSettings();
        if (!empty($domainSettings['academyEnabled']) && !empty($domainSettings['zftshDefaults']) && $viewer->academyStatus() == 'methodist'){
            return false;
        }

        // No blocked
        if($viewer->isBlockedBy($subject))
        {
            return false;
        }

        // Check if friendship is allowed in the network
        $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
        if(!$eligible)
        {
            return '';
        }

        // check admin level setting if you can befriend people in your network
        else if($eligible == 1)
        {

            $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
            $networkMembershipName = $networkMembershipTable->info('name');

            $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
            $select
                    ->from($networkMembershipName, 'user_id')
                    ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
                    ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
                    ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
            ;

            $data = $select->query()->fetch();

            if(empty($data))
            {
                return '';
            }
        }

        // One-way mode
        $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
        if(!$direction)
        {
            $viewerRow = $viewer->membership()->getRow($subject);
            $subjectRow = $subject->membership()->getRow($viewer);
            $params = array();

            // Viewer?
            if(null === $subjectRow)
            {
                // Follow
                $params[] = array(
                    'label' => 'Follow',
                    'iconfont' => 'fa fa-user-plus',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'add',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            else if($subjectRow->resource_approved == 0)
            {
                // Cancel follow request
                $params[] = array(
                    'label' => 'Cancel Follow Request',
                    'iconfont' => 'fa fa-remove',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'cancel',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            else
            {
                // Unfollow
                $params[] = array(
                    'label' => 'Unfollow',
                    'iconfont' => 'fa fa-remove',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'remove',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            // Subject?
            if(null === $viewerRow)
            {
                // Do nothing
            }
            else if($viewerRow->resource_approved == 0)
            {
                // Approve follow request
                $params[] = array(
                    'label' => 'Approve Follow Request',
                    'iconfont' => 'fa fa-user-plus',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'confirm',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            else
            {
                // Remove as follower?
                $params[] = array(
                    'label' => 'Remove as Follower',
                    'iconfont' => 'fa fa-remove',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'remove',
                        'user_id' => $subject->getIdentity(),
                        'rev' => true,
                    ),
                );
            }
            if(count($params) == 1)
            {
                return $params[0];
            }
            else if(count($params) == 0)
            {
                return false;
            }
            else
            {
                return $params;
            }
        }

        // Two-way mode
        else
        {
            $row = $viewer->membership()->getRow($subject);
            if(null === $row)
            {
                // Add
                return array(
                    'label' => 'Add to My Friends',
                    'iconfont' => 'fa fa-user-plus',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'add',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            else if($row->user_approved == 0)
            {
                // Cancel request
                return array(
                    'label' => 'Cancel Friend Request',
                    'iconfont' => 'fa fa-remove',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'cancel',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            else if($row->resource_approved == 0)
            {
                // Approve request
                return array(
                    'label' => 'Approve Friend Request',
                    'iconfont' => 'fa fa-user-plus',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'confirm',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
            else
            {
                // Remove friend
                return array(
                    'label' => 'Remove from Friends',
                    'iconfont' => 'fa fa-user-times',
                    'class' => 'smoothbox',
                    'route' => 'user_extended',
                    'params' => array(
                        'controller' => 'friends',
                        'action' => 'remove',
                        'user_id' => $subject->getIdentity()
                    ),
                );
            }
        }
    }

    public function onMenuInitialize_UserProfileBlock($row)
    {
        //“Блокировать” - убрать для админов всех уровней. Не используется
        return false;
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        // Can't block self or if not logged in
        if(!$viewer->getIdentity() || $viewer->getGuid() == $subject->getGuid())
        {
            return false;
        }

        if(!Engine_Api::_()->authorization()->isAllowed('user', $viewer, 'block'))
        {
            return false;
        }

        if(!$subject->isBlockedBy($viewer))
        {
            return array(
                'label' => 'Block Member',
                'iconfont' => 'fa fa-ban',
                'class' => 'smoothbox',
                'route' => 'user_extended',
                'params' => array(
                    'controller' => 'block',
                    'action' => 'add',
                    'user_id' => $subject->getIdentity()
                ),
            );
        }
        else
        {
            return array(
                'label' => 'Unblock Member',
                'iconfont' => 'fa fa-ban',
                'class' => 'smoothbox',
                'route' => 'user_extended',
                'params' => array(
                    'controller' => 'block',
                    'action' => 'remove',
                    'user_id' => $subject->getIdentity()
                ),
            );
        }
    }

    public function onMenuInitialize_UserProfileReport($row)
    {
        //“Пожаловаться” - убрать для всех
        return false;
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        if(!$viewer->getIdentity() ||
                !$subject->getIdentity() ||
                $viewer->isSelf($subject))
        {
            return false;
        }
        else
        {
            return array(
                'label' => 'Report',
                'iconfont' => 'fa fa-thumbs-down',
                'class' => 'smoothbox',
                'route' => 'default',
                'params' => array(
                    'module' => 'core',
                    'controller' => 'report',
                    'action' => 'create',
                    'subject' => $subject->getGuid(),
                    'format' => 'smoothbox',
                ),
            );
        }
    }

    public function onMenuInitialize_UserProfileSchools($row)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        if ($viewer->getIdentity() != $subject->getIdentity()){
            return false;
        }

        $data = $subject->getSchoolNotifyData();
        $count = 0;
        if (!empty($data['project_moderation_count'])){
            $count = $data['project_moderation_count'];
        }
        if (!$count && !$viewer->hasSchoolAdminRights('project')){
            return false;
        }

        return array(
            'label' => Zend_Registry::get('Zend_Translate')->translate('Manage Schools') . ( $count ? ' ('.$count.')' : ''),
            'iconfont' => 'fa fa-globe',
            'route' => 'school_extended',
            'class' => $count ? 'important' : '',
            'params' => [
                'module' => 'school',
                'controller' => 'index',
                'action' => 'manage'
            ],
        );
    }

    public function onMenuInitialize_UserProfileAdmin($row)
    {
        return false;
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        if($subject->authorization()->isAllowed($viewer, 'delete'))
        {
            if(!$viewer->isAdmin())
            {
                return false;
            }
            else
            {
                return array(
                    'label' => 'Admin Settings',
                    'icon' => 'application/modules/User/externals/images/edit.png',
                    'class' => 'smoothbox',
                    'route' => 'admin_default',
                    'params' => array(
                        'module' => 'user',
                        'controller' => 'manage',
                        'action' => 'edit',
                        'id' => $subject->getIdentity(),
                        'format' => 'smoothbox',
                    ),
                );
            }
        }
    }

}
