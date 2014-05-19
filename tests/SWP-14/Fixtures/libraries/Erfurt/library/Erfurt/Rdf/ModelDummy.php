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
        //used for Member/UserControllerTest: testUnsubscribeFromFeed
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
            $result = array();
            $result[0]['subscription'] = 'validSubscription';
            $result[0]['member'] = 'validMemberUri';
            $result[0]['topic'] = 'validTopic';
        }
        //used for GroupControllerTest: testGetGroup
        if ($this->testMethod == 'testGetGroup') {
            $result = array();
            $result[0]['name'] = 'validGroupName';
            $result[0]['topic'] = 'validGroupTopic';
        }
        //used for GroupControllerTest: testIsSubscribed
        if ($this->testMethod == 'testIsSubscribed') {
            $result = array();
            $result[0]= 'validSubscriptionState';
        }
        //used for GroupControllerTest: testGetSubscribedResources
        if ($this->testMethod == 'testGetSubscribedResources') {
            $result = array();
            $result[0]['resUri']= 'validResourceUri';
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
     * Implementation not needed
     *
     * @param string|null $subjectSpec
     * @param string|null $predicateSpec
     * @param string|null $objectSpec
     */
    public function deleteMatchingStatements ($subjectSpec, $predicateSpec, $objectSpec, array $options = array())
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
    /**
     * Implementation not needed.
     * @param string $subject
     * @param string $predicate
     * @param array $object
     */
    public function addStatement($subject, $predicate, array $object)
    {
    }
    /**
     * Implementation not needed.
     * @param string $subject
     * @param string $predicate
     * @param array $object
     */
    public function deleteStatement($subject, $predicate, array $object)
    {
    }
}