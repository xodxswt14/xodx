<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/LoggerDummy.php');
require_once (__DIR__ . '/../Erfurt/library/Erfurt/Rdf/ModelDummy.php');

/**
 * This class is a Saft_Bootstrap dummy.
 * @author Stephan
 */
class BootstrapDummy
{
    protected $_app;
    
    public function __construct($app) {
        $this->_app = $app;
    }
    /**
     * Returns dummy dependent on $resourceName.
     * @param type $resourceName
     * @return \LoggerDummy|\ModelDummy
     */
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