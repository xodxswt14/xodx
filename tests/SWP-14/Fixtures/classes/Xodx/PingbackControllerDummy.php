<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/../../libraries/Saft/ControllerDummy.php');

/**
 * This class is a Xodx_PingbackController dummy.
 * @author Stephan
 */
class PingbackControllerDummy extends ControllerDummy
{
   
    /**
     * Method is used for testing other methods.
     * 
     * @param string $source
     * @param string $target
     * @param string $comment
     */
    public function sendPing ($source, $target, $comment = null)
    {
        return TRUE;
    }
}