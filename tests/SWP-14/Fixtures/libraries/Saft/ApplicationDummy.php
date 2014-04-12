<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/../../classes/Xodx/ResourceControllerDummy.php');
require_once (__DIR__ . '/../../classes/Xodx/PushControllerDummy.php');
require_once (__DIR__ . '/../../classes/Xodx/UserControllerDummy.php');
require_once (__DIR__ . '/../../classes/Xodx/PersonControllerDummy.php');
require_once (__DIR__ . '/../../libraries/Saft/Helper/LinkeddataHelperDummy.php');
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
     * @return \PushControllerDummy|\ResourceControllerDummy|\UserControllerDummy|PersonControllerDummy
     */
    public function getController ($controllerName)
    {
        if ($controllerName == 'Xodx_ResourceController'){
            return new ResourceControllerDummy($this);
        }
        if ($controllerName == 'Xodx_PushController'){
            return new PushControllerDummy($this);
        }
        if ($controllerName == 'Xodx_UserController'){
            return new UserControllerDummy($this);
        }
        if ($controllerName == 'Xodx_PersonController'){
            return new PersonControllerDummy($this);
        }
    }
    /**
     * Returns HelperDummy dependent on $helperName.
     * @param String $helperName
     * @return \LinkeddataHelperDummy
     */
    public function getHelper ($helperName)
    {
        if ($helperName == 'Saft_Helper_LinkeddataHelper'){
            return new LinkeddataHelperDummy($this);
        }
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

