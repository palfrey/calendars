<?php
/**
 * Dorkbot events feed
 * copyright (c) 2009 Tom Parker
 * dorkbot@tevp.net
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public
 * License along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

require_once('lastRSS.php');
require_once( 'iCalcreator.class.php' );
$v = new vcalendar(); // create a new calendar instance
$v->parse("dorkbot.ics");
$lr = new lastRSS();
$data = $lr->Get("dorkbot.rss");

$mapping = array();

foreach($data['items'] as $item)
{
	$link = $item['link'];
	$lines = explode("\n",html_entity_decode($item['description']));
	$inloc = False;
	$loc = "";
	foreach($lines as $line)
	{
		if ($line == "<h2>Location</h2>")
		{
			$inloc = True;
		}
		else if ($inloc && $line == "<br />")
		{
			$inloc = False;
		}
		else if ($inloc)
		{
			$l = trim(strip_tags($line));
			if ($l == "")
				continue;
			if ($l[-1] != ",")
				$l .= ",";
			$l .= " ";
			$loc .= $l;
		}
	}
	$loc = substr($loc, 0, -2);
	#print "Location: $loc\n";
	$mapping[$link] = urlencode($loc);
}
while( $vevent = $v->getComponent( 'vevent' )) {
	$loc = $mapping[$vevent->getProperty("SUMMARY")];
	if ($loc!= "")
	{
		print "loc: $loc\n";
		$vevent->setProperty( 'LOCATION', $loc);
		$v->addComponent($vevent);
	}
}

print $v->createCalendar();
?>
