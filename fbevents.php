<?
require_once( 'iCalcreator.class.php' );
require_once( 'tz.php' );
$out = new vcalendar();
$v = new vcalendar(); // create a new calendar instance
$out->setXprop("X-WR-CALNAME","Facebook events");
$out->setConfig( 'unique_id', 'tevp.net' ); // set Your unique id, required if property UID is missing
$out->setProperty( 'method', 'PUBLISH' ); // required of some calendar software

$uid = $_GET["uid"];
$key = $_GET["key"];
$timezone = $_GET["timezone"];
$fb_url = $_GET["fb_url"];

$fb_url = "http://www.facebook.com/ical/u.php?uid=707610112&key=7d5c604e1c";

#$action = "generate";

if (!isset($timezone))
	$timezone = "Europe/London";

if (isset($action) && $action == "generate")
{
	preg_match("/http:\/\/www.facebook.com\/ical\/u.php\?uid=(\d+)&key=([a-f\d]+)/", $fb_url, $matches);
	count($matches) == 3 or die("Bad matches!\n");
	$url = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?uid=".$matches[1]."&key=".$matches[2];
	print "Link is $url<br />\n";
}

if (!isset($key) || !isset($uid) || isset($action))
{
	print "<form id=\"form\" action=\"{$_SERVER['PHP_SELF']}\">\n";
	?>
<input type="hidden" name="action" value="generate" />
<select name="timezone" id="timezone">
<?
	foreach ($_tz as $key => $value)
	{
		if ($key == $timezone)
			print "<option selected value=\"$key\">$key</option>\n";
		else
			print "<option value=\"$key\">$key</option>\n";
	}
?>
</select>
<?
	print "<input type=\"text\" name=\"fb_url\" value=\"$fb_url\">\n";
	print "</form>\n";
	exit;
}

#$uid = "707610112";
#$key = "7d5c604e1c";

$fname = "facebook-$uid-$key.ics";
$age = filemtime($fname);
if (!$age || time()-$age > 60*60)
{
	$data = file_get_contents("http://www.facebook.com/ical/u.php?uid=$uid&key=$key");
	if ($data == "")
		die("Can't read from Facebook!\n");
	file_put_contents($fname, $data);
}

$v->parse($fname);

$tz = genTimezone ($timezone);
$tzid = $tz->getProperty("TZID");

$out->addComponent($tz);

while( $vevent = $v->getComponent( 'vevent' )) {
	$description = $vevent->getProperty( 'description' );
	#$description = urlencode($description);
	$vevent->deleteProperty('description');
	$vevent->setProperty('description',$description);
	
	$start = $vevent->getProperty('DTSTART');
	$start['tz'] = $tzid;
	$vevent->setProperty('DTSTART',$start);

	$end = $vevent->getProperty('DTEND');
	$end['tz'] = $tzid;
	$vevent->setProperty('DTEND',$end);

	$out->addComponent($vevent);
}

$str = $out->createCalendar();                   // generate and get output in string, for testing?
echo $str;
?>

