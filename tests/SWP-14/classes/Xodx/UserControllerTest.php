<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once(__DIR__ . '/../../../../libraries/Saft/Controller.php');
require_once(__DIR__ . '/../../../../classes/Xodx/ResourceController.php');
require_once(__DIR__ . '/../../../../classes/Xodx/UserController.php');
require_once (__DIR__ . '/../../Fixtures/libraries/Saft/ApplicationDummy.php');
require_once(__DIR__ . '/../../../../libraries/lib-dssn-php/DSSN/Activity/Feed/Factory.php');
require_once(__DIR__ . '/../../../../libraries/lib-dssn-php/DSSN/Exception.php');
/**
 * This class tests \classes\Xodx\UserController.php
 * @author Stephan
 */
class Xodx_UserControllerTest extends PHPUnit_Framework_Testcase
{   
    /**
     * This is a valid Uri of a feed.
     * @var UriString
     */ 
    protected $validFeedUri = 'validFeedUri';
    /**
     * This is a invalid Uri of a feed.
     * @var UriString
     */
    protected $invalidFeedUri = 'invalidFeedUri';
    /**
     * This is a valid Uri of a unsubscriber.
     * @var UriString 
     */
    protected $validUnsubscriberUri = 'validUnsubscriberUri';
    /**
     * This is a invalid Uri of a unsubscriber.
     * @var UriString
     */
    protected $invalidUnsubscriberUri = 'invalidUnsubscriberUri';
    /**
     * This is a valid Uri of a resource.
     * @var UriString
     */
    protected $validResourceUri = 'validResourceUri';
    /**
     * This is a invalid Uri of a resource.
     * @var UriString
     */
    protected $invalidResourceUri = 'invalidResourceUri';
    /**
     * An application for testing purposes.
     * @var \Fixtures\classes\Xodx\ApplicationDummy
     */
    protected $app = NULL;
    /**
     * The UserController loaded in initFixture.
     * (A proxyclass which contains some small classchanges for testing.)
     * @var \UserController(Proxy)
     */
    protected $userController = NULL;
    /**
     * Creates an Application- and UserControllerDummy
     * @param $proxy  true for UserControllerProxy false for normal UserController
     */
    public function initFixture($proxy = FALSE, $testMethod = null)
    {
        $this->app = new ApplicationDummy();
        $this->app->testMethod = $testMethod;
        if ($proxy) {
            $this->userController = new Xodx_UserControllerProxy($this->app);
        } else { //proxy == false, standard
            $this->userController = new Xodx_UserController($this->app);
        }
        $this->setBaseUriFixture();
    }
    /**
     * Sets the BaseUri of $this->app
     * @param type $valid true vor valid, false for invalid
     */
    public function setBaseUriFixture($valid = true)
    {
        if ($valid) {
            $base_uri = 'validBaseUri';
        } else {
            $base_uri = 'invalidBaseUri';
        }
        $this->app->setBaseUri($base_uri);
    }
    /**
     * Test: Method returns all activities the user is subscribed to
     * @covers UserController::getActivityStream ()
     */
    public function testGetActivityStream()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * @covers UserController::getActivityStreamAction ()
     */
    public function testGetActivityStreamAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * @covers UserController::getPersonUriAction ()
     */
    public function testGetPersonUriAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Find all resources a user is subscribed to via Activity Feed
     * @covers UserController::getSubscribedResources ()
     */
    public function testGetSubscribedResources()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This method creates a new object of the class Xodx_User
     * @covers UserController::getUser ()
     */
    public function testGetUser()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Get the userAccount of a Person
     * @covers UserController::getUserForPerson ()
     */
    public function testGetUserForPerson()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Returns the user uri of a user which is accosiated to the given person
     * @covers UserController::getUserUri ()
     */
    public function testGetUserUri()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * @covers UserController::homeAction ()
     */
    public function testHomeAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Check if a user is already subscribed to a feed
     * @covers UserController::_isSubscribed ()
     */
    public function testIsSubscribed()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This method subscribes a user to a feed
     * @covers UserController::_subscribeToFeed ()
     */
    public function testSubscribeToFeed()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * @covers UserController::subscribeToResource ()
     */
    public function testSubscribeToResource()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Unsubscribes a user from a feed (he is subscribed to)
     * @covers UserController::_unsubscribeFromFeed ()
     */
    public function testUnsubscribeFromFeed() 
    {       
        $this->initFixture(TRUE, 'testUnsubscribeFromFeed');
        $this->userController->_unsubscribeFromFeed($this->validUnsubscriberUri, 
            $this->validFeedUri, TRUE);
        //$this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Unsubscriebes a user from a resource
     * @covers UserController::unsubscribeFromResource ()
     */
    public function testUnsubscribeFromResourceFeedUriIsNotNull() 
    {     
        $this->initFixture(TRUE, 'testUnsubscribeFromResourceFeedUriIsNotNull');
        
        $this->userController->unsubscribeFromResource($this->validUnsubscriberUri,
                $this->validResourceUri, $this->validFeedUri, TRUE);
        
        $this->assertAttributeEquals($this->validFeedUri, 'feedUri', $this->userController);
    }
    /**
     * Test: Unsubscriebes a user from a resource
     * @covers UserController::unsubscribeFromResource ()
     */
    public function testUnsubscribeFromResourceFeedUriIsNull() 
    {   
        $this->initFixture(TRUE, 'testUnsubscribeFromResourceFeedUriIsNull');
        
        $this->userController->unsubscribeFromResource($this->validUnsubscriberUri,
                $this->validResourceUri,null, TRUE);
        $this->assertAttributeEquals($this->validFeedUri, 'feedUri', $this->userController);
    }
    /**
     * Test: This function verifies the given credentials for a user
     * @covers UserController::verifyPasswordCredentials ()
     */
    public function testVerifyPasswordCredentials()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
}
/**
 * A proxyclass for \classes\Xodx\UserController.
 * @author Stephan
 */
class Xodx_UserControllerProxy extends Xodx_UserController
{
    /**
    * @var global feedUri for testing purposes
    */
    protected $feedUri = null;
    /**
     * Added global feedUri for assertiontest.
     * @param type $unsubscriberUri
     * @param type $resourceUri
     * @param type $feedUri
     * @param type $local
     */
    public function unsubscribeFromResource($unsubscriberUri, $resourceUri, $feedUri = null, $local = false) 
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();      
        // Get Uri of friend's feed (if not given)
        if ($feedUri === null) {
            $feedUri = $this->getActivityFeedUri($resourceUri);
        }
        //set global feedUri
        $this->feedUri = $feedUri;
        
        $this->_unsubscribeFromFeed($unsubscriberUri, $feedUri, $local);
    }
    /**
     * Overwritten for easy testing.
     * @param type $resourceUri if valid changes feedUri to valid
     * @return UriString valid dependance on resourceUri
     */
    public function getActivityFeedUri($resourceUri)
    {
        $feedUri = NULL;   
        if ($resourceUri == 'validResourceUri') {
            $feedUri = 'validFeedUri';
        }
        return $feedUri;  
    }
    /**
     * returns validUnsubscriberUri
     * @param type $personUri
     * @return UriString validUnsubscriberUri
     */
    public function getUserUri($personUri)
    {
       return 'validUnsubscriberUri';
    }
    /**
     * Overwritten for easy testing.
     * @param type $userUri
     * @param type $feedUri
     * @return boolean true if user- and feedUri are valid 
     */
    private function _isSubscribed($userUri, $feedUri) {
        if (($userUri == 'validUnsubcriberUri')
                && ($feedUri == 'validFeedUri')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    /**
     * Public function which overwrites and returns the protected one.
     * 
     * @param UriString $unsubscriberUri
     * @param UriString $feedUri
     * @param UriString $local
     * @return \classes\Xodx\UserController::_unsubscribeFromFeed()
     */
    public function _unsubscribeFromFeed($unsubscriberUri, $feedUri, $local = false)
    {
        return parent::_unsubscribeFromFeed($unsubscriberUri, $feedUri, $local);
    }
}