<?
/**
 * Upcoming events export fixer
 * copyright (c) 2008 Tom Parker
 * timezones@tevp.net
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

error_reporting(E_ERROR);

require_once( 'iCalcreator.class.php' );
require_once( 'tz.php' );
$out = new vcalendar();
$v = new vcalendar(); // create a new calendar instance
$out->setXprop("X-WR-CALNAME","Upcoming events");
$out->setConfig( 'unique_id', 'tevp.net-projects-calendars-upcoming' ); // set Your unique id, required if property UID is missing
$out->setProperty( 'method', 'PUBLISH' ); // required of some calendar software

$timezone = $_GET["timezone"];
$webcal = $_GET["webcal"];

if (!isset($timezone))
	$timezone = "Europe/London";

if (isset($action) && $action == "generate")
{
	preg_match("/webcal:\/\/upcoming.yahoo.com\/calendar\/v2\/my_events\/(\d+)\//", $webcal, $matches);
	if (count($matches) == 2)
	{
		$url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?id=".$matches[1]."&timezone=".$timezone;
		print "Calendar URL is <a href=\"$url\">$url</a><br /><br />\n";
	}
	else
	{
		print_r($matches);
		print "Bad matches! '$webcal' is not a valid Upcoming webcal URL!<br />\n";
	}
}

if (!isset($id))
{
	print "<form id=\"form\" action=\"{$_SERVER['PHP_SELF']}\">\n";
	?>
<input type="hidden" name="action" value="generate" />
Timezone: <select name="timezone" id="timezone">
<?
	foreach ($_tz as $key => $value)
	{
		$nice = str_replace("_"," ", $key);
		if ($key == $timezone)
			print "<option selected value=\"$key\">$nice</option>\n";
		else
			print "<option value=\"$key\">$nice</option>\n";
	}
?>
</select><br/>
Upcoming iCal url (goto <a href="http://upcoming.yahoo.com/myevents/">here</a>, click "subscribe all events", right-click on "Add to iCal" and copy the URL):
<?
	
	print "<input type=\"text\" name=\"webcal\" value=\"$webcal\"><br />\n";
	print "<input type=\"submit\" name=\"blah\" value=\"Get calendar URL\">\n";
	print "</form>\n";
	exit;
}

$self_age = filemtime($_SERVER['SCRIPT_FILENAME']);
$self_age_fm = strftime("%Y%M%DT%H%M%SZ", $self_age);

$fname = "upcoming-$id.ics";
$age = filemtime($fname);
if (!$age || time()-$age > 60*60)
{
	$data = file_get_contents("http://upcoming.yahoo.com/calendar/v2/my_events/$id/");
	if ($data == "")
		die("Can't read from Upcoming!\n");
	file_put_contents($fname, $data);
}

$v->parse($fname);

$tz = genTimezone ($timezone);
$tzid = $tz->getProperty("TZID");

$out->addComponent($tz);

while( $vevent = $v->getComponent( 'vevent' )) {
	$start = $vevent->getProperty('DTSTART');
	$start['tz'] = $tzid;
	$vevent->setProperty('DTSTART',$start);

	$end = $vevent->getProperty('DTEND');
	$end['tz'] = $tzid;
	$vevent->setProperty('DTEND',$end);

	$vevent->deleteProperty('CLASS'); // stop blanking some events

	$stamp = $vevent->getProperty('DTSTAMP');
	$stamp = mktime($stamp['hour'],$stamp['minute'],$stamp['second'],$stamp['month'],$stamp['day'],$stamp['year']);
	if ($stamp < $self_age)
	{
		$vevent->setProperty('DTSTAMP', array("timestamp"=>$self_age));
		$vevent->setProperty('LAST-MODIFIED', array("timestamp"=>$self_age));
	}

	$out->addComponent($vevent);
}

$str = $out->createCalendar();                   // generate and get output in string, for testing?
echo $str;
?>

