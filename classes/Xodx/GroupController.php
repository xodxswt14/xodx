<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class manages Groups. This includes so far:
 * - Creating a group
 * - Deleting a group
 * - Joining a group
 * - Leaving a group
 * - Changing group attributes
 * - Getting a group from its uri
 *
 * @author Jan Buchholz
 * @author Stephan Kemper
 * @author Lukas Werner
 * @author Gunnar Warnecke
 * @author Toni Pohl
 * @author Thomas Guett
 * @author Henrik Hillebrand
 */
class Xodx_GroupController extends Xodx_ResourceController
{
    /**
     * @var Xodx_Group A registry of already loaded XodX_Group objects
     */
    private $_groups = array();

    /**
     * A view action to show a group
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function showAction($template)
    {
        $bootstrap  = $this->_app->getBootstrap();
        $model      = $bootstrap->getResource('model');
        $request    = $bootstrap->getResource('request');
        $logger     = $bootstrap->getResource('logger');
        $groupUri  = urldecode($request->getValue('uri', 'get'));
        $id         = $request->getValue('id', 'get');
        $controller = $request->getValue('c', 'get');


        if ($id !== null) {
            $groupUri = $this->_app->getBaseUri() . '?c=' . $controller . '&id=' . $id;
        }

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';

        //GroupQuery fetching group information
        $groupQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupQuery.= 'SELECT ?name ?maker ?description ' .  PHP_EOL;
        $groupQuery.= 'WHERE { ' .  PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> a foaf:Group  . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:name ?name . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:primaryTopic ?description . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:maker ?maker .' . PHP_EOL;
        $groupQuery.= '}'; PHP_EOL;

        //MemberQuery fetching all members of group
        $memberQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $memberQuery.= 'SELECT ?member ' .  PHP_EOL;
        $memberQuery.= 'WHERE { ' .  PHP_EOL;
        $memberQuery.= '   <' . $groupUri . '> a foaf:Group  . ' . PHP_EOL;
        $memberQuery.= '   <' . $groupUri . '> foaf:member ?member .' . PHP_EOL;
        $memberQuery.= '}'; PHP_EOL;

        $group = $model->sparqlQuery($groupQuery);
        $members = $model->sparqlQuery($memberQuery);

        $userController = $this->_app->getController('Xodx_UserController');
        $user = $userController->getUser();

        $memberController = $this->_app->getController('Xodx_MemberController');
        $activities = $memberController->getActivityStream($groupUri);

        $nameHelper = new Xodx_NameHelper($this->_app);
        $makerName = $nameHelper->getName($group[0]['maker']);
        
        foreach ($activities as &$activity) {
            $activity['personUri'] = $this->getPersonByAuthorUri($activity['authorUri']);
            $activity['groupUri']  = $this->getGroupByAuthorUri($activity['authorUri']);
            $activity['personName'] = $nameHelper->getName($activity['personUri']);
        }

        if($user->getName() == 'guest') {
            $template->isGuest = true;
        } else {
            $template->isGuest = false;
        }

        //Checks if user is member of group
        $isMember = false;
        foreach($members as $member) {
            if($member['member'] === $user->getPerson()) {
                $isMember = true;
            }
        }

        //Checks if user is maker and marks him as member
        if($user->getPerson() == $group[0]['maker']) {
            $template->isMaker = true;
            $isMember = true;
        } else {
            $template->isMaker = false;
        }

        // Redirect from show to home if user is member
        if($isMember) { // Redirect user from home to show if he is not a member
            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'group');
            $location->setParameter('id', $id);
            $location->setParameter('uri', $groupUri);
            $location->setParameter('a', 'home');
            $template->redirect($location);
        }

        // Refine array of group members
        for($i = 0; $i < count($members); $i++) {
            $members[$i]['memberName'] = $nameHelper->getName($members[$i]['member']);
        }

        $template->groupshowName = $group[0]['name'];
        $template->groupDescription = $group[0]['description'];
        $template->groupUri = $groupUri;
        $template->groupMaker = $group[0]['maker'];
        $template->groupMakerName = $makerName;
        $template->groupMembers = $members;
        $template->groupshowActivities = $activities;

        return $template;
    }

    /**
     * If the author of an activity is made up of both, the person- and the groupUri, this returns the personUri
     * @param Uri $authorUri Uri that is to be manipulated
     * @return Uri the extracted personUri
     */
    public function getPersonByAuthorUri($authorUri)
    {
        $pos = strpos($authorUri, 'http', 5);
        if (!$pos) {
            return $authorUri;
        } else {
            return substr($authorUri, 0, $pos);
        }
    }

    /**
     * If the author of an activity is made up of both, the person- and the groupUri, this returns the groupUri, otherwise FALSE
     * @param Uri $authorUri Uri that is to be manipulated
     * @return Uri|Boolean The extracted groupUri or FALSE
     */
    public function getGroupByAuthorUri ($authorUri)
    {
        $pos = strpos($authorUri, 'http', 5);
        if (!$pos) {
            return FALSE;
        } else {
            return substr($authorUri, $pos);
        }
    }
    
    /**
     * A view action to show the home of a group
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function homeAction($template)
    {
        $bootstrap  = $this->_app->getBootstrap();
        $model      = $bootstrap->getResource('model');
        $request    = $bootstrap->getResource('request');
        $logger     = $bootstrap->getResource('logger');
        $groupUri  = urldecode($request->getValue('uri', 'get'));
        $id         = $request->getValue('id', 'get');
        $controller = $request->getValue('c', 'get');

        if ($id !== null) {
            $groupUri = $this->_app->getBaseUri() . '?c=' . $controller . '&id=' . $id;
        }

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';

        //GroupQuery fetching group information
        $groupQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupQuery.= 'SELECT ?name ?maker ?description ' .  PHP_EOL;
        $groupQuery.= 'WHERE { ' .  PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> a foaf:Group  . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:name ?name . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:primaryTopic ?description . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:maker ?maker .' . PHP_EOL;
        $groupQuery.= '}'; PHP_EOL;

        $group = $model->sparqlQuery($groupQuery);

        //MemberQuery fetching all members of group
        $memberQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $memberQuery.= 'SELECT ?member ' .  PHP_EOL;
        $memberQuery.= 'WHERE { ' .  PHP_EOL;
        $memberQuery.= '   <' . $groupUri . '> a foaf:Group  . ' . PHP_EOL;
        $memberQuery.= '   <' . $groupUri . '> foaf:member ?member .' . PHP_EOL;
        $memberQuery.= '}'; PHP_EOL;

        $members = $model->sparqlQuery($memberQuery);

        $userController = $this->_app->getController('Xodx_UserController');
        $user = $userController->getUser();

        // Get group activity stream
        $memberController = $this->_app->getController('Xodx_MemberController');
        $activities = $memberController->getActivityStream($groupUri);

        $nameHelper = new Xodx_NameHelper($this->_app);
        $makerName = $nameHelper->getName($group[0]['maker']);

        foreach ($activities as &$activity) {
            $activity['personUri'] = $this->getPersonByAuthorUri($activity['authorUri']);
            $activity['groupUri']  = $this->getGroupByAuthorUri($activity['authorUri']);
            $activity['personName'] = $nameHelper->getName($activity['personUri']);
        }

        //Checks if user is member of group
        $isMember = false;
        foreach($members as $member) {
            if($member['member'] === $user->getPerson()) {
                $isMember = true;
            }
        }

        //Checks if user is maker and marks him as member
        if($user->getPerson() == $group[0]['maker']) {
            $template->isMaker = true;
            $isMember = true;
        } else {
            $template->isMaker = false;
        }

        // Redirect from home to login if user is guest
        if($user->getName() == 'guest') {
            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'application');
            $location->setParameter('a', 'login');
            $template->redirect($location);
        } elseif(!$isMember) { // Redirect user from home to show if he is not a member
            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'group');
            $location->setParameter('id', $id);
            $location->setParameter('uri', urlencode($groupUri));
            $location->setParameter('a', 'show');
            $template->redirect($location);
        }

        // Refine array of group members
        for($i = 0; $i < count($members); $i++) {
            $members[$i]['memberName'] = $nameHelper->getName($members[$i]['member']);
        }

        $template->groupshowName = $group[0]['name'];
        $template->groupDescription = $group[0]['description'];
        $template->groupUri = $groupUri;
        $template->groupMaker = $group[0]['maker'];
        $template->groupMakerName = $makerName;
        $template->groupMembers = $members;
        $template->groupshowActivities = $activities;

        return $template;
    }

    /**
     * A view action for creating a new group.
     *
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function creategroupAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $groupname = $request->getValue('groupname', 'post');
        $description = $request->getValue('description', 'post');

        $userController = $this->_app->getController('Xodx_UserController');
        $user = $userController->getUser();

        if ($user->getName() == 'guest') {
            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'application');
            $location->setParameter('a', 'login');

            $template->redirect($location);
        } elseif ($groupname !== null || $description !== null) {
            $formError = array();

            if (empty($groupname)) {
                $formError['groupname'] = true;
            }

            if (empty($description)) {
                $description = "";
            }

            if (count($formError) <= 0) {
                $this->createGroup($groupname, $description);

                $location = new Saft_Url($this->_app->getBaseUri());
                $location->setParameter('c', 'groupprofile');
                $location->setParameter('a', 'list');

                $template->redirect($location);
            } else {
                $template->formError = $formError;
            }
        }

        return $template;
    }

    /**
     * A view action for deleting an existing group.
     *
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     * @todo should use semantic pingback instead of a curl request
     */
    public function deletegroupAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $groupUri = $request->getValue('groupUri', 'post');

        $formError = array();

        if (empty($groupUri) || !Erfurt_Uri::check($groupUri)) {
            $formError['groupUri'] = true;
        }

        if (count($formError) <= 0) {
            $this->deleteGroup($groupUri);

            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'groupprofile');
            $location->setParameter('a', 'list');

            $template->redirect($location);
        } else {
            $template->formError = $formError;
        }

        return $template;
    }

    /**
     * A new action for changing the group name or description.
     *
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function changegroupAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $request = $bootstrap->getResource('request');

        $groupName = $request->getValue('groupname', 'post');
        $description = $request->getValue('description', 'post');
        $groupUri = urldecode($request->getValue('groupUri','get'));

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';

        //GroupQuery fetching group maker
        $groupQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupQuery.= 'SELECT ?maker ' .  PHP_EOL;
        $groupQuery.= 'WHERE { ' .  PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> a foaf:Group  . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:maker ?maker . ' . PHP_EOL;
        $groupQuery.= '}'; PHP_EOL;

        $group = $model->sparqlQuery($groupQuery);

        $userController = $this->_app->getController('Xodx_UserController');
        $user = $userController->getUser();

        if ($group[0]['maker'] != $user->getPerson()) {
            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'groupprofile');
            $location->setParameter('a', 'list');

            $template->redirect($location);
        } elseif (empty($groupUri) || !Erfurt_Uri::check($groupUri)) {
            $location = new Saft_Url($this->_app->getBaseUri());
            $location->setParameter('c', 'groupprofile');
            $location->setParameter('a', 'list');

            $template->redirect($location);
        } elseif ($groupName === null && $description === null) {
            $template->groupUri = $groupUri;
            $template->groupName = $this->getGroup($groupUri)->getName();
            $template->description = $this->getGroup($groupUri)->getDescription();
        } else {
            $formError = array();

            if (empty($groupName)) {
                $formError['groupname'] = true;
            }

            if (empty($description)) {
                $description = "";
            }

            if (count($formError) <= 0) {
                $this->changeGroup($groupUri, $groupName, $description);

                $location = new Saft_Url($this->_app->getBaseUri());
                $location->setParameter('c', 'groupprofile');
                $location->setParameter('a', 'list');

                $template->redirect($location);
            } else {
                $template->groupUri = $groupUri;
                $template->groupName = $groupName;
                $template->description = $description;
                $template->formError = $formError;
            }
        }

        return $template;
    }

    /**
     * This creates a new group with the given name.
     * This function is usually called internally
     * @param Uri $groupUri Uri of the new group
     * @param String $name Name of the new group
     * @todo $groupUri might not be needed
     */
    public function createGroup ($name, $description = '')
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsDssn = 'http://purl.org/net/dssn/';

        $groupUri = $this->_app->getBaseUri() . '?c=group&id=' . urlencode($name);

        // verify that there is not already a group with that name
        $testQuery  = 'ASK {' . PHP_EOL;
        $testQuery .= '<' . $groupUri . '> ?p ?o' . PHP_EOL;
        $testQuery .= '}';
        if ($model->sparqlQuery($testQuery)) {
            $logger->error('GroupController/createGroup: Groupname already taken: ' . $name);
            throw new Exception('Groupname already taken.');
        } else {
            // feed for the new group
            $newGroupFeed = $this->_app->getBaseUri() . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);
            // Uri of the group's admin ( its foaf:maker)
            $userController = $this->_app->getController('Xodx_UserController');

            // Verify that a user of that instance (disparate 'guest') is logged in
            $adminName = $userController->getUser()->getName();
            if ($adminName == 'unkown' || $adminName == 'guest') {
                $logger->error('GroupController/createGroup: Unknown user tried to create group');
                throw new Exception('Please log in to create a group');
            } else {
                $adminUri = $userController->getUser()->getPerson();
            }

            $newGroup = array(
                $groupUri => array(
                    EF_RDF_TYPE => array(
                        array('type' => 'uri', 'value' => $nsFoaf . 'Group')
                    ),
                    $nsDssn . 'activityFeed' => array(
                        array('type' => 'uri', 'value' => $newGroupFeed)
                    ),
                    $nsFoaf . 'maker' => array(
                        array('type' => 'uri', 'value' => $adminUri)
                    ),
                    $nsFoaf . 'name' => array(
                        array('type' => 'literal', 'value' => $name)
                    ),
                    $nsFoaf . 'primaryTopic' => array(
                        array('type' => 'literal', 'value' => $description)
                    )
                )
            );
            $model->addMultipleStatements($newGroup);

            $memberController = $this->_app->getController('Xodx_MemberController');
            $memberController->addMember($adminUri, $groupUri);

            $this->joinGroup($adminUri, $groupUri);
        }
    }

     /**
     * This deletes a group with the given Uri.
     * This function is usually called internally
     * @param Uri $groupUri Uri of the group to be deleted
     */
    public function deleteGroup ($groupUri)
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsDssn = 'http://purl.org/net/dssn/';

        // verify that there is a group with that uri
        $testQuery  = 'ASK {' . PHP_EOL;
        $testQuery .= '<' . $groupUri . '> ?p ?o' . PHP_EOL;
        $testQuery .= '}';
        $result = $model->sparqlQuery($testQuery);
        if (!$result) {
            $logger->error('GroupController/deleteGroup: Group does not exist: ' . $groupUri);
            throw new Exception('Group does not exist.');
        } else {
            $name = $this->getGroup($groupUri)->getName();
            // feed of the group
            $groupFeed = $this->_app->getBaseUri() . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);
            // Uri of the group's admin ( its foaf:maker)
            $userController = $this->_app->getController('Xodx_UserController');                
            $personUri = $userController->getUser()->getPerson();

            //verify that User is admin of the group
            $makerQuery  = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
            $makerQuery .= 'ASK {' .PHP_EOL;
            $makerQuery .= '   <' . $groupUri . '>' . ' foaf:maker <' . $personUri . '>.' .PHP_EOL;
            $makerQuery .= '}';

            if ($model->sparqlQuery($makerQuery)) {
                $this->leaveGroup($personUri, $groupUri);
                $deleteQuery  = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
                $deleteQuery .= 'SELECT ?topic ' . PHP_EOL;
                $deleteQuery .= 'WHERE {' . PHP_EOL;
                $deleteQuery .= '<' . $groupUri . '> foaf:primaryTopic ?topic' . PHP_EOL;
                $deleteQuery .= '}';
                $deleteResult = $model->sparqlQuery($deleteQuery);

                $deleteSubscribeQuery  = 'PREFIX dssn: <' . $nsDssn . '> ' . PHP_EOL;
                $deleteSubscribeQuery .= 'SELECT ?subscription ' . PHP_EOL;
                $deleteSubscribeQuery .= 'WHERE {' . PHP_EOL;
                $deleteSubscribeQuery .= '<' . $groupUri . '> dssn:subscribedTo ?subscription' . PHP_EOL;
                $deleteSubscribeQuery .= '}';
                $deleteSubscribeResult = $model->sparqlQuery($deleteSubscribeQuery);

                $deleteMemberQuery  = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
                $deleteMemberQuery .= 'SELECT ?member ' . PHP_EOL;
                $deleteMemberQuery .= 'WHERE {' . PHP_EOL;
                $deleteMemberQuery .= '<' . $groupUri . '> foaf:member ?member' . PHP_EOL;
                $deleteMemberQuery .= '}';
                $deleteMemberResult = $model->sparqlQuery($deleteMemberQuery);

                $deleteGroup = array(
                    $groupUri => array(
                        EF_RDF_TYPE => array(
                            array('type' => 'uri', 'value' => $nsFoaf . 'Group')
                        ),
                        $nsDssn . 'activityFeed' => array(
                            array('type' => 'uri', 'value' => $groupFeed)
                        ),
                        $nsFoaf . 'maker' => array(
                            array('type' => 'uri', 'value' => $personUri)
                        ),
                        $nsFoaf . 'name' => array(
                            array('type' => 'literal', 'value' => $name)
                        ),
                        $nsFoaf . 'primaryTopic' => array(
                            array('type' => 'literal', 'value' => $deleteResult[0]['topic'])
                        )
                    )
                );

                foreach($deleteSubscribeResult as $value) {                    
                    $deleteGroup[$groupUri][$nsDssn . 'subscribedTo'][] = 
                            array('type' => 'uri', 'value' => $value['subscription']);
                }
                foreach($deleteMemberResult as $value) {                    
                    $deleteGroup[$groupUri][$nsFoaf . 'member'][] = 
                            array('type' => 'uri', 'value' => $value['member']);
                }

                $model->deleteMultipleStatements($deleteGroup);
            } else {
                $logger->error('GroupController/deleteGroup: Person is not authorised'
                        . ' to delete the group: ' . $name);
                throw new Exception('Person is not authorised.');
            }
        }
    }

     /**
     * This method creates a new object of the class Xodx_Group
     * @param $groupUri a string which contains the URI of the required group
     * @return Xodx_Group|null instance with the specified URI or null if there is no such group on this server
     */
    public function getGroup ($groupUri)
    {
        if (!isset($this->_groups[$groupUri])) {
           $bootstrap = $this->_app->getBootstrap();
           $model = $bootstrap->getResource('model');
           $logger = $bootstrap->getResource('logger');

           $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
           $query.= 'SELECT ?name ?topic' . PHP_EOL;
           $query.= 'WHERE {' . PHP_EOL;
           $query.= '  <' . $groupUri . '> foaf:name ?name ;' . PHP_EOL;
           $query.= '      foaf:primaryTopic ?topic .' . PHP_EOL;
           $query.= '}' . PHP_EOL;

           $result = $model->sparqlQuery($query);
           if (count($result) > 0) {
               $groupId = $result[0]['name'];
               $groupTopic = $result[0]['topic'];
           } else {
               $logger->error('GroupController/getGroup: Group does not exist ("' . $groupUri . '")');
               throw new Exception('Group does not exist.');
           }
           $group = new Xodx_Group($groupUri, $this->_app);
           $group->setName($groupId);
           $group->setDescription($groupTopic);

           $this->_groups[$groupUri] = $group;
       }
       return $this->_groups[$groupUri];
    }

    /**
     * Declare a person a member of a group
     *
     * @param string $personUri  the URI of the group's new member
     * @param $groupUri   the URI of the group that the member shall be added to
     */
    public function joinGroup ($personUri, $groupUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');
        $userController = $this->_app->getController('Xodx_UserController');

        // Update WebID
        $model->addStatement(
            $personUri,
            'http://xmlns.com/foaf/0.1/member',
            array('type' => 'uri', 'value' => $groupUri)
        );

        $nsAair = 'http://xmlns.notu.be/aair#';
        $activityController = $this->_app->getController('Xodx_ActivityController');

        // Add Activity to activity Stream
        $object = array(
            'type' => 'uri',
            'content' => $groupUri,
            'replyObject' => 'false'
        );
        $activityController->addActivity($personUri, $nsAair . 'Join', $object);
        // Send Ping to group
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($personUri, $groupUri, 'I hereby declare myself a member of this group.');

        // Subscribe to group
        $userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getGroupFeedUri($groupUri);

        if ($feedUri !== null) {
            $logger->debug(
                'GroupController/joinGroup: Found feed for newly joined Group ("'
                . $groupUri . '"): "' . $feedUri . '"'
            );
            $userController->subscribeToResource ($userUri, $groupUri, $feedUri);
        } else {
            $logger->error(
                'GroupController/joinGroup: Couldn\'t find feed for newly joined group ("'
                . $groupUri . '").'
            );
        }
    }

    /**
     * This makes it possible for persons to leave a group
     * @param string $personUri URI of the person who is leaving
     * @param string $groupUri URI of the group to be left
     * @throws Exception if the WebID of this group does not exist
     */
    public function leaveGroup ($personUri, $groupUri)
    {
        // getResources
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');
        $userController = $this->_app->getController('Xodx_UserController');

        // delete Statement added by joinGroup ($personUri, member, $groupUri)
        $statementArray = array (
            $personUri => array (
                'http://xmlns.com/foaf/0.1/member' => array(
                    array (
                        'type'  => 'uri',
                        'value' => $groupUri
                    )
                )
            )
        );
        $model->deleteMultipleStatements($statementArray);
        // Send Ping to group
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($personUri, $groupUri, 'I hereby leave this group.');

        // unsubscribe from group
        $userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getGroupFeedUri($groupUri);

        if ($feedUri !== null) {
            // Logging
            $logger->debug('GroupController/leavegroup: Found feed for group ("' . $groupUri . '"): "' . $feedUri . '"');
            // unsubscription of friend's feed
            $userController->unsubscribeFromResource ($userUri, $groupUri, $feedUri);
        } else {
            // Logging
            $logger->error('GroupController/leavegroup: Couldn\'t find feed for group ("' . $groupUri . '").');
        }
    }

    /**
     * A view action for leaving a specified group.
     *
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function leavegroupAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        // get URI
        $groupUri = $request->getValue('group', 'post');
        $personUri = $request->getValue('person', 'post');

        if ($personUri == null) {
            $userController = $this->_app->getController('Xodx_UserController');
            $personUri = $userController->getUser()->getPerson();
        }

        if (Erfurt_Uri::check($personUri)) {
            // Get remote base uri from group uri
            $uri = "";
            if (($uriArray = parse_url($groupUri))) {
                $uri = $uriArray['scheme'] . '://'
                     . $uriArray['host'];
                if (!empty($uriArray['port'])) {
                    $uri.= ':' . $uriArray['port'];
                }
                if (!empty($uriArray['path'])) {
                    $uri.= $uriArray['path'];
                }
                if(substr($uri, -1) != '/') {
                    $uri.= '/';
                }
                $uri.= '?c=member&a=deletemember';
            }
            if (!empty($uri)) {
                // Send curl post request with needed data
                $fields = array(
                    'personUri' => urlencode($personUri),
                    'groupUri' => urlencode($groupUri)
                );

                $apiStatus = trim($this->_callMemberApi($uri, $fields));
                if ($apiStatus == "success") {
                    $this->leaveGroup($personUri, $groupUri);
                    //Redirect
                    $location = new Saft_Url($this->_app->getBaseUri());

                    $groupName = $this->getGroup($groupUri)->getName();
                    $location->setParameter('c', 'user');
                    $location->setParameter('a', 'home');
                    $template->redirect($location);
                } else {
                    $template->addContent('templates/error.phtml');
                    $template->exception = 'API call failed!';
                }
            } else {
                $template->addContent('templates/error.phtml');
                $template->exception = 'Failed to parse url from $groupUri!';
            }
        } else {
            $template->addContent('templates/error.phtml');
            $template->exception = 'The given URI is not valid: personUri="' . $personUri;
        }

        return $template;
    }

    /**
     * View action for joining a new group.
     *
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     * @todo should use semantic pingback instead of a curl request
     */
    public function joingroupAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        $logger = $bootstrap->getResource('logger');
        $model = $bootstrap->getResource('model');

        // get URI
        $groupUri = $request->getValue('group', 'post');
        $personUri = $request->getValue('person', 'post');

        if ($personUri == null) {
            $userController = $this->_app->getController('Xodx_UserController');
            $personUri = $userController->getUser()->getPerson();
            $user = $userController->getUser();
        }

        // Redirect to login if user is guest
        if($user->getName() == 'guest') {
            $location = new Saft_Url($this->_app->getBaseUri());

            $location->setParameter('c', 'application');
            $location->setParameter('a', 'login');
            $template->redirect($location);
        }

        if (Erfurt_Uri::check($personUri)) {
            $nsFoaf = 'http://xmlns.com/foaf/0.1/';
            //verify that User is not already member of the group
            $memberQuery  = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
            $memberQuery .= 'ASK {' .PHP_EOL;
            $memberQuery .= '   <' . $personUri . '>' . ' foaf:member <' . $groupUri . '>.' .PHP_EOL;
            $memberQuery .= '}';

            if (!$model->sparqlQuery($memberQuery)) {
                // Get remote base uri from group uri
                $uri = "";
                if (($uriArray = parse_url($groupUri))) {
                    $uri = $uriArray['scheme'] . '://'
                         . $uriArray['host'];
                    if (!empty($uriArray['port'])) {
                        $uri.= ':' . $uriArray['port'];
                    }
                    if (!empty($uriArray['path'])) {
                        $uri.= $uriArray['path'];
                    }
                    if(substr($uri, -1) != '/') {
                        $uri.= '/';
                    }
                    $uri.= '?c=member&a=addmember';
                }
                if (!empty($uri)) {
                    // Send curl post request with needed data
                    $fields = array(
                        'personUri' => urlencode($personUri),
                        'groupUri' => urlencode($groupUri)
                    );

                    $apiStatus = trim($this->_callMemberApi($uri, $fields));
                    if ($apiStatus == "success") {
                        $this->joinGroup($personUri, $groupUri);
                        //Redirect
                        $location = new Saft_Url($this->_app->getBaseUri());

                        $groupName = $this->getGroup($groupUri)->getName();
                        $location->setParameter('c', 'group');
                        $location->setParameter('id', $groupName);
                        $location->setParameter('a', 'home');
                        $template->redirect($location);
                    } else {
                        $template->addContent('templates/error.phtml');
                        $template->exception = 'API call failed!';
                    }
                } else {
                    $template->addContent('templates/error.phtml');
                    $template->exception = 'Failed to parse url from $groupUri!';
                }
            } else {
                $template->addContent('templates/error.phtml');
                $template->exception = 'You are already member of that group ("'
                    . $groupUri . '").';
                $logger->error(
                    'GroupController/joinGroup: You are already member of that group ("'
                    . $groupUri . '").'
                );
            }
        } else {
            $template->addContent('templates/error.phtml');
            $template->exception = 'The given URI is not valid: personUri="' . $personUri;
        }
        return $template;
    }

    /**
     * Creates feed on given resource
     * @param Uri $resourceUri the feed searched for
     * @return Uri $feedUri of $resourceUri
     */
    public function getGroupFeedUri($resourceUri)
    {
        $pos = strpos($resourceUri, '?c=');
        $baseUri = substr($resourceUri, 0, $pos);
        $feedUri = $baseUri . '?c=feed&a=getFeed&uri=' . urlencode($resourceUri);
        return $feedUri;
    }

    /**
     * Calls the API created to get group subscribing person
     * @param string $uri URI of the API
     * @param mixed[] $fields Fields to send with
     * @return mixed content got from request
     * @deprecated should be implemented with semantic pingback
     */
    private function _callMemberApi($uri, $fields) {
        // uri-fy the date for the POST Request
        $fields_string = '';
        foreach ($fields as $field => $value) {
            $fields_string .= $field . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        // Open Connection
        $curlConnection = curl_init();

        // Set the uri, number of POST vars and POST data
        curl_setopt($curlConnection, CURLOPT_URL, $uri);
        curl_setopt($curlConnection, CURLOPT_POST, count($fields));
        curl_setopt($curlConnection, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, true);

        // Execute
        $result = curl_exec($curlConnection);

        // Close Connection
        curl_close($curlConnection);

        return $result;
    }

    /**
     * Subscribes a group to a user's group-specific activity feed and vice versa.
     * Should be called while joining a group. Can be reverted using {@link _unsubscribeFromGroupFeed()}
     *
     * @param   string  $personUri  URI of the person joining the group
     * @param   string  $groupUri   URI of the group being joined
     * @see     _unsubscribeGroupFromFeed()
     * @access  private
     */
    private function _subscribeGroupToFeed($personUri, $groupUri)
    {
        $userController  = $this->_app->getController('Xodx_UserController');

        // Subscribe to new member
        $userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getActivityFeedUri($personUri);
        if ($feedUri !== null) {
            $logger->debug(
                'GroupController/_subscribeToGroupFeed: Found feed for newly added member ("'
                . $personUri . '"): "'
                . $feedUri . '"'
            );
            $userController->subscribeToResource ($groupUri, $userUri, $feedUri);
        } else {
            $logger->error(
                'GroupController/_subscribeToGroupFeed: Couldn\'t find feed for newly added member ("'
                . $personUri . '").'
            );
        }
    }

    /**
     * Unsubscribes a user from a group feed and vice versa. Should be called when leaving a group.
     * Reverts the actions of {@link _subscribeToGroupFeed()}
     *
     * @param   string  $personUri  URI of the person leaving the group
     * @param   string  $groupUri   URI of the group being left
     * @see     _subscribeGroupToFeed()
     * @access  private
     */
    private function _unsubscribeGroupFromFeed($personUri, $groupUri)
    {
        $userController  = $this->_app->getController('Xodx_UserController');

        // Unsubscribe from leaving member
        $userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getActivityFeedUri($personUri);
        if ($feedUri !== null) {
            $logger->debug(
                'GroupController/_unsubscribeGroupFromFeed: Found feed for leaving member ("'
                . $personUri . '"): "'
                . $feedUri . '"'
            );
            $userController->unsubscribeFromResource ($groupUri, $userUri, $feedUri);
        } else {
            $logger->error(
                'GroupController/_unsubscribeGroupFromFeed: Couldn\'t find feed for leaving member ("'
                . $personUri . '").'
            );
        }
    }

    /**
     * This method changes the name and description of a group
     *
     * @param string $groupUri The group's URI
     * @param string $newName New name of the group
     * @param string $newTopic New description of the group
     */
    public function changeGroup($groupUri, $newName, $newTopic)
    {
        $bootstrap  = $this->_app->getBootstrap();
        $model      = $bootstrap->getResource('model');
        $logger     = $bootstrap->getResource('logger');
        $nsFoaf     = 'http://xmlns.com/foaf/0.1/';

        if($groupUri!=null) {
            // delete old name from db
            $model->deleteMatchingStatements($groupUri, $nsFoaf . 'name', null);

            // set new name in db
            $setName = array (
                $groupUri => array (
                    $nsFoaf . 'name' => array(
                        array (
                            'type'  => 'literal',
                            'value' => $newName
                        )
                    )
                )
            );
            $model->addMultipleStatements($setName);
            // log new name
            $logger->debug(
                'GroupController/changeGroup: Group ' . $groupUri
                . ' changed its name from \'' . $oldName . '\' to \'' . $newName . '\'.'
            );
        } else {
            $logger->error('GroupController/changeGroup: Group URI is null.');
        }
        if($groupUri!=null) {
            // delete old description(s)
            $model->deleteMatchingStatements($groupUri, $nsFoaf . 'primaryTopic', null);

            // set new description
            $setTopic = array (
                $groupUri => array (
                    $nsFoaf . 'primaryTopic' => array(
                        array (
                            'type'  => 'literal',
                            'value' => $newTopic
                        )
                    )
                )
            );
            $model->addMultipleStatements($setTopic);
            // log new description
            $logger->debug(
                'GroupController/changeGroup: Group ' . $grouUri .
                ' changed its description to \'' . $newTopic . '\'.'
            );
        } else {
            $logger->error('GroupController/changeGroup: Group URI is null.');
        }
    }
}
