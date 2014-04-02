<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

// include password hash functions for 5.3.7 <= PHP < 5.5
require_once('password_compat/lib/password.php');

/**
 */
class Xodx_ApplicationControllerTest extends PHPUnit_Framework_Textcase
{
    /**
     */
    public function testNewuserAction ()
    {
        
    }

    /**
     * The login action takes the given credentials and calls the login method with them
     *
     * @param username (post) the username used for the login
     * @param password (post) the password used for the login (TODO: encrypt)
     */
    public function testLoginAction ()
    {
        
    }

    /**
     * Action to logout the current user
     */
    public function testLogoutAction ()
    {
        
    }

    public function testStatsAction ()
    {
        
    }

    /**
     * The login method checks the given credentials and changes the session properties, if login
     * was successfull.
     *
     * @param $username the username to be verified and logged in
     * @param $password the password to be verified
     * @return boolean if the login was successfull
     */
    public function testLogin ()
    {
        
    }

    /**
     * Checks if thre is a logged in user in the session, alse login as guest:guest
     */
    public function testAuthenticate ()
    {
        
    }

    /**
     * Returns the currently logged in user
     */
    public function testGetUser ()
    {
        
    }
}
