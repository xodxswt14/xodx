<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class is a Saft_Helper dummy.
 * @author Stephan
 */
class HelperDummy
{
    protected $_app;
    /**
     * inter alia required for Saft_Helper_LinkeddataHelperDummy
     * @param type $app 
     */
    public function __construct ($app)
    {
        $this->_app = $app;
    }
}
