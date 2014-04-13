<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/ResourceControllerDummy.php');
/**
 * This class is a Xodx_PersonController dummy.
 * @author Stephan
 */
class PersonControllerDummy extends ResourceControllerDummy
{
    /**
     * Delete an old contact out of the list of freinds of a person
     * This is a one-way connection, the contact doesn't has to approve it
     *
     * @param $personUri the URI of the person from whome the contact should be removed
     * @param $contactUri the URI of the person who sould be removed as friend
     */
    public function deleteFriend ($personUri, $contactUri)
    {
    }
}