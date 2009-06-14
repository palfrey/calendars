<?
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

