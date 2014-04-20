<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/../HelperDummy.php');
/**
 * This class is a Saft_Helper_LinkeddataHelper dummy.
 * @author Stephan
 */
class LinkeddataHelperDummy extends HelperDummy
{
    /**
     * Method checks if a resource given by a URI exists or not
     * @param string $resourceUri
     * @return boolean
     */
    public function resourceDescriptionExists ($resourceUri)
    {
        if ($resourceUri == 'validResourceUri') {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}