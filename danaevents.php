<?php
/**
 * Dana events feed
 * copyright (c) 2008 Tom Parker
 * danaevents@tevp.net
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
require_once('iCalcreator.class.php' );

$lr = new lastRSS();
$data = $lr->Get("http://feeds.feedburner.com/danaevents");
/*print_r($data);
exit;*/

$v = new vcalendar(); // create a new calendar instance
$v->setConfig( 'unique_id', $data['link']); // set your unique id
$v->setProperty( 'method', 'PUBLISH' ); // required of some calendar software
$v->setConfig('filename',"danaevents-".$v->getConfig('filename'));

$v->setXprop("X-WR-CALNAME",$data['title']);
$v->setXprop("X-WR-CALDESC",$data['description']);

foreach($data['items'] as $item)
{
	$lines = explode("\n",$item['description']);
	//<p>28/05/2008, 19:00 - 20:30</p>
	//print $lines[count($lines)-1];
	preg_match("/(\d+)\/(\d+)\/(\d+), (\d+):(\d+) - (\d+):(\d+)/",$lines[count($lines)-1],$matches);
	//print_r($matches);
	//exit;
	$vevent = new vevent(); // create an event calendar component
	$vevent->setProperty( 'dtstart', array( 'year'=>$matches[3], 'month'=>$matches[2], 'day'=>$matches[1], 'hour'=>$matches[4], 'min'=>$matches[5],  'sec'=>0 ));
	$vevent->setProperty( 'dtend', array( 'year'=>$matches[3], 'month'=>$matches[2], 'day'=>$matches[1], 'hour'=>$matches[6], 'min'=>$matches[7],  'sec'=>0 ));
	$vevent->setProperty( 'LOCATION', '165 Queen\'s Gate, South Kensington, London, SW7 5HD' ); // property name - case independent
	$vevent->setProperty( 'summary', $item['title'] );
	
	
	preg_match("/href=\"([^\"]+)/",$lines[0],$matches);
	array_pop($lines);
	array_pop($lines);
	$desc = trim(strip_tags(html_entity_decode(implode("\n",$lines))));
	$desc = str_replace("’","'",$desc);
	$desc = str_replace("‘","'",$desc);
	$desc .= "\n\n<a href=\"".$matches[1]."\">More info</a>";
	$vevent->setProperty( 'description',  $desc);

	$v->setComponent ( $vevent ); // add event to calendar
}
//$v->returnCalendar();
print $v->createCalendar();
?>
