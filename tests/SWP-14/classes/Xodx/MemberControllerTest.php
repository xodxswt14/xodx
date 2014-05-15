<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once(__DIR__ . '/../../../../libraries/Saft/Controller.php');
require_once(__DIR__ . '/../../../../classes/Xodx/ResourceController.php');
require_once(__DIR__ . '/../../../../classes/Xodx/Group.php');
require_once(__DIR__ . '/../../../../classes/Xodx/MemberController.php');
require_once (__DIR__ . '/../../Fixtures/libraries/Saft/ApplicationDummy.php');
require_once(__DIR__ . '/../../Fixtures/libraries/lib-dssn-php/DSSN/Activity/Feed/FactoryDummy.php');
require_once(__DIR__ . '/../../../../libraries/lib-dssn-php/DSSN/Exception.php');
/**
 * This class tests \classes\Xodx\MemberController.php
 * @author Stephan Kemper
 */
class Xodx_MemberControllerTest extends PHPUnit_Framework_Testcase
{
    /**
     * This is a valid Uri of a group.
     * @var UriString
     */
    protected $validGroupUri = 'validGroupUri';
    /**
     * This is a valid Uri of a person.
     * @var UriString
     */
    protected $validPersonUri = 'validPersonUri';
    /**
     * This is a valid Uri of a feed.
     * @var UriString
     */
    protected $validFeedUri = 'validFeedUri';
    /**
     * An application for testing purposes.
     * @var \Fixtures\classes\Xodx\ApplicationDummy
     */
    protected $app = NULL;
    /**
     * The memberController loaded in initFixture.
     * (A proxyclass which contains some small classchanges for testing.)
     * @var \MemberController(Proxy)
     */
    protected $memberController = NULL;
    /**
     * Creates an Application- and MemberControllerDummy
     * @param $proxy  true for MemberControllerProxy false for normal MemberController
     */
    public function initFixture ($proxy = FALSE, $testMethod = null)
    {
        $this->app = new ApplicationDummy();
        $this->app->testMethod = $testMethod;
        if ($proxy) {
            $this->memberController = new Xodx_MemberControllerProxy($this->app);
        } else { //proxy == false, standard
            $this->memberController = new Xodx_MemberController($this->app);
        }
    }

    /**
     * @covers MemberController::addMember
     */
    public function testAddMember ()
    {
        $this->initFixture(TRUE, 'testAddMember');
        $this->memberController->addMember($this->validPersonUri,$this->validGroupUri);
    }
    /**
     * @covers MemberController::deleteMember
     */
    public function testDeleteMember ()
    {
        $this->initFixture(TRUE, 'testDeleteMember');
        $this->memberController->deleteMember($this->validPersonUri,$this->validGroupUri);
    }
    /**
     * @covers MemberController::_isSubscribed
     */
    public function testIsSubscribed()
    {
        $this->initFixture(TRUE, 'testIsSubscribed');
        $this->memberController->_isSubscribed ($this->validGroupUri, $this->validFeedUri);
    }
    /**
     * @covers MemberController::_unsubscribeFromFeed
     */
    public function testUnsubscribeFromFeed()
    {
        $this->initFixture(TRUE, 'testUnsubscribeFromFeed');
        $this->memberController->_unsubscribeFromFeed($this->validGroupUri, $this->validFeedUri);
    }
    /**
     * @covers MemberController::_subscribeToFeed
     */
    public function testsubscribeToFeed()
    {
        $this->initFixture(TRUE, 'testSubscribeToFeed');
        $this->memberController->_subscribeToFeed($this->validGroupUri, $this->validFeedUri);
    }
    /**
     * @covers MemberController::getActivityStream
     */
    public function testGetActivityStream()
    {
        $this->initFixture(TRUE, 'testGetActivityStream');
        $group = new Xodx_Group($this->validGroupUri, $this->app);
        $this->memberController->getActivityStream($group);
    }    
    /**
     * @covers MemberController::getSubscribedResources
     */
    public function testGetSubscribedResources() {
        $this->initFixture(FALSE, 'testGetSubscribedResources');
        $group = new Xodx_Group($this->validGroupUri, $this->app);
        $this->memberController->getSubscribedResources($group);
    }
}
/**
 * A proxyclass for \classes\Xodx\MemberController.
 * @author Stephan
 */
class Xodx_MemberControllerProxy extends Xodx_MemberController
{
    /**
     * This is a Dummyclass used in add-/deleteMember
     * 
     * @param $resourceUri - the URI of the ressource to be looked up
     */
    public function getActivityFeedUri($resourceUri)
    {
        return 'validFeedUri';
    }
    /**
     * Check if a group is already subscribed to a feed
     * @param $groupUri the uri of the user in question
     * @param $feedUri the uri of the feed in question
     */
    public function _isSubscribed ($groupUri, $feedUri)
    {
        return parent::_isSubscribed ($groupUri, $feedUri);
    }
    /**
     * This method unsubscribes a group from a feed
     * @param URI $subsciberUri the uri of the subscriber who wants to unsubscribe
     * @param URI $feedUri the uri of the feed where the group wants to unsubscribe
     * @param boolean $local Indicates whether the feed is stored locally
     */
    public function _unsubscribeFromFeed ($unsubscriberUri, $feedUri, $local = false)
    {
        return parent::_unsubscribeFromFeed ($unsubscriberUri, $feedUri, $local = false);
    }
    /**
     * This method subscribes a group to a feed
     * @param URI $unscriberUri the uri of the group which wants to be subscribed
     * @param URI $feedUri the uri of the feed where the group wants to subscribe
     */
    public function _subscribeToFeed ($subscriberUri, $feedUri, $local = false)
    {
        return parent::_subscribeToFeed ($subscriberUri, $feedUri, $local = false);
    }
    /**
     * Find all resources a user is subscribed to via Activity Feed
     * @param $userUri the uri of the user in question
     * @return array $subscribedResources all resource a user is subscribed to
     */
    public function getSubscribedResources (Xodx_Group $group)
    {
        $resources = array('validResourceUri1', 'validResourceUri2');
        return $resources;
    }
}