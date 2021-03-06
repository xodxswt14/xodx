<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The PersonController is responsible for all action concerning Persons.
 * This is showing the profile, befriending and maybe more in the future.
 */
class Xodx_PersonController extends Xodx_ResourceController
{
    /**
     * The cache-array of already queried persons to not query for the same person twice
     */
    private $_persons = array();

    /**
     * A view action to show a person
     */
    public function showAction ($template)
    {
        $bootstrap  = $this->_app->getBootstrap();
        $model      = $bootstrap->getResource('model');
        $request    = $bootstrap->getResource('request');
        $logger     = $bootstrap->getResource('logger');
        $personUri  = $request->getValue('uri', 'get');
        $id         = $request->getValue('id', 'get');
        $controller = $request->getValue('c', 'get');

        // get URI
        if ($id !== null) {
            $personUri = $this->_app->getBaseUri() . '?c=' . $controller . '&id=' . $id;
        }

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';

        $profileQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $profileQuery.= 'SELECT ?depiction ?name ?nick ' .  PHP_EOL;
        $profileQuery.= 'WHERE { ' .  PHP_EOL;
        $profileQuery.= '   <' . $personUri . '> a foaf:Person . ' . PHP_EOL;
        $profileQuery.= 'OPTIONAL {<' . $personUri . '> foaf:depiction ?depiction .} ' . PHP_EOL;
        $profileQuery.= 'OPTIONAL {<' . $personUri . '> foaf:name ?name .} ' . PHP_EOL;
        $profileQuery.= 'OPTIONAL {<' . $personUri . '> foaf:nick ?nick .} ' . PHP_EOL;
        $profileQuery.= '}'; PHP_EOL;

        // TODO deal with language tags
        $contactsQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $contactsQuery.= 'SELECT ?contactUri ?name ?nick ' . PHP_EOL;
        $contactsQuery.= 'WHERE { ' . PHP_EOL;
        $contactsQuery.= '   <' . $personUri . '> foaf:knows ?contactUri . ' . PHP_EOL;
        $contactsQuery.= '   OPTIONAL {?contactUri foaf:name ?name .} ' . PHP_EOL;
        $contactsQuery.= '   OPTIONAL {?contactUri foaf:nick ?nick .} ' . PHP_EOL;
        $contactsQuery.= '}';
        
        $groupQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupQuery.= 'SELECT ?groupUri ' .  PHP_EOL;
        $groupQuery.= 'WHERE { ' .  PHP_EOL;
        $groupQuery.= '   ?groupUri foaf:maker <' . $personUri . '> ' . PHP_EOL;
        $groupQuery.= '}'; PHP_EOL;

        $groupsQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
        $groupsQuery.= 'SELECT ?groupUri ?maker ?name ' . PHP_EOL;
        $groupsQuery.= 'WHERE { ' . PHP_EOL;
        $groupsQuery.= '   <' . $personUri . '> foaf:member ?groupUri . ' . PHP_EOL;
        $groupsQuery.= '   OPTIONAL {?groupUri foaf:maker ?maker .} ' . PHP_EOL;
        $groupsQuery.= '   OPTIONAL {?groupUri foaf:name ?name .} ' . PHP_EOL;
        $groupsQuery.= '}';

        $profile = $model->sparqlQuery($profileQuery);
        $groups = $model->sparqlQuery($groupQuery);
        
        if (count($profile) < 1) {
            $linkeddataHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
            $newStatements = $linkeddataHelper->getResource($personUri);
            if ($newStatements !== null) {
                $logger->info('Import Profile with LinkedDate');

                $modelNew = new Erfurt_Rdf_MemoryModel($newStatements);
                $newStatements = $modelNew->getStatements();

                $profile = array();
                $profile[0] = array(
                    'depiction' => $modelNew->getValue($personUri, $nsFoaf . 'depiction'),
                    'name' => $modelNew->getValue($personUri, $nsFoaf . 'name'),
                    'nick' => $modelNew->getValue($personUri, $nsFoaf . 'nick')
                );
            }
            $friends = $modelNew->getValues($personUri, $nsFoaf . 'knows');
            $groups = $modelNew->getValues($personUri, $nsFoaf . 'member');

            $knows = array();
            $member = array();

            foreach($friends as $friend) {
                $knows[] = array(
                    'contactUri' => $friend['value'],
                    'name' => '',
                    'nick' => ''
                );
            }
            
            foreach($groups as $group) {
                $member[] = array(
                    'groupUri' => $group['value'],
                    'name' => ''
                );
            }

        } else {
            $knows = $model->sparqlQuery($contactsQuery);
            $member = $model->sparqlQuery($groupsQuery);
        }

        $activityController = $this->_app->getController('Xodx_ActivityController');
        $activities = $activityController->getActivities($personUri);

        $news = $this->getNotifications($personUri);

        /* get loged in user */
        $userController = $this->_app->getController('Xodx_UserController');
        $user = $userController->getUser();

        if (false) {
            $template->profileshowLoggedIn = false;
        }
        /* if someone is loged in, show add as Friend, else not */
        $knowsQuery = 'ASK { <' . $user->getPerson() . '> foaf:knows <' . $personUri . '>  }';
        if(
            $user->getName() == 'guest' ||
            $user->getPerson() == $personUri ||
            $model->sparqlQuery($knowsQuery)
        ) {
            $template->profileshowLoggedIn = false;
            /* if someone is logged in and knows the Person, set personUri for unfriending */
            if($model->sparqlQuery($knowsQuery)) {
                $template->profileshowLogInUri = $user->getPerson();
            }
        } else {
            $template->profileshowLogInUri = $user->getPerson();
            $template->profileshowLoggedIn = true;
        }
        
        // Test if user is not logged in, is on his own profile or knows the person he is visiting
        if($user->getName() == 'guest') {
            $template->isGuest = true;
            $template->profileshowLoggedIn = false;
            $template->profileshowLogInUri = NULL;
        } elseif($user->getPerson() == $personUri) {
            $template->isGuest = false;
            $template->isOwnProfile = true;
            $template->profileshowLoggedIn = true;
            $template->profileshowLogInUri = $user->getPerson();
        } elseif($model->sparqlQuery($knowsQuery)) {
            $template->knowsPerson = true;
            $template->profileshowLogInUri = $user->getPerson();
            $template->profileshowLoggedIn = true;
        } else {
            $template->knowsPerson = false;
        }

        $template->profileshowPersonUri = $personUri;
        $template->profileshowDepiction = $profile[0]['depiction'];
        $template->profileshowName = $profile[0]['name'];
        $template->profileshowNick = $profile[0]['nick'];
        $template->profileshowActivities = $activities;
        $template->profileshowKnows = $knows;
        $template->profileshowMember = $member;
        $template->profileshowNews = $news;
        $template->addContent('templates/profileshow.phtml');

        return $template;
    }

    public function rdfAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $request = $bootstrap->getResource('request');

        $objectId = $request->getValue('id', 'get');
        $controller = $request->getValue('c', 'get');
        $personUri = $this->_app->getBaseUri() . '?c=' . $controller . '&id=' . $objectId;
        $documentUri = new Saft_Url($request);

        $mimetypeHelper = $this->_app->getHelper('Saft_Helper_MimetypeHelper');
        $mime = $mimetypeHelper->matchFromRequest($request, $this->rdfTypes);

        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>' . PHP_EOL;
        $query.= 'SELECT ?resourceUri ?p ?o' . PHP_EOL;
        $query.= 'WHERE {' . PHP_EOL;
        $query.= '  ?resourceUri ?p ?o.' . PHP_EOL;
        $query.= '  {' . PHP_EOL;
        $query.= '    ?documentUri a foaf:PersonalProfileDocument .' . PHP_EOL;
        $query.= '    ?documentUri foaf:primaryTopic ?personUri .' . PHP_EOL;
        $query.= '    FILTER(sameTerm(?documentUri, <' . $documentUri . '>))' . PHP_EOL;
        $query.= '    FILTER(?resourceUri=?documentUri)' . PHP_EOL;
        $query.= '  } UNION {' . PHP_EOL;
        $query.= '    ?personUri a foaf:Person.' . PHP_EOL;
        $query.= '    FILTER(sameTerm(?personUri, <' . $personUri . '>))' . PHP_EOL;
        $query.= '    FILTER(?resourceUri=?personUri)' . PHP_EOL;
        $query.= '  }' . PHP_EOL;
        $query.= '}' . PHP_EOL;

        $queryObject = Erfurt_Sparql_SimpleQuery::initWithString($query);

        $modelUri = $model->getModelIri();

        try {
            $format = Erfurt_Syntax_RdfSerializer::normalizeFormat($mime);
            $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);
            $rdfData = $serializer->serializeQueryResultToString($queryObject, $modelUri);
            $template->setHeader('Content-type', $mime);

            $template->setRawContent($rdfData);
        } catch (Exception $e) {
            $template->setResponseCode(404);
            $template->setRawContent($e);
        }

        return $template;
    }

    /**
     * View action for adding a new friend. (This action should be called from a form)
     */
    public function addfriendAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        // get URI
        $personUri = $request->getValue('person', 'post');
        $friendUri = $request->getValue('friend', 'post');

        if (Erfurt_Uri::check($personUri) && Erfurt_Uri::check($friendUri)) {
            $personController = $this->_app->getController('Xodx_PersonController');
            $personController->addFriend($personUri, $friendUri);

            //Redirect
            $location = new Saft_Url($this->_app->getBaseUri());

            $location->setParameter('c', 'user');
            $location->setParameter('a', 'home');
            $template->redirect($location);
        } else {
            $template->addContent('templates/error.phtml');
            $template->exception = 'At least one of the given URIs is not valid: personUri="' . $personUri . '", friendUri="' . $friendUri . '".';
        }

        return $template;
    }
    
    /**
     * View action for deleting a new friend. (This action should be called from a form)
     */
    public function deletefriendAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');
        
        // get URI
        $personUri = $request->getValue('person', 'post');
        $friendUri = $request->getValue('friend', 'post');

        if (Erfurt_Uri::check($personUri) && Erfurt_Uri::check($friendUri)) {
            $personController = $this->_app->getController('Xodx_PersonController');
            $personController->deleteFriend($personUri, $friendUri);

            //Redirect
            $location = new Saft_Url($this->_app->getBaseUri());
            
            $location->setParameter('c', 'user');
            $location->setParameter('a', 'home');
            $template->redirect($location);
        } else {
            $template->addContent('templates/error.phtml');
            $template->exception = 'At least one of the given URIs is not valid: personUri="' . $personUri . '", friendUri="' . $friendUri . '".';
        }        

        return $template;
    }

    /**
     * Get a DSSN_Foaf_Person object representing the specified person
     *
     * @param $personUri the URI of the person who sould be represented by the returned object
     * @return a DSSN_Foaf_Person object
     */
    public function getPerson ($personUri)
    {
        if (!isset($this->_persons[$psersonUri])) {
            $person = new DSSN_Foaf_Person($personUri);
            $this->_persons[$personUri] = $person;
        }
        return $this->_persons[$personUri];
    }

    /**
     * This method gets the userAccount responsible for a given person.
     *
     * @param $personUri the URI of the person whoes account should be returned
     * @returns Xodx_User account of this person
     */
    public function getUserForPerson ($personUri)
    {
        $model = $this->_app->getBootstrap()->getResource('model');
        $userController = $this->_app->getController('Xodx_UserController');

        $userQuery = 'SELECT ?user' . PHP_EOL;
        $userQuery.= 'WHERE {' . PHP_EOL;
        $userQuery.= '    ?user sioc:account_of <' . $personUri . '>.' . PHP_EOL;
        $userQuery.= '}' . PHP_EOL;
        $userQuery.= 'LIMIT 1' . PHP_EOL;

        $result = $model->sparqlQuery($userQuery);

        $user = $userController->getUser($result[0]['user']);

        return $user;
    }

    /**
     * Add a new contact to the list of freinds of a person
     * This is a one-way connection, the contact doesn't has to approve it
     *
     * @param $personUri the URI of the person to whome the contact should be added
     * @param $contactUri the URI of the person who sould be added as friend
     */
    public function addFriend ($personUri, $contactUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');
        $userController = $this->_app->getController('Xodx_UserController');

        $ldHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
        if (!$ldHelper->resourceDescriptionExists($contactUri)) {
            throw new Exception('The WebID of your friend does not exist.');
        }

        // Update WebID
        $model->addStatement($personUri, 'http://xmlns.com/foaf/0.1/knows', array('type' => 'uri', 'value' => $contactUri));

        $nsAair = 'http://xmlns.notu.be/aair#';
        $activityController = $this->_app->getController('Xodx_ActivityController');

        // Add Activity to activity Stream
        $object = array(
            'type' => 'uri',
            'content' => $contactUri,
            'replyObject' => 'false'
        );
        $activityController->addActivity($personUri, $nsAair . 'MakeFriend', $object);

        // Send Ping to new friend
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($personUri, $contactUri, 'Do you want to be my friend?');

        // Subscribe to new friend
        $userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getActivityFeedUri($contactUri);
        if ($feedUri !== null) {
            $logger->debug('PersonController/addfriend: Found feed for newly added friend ("' . $contactUri . '"): "' . $feedUri . '"');
            $userController->subscribeToResource ($userUri, $contactUri, $feedUri);
        } else {
            $logger->error('PersonController/addfriend: Couldn\'t find feed for newly added friend ("' . $contactUri . '").');
        }
    }
    
    /**
     * Delete an old contact out of the list of freinds of a person
     * This is a one-way connection, the contact doesn't has to approve it
     *
     * @param URI $personUri the URI of the person from whome the contact should be removed
     * @param URI $contactUri the URI of the person who sould be removed as friend
     */
    public function deleteFriend ($personUri, $contactUri)
    {
        
        // getResources
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');
        $userController = $this->_app->getController('Xodx_UserController');

        // check friend's Uri
        $ldHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
        if (!$ldHelper->resourceDescriptionExists($contactUri)) {
            throw new Exception('The WebID of your friend does not exist.');
        }
        // delete Statement added by addFriend ($personUri, knows, $contactUri)
        $statementArray = array (
            $personUri => array (                               
                'http://xmlns.com/foaf/0.1/knows' => array(     
                    array (                                     
                        'type'  => 'uri',
                        'value' => $contactUri
                    )
                )
            )
        );        
        $model->deleteMultipleStatements($statementArray);

        // unsubscribe from friend        
        $userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getActivityFeedUri($contactUri);
        if ($feedUri !== null) 
        {
            // Logging
            $logger->debug('PersonController/deletefriend: Found feed for friend ("' . $contactUri . '"): "' . $feedUri . '"');
            // unsubscription of friend's feed            
            $userController->unsubscribeFromResource ($userUri, $contactUri, $feedUri);
        } else {
            // Logging
            $logger->error('PersonController/deletefriend: Couldn\'t find feed for friend ("' . $contactUri . '").');
        }
    }
    

    /**
     * Returns the feed of the specified $type of the person
     * @param $personUri the URI of the person whoes feed sould be returned
     */
    public function getFeed ($personUri, $type = 'activity')
    {
        $model = $this->_app->getBootstrap()->getResource('model');

        $nsDssn = 'http://purl.org/net/dssn/';

        $feedProp = '';
        if ($type == 'activity') {
            $feedProp = $nsDssn . 'activityFeed';
        } else if ($type == 'sync') {
            $feedProp = $nsDssn . 'syncFeed';
        }

        $feedResult = $model->sparqlQuery(
            'PREFIX atom: <http://www.w3.org/2005/Atom/> ' .
            'PREFIX aair: <http://xmlns.notu.be/aair#> ' .
            'SELECT ?feed ' .
            'WHERE { ' .
            '   <' . $personUri . '> <' . $feedProp . '> ?feed . ' .
            '}'
        );

        return $feedResult[0]['feed'];
    }

    /**
     * Get an array of new notifications for the person
     *
     * @param $personUri the URI of the person whoes notifications should be returned
     */
    public function getNotifications ($personUri)
    {
        $model = $this->_app->getBootstrap()->getResource('model');

        $pingResult = $model->sparqlQuery(
            'PREFIX pingback: <http://purl.org/net/pingback/> ' .
            'SELECT ?ping ?source ?target ?comment ' .
            'WHERE { ' .
            '   <' . $personUri . '> pingback:ping ?ping . ' .
            '   ?ping a                pingback:Item ; ' .
            '         pingback:source  ?source ; ' .
            '         pingback:target  ?target ; ' .
            '         pingback:comment ?comment . ' .
            '} '
        );

        return $pingResult;
    }

    /**
     * Quick fix for Erfurt issue #24 (https://github.com/AKSW/Erfurt/issues/24)
     */
    private static function _issueE24fix ($date)
    {
        if (substr($date, 11, 1) != 'T') {
            $dateObj = date_create($date);
            return date_format($dateObj, 'c');
        } else {
            return $date;
        }
    }
}
