<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @author Stephan
 */

class Xodx_ResourceControllerTest extends PHPUnit_Framework_Testcase
{

    public function testIndexAction ()
    {
        
    }

    public function testShowAction ()
    {
        
    }

    public function testRdfAction ()
    {
        
    }

    public function testImgAction ()
    {
        
    }

    public function testGetType ()
    {
        
    }

    public function testGetActivityFeedUri()
    {
        
    }

    public function testImportResource()
    {
        //@TODO change $this to a resourcecontroller instance
        //$template->disableLayout();
        echo $this->importResource('http://dbpedia.org/resource/Hamburger_SV');
        return $template;

    }

}
