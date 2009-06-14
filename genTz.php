<?
/**
 * Timezone data generator
 * copyright (c) 2009 Tom Parker
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

require_once( 'iCalcreator.class.php' );
$v = new vcalendar(); // create a new calendar instance
$v->parse($argv[1]);

echo "<?\n\$_tz = array(";
foreach ($v->components as $tz)
{
	if (!is_a($tz, "vtimezone"))
		continue;
	$location = $tz->getProperty("X-LIC-LOCATION");
	$name = trim($location[1]);
	if ($name == "")
	{
		$location = $tz->getProperty("TZID");
		$name = trim($location);
	}
	$junk = array();
	echo "\"$name\" => \"".urlencode($tz->createComponent($junk))."\",\n";
}
echo <<<EOF
);

function genTimezone(\$name)
{
	global \$_tz;
	\$raw = urldecode(\$_tz[\$name]);
	\$ical = "BEGIN:VCALENDAR\\n".\$raw."\\nEND:VCALENDAR\\n";
	\$vcal = new vcalendar();
	\$vcal->parseString(\$ical);
	return \$vcal->components[0];
}

?>
EOF
?>

