<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (__DIR__ . '/../../libraries/Saft/ControllerDummy.php');

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
    
    //PushController: If this method successfully unsubscribed it returns true
    public function unsubscribe ($feedUri)
    {
        return TRUE;
    }
}