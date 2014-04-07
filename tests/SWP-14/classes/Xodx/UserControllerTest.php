<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */


/**
$main_dir = rtrim(dirname(__FILE__), '/\\');

# Set include paths
$includePath  = get_include_path() . PATH_SEPARATOR;

$includePath .= $main_dir . '/classes/' . PATH_SEPARATOR;
$includePath .= $main_dir . '/classes/Xodx/' . PATH_SEPARATOR;
$includePath .= $main_dir . '/libraries/' . PATH_SEPARATOR;
$includePath .= $main_dir . '/libraries/Erfurt/library/' . PATH_SEPARATOR;
$includePath .= $main_dir . '/libraries/lib-dssn-php/' . PATH_SEPARATOR;
$includePath .= $main_dir . '/libraries/ARC2/' . PATH_SEPARATOR;

set_include_path($includePath);
*/


//require_once('/password_compat/lib/password.php');
//require_once(__DIR__ . '/../../../TestBootstrap.php');
require_once(__DIR__ . '/../../../../libraries/Saft/Controller.php');
require_once(__DIR__ . '/../../../../classes/Xodx/ResourceController.php');
require_once(__DIR__ . '/../../../../classes/Xodx/UserController.php');
//require_once('/../../../../libraries/password_compat/lib/password.php');
//require_once('/password_compat/lib/password.php');

require_once (__DIR__ . '/../../Fixtures/classes/Xodx/ApplicationDummy.php');
/**
 * @author Stephan
 */
class Xodx_UserControllerTest extends PHPUnit_Framework_Testcase
{

    /*
     * activity feed of test2
     * http://127.0.0.1:8080/?c=feed&a=getFeed&uri=http%3A%2F%2F127.0.0.1%3A8080%2F%3Fc%3Dperson%26id%3Dtest2http://127.0.0.1:8080/?c=feed&a=getFeed&uri=http%3A%2F%2F127.0.0.1%3A8080%2F%3Fc%3Dperson%26id%3Dtest2
     * 
     * 
     * 
     *  get feed of personid
     * 
     *  result:
     * http://127.0.0.1:8080/?c=feed&a=getFeed&uri=http%3A%2F%2F127.0.0.1%3A8080%2F%3Fc%3Dperson%26id%3Dtest
     * 
        PREFIX atom: <http://www.w3.org/2005/Atom/>
        PREFIX aair: <http://xmlns.notu.be/aair#>
        SELECT ?feed 
        WHERE { 
            <http://127.0.0.1:8080/?c=person&id=test> <http://purl.org/net/dssn/activityFeed> ?feed . 
        }
     
     *  subUri of a feed
     *  result:
     * http://127.0.0.1:8080/&c=ressource&id=60743c43fe6902335feb85548af6fe8f
     * 

        PREFIX dssn: <http://purl.org/net/dssn/>
        Select ?subUri
        WHERE {
            ?subUri dssn:subscriptionTopic <http://127.0.0.1:8080/?c=feed&a=getFeed&uri=http%3A%2F%2F127.0.0.1%3A8080%2F%3Fc%3Dresource%26id%3D1b17f9a68b8433ceaec2167aa71e1219>
        }
      
      
     * 
     * feed of a post:
     * http://127.0.0.1:8080/?c=feed&a=getFeed&uri=http%3A%2F%2F127.0.0.1%3A8080%2F%3Fc%3Dresource%26id%3D1b17f9a68b8433ceaec2167aa71e1219
     * 
     * suburi of this post:
     * http://127.0.0.1:8080/&c=ressource&id=60743c43fe6902335feb85548af6fe8f
     */
    
    protected $feedUriPost = 'http://127.0.0.1:8080/?c=feed&a=getFeed&uri=http%3A%2F%2F127.0.0.1%3A8080%2F%3Fc%3Dresource%26id%3D1b17f9a68b8433ceaec2167aa71e1219';
    protected $subUriPost = 'http://127.0.0.1:8080/&c=ressource&id=60743c43fe6902335feb85548af6fe8f';
    
    protected $feedUri = null;
    protected $unsubscriberUri = 'http://127.0.0.1:8080/?c=person&id=test';
    protected $rescourceUri = 'http://127.0.0.1:8080/?c=person&id=test2';

    
    protected $validFeedUri = 'validFeedUri';
    protected $invalidFeedUri = 'invalidFeedUri';

    protected $validUnsubscriberUri = 'validUnsubscriberUri';
    protected $invalidUnsubscriberUri = 'invalidUnsubscriberUri';

    protected $validResourceUri = 'validResourceUri';
    protected $invalidResourceUri = 'invalidResourceUri';



    //private $app = NULL;
    //private $userController = NULL;
    protected $app = NULL;
    protected $userController = NULL;
    
    
    
    
     public function initFixture()
    {
        $this->app = new ApplicationDummy();
        //$this->usercontroller = new \Xodx_UserController($this->app);
        //die nehmen$this->userController = new Xodx_UserController($this->app);
        $this->userController = new Xodx_UserControllerProxy($this->app);
        
        $this->setBaseUriFixture();
    }
    
    public function setBaseUriFixture()
    {
        //$base_uri = 'http://127.0.0.1:8080/?';
        $base_uri = 'validBaseUri';
        $this->app->setBaseUri($base_uri);
    }
    
    
    
    


    public function testGetActivityStream()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    public function testGetActivityStreamAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    public function testGetPersonUriAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    public function testGetSubscribedResources()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    public function testGetUser()
    {
        $this->markTestSkipped('TO BE DONE!');
    }

    public function testGetUserForPerson()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    public function testGetUserUri()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    public function testHomeAction()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    public function testIsSubscribed()
    {
        $this->markTestSkipped('TO BE DONE!');
    }

    public function testSubscribeToFeed()
    {
        $this->markTestSkipped('TO BE DONE!');
    }

    public function testSubscribeToResource()
    {
        $this->markTestSkipped('TO BE DONE!');
    }

    public function testUnsubscribeFromFeedTypeIsNsFoafPerson() 
    {       
        $this->markTestSkipped('TO BE DONE!');
    }

    public function testUnsubscribeFromFeedTypeIsNotNsFoafPerson() 
    {       
        $this->markTestSkipped('TO BE DONE!');
    }

    public function testUnsubscribeFromResourceFeedUriIsNotNull() 
    {     
        
        $this->initFixture();
        
        //$this->userController->unsubscribeFromResource($this->unsubscriberUri,
        //        $this->rescourceUri, $this->feedUriPost);
        
        $this->userController->unsubscribeFromResource($this->validUnsubscriberUri,
                $this->validResourceUri, $this->validFeedUri);
        
        
        //$this->assertAttributeEquals($this->feedUriPost, 'feedUri', $this->userController);
        $this->assertAttributeEquals($this->validFeedUri, 'feedUri', $this->userController);
        //$this->markTestSkipped('TO BE DONE!');
    }

    public function testUnsubscribeFromResourceFeedUriIsNull() 
    {   
        //$this->markTestSkipped('TO BE DONE!');
        $this->initFixture();
        
        $this->userController->unsubscribeFromResource($this->validUnsubscriberUri, $this->validResourceUri);
        $this->assertAttributeEquals($this->validFeedUri, 'feedUri', $this->userController);
    }

    
    public function testVerifyPasswordCredentials()
    {
        $this->markTestSkipped('TO BE DONE!');
    }
    
    
    
   
}

class Xodx_UserControllerProxy extends Xodx_UserController{
    /**
    * @var global feedUri for testing purposes
    */
    protected $feedUri = null;
   
    
    public function unsubscribeFromResource($unsubscriberUri, $resourceUri, $feedUri = null, $local = false) 
    {           
        
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();      
        
        // Get Uri of friend's feed (if not given)
        if ($feedUri === null) {
            $feedUri = $this->getActivityFeedUri($resourceUri);
        }
        
        //set global feedUri
        $this->feedUri = $feedUri;
        
        $this->_unsubscribeFromFeed($unsubscriberUri, $feedUri, $local);
        
    }
    
    public function getActivityFeedUri($resourceUri)
    {
        $feedUri = NULL;   
        if ($resourceUri == 'validResourceUri') {
            $feedUri = 'validFeedUri';
        }

        //$this->feedUri = $feedUri;
        return $feedUri;
           
    }
    
    public function getUserUri($personUri)
    {
       return 'validUnsubscriberUri';
    }
    
    
    
     private function _isSubscribed($userUri, $feedUri) {
        if (($userUri == 'validUnsubcriberUri')
                && ($feedUri == 'validFeedUri')) {
            return TRUE;
        }
    }
     
   
    
    
}