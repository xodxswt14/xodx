<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class tests \classes\Xodx\ResourceController.php
 * @author Stephan
 */

class Xodx_ResourceControllerTest extends PHPUnit_Framework_Testcase
{
    /**
     * indexAction decides to show a html or a serialized view of a resource if no action is given
     * @covers ResourceController::indexAction ()
     */
    public function testIndexAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Method returns all tuples of a resource to html template
     * @covers ResourceController::showAction ()
     */
    public function testShowAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * rdfAction returns a serialized view of a resource according to content type
     * @covers ResourceController::rdfAction ()
     */
    public function testRdfAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * rdfAction returns a serialized view of a resource according to content type
     * @covers ResourceController::imgAction ()
     */
    public function testImgAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * get the type of a ressource
     * @covers ResourceController::getType ()
     */
    public function testGetType ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * methods looks up a ressource to get the Uri of the activity feed
     * and returns it if succesfull
     * @covers ResourceController::getActivityFeedUri ()
     */
    public function testGetActivityFeedUri()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * method imports a resource into the own model
     * @return type
     */
    public function testImportResource()
    {
        //@TODO change $this to a resourcecontroller instance
        //$template->disableLayout();
        echo $this->importResource('http://dbpedia.org/resource/Hamburger_SV');
        return $template;

    }

}
