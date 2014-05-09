<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class represents a group it is very similar to a user
 *
 * foaf: http://xmlns.com/foaf/spec/
 *
 * @author Thomas Guett
 * @author Gunnar Warnecke
 */
class Xodx_Group
{
    /**
     * Groups URI
     * @var string
     */
    private $_uri;
    /**
     * Groups name (foaf:nick)
     * @var string
     */
    private $_name;
    /**
     * Groups description (foaf:primaryTopic)
     * @var string
     */
    private $_description = null;
    /**
     * Global app variable 
     * @var Xodx_Application 
     */
    private $_app;

    /**
     * constructs a new group instance with set URI
     * 
     * @param string $uri groups uri
     * @param Xodx_Application $app global app variable
     */
    public function __construct ($uri, $app)
    {
        $this->_uri = $uri;
        $this->_app = $app;
    }

    /**
     * Getter Method for groups uri
     * 
     * @return string groups uri
     */
    public function getUri ()
    {
        return $this->_uri;
    }

    /**
     * Getter Method for groups name
     * 
     * @return string groups name
     */
    public function getName ()
    {
        return $this->_name;
    }

    /**
     * Setter Method for groups name
     * 
     * @param string $name groups name
     */
    public function setName ($name)
    {
        $this->_name = $name;
    }

    /**
     * Method to get the description of the group from the database
     * 
     * @return string groups description from database
     */
    public function getDescription ()
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        // SPARQL-Query
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
        $query.= 'SELECT  ?description ' . PHP_EOL;
        $query.= 'WHERE {' . PHP_EOL;
        $query.= '   <' . $this->_uri . '> foaf:primaryTopic ?description' . PHP_EOL;
        $query.= '}' . PHP_EOL;

        $groupDescription = $model->sparqlQuery($query);

        return $this->$groupDescription[0]['description'];
    }

    /**
    * Method to set the description of the group
    *
    * @param string $description contains the group topic (foaf:primaryTopic)
    */
    public function setDescription ($description)
    {
        $this->_description = $description;
    }
}
