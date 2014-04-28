<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Description of MemberController
 *
 * @author Stephan
 */
class Xodx_MemberController extends Xodx_ResourceController
{

    /**
     * A view action for adding a new member.
     * 
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function addmemberAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $groupUri = $request->getValue('group', 'post');
        $personUri = $request->getValue('person', 'post');
        echo 'test';
        $personUri = 'http://127.0.0.1:8080/?c=person&id=test7';
        $groupUri = 'http://127.0.0.1:8080/?c=Group&id=gtest7';
        $this->addMember($personUri, $groupUri);
        echo 'test1';
        $location = new Saft_Url($this->_app->getBaseUri());
        $location->setParameter('c', 'groupprofile');
        $location->setParameter('a', 'list');

        $template->redirect($location);

        return $template;
    }

    /**
     * A view action for deleting an existing member.
     * 
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function deletememberAction($template)
    {
        $bootstrap = $this->_app->getBootstrap();
        $request = $bootstrap->getResource('request');

        $groupUri = $request->getValue('group', 'post');
        $personUri = $request->getValue('person', 'post');

        $personUri = 'http://127.0.0.1:8080/?c=person&id=test7';
        $groupUri = 'http://127.0.0.1:8080/?c=Group&id=gtest7';
        $this->deleteMember($personUri, $groupUri);

        $location = new Saft_Url($this->_app->getBaseUri());
        $location->setParameter('c', 'groupprofile');
        $location->setParameter('a', 'list');

        $template->redirect($location);

        return $template;
    }

    /**
     * This adds a new member to a specified group.
     * 
     * @param URI $personUri Uri of the new member
     * @param URI $groupUri Uri of the group
     * @throws Exception if personUri not found
     */
    public function addMember($personUri, $groupUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');

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

        // Send Ping to new friend
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($groupUri, $personUri, 'You joined this group.');

        // Subscribe to new friend
        //$userUri = $userController->getUserUri($personUri);
        $feedUri = $this->getActivityFeedUri($personUri);

        $logger->debug('FeedUri  ' . $feedUri . 'end');

        if ($feedUri !== null) {
            $logger->debug('MemberController/addMember: Found feed for newly added member ("' . $personUri . '"): "' . $feedUri . '"');
            $this->subscribeToResource ($groupUri, $personUri, $feedUri);
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
    public function deleteMember($personUri, $groupUri)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $model  = $bootstrap->getResource('model');

        $ldHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
        if (!$ldHelper->resourceDescriptionExists($personUri)) {
            throw new Exception('The WebID of your friend does not exist.');
        }

        // Update WebID
        // delete Statement added by addMember ($groupUri, member, $personUri)
        $model->deleteStatement($groupUri, 'http://xmlns.com/foaf/0.1/member', array('type' => 'uri', 'value' => $personUri));

        $nsAair = 'http://xmlns.notu.be/aair#';

        // Send Ping to new friend
        $pingbackController = $this->_app->getController('Xodx_PingbackController');
        $pingbackController->sendPing($groupUri, $personUri, 'You left this group.');

        // Subscribe to new friend
        $feedUri = $this->getActivityFeedUri($personUri);

        if ($feedUri !== null) {
            $logger->debug('MemberController/deletemember: Found feed of member ("' . $personUri . '"): "' . $feedUri . '"');
            $this->unsubscribeFromResource ($groupUri, $personUri, $feedUri);
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
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();      

        // Get Uri of resource's feed (if not given)
        if ($feedUri === null) {
            $feedUri = $this->getActivityFeedUri($resourceUri);
        }
        $this->_unsubscribeFromFeed($unsubscriberUri, $feedUri, $local);
    }

     /**
     * This method subscribes a group to a feed
     * @param URI $unscriberUri the uri of the group which wants to be subscribed
     * @param URI $feedUri the uri of the feed where the group wants to subscribe
     */
    private function _subscribeToFeed ($subscriberUri, $feedUri, $local = false)
    {
        $bootstrap = $this->_app->getBootstrap();
        $logger = $bootstrap->getResource('logger');
        $resourceController = $this->_app->getController('Xodx_ResourceController');
        $groupController = $this->_app->getController('Xodx_GroupController');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $type = $resourceController->getType($subscriberUri);
        
        $logger->debug('GroupUri: ' . $subscriberUri);
        
        if ($type === $nsFoaf . 'Group') {
            $subscriberUri = $groupController->getGroup($subscriberUri)->getUri();
        }
        $logger->debug('GroupUri2: ' . $subscriberUri);
        
        $logger->info('subscribeToFeed: group: ' . $subscriberUri . ', feed: ' . $feedUri);

        if (!$this->_isSubscribed($subscriberUri, $feedUri)) {
            $pushController = $this->_app->getController('Xodx_PushController');
            if ($local || $pushController->subscribe($feedUri)) {

                $model    = $bootstrap->getResource('model');

                $nsDssn = 'http://purl.org/net/dssn/';
                $nsRdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

                $subUri = $this->_app->getBaseUri() . '&c=ressource&id=' . md5(rand());
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
     * @param URI $subsciberUri the uri of the subscriber who wants to unsubscribe
     * @param URI $feedUri the uri of the feed where the group wants to unsubscribe
     * @param boolean $local Indicates whether the feed is stored locally
     */
    private function _unsubscribeFromFeed ($unsubscriberUri, $feedUri, $local = false)
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
     * @param $groupUri the uri of the user in question
     * @param $feedUri the uri of the feed in question
     */
    private function _isSubscribed ($groupUri, $feedUri)
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

}
