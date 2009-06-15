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

require_once( 'iCalcreator.class.php' );
require_once( 'tz.php' );

$v = new vcalendar(); // create a new calendar instance

$data = get ("http://dorkbotlondon.org/feeds/icalendar/");
$v->parseString($data);

$out = new vcalendar();
$out->setXprop("X-WR-CALNAME","Dorkbot London events");
$out->setConfig( 'unique_id', 'tevp.net' ); // set Your unique id, required if property UID is missing
$out->setProperty( 'method', 'PUBLISH' ); // required of some calendar software

function get($url, $max_age = 3600)
{
	if (!file_exists("cache"))
		mkdir("cache");
	$fname = "cache/".md5($url);
	$age = filemtime($fname);
	if (!$age || ($max_age != -1 && time()-$age > $max_age))
	{
		$ret = file_get_contents($url);
		file_put_contents($fname, $ret);
	}
	else
		$ret = file_get_contents($fname);
	return $ret;
}

$timezone = "Europe/London";
$tz = genTimezone ($timezone);
$tzid = $tz->getProperty("TZID");
$out->addComponent($tz);

while( $vevent = $v->getComponent( 'vevent' )) {
	$url = $vevent->getProperty("SUMMARY");
	$data = get($url, -1);
	if ($data != "")
	{
		preg_match("/(<h2>.*?)<h2>/s", $data, $matches);
		$data = $matches[1];
		$lines = explode("\n", $data, 2);
		$title = strip_tags($lines[0]);
		#print "Title: $title\n";
		$vevent->setProperty("SUMMARY", $title);

		preg_match("/When:<\/dt><dd>([^<]+)<\/dd>/", $data, $matches);
		$when = explode(",", $matches[1]);
		$time = strptime($when[1], "%e %B %Y");
		$times = explode("-",$when[0]);

		$start = $vevent->getProperty('DTSTART');
		$start['tz'] = $tzid;
		$vevent->setProperty('DTSTART',$start);

		$end = $vevent->getProperty('DTEND');
		$end['tz'] = $tzid;
		$vevent->setProperty('DTEND',$end);

		$content = trim(substr(strstr($data, "</dl>"), 5));
		#print $content."\n";
		$vevent->setProperty('description',$content);
	}
	$loc = $vevent->getProperty("location");
	if ($loc == "Limehouse town hall")
		$vevent->setProperty("location","Limehouse Town Hall, 646 Commercial Road London, E14 7HA, United Kingdom");

	$out->addComponent($vevent);
}

print $out->createCalendar();
?>
