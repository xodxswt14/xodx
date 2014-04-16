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
define('EF_RDF_NS', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('EF_RDF_TYPE', EF_RDF_NS.'type');
/**
 * This class tests \classes\Xodx\GroupController.php
 * @author Stephan
 * @author Jan
 */
class Xodx_GroupControllerTest extends PHPUnit_Framework_Testcase
{
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
    public function initFixture($proxy = FALSE, $testMethod = null)
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
     * @covers GroupController:createGroup
     */
    public function testCreateGroup() {
        $this->initFixture(FALSE, 'testCreateGroup');
        $this->groupController->createGroup(null, 'gtest');
    }
    public function testDeleteGroup() {
        $this->initFixture(TRUE, 'testDeleteGroup');
        $this->groupController->deleteGroup('http://127.0.0.1:8080/?c=Group&id=gtest');
        //$this->markTestSkipped('To Be Done!');
    }
}


class Xodx_GroupControllerProxy extends Xodx_GroupController
{
    public function getGroup($userUri = null) {
        return 'groupName';
    }
}