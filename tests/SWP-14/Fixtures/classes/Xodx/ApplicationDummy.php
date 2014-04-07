<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//require_once (__DIR__ . '/../../../../../libraries/Saft/Bootstrap.php');

require_once (__DIR__ . '/ResourceControllerDummy.php');
require_once (__DIR__ . '/../../libraries/Saft/BootstrapDummy.php');

class ApplicationDummy
{
    protected $_appNamespace = null;
    protected $_layout = null;

    private $_bootstrap = null;
    private $_baseUri = null;
    private $_baseDir = null;

    private $_controllers = array();

    public function getBootstrap()
    {
        $this->_bootstrap = new BootstrapDummy($this);
        return $this->_bootstrap;
    }

    public function getController ($controllerName)
    {
        if ($controllerName == 'Xodx_ResourceController'){
            return new ResourceControllerDummy($this);
        }
        if ($controllerName == 'Xodx_PushController'){
            return new PushControllerDummy($this);
        }
    }

    public function getHelper ($helperName)
    {
    }

    public function run()
    {
    }


    public function runJobs()
    {
    }

    public function setBaseUri ($baseUri)
    {
        $this->_baseUri = $baseUri;
    }

    public function getBaseUri ()
    {
        return $this->_baseUri;
    }

    public function setBaseDir ($baseDir)
    {
    }

    public function getBaseDir ()
    {
    }

    public function setAppNamespace ($namespace)
    {
    }

    public function ns ($prefix)
    {
    }
}

