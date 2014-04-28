<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class manages the profiles of groups:
 *
 * @author Toni
 */
class Xodx_GroupprofileController extends Xodx_ResourceController
{

    /**
     * A view action to show the profiles of the groups
     * @param Saft_Layout $template used template
     * @return Saft_Layout modified template
     */
    public function listAction($template)
    {
        $model = $this->_app->getBootstrap()->getResource('Model');
        $groupprofiles = $model->sparqlQuery(
            'PREFIX foaf: <http://xmlns.com/foaf/0.1/> ' .
            'SELECT DISTINCT ?group ' .
            'WHERE { ' .
            '   ?group a foaf:Group . ' .
            '}'
        );

        $groups = array();

        $nameHelper = new Xodx_NameHelper($this->_app);

        foreach ($groupprofiles as $groupprofile) {
            $groups[] = array(
                'group' => $groupprofile['group'],
                'name' => $nameHelper->getName($groupprofile['group'])
            );
        }

        $template->grouplistList = $groups;
        $template->addContent('templates/grouplist.phtml');

        return $template;
    }
}
