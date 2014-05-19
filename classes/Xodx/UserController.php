<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

// include password hash functions for 5.3.7 <= PHP < 5.5
require_once('password_compat/lib/password.php');

/**
 * This class manages instances of Xodx_User.
 * this includes:
 *  - subscribing to a feed
 *  - unsubscribing from a feed
 *  - getting notifications
 * 
 * @author Natanael Arndt
 * @author Jan Buchholz
 */
class Xodx_UserController extends Xodx_ResourceController
{
    /**
     * A registry of already loaded Xodx_User objects
     */
    private $_users = array();

    public function homeAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $request = $bootstrap->getResource('request');
        $memberController = $this->_app->getController('Xodx_MemberController');
        $groupController = $this->_app->getController('Xodx_GroupController');

        $user = $this->getUser();

        if ($user->getName() !== 'guest') {
            $personUri = $user->getPerson();

            $nsFoaf = 'http://xmlns.com/foaf/0.1/';

            $profileQuery = 'PREFIX foaf: <' . $nsFoaf . '>' . PHP_EOL;
            $profileQuery.= 'SELECT ?depiction ?name ?nick' . PHP_EOL;
            $profileQuery.= 'WHERE {' . PHP_EOL;
            $profileQuery.= '   <' . $personUri . '> a foaf:Person .' . PHP_EOL;
            $profileQuery.= '   OPTIONAL {<' . $personUri . '> foaf:depiction ?depiction .}' . PHP_EOL;
            $profileQuery.= '   OPTIONAL {<' . $personUri . '> foaf:name ?name .}' . PHP_EOL;
            $profileQuery.= '   OPTIONAL {<' . $personUri . '> foaf:nick ?nick .}' . PHP_EOL;
            $profileQuery.= '}';

            // TODO deal with language tags
            $contactsQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
            $contactsQuery.= 'SELECT ?contactUri ?name ?nick ' . PHP_EOL;
            $contactsQuery.= 'WHERE { ' . PHP_EOL;
            $contactsQuery.= '   <' . $personUri . '> foaf:knows ?contactUri . ' . PHP_EOL;
            $contactsQuery.= '   OPTIONAL {?contactUri foaf:name ?name .} ' . PHP_EOL;
            $contactsQuery.= '   OPTIONAL {?contactUri foaf:nick ?nick .} ' . PHP_EOL;
            $contactsQuery.= '}';

            $groupsQuery = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
            $groupsQuery.= 'SELECT ?groupUri ?name ?maker ' . PHP_EOL;
            $groupsQuery.= 'WHERE { ' . PHP_EOL;
            $groupsQuery.= '   <' . $personUri . '> foaf:member ?groupUri . ' . PHP_EOL;
            $groupsQuery.= '   OPTIONAL {?groupUri foaf:maker ?maker .} ' . PHP_EOL;
            $groupsQuery.= '   OPTIONAL {?groupUri foaf:name ?name .} ' . PHP_EOL;
            $groupsQuery.= '}';

            $profile = $model->sparqlQuery($profileQuery);

            if (count($profile) > 0) {
                $knows = $model->sparqlQuery($contactsQuery);
                $member = $model->sparqlQuery($groupsQuery);

                $activities = $this->getActivityStream($user);
                // Add to your own ActivityStream those belonging to groups you are subscribed to
                foreach ($member as $subscribedGroup) {
                    $activities = array_merge($activities, 
                            $memberController->getActivityStream($subscribedGroup['groupUri'])
                            );
                }                
                $activities = $this->sortActivities($activities);

                $nameHelper = new Xodx_NameHelper($this->_app);
                $groupController = $this->_app->getController('Xodx_GroupController');

                foreach ($activities as &$activity) {
                    $activity['personUri'] = $groupController->getPersonByAuthorUri($activity['authorUri']);
                    $activity['groupUri']  = $groupController->getGroupByAuthorUri($activity['authorUri']);
                    $activity['personName'] = $nameHelper->getName($activity['personUri']);
                    if (!empty($activity['groupUri'])) {
                        $activity['groupName'] = $nameHelper->getName($activity['groupUri']);
                    }
                }

                $factory = new Xodx_NotificationFactory($this->_app);
                $notifications = $factory->getForUser($user->getUri(), false);

                $template->profileshowPersonUri = $personUri;
                $template->profileshowDepiction = $profile[0]['depiction'];
                $template->profileshowName = $profile[0]['name'];
                $template->profileshowNick = $profile[0]['nick'];
                $template->profileshowActivities = $activities;
                $template->profileshowKnows = $knows;
                $template->profileshowNews = $notifications;
                $template->profileshowMember = $member;
            }
        }
        return $template;
    }

    /**
     * Sorts the given array of activities in order of their publishing dates
     * @param  Array $activities Array of activities
     * @return Array $activities Sorted array of activities
     */
    public function sortActivities ($activities)
    {
        $tmp = Array();
        foreach($activities as &$act) {
            $tmp[] = &$act["pubDate"];
        }
        array_multisort($tmp, SORT_DESC, $activities);

        return $activities;
    }


    public function getPersonUriAction ($template)
    {
        $personUri = $this->getUser()->getPerson();

        $template->disableLayout();
        $template->setRawContent($personUri);

        return $template;
    }

    public function getActivityStreamAction ($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $activities = $this->getActivityStream();
        $request = $bootstrap->getResource('request');

        $num = $request->getValue('num');
        $own = $request->getValue('own');

        if ($own === null || $own == 'true') {
            $own = true;
        } else if ($own == 'false') {
            $own = false;
            $personUri = $this->getUser()->getPerson();
        }

        $stream = '';
        foreach ($activities as $activity) {
            if (!$own && $activity['authorUri'] == $personUri) {
                continue;
            }
            if ($num !== null) {
                if ($num < 1) {
                    break;
                } else {
                    $num--;
                }
            }
            $stream .= $activity['uri'] . PHP_EOL;
        }

        $template->disableLayout();
        $template->setRawContent($stream);
        return $template;
    }

    /**
     *
     * Enter description here ...
     * @param URI $subscriberUri
     * @param URI $resourceUri
     * @param URI $feedUri
     * @param boolean $local specifies if the feed should not be subscribed at the hub
     *                       (this is meant for local resources)
     */
    public function subscribeToResource ($subscriberUri, $resourceUri, $feedUri = null, $local = false)
    {
        $bootstrap = $this->_app->getBootstrap();

        $model = $bootstrap->getResource('model');

        if ($feedUri === null) {
            $feedUri = $this->getActivityFeedUri($resourceUri);
        }

        $feedObject = array(
            'type' => 'uri',
            'value' => $feedUri
        );

        $nsDssn = 'http://purl.org/net/dssn/';
        $model->addStatement($resourceUri, $nsDssn . 'activityFeed', $feedObject);

        $this->_subscribeToFeed($subscriberUri, $feedUri, $local);

    }

    /**
     * This method subscribes a user to a feed
     * @param $userUri the uri of the user who wants to be subscribed
     * @param $feedUri the uri of the feed where she wants to subscribe
     */
    private function _subscribeToFeed ($subscriberUri, $feedUri, $local = false)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $resourceController = $this->_app->getController('Xodx_ResourceController');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $type = $resourceController->getType($subscriberUri);

        if ($type === $nsFoaf . 'Person') {
            $subscriberUri = $this->getUserUri($subscriberUri);
        }

        $logger->info('subscribeToFeed: user: ' . $subscriberUri . ', feed: ' . $feedUri);

        if (!$this->_isSubscribed($subscriberUri, $feedUri)) {
            $pushController = $this->_app->getController('Xodx_PushController');
            if ($local || $pushController->subscribe($feedUri)) {

                $model    = $bootstrap->getResource('model');

                $nsDssn = 'http://purl.org/net/dssn/';
                $nsRdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

                $subUri = $this->_app->getBaseUri() . '&c=ressource&id=' . md5(uniqid(rand(), true));
                $cbUri  = $this->_app->getBaseUri() . '?c=push&a=callback';

                $subscription = array(
                    $subUri => array(
                        $nsRdf . 'type' => array(
                            array('type' => 'uri', 'value' => $nsDssn . 'Subscription')
                        ),
                        $nsDssn . 'subscriptionCallback' => array(
                            array('type' => 'uri', 'value' => $cbUri)
                        ),
                        $nsDssn . 'subscriptionTopic' => array(
                            array('type' => 'uri', 'value' => $feedUri)
                        )
                    )
                );

                if (!$local) {
                    $feed = DSSN_Activity_Feed_Factory::newFromUrl($feedUri);

                    $subscription[$subUri][$nsDssn . 'subscriptionHub'][] = array(
                        'type' => 'uri', 'value' => $feed->getLinkHub()
                    );
                }

                $subscribeStatement = array(
                    $subscriberUri => array(
                        $nsDssn . 'subscribedTo' => array(
                            array('type' => 'uri', 'value' => $subUri)
                        )
                    )
                );

                $model->addMultipleStatements($subscription);
                $model->addMultipleStatements($subscribeStatement);
            }
        }
    }

    /**
     * This method creates a new object of the class Xodx_User
     * @param $userUri a string which contains the URI of the required user
     * @return Xodx_User instance with the specified URI
     */
    public function getUser ($userUri = null)
    {
        if ($userUri === null) {
            $applicationController = $this->_app->getController('Xodx_ApplicationController');
            $userId = $applicationController->getUser();
            $userUri = $this->_app->getBaseUri() . '?c=user&id=' . $userId;
        }

        if (!isset($this->_users[$userUri])) {

            $bootstrap = $this->_app->getBootstrap();
            $model = $bootstrap->getResource('model');

            $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
            $query.= 'SELECT ?name ?person' . PHP_EOL;
            $query.= 'WHERE {' . PHP_EOL;
            $query.= '  <' . $userUri . '> foaf:accountName ?name ;' . PHP_EOL;
            $query.= '      sioc:account_of ?person .' . PHP_EOL;
            $query.= '}' . PHP_EOL;

            $result = $model->sparqlQuery($query);
            if (count($result) > 0) {
                $userId = $result[0]['name'];
                $personUri = $result[0]['person'];
            } else {
                // This case is needed because ne guest account exists but it should throw an
                // Exception if this issue is cleared
                if (!isset($userId)) {
                    $userId = 'unkown';
                }
                $personUri = null;
            }

            $user = new Xodx_User($userUri);
            $user->setName($userId);
            $user->setPerson($personUri);

            $this->_users[$userUri] = $user;
        }

        return $this->_users[$userUri];
    }

    /**
     * Get the userAccount of a Person
     * @param string $personUri the uri of the person
     * @return Xodx_User for the given person
     */
    public function getUserForPerson ($personUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        // SPARQL-Query
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
        $query.= 'SELECT  ?userUri ' . PHP_EOL;
        $query.= 'WHERE {' . PHP_EOL;
        $query.= '   <' . $personUri . '> foaf:account ?userUri. ' . PHP_EOL;
        $query.= '}' . PHP_EOL;

        $userResult = $model->sparqlQuery($query);

        if (count($userResult[0])>0) {
            return $this->getUser($userResult[0]['userUri']);
        } else {
            return null;
        }
    }

    /**
     * Returns the user uri of a user which is accosiated to the given person
     * @deprecated use getUserForPerson
     */
    public function getUserUri ($personUri)
    {
        $user = $this->getUserForPerson($personUri);
        if ($user !== null) {
            return $user->getUri();
        } else {
            return null;
        }
    }

    /**
     * This function verifies the given credentials for a user
     * @param $userName a string with the username of the user
     * @param $password a string containing the password of the given user
     */
    public function verifyPasswordCredentials ($userName, $password)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        // TODO prevent sparql injection

        $query = '' .
            'PREFIX ow: <http://ns.ontowiki.net/SysOnt/> ' .
            'PREFIX sioc: <http://rdfs.org/sioc/ns#> ' .
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' .
            'SELECT ?userUri ?passwordHash ' .
            'WHERE { ' .
            '   ?userUri a sioc:UserAccount ; ' .
            '       foaf:accountName "' . $userName . '" ; ' .
            '       ow:userPassword ?passwordHash . ' .
            '}';
        $passwordQueryResult = $model->sparqlQuery($query);

        if (count($passwordQueryResult) > 0) {
            $passwordHash = $passwordQueryResult[0]['passwordHash'];
            return password_verify($password, $passwordHash);
        } else {
            return false;
        }
    }

    /**
     * Check if a user is already subscribed to a feed
     * @param $userUri the uri of the user in question
     * @param $feedUri the uri of the feed in question
     */
    private function _isSubscribed ($userUri, $feedUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        $query = 'PREFIX dssn: <http://purl.org/net/dssn/> ' . PHP_EOL;
        $query.= 'ASK  ' . PHP_EOL;
        $query.= 'WHERE { ' . PHP_EOL;
        $query.= '   <' . $userUri . '> dssn:subscribedTo      ?subUri. ' . PHP_EOL;
        $query.= '        ?subUri       dssn:subscriptionTopic <' . $feedUri. '> . ' . PHP_EOL;
        $query.= '}' . PHP_EOL;
        $subscribedResult = $model->sparqlQuery($query);

        return count($subscribedResult);
        // @todo ask Natanael about this 

        if (count($subscribedResult) > 0) {
            if (is_array($subscribedResult)) {
                // Erfurt problem
                return empty($subscribedResult[0]['__ask_retval']);
            } else if (is_bool($subscribedResult)) {
                return $subscriptionResult;
            } else {
                $logger = $bootstrap->getResource('logger');
                $logger->info('isSubscribed: user: ' . $userUri . ', feed: ' . $feedUri . '. ASK Query returned unexpectedly: ' . var_export($subscriptionResult));

                throw new Exception('Erfurt returned an unexpected result to the ask query.');
            }
        }
        return null;
    }

    /**
     *
     * Method returns all activities the user is subscribed to
     * @param Xodx_User $user
     * @return array of activities
     */
    public function getActivityStream (Xodx_User $user = null)
    {
        if ($user === null) {
            $user = $this->getUser();
        }

        $subscribedResources = $this->getSubscribedResources($user);

        $activityController = $this->_app->getController('Xodx_ActivityController');
        $activities = array();

        foreach ($subscribedResources as $resourceUri) {
            $act = $activityController->getActivities($resourceUri);
            $activities = array_merge($activities, $act);
        }
        $tmp = Array();
        foreach($activities as &$act) {
            $tmp[] = &$act["pubDate"];
        }
        array_multisort($tmp, SORT_DESC, $activities);

        return $activities;
    }

    /**
     * Find all resources a user is subscribed to via Activity Feed
     * @param $userUri the uri of the user in question
     * @return array $subscribedResources all resource a user is subscribed to
     */
    public function getSubscribedResources (Xodx_User $user)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');
        $userUri = $user->getUri();

        // SPARQL-Query
        $query = 'PREFIX dssn: <http://purl.org/net/dssn/> ' . PHP_EOL;
        $query.= 'SELECT  DISTINCT ?resUri' . PHP_EOL;
        $query.= 'WHERE {' . PHP_EOL;
        $query.= '   <' . $userUri . '> dssn:subscribedTo        ?subUri. ' . PHP_EOL;
        $query.= '   ?subUri            dssn:subscriptionTopic   ?feedUri. ' . PHP_EOL;
        $query.= '   ?resUri            dssn:activityFeed   ?feedUri. ' . PHP_EOL;
        $query.= '}' . PHP_EOL;

        $result = $model->sparqlQuery($query);

        $subscribedResources = array();

        // results in array
        foreach ($result as $resource) {
            if (isset($resource['resUri'])) {
                $subscribedResources[] = $resource['resUri'];
            }
        }

        return $subscribedResources;
    }

/**
 * Unsubscribes a user from a resource
 * 
 * @param URI $unsubscriberUri Uri of the person who wants to unsubscribe from a resource
 * @param URI $resourceUri Uri of the resource that ist to be unsubscribed
 * @param URI $feedUri Feed of the given resource
 * @param boolean $local Indicates whether the resource is stored locally
 */
public function unsubscribeFromResource ($unsubscriberUri, $resourceUri, $feedUri = null, $local = false) 
{           

    // getResources & set namespaces
    $bootstrap = $this->_app->getBootstrap();      

    // Get Uri of friend's feed (if not given)
    if ($feedUri === null) {
        $feedUri = $this->getActivityFeedUri($resourceUri);
    }

    $this->_unsubscribeFromFeed($unsubscriberUri, $feedUri, $local);
}

/**
 * Unsubscribes a user from a feed (he is subscribed to)
 * 
 * @param URI $unsubscriberUri Uri of the person who wants to unsubscribe from a feed
 * @param URI $feedUri Uri of the feed that ist to be unsubscribed
 * @param boolean $local $local Indicates whether the feed is stored locally
 */
protected function _unsubscribeFromFeed ($unsubscriberUri, $feedUri, $local = false) 
{       

    // getResources & set namespaces
    $bootstrap = $this->_app->getBootstrap();
    $logger = $bootstrap->getResource('logger');
    $resourceController = $this->_app->getController('Xodx_ResourceController');        
    $nsFoaf = 'http://xmlns.com/foaf/0.1/';

    // getUserUri of unsubscriber (if not already given)
    $type = $resourceController->getType($unsubscriberUri);                
    if ($type === $nsFoaf . 'Person') {
        $unsubscriberUri = $this->getUserUri($unsubscriberUri);
    }

    // Logging
    $logger->info('unsubscribeFromFeed: user: ' . $unsubscriberUri . ', feed: ' . $feedUri);

    // unsubscribe from feed given by $feedUri

    if ($this->_isSubscribed($unsubscriberUri, $feedUri)) {            
        // getResources
        $pushController = $this->_app->getController('Xodx_PushController');

        if ($local || $pushController->unsubscribe($feedUri)) { // this unsubscribes the user from the feed                               

            // getResources & set namespaces
            $model = $bootstrap->getResource('model');              
            $nsDssn = 'http://purl.org/net/dssn/';
            $nsRdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

            /*
             * get $subUri from DB
             * $subUri is a randomly created Uri used to identify the connection to the feed (see _subscribeToFeed)
             */
            $subUriQuery  = 'PREFIX dssn: <' . $nsDssn . '>' . PHP_EOL;
            $subUriQuery .= 'SELECT ?subUri' . PHP_EOL;
            $subUriQuery .= 'WHERE {' . PHP_EOL;
            $subUriQuery .= '?subUri dssn:subscriptionTopic <' . $feedUri . '> .' . PHP_EOL;
            $subUriQuery .= '}';
            // execute Query and extract $subUri
            $result = $model->sparqlQuery($subUriQuery);
            if (count($result) > 0) {
                $subUri = $result[0]['subUri'];
            } else {
                throw Exception('Could not find subUri');
            }                
            $cbUri = $this->_app->getBaseUri() . 'c=push&a=callback';

            /*
             * delete the following tripels:
             * ($subUri, type, Subscription)
             * ($subUri, subscriptionCallback, $cbUri)
             * ($subUri, subscriptionTopic, $feedUri)
             */
            $subscriptionStatementsArray = array(
                $subUri => array(
                    $nsRdf . 'type' => array(
                        array('type' => 'uri', 'value' => $nsDssn . 'Subscription')
                    ),
                    $nsDssn . 'subscriptionCallback' => array(
                        array('type' => 'uri', 'value' => $cbUri)
                    ),
                    $nsDssn . 'subscriptionTopic' => array(
                        array('type' => 'uri', 'value' => $feedUri)
                    )
                )                    
            );

            if (!$local) {
                $feed = DSSN_Activity_Feed_Factory::newFromUrl($feedUri);       
                /* delete the following tripels:
                 * ($subUri, subscriptionHub, $feed->getLinkHub() )
                 * ($unsubscriberUri, subscribedTo, $subUri)
                 */
                $subscriptionStatementsArray[$subUri][$nsDssn . 'subscriptionHub'][] = array(
                    'type'  => 'uri', 
                    'value' => $feed->getLinkHub()                        
                );
            }
            $subscribeStatementArray = array(
                $unsubscriberUri => array (                 
                    $nsDssn . 'subscribedTo' => array (     
                        array(                              
                            'type' => 'uri',
                            'value' => $subUri
                        )
                    )
                )
            );
                $model->deleteMultipleStatements($subscriptionStatementsArray);
                $model->deleteMultipleStatements($subscribeStatementArray);                
        }
    }
}

/**
 * 
 * @param type $template
 */
public function addmemberAction($template) {
    $bootstrap = $this->_app->getBootstrap();
    $request = $bootstrap->getResource('request');
    $pushController = $this->_app->getController('Xodx_PushController');

    $groupUri = $request->getValue('groupUri', 'post');
    $personUri = $request->getValue('personUri', 'post');

    $formError = array();

    if (empty($groupUri) || !Erfurt_Uri::check($groupUri)) {
        $formError['groupUri'] = true;
    }

    if (empty($personUri) || !Erfurt_Uri::check($personUri)) {
        $formError['personUri'] = true;
    }

    if (count($formError) <= 0) {
        $pos = strpos($resourceUri, '?c=');
        $baseUri = substr($resourceUri, 0, $pos);
        $feedUri = $baseUri . '?c=feed&a=getFeed&uri=' .
                urlencode($personUri) . '&groupUri=' . urlencode($groupUri);
        $pushController->subscribe($feedUri);
        $template->disableLayout();
        $template->setRawContent('success');
    } else {
        $template->formError = $formError;
        $template->disableLayout();
        $template->setRawContent('fail');
    }
}

/**
 * 
 * @param type $template
 */
public function deletememberAction($template) {
    $bootstrap = $this->_app->getBootstrap();
    $request = $bootstrap->getResource('request');
    $pushController = $this->_app->getController('Xodx_PushController');

    $groupUri = $request->getValue('groupUri', 'post');
    $personUri = $request->getValue('personUri', 'post');

    $formError = array();

    if (empty($groupUri) || !Erfurt_Uri::check($groupUri)) {
        $formError['groupUri'] = true;
    }

    if (empty($personUri) || !Erfurt_Uri::check($personUri)) {
        $formError['personUri'] = true;
    }

    if (count($formError) <= 0) {
        $pos = strpos($resourceUri, '?c=');
        $baseUri = substr($resourceUri, 0, $pos);
        $feedUri = $baseUri . '?c=feed&a=getFeed&uri=' .
                urlencode($personUri) . '&groupUri=' . urlencode($groupUri);
        $pushController->unsubscribe($feedUri);
        $template->disableLayout();
        $template->setRawContent('success');
    } else {
        $template->formError = $formError;
        $template->disableLayout();
        $template->setRawContent('fail');
    }
}

}
