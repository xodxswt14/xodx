<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/ResourceControllerDummy.php');
require_once (__DIR__ . '/../../../../../classes/Xodx/Group.php');
/**
 * This class is a Xodx_MemberController dummy.
 * @author Stephan
 */
class MemberControllerDummy extends ResourceControllerDummy
{
    /**
     * Method is called in groupController but an implementation
     * is not needed for testing.
     * 
     * @param URI $personUri Uri of the new member
     * @param URI $groupUri Uri of the group
     * @throws Exception if personUri not found
     */
    public function addMember($personUri, $groupUri)
    {
    }
    /**
     * Method is called in groupController but an implementation
     * is not needed for testing.
     * 
     * @param URI $personUri Uri of the new member
     * @param URI $groupUri Uri of the group
     * @throws Exception if personUri not found
     */
    public function deleteMember($personUri, $groupUri)
    {
    }
    /**
     * Method is called in groupController but an implementation
     * is not needed for testing.
     * 
     * Unsubscriebes a user from a resource
     * 
     * @param type $unsubscriberUri Uri of the person who wants to unsubscribe from a resource
     * @param type $resourceUri Uri of the resource that ist to be unsubscribed
     * @param type $feedUri Feed of the given resource
     * @param type $local Indicates whether the resource is stored locally
     */
    public function unsubscribeFromResource($unsubscriberUri, $resourceUri, $feedUri = null, $local = false) 
    { 
    }
}