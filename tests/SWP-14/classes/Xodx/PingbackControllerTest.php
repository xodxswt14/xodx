<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class tests \classes\Xodx\PingbackController.php
 * @author Stephan
 */
class Xodx_PingbackControllerTest extends PHPUnit_Framework_Testcase
{
    /**
     * @covers PingbackController::pingAction ()
     */
    public function testPingAction2()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: receive a ping API
     * @covers PingbackController::recievePing ()
     */
    public function testReceivePing()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Parts of this method are taken from messages.php of the my-profile project
     *  Method sends a ping with help of cURL if $target is a resource with given
     *  Pingback Server
     * @covers PingbackController::sendPing ()
     */
    public function testSendPing()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test:  Adds a ping resource of rdf:type http://purl.org/net/pingback/Item' to model
     *  if incoming ping succeeded
     * @covers PingbackController::_addPingback ()
     */
    public function testAddPingback()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Method deletes stored statements of rdf:type http://purl.org/net/pingback/Item if no
     *  triples were found while analysing an in coming ping
     * @covers PingbackController::_deleteInvalidPingbacks ()
     */
    public function testDeleteInvalidPingbacks()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Checks if $targetUri is a resource in our model
     * @covers PingbackController::_checkTargetExists ()
     */
    public function testCheckTargetExists()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Methods checks if $sourceUri is reachable with help of LinkeddataWrapper and if successfull
     *  it also checks if statements with $targetUri as object exist.
     * @covers PingbackController::_getResourceFromWrapper ()
     */
    public function testGetResourceFromWrapper()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * Test: Method checks with help of SPARQL if a Ping exists
     * @covers PingbackController::_pingbackExists ()
     */
    public function testPingbackExists()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    /**
     * @covers PingbackController::_pingAction ()
     * @param type $template
     * @return type
     */
    public function testPingAction ($template) {
        //@TODO change $this to an pingbackcontroller instance
        echo $this->receivePing(
            'http://xodx.local/?c=resource&id=1b8c874744236dcdfcaaf08c817aa633',
            'http://xodx.local/?c=resource&id=805e60023b6f23384929d7869bd24185'
        );
        return $template;

     }
}
