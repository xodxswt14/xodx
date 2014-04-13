<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class is a Saft_Layout dummy.
 * @author Stephan
 */
class LayoutDummy
{
    public function __construct ($app)
    {
        $this->_app = $app;
    }

    public function addContent ($contentFile)
    {
    }
    /**
     * With the method the browser can be redirected to a new location
     */
    public function redirect ($location, $responseCode = 303)
    {
    }
}