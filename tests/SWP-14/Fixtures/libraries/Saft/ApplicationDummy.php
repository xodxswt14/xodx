<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/../../classes/Xodx/ResourceControllerDummy.php');
require_once (__DIR__ . '/BootstrapDummy.php');

/**
 * This class is a Saft_Application dummy.
 * @author Stephan
 */
class ApplicationDummy
{
    protected $_appNamespace = null;
    protected $_layout = null;

    private $_bootstrap = null;
    private $_baseUri = null;
    private $_baseDir = null;

    private $_controllers = array();
    
    /**
     * For the first returns only Saft_Bootstrap.
     * @return SaftBootstrap
     */
    public function getBootstrap()
    {
        $this->_bootstrap = new BootstrapDummy($this);
        return $this->_bootstrap;
    }
    /**
     * Returns ControllerDummy dependent on $controllerName.
     * @param type $controllerName
     * @return \PushControllerDummy|\ResourceControllerDummy
     */
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
    /**
     * Sets global baseUri
     * @param type $baseUri
     */
    public function setBaseUri ($baseUri)
    {
        $this->_baseUri = $baseUri;
    }
    /**
     * gets global baseUri.
     * @return baseUri
     */
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

