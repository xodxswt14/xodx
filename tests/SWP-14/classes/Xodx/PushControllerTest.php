<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class tests \classes\Xodx\PushController.php
 * @author Stephan
 */
class Xodx_PushControllerTest extends PHPUnit_Framework_Testcase
{
    /**
     * 
     * @covers PushController::__construct ()
     */
    public function testConstruct ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This is the subscribe method, which is called internally if some component wants to
     *  be notified on updates of a feed
     *  This method implements section 6.1 of the pubsubhubbub spec:
     *  http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html#anchor5
     * @covers PushController::subscribe ()
     */
    public function testSubscribe ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This is the publish method, which is called internally if a feed has been changed
     * This method implements section 7.1 of the pubsubhubbub spec:
     *  http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html#anchor9
     * @covers PushController::publish ()
     */
    public function testPublish ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This action is used as callback for the subscriber and it will be triggered if the hub
     *  notifies us about updates
     *  The hub will call this action and give us the updates for the feed
     *  This method implements section 6.2 of the pubsubhubbub spec:
     *  http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html#verifysub
     * @covers PushController::callbackAction ()
     */
    public function testCallbackAction ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * 
     * @covers PushController::getDefaultHubUrl ()
     */
    public function testGetDefaultHubUrl ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * 
     * @covers PushController::isSubscribed ()
     */
    private function testIsSubscribed ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: This function gets a Request Body and tries to find a Feed URL
     * @covers PushController::getFeedUriFromBody ()
     */
    private function testGetFeedUriFromBody ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Unsubscription method. This is called when a component does no longer want to be
     *  notified on updates of a feed
     *  This method implements section 6.1 of the pubsubhubbub spec:
     *  http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html#anchor5     
     * @covers PushController::unsubscribe ()
     */
    public function testUnsubscribe ()
    {
        $this->markTestSkipped('TO BE DONE!');
    }    
}
