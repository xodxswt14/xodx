<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class is a Xodx_Controller dummy.
 * @author Stephan
 */
class ControllerDummy
{
    protected $_app = null;
    /**
     * inter alia required for Xodx_ResourceControllerDummy
     * @param \Fixtures\libraries\Saft\Application $app 
     */
    public function __construct($app)
    {
        $this->_app = $app;
    }
}