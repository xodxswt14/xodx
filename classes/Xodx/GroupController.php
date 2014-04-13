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
            $groupUri = $this->_app->getBaseUri() . '?c=Group&id=' . urlencode($groupUri);
        }

        // verify that there is not already a group with that name            
        $testQuery  = 'ASK {' . PHP_EOL;
        $testQuery .= '<' . $groupUri . '> ?p ?o' . PHP_EOL;
        $testQuery .= '}';            
        $result = $model->sparqlQuery($testQuery);
        if ($model->sparqlQuery($testQuery)) {                
            die('Gruppe existiert bereits');
            // @todo throw Exception & log event
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
                    )                     
                )
            );
            $model->addMultipleStatements($newGroup);
        }
    }              
     /**
     * This deletes a group with the given name.
     * This function is usually called internally
     * @param Uri $groupUri Uri of the group to be deleted
     * @param String $name Name of the group to be deleted
     * @todo $groupUri might not be needed
     */
    public function deleteGroup ($groupUri = null, $name)
    {
        // getResources & set namespaces
        $bootstrap = $this->_app->getBootstrap();     
        $model = $bootstrap->getResource('model');
        $logger = $bootstrap->getResource('logger');

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsDssn = 'http://purl.org/net/dssn/';

        // fetch empty groupUri
        if ($groupUri === null) {
            $groupUri = $this->_app->getBaseUri() . '?c=Group&id=' . urlencode($groupUri);
        }

        // verify that there is a group with that name            
        $testQuery  = 'ASK {' . PHP_EOL;
        $testQuery .= '<' . $groupUri . '> ?p ?o' . PHP_EOL;
        $testQuery .= '}';            
        $result = $model->sparqlQuery($testQuery);
        if (!$result) {
            die('Gruppe existiert nicht');
            // @todo throw Exception & log event
        } else {                                                                                                  
            // feed of the group
            $groupFeed = $this->_app->getBaseUri() . '?c=feed&a=getFeed&uri=' . urlencode($groupUri);
            // Uri of the group's admin ( its foaf:maker)
            $userController = $this->_app->getController('Xodx_UserController');                
            $userUri = $userController->getUser()->getPerson();

            //verify that User is admin of the group
            $makerQuery  = 'PREFIX foaf: <' . $nsFoaf . '> ' . PHP_EOL;
            $makerQuery .= 'SELECT ?maker' . PHP_EOL;
            $makerQuery .= 'WHERE {' .PHP_EOL;
            $makerQuery .= '   ?maker foaf:maker ' . '<' . $groupUri . '> .' .PHP_EOL;
            $makerQuery .= '}';
            $makerResult = $model->sparqlQuery($testQuery);
            $maker = $makerResult[0]['maker'];
            
            if ($maker == $userUri) {
                $deleteGroup = array(
                    $groupUri => array(
                        EF_RDF_TYPE => array(
                            array('type' => 'uri', 'value' => $nsFoaf . 'Group')
                        ),
                        $nsDssn . 'activityFeed' => array(
                            array('type' => 'uri', 'value' => $groupFeed)
                        ),
                        $nsFoaf . 'maker' => array(
                            array('type' => 'uri', 'value' => $userUri)
                        ),
                        $nsFoaf . 'nick' => array(
                            array('type' => 'literal', 'value' => $name)
                        )                     
                    )
                );
                $model->deleteMultipleStatements($deleteGroup);
            } else {
                die('User ist nicht Gruppenersteller');
                // @todo throw Exception & log event    
            }
        }
    }  
}