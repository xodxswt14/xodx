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
 * - Getting a group from its uri
 * 
 * @author Jan Buchholz
 * @author Stephan Kemper
 * @author Lukas Werner
 * @author Gunnar Warnecke
 */
class Xodx_GroupController extends Xodx_ResourceController
{
    /**
     * @var Xodx_Group A registry of already loaded XodX_Group objects
     */
    private $_groups = array();

    public function showAction($template)
    {
        $bootstrap  = $this->_app->getBootstrap();
        $model      = $bootstrap->getResource('model');
        $request    = $bootstrap->getResource('request');
        $logger     = $bootstrap->getResource('logger');
        $groupUri  = $request->getValue('uri', 'get');
        $id         = $request->getValue('id', 'get');
        $controller = $request->getValue('c', 'get');
        
        if ($id !== null) {
            $groupUri = $this->_app->getBaseUri() . '?c=' . $controller . '&id=' . $id;
        }
        
        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        
        $groupQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupQuery.= 'SELECT ?nick ' .  PHP_EOL;
        $groupQuery.= 'WHERE { ' .  PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> a foaf:Group . ' . PHP_EOL;
        $groupQuery.= 'OPTIONAL {<' . $groupUri . '> foaf:nick ?nick .} ' . PHP_EOL;
        $groupQuery.= '}'; PHP_EOL;
        
        $group = $model->sparqlQuery($groupQuery);
        
        $template->groupshowNick = $group[0]['nick'];
        $template->groupUri = $groupUri;
        
        return $template;
    }
    
    public function homeAction($template)
    {
        $bootstrap  = $this->_app->getBootstrap();
        $model      = $bootstrap->getResource('model');
        $request    = $bootstrap->getResource('request');
        $logger     = $bootstrap->getResource('logger');
        $groupUri  = $request->getValue('uri', 'get');
        $id         = $request->getValue('id', 'get');
        $controller = $request->getValue('c', 'get');
        
        if ($id !== null) {
            $groupUri = $this->_app->getBaseUri() . '?c=' . $controller . '&id=' . $id;
        }
        
        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        
        $groupQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupQuery.= 'SELECT ?name ?maker ' .  PHP_EOL;
        $groupQuery.= 'WHERE { ' .  PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> a foaf:Group  . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:name ?name . ' . PHP_EOL;
        $groupQuery.= '   <' . $groupUri . '> foaf:maker ?maker .' . PHP_EOL;
        $groupQuery.= '}'; PHP_EOL;
        
        $group = $model->sparqlQuery($groupQuery);
        
        /* get loged in user */
        $userController = $this->_app->getController('Xodx_UserController');
        $user = $userController->getUser();
        
        $activityController = $this->_app->getController('Xodx_ActivityController');
        $activities = $activityController->getActivities($groupUri);
        
        if($user->getPerson() == $group[0]['maker']) {
            $template->isMaker = true;
        } else {
            $template->isMaker = false;
        }
        
        $template->groupUri = $groupUri;
        $template->groupshowName = $group[0]['name'];
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

        $formError = array();

        if (empty($groupname)) {
            $formError['groupname'] = true;
        }

        if (count($formError) <= 0) {
            $this->createGroup(null, $groupname);

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
     * A view action for deleting an existing group.
     * 
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
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
            $location->setParameter('c', 'groupProfile');
            $location->setParameter('a', 'list');

            $template->redirect($location);
        } else {
            $template->formError = $formError;
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
    public function createGroup ($groupUri = null, $name)
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsDssn = 'http://purl.org/net/dssn/';

        // fetch empty groupUri
        if ($groupUri === null) {
            $groupUri = $this->_app->getBaseUri() . '?c=group&id=' . urlencode($name);
        }

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
                        array('type' => 'literal', 'value' => 'Enter description here...')
                    )
                )
            );
            $model->addMultipleStatements($newGroup);
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
                $deleteQuery  = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
                $deleteQuery .= 'SELECT ?topic ' . PHP_EOL;
                $deleteQuery .= 'WHERE {' . PHP_EOL;
                $deleteQuery .= '<' . $groupUri . '> foaf:primaryTopic ?topic' . PHP_EOL;
                $deleteQuery .= '}';            
                $deleteResult = $model->sparqlQuery($deleteQuery);

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
                        $nsFoaf . 'nick' => array(
                            array('type' => 'literal', 'value' => $name)
                        ),
                        $nsFoaf . 'primaryTopic' => array(
                            array('type' => 'literal', 'value' => $deleteResult[0]['topic'])
                        )
                    )
                );
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
     * @return Xodx_Group instance with the specified URI
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
               $logger->error('GroupController/getGroup: Group does not exist.' . $groupUri);
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
        // get feedUri from given groupUri
        $pos = strpos($groupUri, '?c=');        
        $baseUri = substr($groupUri, 0, $pos);
        $feedUri = $baseUri . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);

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

        // unsubscribe from group        
        $userUri = $userController->getUserUri($personUri);
        // get feedUri from given groupUri
        $pos = strpos($groupUri, '?c=');        
        $baseUri = substr($groupUri, 0, $pos);
        $feedUri = $baseUri . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);

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
            $memberController = $this->_app->getController('Xodx_MemberController');
            $memberController->deleteMember($personUri, $groupUri);

            $this->leaveGroup($personUri, $groupUri);
            //Redirect
            $location = new Saft_Url($this->_app->getBaseUri());

            $location->setParameter('c', 'user');
            $location->setParameter('a', 'home');
            $template->redirect($location);
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
     */
    public function joingroupAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        $logger = $bootstrap->getResource('logger');

        // get URI
        $groupUri = $request->getValue('group', 'post');
        $personUri = $request->getValue('person', 'post');

        if ($personUri == null) {
            $userController = $this->_app->getController('Xodx_UserController');
            $personUri = $userController->getUser()->getPerson();
        }

        if (Erfurt_Uri::check($personUri)) {
            $memberController = $this->_app->getController('Xodx_MemberController');
            $memberController->addMember($personUri, $groupUri);

            $this->joinGroup($personUri, $groupUri);
            //Redirect
            $location = new Saft_Url($this->_app->getBaseUri());

            $location->setParameter('c', 'user');
            $location->setParameter('a', 'home');
            $template->redirect($location);
        } else {
            $template->addContent('templates/error.phtml');
            $template->exception = 'The given URI is not valid: personUri="' . $personUri;
        }
        return $template;
    }
}
