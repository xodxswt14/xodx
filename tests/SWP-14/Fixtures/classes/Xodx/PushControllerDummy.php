<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/../../libraries/Saft/ControllerDummy.php');

/**
 * This class is a Xodx_PushController dummy.
 * @author Stephan
 */
class PushControllerDummy extends ControllerDummy
{
    
    public function __construct ($app)
    {
    }

    public function subscribe ($feedUri)
    {
    }  
    
    public function publish ($topicUri)
    {
    }

    public function callbackAction ($template)
    {
    }

    public function getDefaultHubUrl ()
    {
    }

    private function _isSubscribed ($feed)
    {
    }
    
    private function _getFeedUriFromBody ($body)
    {
    }
    
    /**
     * PushController: If this method successfully unsubscribed it returns true
     * hint: feedUri is always 'validFeedUri', it's case in UserController l 482
     */
    public function unsubscribe ($feedUri)
    {
        return TRUE;
    }
}