<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class is a Saft_Request dummy.
 * @author Stephan
 */
class RequestDummy
{
    /**
    * Returns the value for the given key.
    * @param $key The key of the value which should be returned
    * @param $method optional, if this parameter is specified only values transfered with this method
    *              are taken into account. If this parameter is empty the priority is get, post,
    *              session (the last overwerites the first).
    */
    public function getValue ($key, $method = null)
    {
        if ($key == 'person') {
            //in other testcases i would use validPersonUri
            //but in PersonController->deleteFriendAction where this function is used
            //Erfurt_Uri::check needs a legal Uri
            return 'http://127.0.0.1:8080/?c=person&id=test';
        } else if ($key == 'friend') {
            return 'http://127.0.0.1:8080/?c=person&id=test2';
        }
        
    }
}