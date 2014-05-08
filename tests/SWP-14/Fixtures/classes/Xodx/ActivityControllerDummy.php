<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/ResourceControllerDummy.php');
/**
 * This class is a Xodx_ActivityController dummy.
 * @author Stephan
 */
class ActivityControllerDummy extends ResourceControllerDummy
{
    /**
     * This method adds a new activity to the store
     * @param $actorUri String Uri of the activity's actor. This is either a user or a group
     * @param $verbUri String Determines the type of activity (Note, Photo, ...)
     * @param $objectUri String Content of the activity
     * @param $personUri String If the actor is a group i.e. the activity is posted in a group this determines the user who published this activity. Otherwise this is null
     * TODO should be replaced by a method which takes a DSSN_Activity object
     */
    public function addActivity ($actorUri, $verbUri, $object, $personUri = null)
    {
    }
}