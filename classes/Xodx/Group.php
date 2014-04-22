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
 */
class Xodx_Group
{
    /**
    * a group has a unique URI, a name and may also have a Description
    *
    */
    private $_uri;
    private $_name;
    private $_description = null;
    private $_app;

    /**
    * constructs a new group instance with set URI
    *
    */
    public function __construct ($uri, $app)
    {
        $this->_uri = $uri;
        $this->_app = $app;
    }

    /**
    * Method to get the URI of the group
    *
    */
    public function getUri ()
    {
        return $this->_uri;
    }

    /**
    * Method to get the name of the group
    *
    */
    public function getName ()
    {
        return $this->_name;
    }

    /**
    * Method to set the name of the group
    *
    */
    public function setName ($name)
    {
        $this->_name = $name;
    }

    /**
    * Method to get the description of the group from the database
    *
    */
    public function getDescription ()
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        // SPARQL-Query
        $query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' . PHP_EOL;
        $query.= 'SELECT  ?description ' . PHP_EOL;
        $query.= 'WHERE {' . PHP_EOL;
        $query.= '   <' . $this->_uri . '> foaf:topic ?description' . PHP_EOL;
        $query.= '}' . PHP_EOL;

        $groupDescription = $model->sparqlQuery($query);

        return $this->$groupDescription[0]['description'];
    }

    /**
    * Method to set the description of the group
    *
    * @param string $description contains the group topic
    */
    public function setDescription ($description)
    {
        $this->_description = $description;
    }
}
