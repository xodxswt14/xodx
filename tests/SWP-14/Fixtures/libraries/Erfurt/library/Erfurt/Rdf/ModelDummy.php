<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class is a Erfurt_Rdf_Model dummy.
 * @author Stephan
 */
class ModelDummy
{
    /**
     *
     * @var String contains information about the current tested method
     */
    public $testMethod = null;
    /**
     * 
     * @param String $testMethod this is needed to return different sparqlQuerys
     */
    public function __construct($testMethod) {
        $this->testMethod = $testMethod;
    }
    /**
     * A positive query returns a valid subUri.
     * @param type $query
     * @param type $options
     * @return subUri valid
     */
    public function sparqlQuery($query, $options = array())
    {
        $result = null;
        //used for UserControllerTest: testUnsubscribeFromFeed
        if ($this->testMethod == 'testUnsubscribeFromFeed') {
            $result = array();
            $result[0]['subUri'] = 'validSubUri';      
        }
        //used for GroupControllerTest: testCreateGroup groupname exists
        if ($this->testMethod == 'testCreateGroup') {
            $result = FALSE;
        }
        //used for GroupControllerTest: testDeleteGroup 
        if ($this->testMethod == 'testDeleteGroup') {
            $result = TRUE;
        }
        //used for GroupControllerTest: testGetGroup
        if ($this->testMethod == 'testGetGroup') {
            $result = array();
            $result[0]['name'] = 'validGroupName';
            $result[0]['topic'] = 'validGroupTopic';
        }
        return $result;
    }
    /**
     * Implementation not needed.
     * @param array $statements
     * @param boolean $useAc
     */
    public function deleteMultipleStatements(array $statements, $useAc = true)
    {
    }
    /**
     * Implementation not needed.
     * @param array $statements
     * @param boolean $useAc
     */
    public function addMultipleStatements(array $statements, $useAc = true)
    {
    }
}