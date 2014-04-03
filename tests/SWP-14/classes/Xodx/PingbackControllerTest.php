<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @author Stephan
 */
class Xodx_PingbackControllerTest extends PHPUnit_Framework_Textcase
{

    public function testPingAction2()
    {
        
    }

    public function testReceivePing()
    {
        
    }

    public function testSendPing()
    {
        
    }

    public function testAddPingback()
    {
        
    }

    public function testDeleteInvalidPingbacks()
    {
        
    }

    public function testCheckTargetExists()
    {
        
    }

    public function testGetResourceFromWrapper()
    {
        
    }

    public function testPingbackExists()
    {
        
    }

    public function testPingAction ($template) {
        //@TODO change $this to an pingbackcontroller instance
        echo $this->receivePing(
            'http://xodx.local/?c=resource&id=1b8c874744236dcdfcaaf08c817aa633',
            'http://xodx.local/?c=resource&id=805e60023b6f23384929d7869bd24185'
        );
        return $template;

     }
}
