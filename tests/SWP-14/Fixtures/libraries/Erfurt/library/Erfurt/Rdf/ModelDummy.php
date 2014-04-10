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
     * A positive query returns a valid subUri.
     * @param type $query
     * @param type $options
     * @return subUri valid
     */
    public function sparqlQuery($query, $options = array())
    {
        $result = array();
        $result[0]['suburi'] = 'validSubUri';
        return $result[0]['suburi'];
    }
    
    public function deleteMultipleStatements(array $statements, $useAc = true)
    {
    }
}