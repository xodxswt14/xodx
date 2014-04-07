<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (__DIR__ . '/../../libraries/Saft/ControllerDummy.php');

class ResourceControllerDummy extends ControllerDummy
{
   
  
    public function indexAction ($template)
    {
    }

    public function showAction ($template)
    {
    }

    public function rdfAction ($template)
    {
    }


    public function imgAction ($template)
    {
    }


    public function getType ($resourceUri)
    {
        if ($resourceUri == 'validUnsubscriberUri') {
            $nsFoaf = 'http://xmlns.com/foaf/0.1/';
            $type = $nsFoaf . 'Person';
        }
        if ($resourceUri == 'invalidUnsubscriberUri') {
            $type = 'invalid';
        }
        return $type;
    }


   
    public function getActivityFeedUri($resourceUri)
    {
    }

    
    public function importResource($resourceUri)
    {
    }

    
    public function testImportResourceAction($template)
    {
    }
}