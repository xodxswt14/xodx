<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once(__DIR__ . '/../../../../libraries/Saft/Controller.php');
require_once(__DIR__ . '/../../../../classes/Xodx/ResourceController.php');
require_once(__DIR__ . '/../../../../classes/Xodx/GroupController.php');
require_once (__DIR__ . '/../../Fixtures/libraries/Saft/ApplicationDummy.php');
require_once (__DIR__ . '/../../../../classes/Xodx/User.php');
require_once (__DIR__ . '/../../../../classes/Xodx/Group.php');
define('EF_RDF_NS', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('EF_RDF_TYPE', EF_RDF_NS.'type');
/**
 * This class tests \classes\Xodx\GroupController.php
 * @author Stephan Kemper
 * @author Jan Buchholz
 */
class Xodx_GroupControllerTest extends PHPUnit_Framework_Testcase
{
    /**
     * This is a valid Uri of a group.
     * @var UriString
     */
    protected $validGroupUri = 'validGroupUri';
    /**
     * This is a valid Uri of a person.
     * @var string
     */
    protected $validPersonUri = 'validPersonUri';
    /**
     * An application for testing purposes.
     * @var \Fixtures\classes\Xodx\ApplicationDummy
     */
    protected $app = NULL;
    /**
     * The GroupController loaded in initFixture.
     * (A proxyclass which contains some small classchanges for testing.)
     * @var \GroupController(Proxy)
     */
    protected $groupController = NULL;
    /**
     * Creates an Application- and GroupControllerDummy
     * @param $proxy  true for GroupControllerProxy false for normal UserController
     */
    public function initFixture ($proxy = FALSE, $testMethod = null)
    {
        $this->app = new ApplicationDummy();
        $this->app->testMethod = $testMethod;
        if ($proxy) {
            $this->groupController = new Xodx_GroupControllerProxy($this->app);
        } else { //proxy == false, standard
            $this->groupController = new Xodx_GroupController($this->app);
        }
        $this->setBaseUriFixture();
    }
    /**
     * Sets the BaseUri of $this->app
     * @param type $valid true vor valid, false for invalid
     */
    public function setBaseUriFixture ($valid = true)
    {
        if ($valid) {
            $base_uri = 'validBaseUri';
        } else {
            $base_uri = 'invalidBaseUri';
        }
        $this->app->setBaseUri($base_uri);
    }
    /**
     * Tests if a new Group is created correctly
     * @covers GroupController:createGroup
     */
    public function testCreateGroup ()
    {
        $this->initFixture(FALSE, 'testCreateGroup');
        $this->groupController->createGroup('gtest', 'gtestdesc');
    }

    /**
     * Tests if a new Group is joined correctly
     * @covers GroupController:joinGroup
     */
    public function testJoinGroup ()
    {
        $this->initFixture(TRUE, 'testJoinGroup');
        $this->groupController->joinGroup('anyPersonURI', $this->validGroupUri);
    }

    /**
     * Tests if a new Group is leaved correctly
     * @covers GroupController:joinGroup
     */
    public function testLeaveGroup ()
    {
        $this->initFixture(TRUE, 'testLeaveGroup');
        $this->groupController->leaveGroup('anyPersonURI', $this->validGroupUri);
    }

    /**
     * Tests if a Group is changed correctly
     * @covers GroupController::changeGroup
     */
    public function testChangeGroup () {
        $this->initFixture(TRUE, 'testChangeGroup');
        $this->groupController->changeGroup($this->validGroupUri, "newName", "newTopic");
    }

    /**
     * Tests if a Group is deleted correctly
     * @covers GroupController::deleteGroup
     */
    public function testDeleteGroup ()
    {
        $this->initFixture(TRUE, 'testDeleteGroup');
        $this->groupController->deleteGroup($this->validGroupUri);
    }

    /**
     * @covers GroupController::getGroup
     */
    public function testGetGroup ()
    {
        $this->initFixture(FALSE, 'testGetGroup');
        $this->groupController->getGroup($this->validGroupUri);
    }
    /**
     * @covers GroupController::getGroupFeedUri
     */
    public function testGetGroupFeedUri()
    {
        $this->initFixture(FALSE, 'testGetGroupFeedUri');
        $resourceUri = 'baseUri/?c=testcontr&a=testapl';
        $feedUri = 'baseUri/' . '?c=feed&a=getFeed&uri=' . urlencode($resourceUri);
        $this->assertEquals($feedUri,  $this->groupController->getGroupFeedUri($resourceUri));
    }
    
    /**
     * @covers GroupController::getGroupByAuthorUri
     */
    public function testGetGroupByAuthorUri() {
        $this->initFixture(FALSE, 'testGetGroupByAuthorUri');
        $authorUri = 'http' . $this->validPersonUri . 'http' . $this->validGroupUri;
        $this->assertEquals('http' . $this->validGroupUri, $this->groupController->getGroupByAuthorUri($authorUri));
    }
    /**
     * @covers GroupController::getPersonByAuthorUri
     */
    public function testGetPersonByAuthorUri() {
        $this->initFixture(FALSE, 'testGetPersonByAuthorUri');
        $authorUri = 'http' . $this->validPersonUri . 'http' . $this->validGroupUri;
        $this->assertEquals('http' . $this->validPersonUri, $this->groupController->getPersonByAuthorUri($authorUri));
    }
}
/**
 * A proxyclass for \classes\Xodx\GroupController.
 * @author Stephan
 */
class Xodx_GroupControllerProxy extends Xodx_GroupController
{
    public function getGroup($userUri = null)
    {
        $group = new Xodx_Group($userUri, new ApplicationDummy());
        $group->setName('validName');
        $group->setDescription('validDescription');
        return $group;
    }
}