<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
require_once(__DIR__ . '/../FeedDummy.php');
/**
 * This class is a DSSN_Activity_Feed_Factory dummy.
 * @author Stephan Kemper
 */
class DSSN_Activity_Feed_Factory
{
    public static function newFromUrl($url = null)
    {
        $feed = new DSSN_Activity_FeedDummy();
        return $feed;
    }
}