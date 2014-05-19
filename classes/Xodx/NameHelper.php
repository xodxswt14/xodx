<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class Xodx_NameHelper extends Saft_Helper
{
    private $_names;
    private $_languages;
    private $_properties;

    public function __construct ($app)
    {
        $this->_app = $app;
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $this->_languages = $this->_parseLanguageString($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        } else {
            $this->_languages = array();
        }
        $this->_languages[] = '';

        $nsFoaf = 'http://xmlns.com/foaf/0.1/';
        $nsRdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $nsRdfs = 'http://www.w3.org/2000/01/rdf-schema#';
        $nsDc = 'http://purl.org/dc/terms/';
        $this->_properties = array($nsFoaf . 'name', $nsFoaf . 'nick', $nsRdfs . 'label', $nsDc . 'title');
    }

    /**
     *
     * Method tries to find a name with help of predicates given in _properties.
     * @param string $resourceUri or false if no success
     * @param boolean $cache (default true) option to cache a found name
     * @param boolean $redirect (default true) option to use linked data
     */
    public function getName ($resourceUri, $cache = true, $redirect = true)
    {
        $bootstrap = $this->_app->getBootstrap();
        $model = $bootstrap->getResource('model');

        $query = '' .
            'SELECT ?name ' .
            'WHERE { ';
        foreach ($this->_properties as $property) {
            $query .= '   { <' . $resourceUri . '> <' . $property . '> ?name . } ' .
                ' UNION ';
        }

        if (substr($query, -7, 7) == ' UNION ') {
            $query = substr($query, 0, strlen($query) - 7);
        }

        $query .= ' FILTER(';
        foreach ($this->_languages as $language) {
            $query .= ' lang(?name) = "' . $language . '" ||';
        }

        if (substr($query, -2, 2) == '||') {
            $query = substr($query, 0, strlen($query) - 2);
        }
        $query .= ') .';
        $query .= '} ';

        $names = $model->sparqlQuery($query);

        if (isset($names[0]['name'])) {
            return $names[0]['name'];
        }

        if ($redirect === true) {
            return $this->_getNameByLinkedData($resourceUri, $cache);
        } else {
            return false;
        }
    }

    /**
     *
     * Method can be used to look up a resources name with help of linked data
     * @param string $resourceUri
     * @param boolean $cache option to save found predicate and name in store (default = true)
     * @return string of the found name or false if no success
     */
    private function _getNameByLinkedData ($resourceUri, $cache = true) {

        $linkeddataHelper = $this->_app->getHelper('Saft_Helper_LinkeddataHelper');
        $statements = $linkeddataHelper->getResource($resourceUri);

        if ($statements !== null) {
            $memModel = new Erfurt_Rdf_MemoryModel($statements);

            $returnStatement = $memModel->getPO($resourceUri);

            foreach ($returnStatement as $predicate => $objectStatement) {
                if (in_array($predicate, $this->_properties)) {
                    if ($cache === true) {
                        $nameObject = array(
                            'type' => 'literal',
                            'value' => $objectStatement[0]['value']
                        );

                        $bootstrap = $this->_app->getBootstrap();
                        $model     = $bootstrap->getResource('model');
                        $model->addStatement($resourceUri, $predicate, $nameObject);
                    }
                    return $objectStatement[0]['value'];
                }
            }
        }
        return false;
    }

    private function _parseLanguageString ($langString)
    {
        $langcode = explode(",", $langString);
        $langPriority = array();

        foreach ($langcode as $lang) {
            $lang = explode(";", $lang);
            $langPriority[] = $lang[0];
        }
        return $langPriority;
    }

    /**
     * Temporarily moved here because of necessary changes
     * Looks up if a natural word instead of $uri exists and returns this
     *
     * @param unknown_type $uri a uri
     * @return string spoken word
     */
    public static function getSpokenWord($uri)
    {

        $nsXsd      = 'http://www.w3.org/2001/XMLSchema#';
        $nsRdf      = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $nsSioc     = 'http://rdfs.org/sioc/ns#';
        $nsSioct    = 'http://rdfs.org/sioc/types#';
        $nsAtom     = 'http://www.w3.org/2005/Atom/';
        $nsAair     = 'http://xmlns.notu.be/aair#';
        $nsXodx     = 'http://xodx.org/ns#';
        $nsFoaf     = 'http://xmlns.com/foaf/0.1/';
        $nsOv       = 'http://open.vocab.org/docs/';
        $nsPingback = 'http://purl.org/net/pingback/';
        $nsDssn     = 'http://purl.org/net/dssn/';

        $words = array(
            $nsAair  . 'MakeFriend'     => 'friended',
            $nsAair  . 'Post'           => 'posted',
            $nsAair  . 'Share'          => 'shared',
            $nsAair  . 'Note'           => 'status note',
            $nsAair  . 'Bookmark'       => 'link',
            $nsAair  . 'Photo'          => 'image',
            $nsAair  . 'Comment'        => 'comment',
            $nsSioct . 'Comment'        => 'comment',
            $nsSioc  . 'Comment'        => 'comment',
            $nsSioc  . 'Note'           => 'status note',
            $nsFoaf  . 'Image'          => 'image',
            $nsAair  . 'Join'           => 'joined',
            $nsAair  . 'StartFollowing' => 'has new member'
        );

        if (isset($words[$uri])) {
            return $words[$uri];
        } else {
            return $uri;
        }
    }

    /**
     * Method to get a base uri of a given resource
     * 
     * @param string $resourceUri
     * @return string the baseUri searched for
     */
    public function getBaseUriByResourceUri($resourceUri)
    {
        $baseUri = "";
        if (($baseUriArray = parse_url($resourceUri)) && (count($baseUriArray) > 1)) {
            $baseUri = $baseUriArray['scheme'] . '://'
                 . $baseUriArray['host'];
            if (!empty($baseUriArray['port'])) {
                $baseUri.= ':' . $baseUriArray['port'];
            }
            if (!empty($baseUriArray['path'])) {
                $baseUri.= $baseUriArray['path'];
            }
            if(substr($baseUri, -1) != '/') {
                $baseUri.= '/';
            }
        }
        return $baseUri;
    }
}
