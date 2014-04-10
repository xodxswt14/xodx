<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class tests \classes\Xodx\PersonController.php
 * @author Stephan
 */
class Xodx_PersonControllerTest extends PHPUnit_Framework_Testcase
{
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
     * @covers PersonController::deleteFriendAction ()
     */
    public function testDeleteFriendAction()
    {
        $this->markTestSkipped('TO BE DONE!');
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
        $this->markTestSkipped('TO BE DONE!');
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
