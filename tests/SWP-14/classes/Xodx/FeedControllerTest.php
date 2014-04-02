<?php
/**
 * This file is part of the {@link http://aksw.org/Projects/Xodx Xodx} project.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class Xodx_FeedControllerTest extends PHPUnit_Framework_Textcase
{

    public function testGetFeedAction()
    {
        
    }

    public function testFeedToActivity ()
    {
        
    }

    public function testGetFeedResource()
    {
        
    }

    public  function testFeedAction ()
    {
     $feed = '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:activity="http://activitystrea.ms/schema/1.0/">
    <title>Activity Feed for Norman Radtke</title>
    <id>http://xodx.local/?c=person&amp;id=splatte</id>
    <link rel="hub" href="http://pubsubhubbub.appspot.com"/>
    <link rel="self" type="application/atom+xml" href="http://xodx.local/?c=feed&amp;a=getFeed&amp;uri=http%3A%2F%2Fxodx.local%2F%3Fc%3Dperson%26id%3Dsplatte"/>
    <updated>2012-10-29T19:57:26+01:00</updated>


    <entry>
      <title>&quot;Norman Radtke&quot; did &quot;http://xmlns.notu.be/aair#Post&quot; a &quot;http://rdfs.org/sioc/ns#Comment&quot;</title>
      <id>' . htmlentities($this->_app->getBaseUri() . '?c=resource&id=1234' . md5(rand())) . '</id>
      <link href="http://xodx.local/?c=resource&amp;id=e72f0e767aea9e952478ecef5973c8c3" />
      <published>2012-10-29T19:57:26+01:00</published>
      <updated>2012-10-29T19:57:26+01:00</updated>
      <author>
        <name>Norman Radtke</name>
        <uri>http://xodx.local/?c=person&amp;id=splatte</uri>
      </author>
      <activity:verb>http://xmlns.notu.be/aair#Post</activity:verb>
      <activity:object>
        <id>http://xodx.local/?c=resource&amp;id=35da4c92351534bc362fdbc5be62fe27</id>

        <content>Hallo</content>

        <published>2012-10-29T19:57:26+01:00</published>

        <activity:object-type>http://rdfs.org/sioc/ns#Comment</activity:object-type>
              </activity:object>

          </entry>

</feed>';
     //TODO change this to an (feedcontroller-)instance for testing purposes 
     $this->feedToActivity($feed);
     return $template;
    }
}
