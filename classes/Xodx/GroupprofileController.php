<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @author Toni
 * Temporary controller for template testing.
 */
class Xodx_GroupprofileController extends Xodx_ResourceController
{
    public function listAction($template)
    {
        $model = $this->_app->getBootstrap()->getResource('Model');

        $groupprofiles = $model->sparqlQuery(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' .
            'SELECT DISTINCT ?person ' .
            'WHERE { ' .
            '   ?person a foaf:Person . ' .
            '}'
        );

        $groups = array();

        $nameHelper = new Xodx_NameHelper($this->_app);

        foreach ($groupprofiles as $groupprofile) {
            $groups[] = array(
                'group' => $groupprofile['person'],
                'name' => $nameHelper->getName($groupprofile['person'])
            );
        }

        $template->grouplistList = $groups;
        $template->addContent('templates/grouplist.phtml');

        return $template;
    }
}
