<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once(__DIR__ . '/../../../../libraries/Saft/Controller.php');
require_once(__DIR__ . '/../../../../classes/Xodx/ResourceController.php');
require_once(__DIR__ . '/../../../../classes/Xodx/PersonController.php');
require_once (__DIR__ . '/../../Fixtures/libraries/Saft/ApplicationDummy.php');
require_once (__DIR__ . '/../../Fixtures/libraries/Saft/LayoutDummy.php');
/**
 * requirements for deleteFriendAction()
 * the original classes are reqiured (dummy does not work)
 */
require_once (__DIR__ . '/../../../../libraries/Erfurt/library/Erfurt/Uri.php');
require_once (__DIR__ . '/../../../../libraries/Saft/Url.php');
require_once (__DIR__ . '/../../../../libraries/Saft/Request.php');
/**
 * This class tests \classes\Xodx\PersonController.php
 * @author Stephan
 */
class Xodx_PersonControllerTest extends PHPUnit_Framework_Testcase
{
    /**
     * This is a valid Uri of a person.
     * @var UriString
     */
    protected $validPersonUri = 'validPersonUri';
    /**
     * A valid contactUri for deleteFriend()
     * @var UriString
     */
    protected $validResourceUri = 'validResourceUri';
    /**
     * An application for testing purposes.
     * @var \Fixtures\classes\Xodx\ApplicationDummy
     */
    protected $app = NULL;
    /**
     * The PersonController loaded in initFixture.
     * (A proxyclass which contains some small classchanges for testing.)
     * @var \PersonController(Proxy)
     */
    protected $personController = NULL;
    /**
     *
     * @var \Fixtures\libraries\Saft\LayoutDummy
     */
    protected $template = NULL;
    /**
     * Creates an Application- and PersonControllerDummy
     * @param $proxy  true for PersonControllerProxy false for normal PersonController
     */
    public function initFixture($proxy = FALSE, $testMethod = null)
    {
        $this->app = new ApplicationDummy(TRUE);
        $this->app->testMethod = $testMethod;
        if ($proxy) {
            $this->personController = new Xodx_PersonControllerProxy($this->app);
        } else { //proxy == false, standard
            $this->personController = new Xodx_PersonController($this->app);
        }
    }
    /**
     * Test: A view action to show a person
     * @covers PersonController::showAction ()
     */
    public function testShowAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * @covers PersonController::rdfAction ()
     */
    public function testRdfAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: View action for adding a new friend. (This action should be called from a form)
     * @covers PersonController::addFriendAction ()
     */
    public function testAddfriendAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: View action for deleting a new friend. (This action should be called from a form)
     * @todo Test returned template.
     * @covers PersonController::deleteFriendAction ()
     */
    public function testDeleteFriendAction()
    {
        $this->initFixture(FALSE, 'testDeleteFriendAction');
        $this->template = new LayoutDummy($this->app);
        $this->personController->deleteFriendAction($this->template);
    }
    /**
     * Test: Get a DSSN_Foaf_Person object representing the specified person
     * @covers PersonController::getPerson ()
     */
    public function testGetPerson ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This method gets the userAccount responsible for a given person.
     * @covers PersonController::getUserForPerson ()
     */
    public function testGetUserForPerson ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Add a new contact to the list of freinds of a person
     *  This is a one-way connection, the contact doesn't has to approve it
     * @covers PersonController::addFriend ()
     */
    public function testAddFriend ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Delete an old contact out of the list of freinds of a person
     *  This is a one-way connection, the contact doesn't has to approve it
     * @covers PersonController::deleteFriend ()
     */
    public function testDeleteFriend ()
    {
        $this->initFixture(TRUE, 'testDeleteFriend');
        $this->personController->deleteFriend($this->validPersonUri, $this->validResourceUri);
    }
    /**
     * Test: Returns the feed of the specified $type of the person
     * @covers PersonController::getFeed ()
     */
    public function testGetFeed ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Get an array of new notifications for the person
     * @covers PersonController::getNotifications ()
     */
    public function testGetNotifications ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
}
/**
 * A proxyclass for \classes\Xodx\PersonController.
 * @author Stephan
 */
class Xodx_PersonControllerProxy extends Xodx_PersonController
{
     /**
     *
     * methods looks up a contact to get the Uri of the activity feed
     * and returns it if succesfull
     * @param $resourceUri - the URI of the ressource to be looked up
     */
    public function getActivityFeedUri($resourceUri)
    {
        if ($resourceUri == 'validResourceUri') {
            return 'validFeedUri';
        } else {
            return 'invalidFeedUri';
        }
    }
}
