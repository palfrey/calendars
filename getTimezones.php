<?
require_once( 'iCalcreator.class.php' );
$v = new vcalendar(); // create a new calendar instance
$v->parse($argv[1]);

echo "<?\n\$tz = array(";
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
echo ");\n?>\n"
?>

