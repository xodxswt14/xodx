<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Manages Interaction between group and member
 *
 * @author Stephan Kemper
 * @author Lukas Werner
 */
class Xodx_MemberController extends Xodx_ResourceController
{
    /**
     * API Action for getting group subsribe to person
     * 
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     * @todo more security needed, change hardcoded 'success' and 'fail'
     * @deprecated should be implemented with semantic pingback
     */
    public function addmemberAction ($template) {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

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
            $this->addMember($personUri, $groupUri);
            $template->disableLayout();
            $template->setRawContent('success');
        } else {
            $template->formError = $formError;
            $template->disableLayout();
            $template->setRawContent('fail');
        }

        return $template;
    }

    /**
     * API Action for getting group unsubscribe from person
     * 
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     * @todo more security needed, change hardcoded 'success' and 'fail'
     * @deprecated should be implemented with semantic pingback
     */
    public function deletememberAction ($template) {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

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
            $this->deleteMember($personUri, $groupUri);
            $this->deleteMemberActiviies($personUri, $groupUri);
            $template->disableLayout();
            $template->setRawContent('success');
        } else {
            $template->formError = $formError;
            $template->disableLayout();
            $template->setRawContent('fail');
        }

        return $template;
    }

    /**
     * This adds a new member to a specified group.
     * 
     * @param URI $personUri Uri of the new member
     * @param URI $groupUri Uri of the group
     * @throws Exception if personUri not found
     */
    public function addMember ($personUri, $groupUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');
        $nameHelper = new Xodx_NameHelper($this->_app);

        $ldHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
        if (!$ldHelper->resourceDescriptionExists($personUri)) {
            throw new Exception('The WebID of your friend does not exist.');
        }

        // Update WebID
        $model->addStatement($groupUri, 'http://xmlns.com/foaf/0.1/member', array('type' => 'uri', 'value' => $personUri));

        $nsAair = 'http://xmlns.notu.be/aair#';
        $activityController = $this->_app->getController('Xodx_ActivityController');

        // Add Activity to activity Stream
        $object = array(
            'type' => 'uri',
            'content' => $personUri,
            'replyObject' => 'false'
        );
        $activityController->addActivity($groupUri, $nsAair . 'StartFollowing', $object);

        // Send Ping to new member
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($groupUri, $personUri, 'You joined this group.');

        // Subscribe to new member
        $baseUri = $nameHelper->getBaseUriByResourceUri($personUri);
        $feedUri = $baseUri .  '?c=feed&a=getFeed&uri=' .
                urlencode($personUri) . '&groupUri=' . urlencode($groupUri);

        //$personUri extended to identify an actor for every grouppost
        if ($feedUri !== null) {
            $logger->debug('MemberController/addMember: Found feed for newly added member ("' . $personUri . '"): "' . $feedUri . '"');
            $this->subscribeToResource ($groupUri, $personUri . $groupUri, $feedUri);
        } else {
            $logger->error('MemberController/addMember: Couldn\'t find feed for newly added member ("' . $personUri . '").');
        }
   }

    /**
     * This removes a member of a specified group.
     * 
     * @param URI $personUri Uri of the existing member
     * @param URI $groupUri Uri of the group
     * @throws Exception if personUri not found
     */
    public function deleteMember ($personUri, $groupUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');
        $nameHelper = new Xodx_NameHelper($this->_app);

        $ldHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
        if (!$ldHelper->resourceDescriptionExists($personUri)) {
            throw new Exception('The WebID of your friend does not exist.');
        }

        // Update WebID
        // delete Statement added by addMember ($groupUri, member, $personUri)
        $model->deleteStatement($groupUri, 'http://xmlns.com/foaf/0.1/member', array('type' => 'uri', 'value' => $personUri));

        // Send Ping to member
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($groupUri, $personUri, 'You left this group.');

        // Unsubscribe from member
        $baseUri = $nameHelper->getBaseUriByResourceUri($personUri);
        $feedUri = $baseUri .  '?c=feed&a=getFeed&uri=' .
                urlencode($personUri) . '&groupUri=' . urlencode($groupUri);

        if ($feedUri !== null) {
            $logger->debug('MemberController/deletemember: Found feed of member ("' . $personUri . '"): "' . $feedUri . '"');
            $this->unsubscribeFromResource ($groupUri, $personUri. $groupUri, $feedUri);
        } else {
            $logger->error('MemberController/deleteMember: Couldn\'t find feed of member ("' . $personUri . '").');
        }
    }

   /**
    * Subscribes a user to a resource
    * 
    * @param URI $unsubscriberUri Uri of the group which wants to subscribe from a resource
    * @param URI $resourceUri Uri of the resource that ist to be subscribed
    * @param URI $feedUri Feed of the given resource
    * @param boolean $local Indicates whether the resource is stored locally
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
    * Unsubscribes a user from a resource
    * 
    * @param URI $unsubscriberUri Uri of the group which wants to unsubscribe from a resource
    * @param URI $resourceUri Uri of the resource that ist to be unsubscribed
    * @param URI $feedUri Feed of the given resource
    * @param boolean $local Indicates whether the resource is stored locally
    */
    public function unsubscribeFromResource ($unsubscriberUri, $resourceUri, $feedUri = null, $local = false) 
    {             
        // Get Uri of resource's feed (if not given)
        if ($feedUri === null) {
            $feedUri = $this->getActivityFeedUri($resourceUri);
        }
        $this->_unsubscribeFromFeed($unsubscriberUri, $feedUri, $local);
    }

     /**
     * This method subscribes a group to a feed
      * 
     * @param URI $unscriberUri the uri of the group which wants to be subscribed
     * @param URI $feedUri the uri of the feed where the group wants to subscribe
     */
    protected function _subscribeToFeed ($subscriberUri, $feedUri, $local = false)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $resourceController = $this->_app->getController('Xodx_ResourceController');
        $groupController = $this->_app->getController('Xodx_GroupController');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $type = $resourceController->getType($subscriberUri);

        if ($type === $nsFoaf . 'Group') {
            $subscriberUri = $groupController->getGroup($subscriberUri)->getUri();
        }

        $logger->info('subscribeToFeed: group: ' . $subscriberUri . ', feed: ' . $feedUri);

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
     * This method unsubscribes a group from a feed
      * 
     * @param URI $subsciberUri the uri of the subscriber who wants to unsubscribe
     * @param URI $feedUri the uri of the feed where the group wants to unsubscribe
     * @param boolean $local Indicates whether the feed is stored locally
     */
    protected function _unsubscribeFromFeed ($unsubscriberUri, $feedUri, $local = false)
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $resourceController = $this->_app->getController('Xodx_ResourceController');
        $groupController = $this->_app->getController('Xodx_GroupController');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $type = $resourceController->getType($unsubscriberUri);

        if ($type === $nsFoaf . 'Group') {
            $unsubscriberUri = $groupController->getGroup($unsubscriberUri)->getUri();
        }

        $logger->info('unsubscribeFromFeed: group: ' . $unsubscriberUri . ', feed: ' . $feedUri);

        if ($this->_isSubscribed($unsubscriberUri, $feedUri)) {
            $pushController = $this->_app->getController('Xodx_PushController');
            if ($local || $pushController->unsubscribe($feedUri)) {

                $model    = $bootstrap->getResource('model');

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
     * Check if a group is already subscribed to a feed
     * 
     * @param $groupUri the uri of the user in question
     * @param $feedUri the uri of the feed in question
     */
    protected function _isSubscribed ($groupUri, $feedUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        $query = 'PREFIX dssn: <http://purl.org/net/dssn/> ' . PHP_EOL;
        $query.= 'ASK  ' . PHP_EOL;
        $query.= 'WHERE { ' . PHP_EOL;
        $query.= '   <' . $groupUri . '> dssn:subscribedTo      ?subUri. ' . PHP_EOL;
        $query.= '        ?subUri       dssn:subscriptionTopic <' . $feedUri. '> . ' . PHP_EOL;
        $query.= '}' . PHP_EOL;
        $subscribedResult = $model->sparqlQuery($query);

        return count($subscribedResult);
    }

    
    
     /**
     * Method returns all activities the group is subscribed to
     * 
     * @param Uri $groupUri Uri of the group
     * @return array of activities
     */
    public function getActivityStream ($groupUri)
    {
        $subscribedResources = $this->getSubscribedResources($groupUri);

        $activityController = $this->_app->getController('Xodx_ActivityController');
        $activities = array();

        foreach ($subscribedResources as $resourceUri) {
            $act = $activityController->getActivities($resourceUri);
            $activities = array_merge($activities, $act);
        }
        $tmp = Array();
        foreach ($activities as &$act) {
            $tmp[] = &$act["pubDate"];
        }
        array_multisort($tmp, SORT_DESC, $activities);

        return $activities;
    }

    /**
     * Find all resources a user is subscribed to via Activity Feed
     * @param $groupUri the uri of the group in question
     * @return array $subscribedResources all resource a group is subscribed to
     */
    public function getSubscribedResources ($groupUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        // SPARQL-Query
        $query = 'PREFIX dssn: <http://purl.org/net/dssn/> ' . PHP_EOL;
        $query.= 'SELECT  DISTINCT ?resUri' . PHP_EOL;
        $query.= 'WHERE {' . PHP_EOL;
        $query.= '   <' . $groupUri . '> dssn:subscribedTo        ?subUri. ' . PHP_EOL;
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
     * Deletes all activities where the activityActor is the member-groupUri
     * 
     * @param Uristring $memberUri Member whose activites are to be deleted
     * @param Uristring $groupUri The group where this activities were posted
     */
    public function deleteMemberActiviies($memberUri, $groupUri)
    {
        $activityController = $this->_app->getController('Xodx_ActivityController');
        $actorUri = $memberUri. $groupUri;
        $activities = $activityController->getActivities($groupUri, $memberUri);
       
         foreach ($activities as $activity) {
            $activityController->deleteActivity($activity, $actorUri);
         }
    }
}
