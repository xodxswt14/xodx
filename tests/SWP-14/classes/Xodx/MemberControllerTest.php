<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once(__DIR__ . '/../../../../libraries/Saft/Controller.php');
require_once(__DIR__ . '/../../../../classes/Xodx/ResourceController.php');
require_once(__DIR__ . '/../../../../classes/Xodx/MemberController.php');
require_once (__DIR__ . '/../../Fixtures/libraries/Saft/ApplicationDummy.php');
/**
 * This class tests \classes\Xodx\MemberController.php
 * @author Stephan
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
}