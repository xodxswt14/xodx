<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once (__DIR__ . '/../../libraries/Saft/ControllerDummy.php');

/**
 * This class is a Xodx_ResourceController dummy.
 * @author Stephan
 */
class ResourceControllerDummy extends ControllerDummy
{
   
    public function indexAction ($template)
    {
    }

    public function showAction ($template)
    {
    }

    public function rdfAction ($template)
    {
    }

    public function imgAction ($template)
    {
    }
    /**
     * If unsubscriberUri (in UserController) is invalid it returns a foafPerson type
     * to get a valid unsubscriberUri
     * 
     * @param type $resourceUri
     * @return foafType
     */
    public function getType ($resourceUri)
    {
        if ($resourceUri == 'invalidUnsubscriberUri') {
            $nsFoaf = 'http://xmlns.com/foaf/0.1/';
            $type = $nsFoaf . 'Person';
        }
        if ($resourceUri == 'validUnsubscriberUri') {
            $type = 'valid';
        }
        return $type;
    }

    public function getActivityFeedUri($resourceUri)
    {
    }

    public function importResource($resourceUri)
    {
    }

    public function testImportResourceAction($template)
    {
    }
}