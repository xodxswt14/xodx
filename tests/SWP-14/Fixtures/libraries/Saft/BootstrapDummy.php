<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (__DIR__ . '/LoggerDummy.php');
require_once (__DIR__ . '/../Erfurt/library/Erfurt/Rdf/ModelDummy.php');

class BootstrapDummy
{
    protected $_app;
    
    
    public function __construct($app) {
        $this->_app = $app;
    }
    
    //returns resource obj
    public function getResource ($resourceName)
    {
        if ($resourceName == 'logger'){
            return new LoggerDummy();
        }
         if ($resourceName == 'model'){
            return new ModelDummy();
        }
    }
}