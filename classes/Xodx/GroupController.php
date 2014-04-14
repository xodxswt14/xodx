<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class manages Groups. This includes so far:
 * 
 * 
 * @author Jan Buchholz
 * @author Stephan Kemper
 */
class Xodx_GroupController extends Xodx_ResourceController
{
    /**
     * @var Xodx_Group A registry of already loaded XodX_Group objects
     */
    private $_groups = array();
    /**
     * This creates a new group with the given name.
     * This function is usually called internally
     * @param Uri $groupUri Uri of the new group
     * @param String $name Name of the new group
     * @todo $groupUri might not be needed
     */
    public function createGroup ($groupUri = null, $name)
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();     
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsDssn = 'http://purl.org/net/dssn/';

        // fetch empty groupUri
        if ($groupUri === null) {
            $groupUri = $this->_app->getBaseUri() . '?c=Group&id=' . urlencode($name);
        }

        // verify that there is not already a group with that name            
        $testQuery  = 'ASK {' . PHP_EOL;
        $testQuery .= '<' . $groupUri . '> ?p ?o' . PHP_EOL;
        $testQuery .= '}';            
        if ($model->sparqlQuery($testQuery)) {         
            
            $logger->error('GroupController/createGroup: Groupname already taken: ' . $name);
            throw Exception('Groupname already taken.');
        } else {                                                                                                  
            // feed for the new group
            $newGroupFeed = $this->_app->getBaseUri() . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);
            // Uri of the group's admin ( its foaf:maker)
            $userController = $this->_app->getController('Xodx_UserController');                
            $adminUri = $userController->getUser()->getPerson();

            $newGroup = array(
                $groupUri => array(
                    EF_RDF_TYPE => array(
                        array('type' => 'uri', 'value' => $nsFoaf . 'Group')
                    ),
                    $nsDssn . 'activityFeed' => array(
                        array('type' => 'uri', 'value' => $newGroupFeed)
                    ),
                    $nsFoaf . 'maker' => array(
                        array('type' => 'uri', 'value' => $adminUri)
                    ),
                    $nsFoaf . 'nick' => array(
                        array('type' => 'literal', 'value' => $name)
                    ),
                    $nsFoaf . 'primaryTopic' => array(
                        array('type' => 'literal', 'value' => 'Enter description here...')
                    )
                )
            );
            $model->addMultipleStatements($newGroup);
        }
    }              
     /**
     * This deletes a group with the given Uri.
     * This function is usually called internally
     * @param Uri $groupUri Uri of the group to be deleted
     * @todo $groupUri might not be needed
     */
    public function deleteGroup ($groupUri)
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();     
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsDssn = 'http://purl.org/net/dssn/';

        // fetch empty groupUri
        if ($groupUri === null) {
            $groupUri = $this->_app->getBaseUri() . '?c=Group&id=' . urlencode($name);
        }

        // verify that there is a group with that name            
        $testQuery  = 'ASK {' . PHP_EOL;
        $testQuery .= '<' . $groupUri . '> ?p ?o' . PHP_EOL;
        $testQuery .= '}';            
        $result = $model->sparqlQuery($testQuery);
        if (!$result) {
            $logger->error('GroupController/deleteGroup: Group does not exist: ' . $name);
            throw Exception('Groupname does not exist.');
        } else {                               
            //TODO name of the group via $name = $this->getGroup()->getName();
            //now its hardcoded
            $name = 'gtest';
            
            // feed of the group
            $groupFeed = $this->_app->getBaseUri() . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);
            // Uri of the group's admin ( its foaf:maker)
            $userController = $this->_app->getController('Xodx_UserController');                
            $personUri = $userController->getUser()->getPerson();

            //verify that User is admin of the group
            $makerQuery  = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
            $makerQuery .= 'ASK {' .PHP_EOL;
            $makerQuery .= '   <' . $groupUri . '>' . ' foaf:maker <' . $personUri . '>.' .PHP_EOL;
            $makerQuery .= '}';
                       
            if ($model->sparqlQuery($testQuery)) {
                $deleteQuery  = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
                $deleteQuery .= 'SELECT ?topic ' . PHP_EOL;
                $deleteQuery .= 'WHERE {' . PHP_EOL;
                $deleteQuery .= '<' . $groupUri . '> foaf:primaryTopic ?topic' . PHP_EOL;
                $deleteQuery .= '}';            
                $deleteResult = $model->sparqlQuery($deleteQuery);
                
                $deleteGroup = array(
                    $groupUri => array(
                        EF_RDF_TYPE => array(
                            array('type' => 'uri', 'value' => $nsFoaf . 'Group')
                        ),
                        $nsDssn . 'activityFeed' => array(
                            array('type' => 'uri', 'value' => $groupFeed)
                        ),
                        $nsFoaf . 'maker' => array(
                            array('type' => 'uri', 'value' => $personUri)
                        ),
                        $nsFoaf . 'nick' => array(
                            array('type' => 'literal', 'value' => $name)
                        ),
                        $nsFoaf . 'primaryTopic' => array(
                            array('type' => 'literal', 'value' => $deleteResult[0]['topic'])
                        )
                    )
                );
                $model->deleteMultipleStatements($deleteGroup);
            } else {
                $logger->error('GroupController/deleteGroup: Person is not authorised'
                        . ' to delete the group: ' . $name);
                throw Exception('Person is not authorised.');
            }
        }
    } 
}