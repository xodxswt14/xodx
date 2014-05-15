<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/ResourceControllerDummy.php');
require_once (__DIR__ . '/../../../../../classes/Xodx/User.php');
/**
 * This class is a Xodx_UserController dummy.
 * @author Stephan
 */
class UserControllerDummy extends ResourceControllerDummy
{
    /**
     * Returns the user uri of a user which is accosiated to the given person
     * @deprecated use getUserForPerson
     */
    public function getUserUri($personUri) {
        if ($personUri == 'validPersonUri') {
            return 'validUserUri';
        } else {
            return 'invalidUserUri';
        }
    }
    /**
     * This method creates a new object of the class Xodx_User
     * @param $userUri a string which contains the URI of the required user
     * @return Xodx_User instance with the specified URI
     */
    public function getUser ($userUri = null)
    {
        $userUri = 'validUserUri';
        $user = new Xodx_User($userUri);
        $user->setName('validPersonName');
        $user->setPerson('validPersonUri');
        return $user;
    }
    /**
     * Method is called in PersonController but an implementation
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
    /**
     * Method is called in PersonController but an implementation
     * is not needed for testing.
     * 
     * Subscriebes a user to a resource
     * 
     * @param type $unsubscriberUri Uri of the person who wants to unsubscribe from a resource
     * @param type $resourceUri Uri of the resource that ist to be unsubscribed
     * @param type $feedUri Feed of the given resource
     * @param type $local Indicates whether the resource is stored locally
     */
    public function subscribeToResource($unsubscriberUri, $resourceUri, $feedUri = null, $local = false) 
    { 
    }
}