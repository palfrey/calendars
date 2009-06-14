<?

error_reporting(E_ERROR);

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

#$fb_url = "http://www.facebook.com/ical/u.php?uid=707610112&key=7d5c604e1c";

#$action = "generate";

if (!isset($timezone))
	$timezone = "Europe/London";

if (isset($action) && $action == "generate")
{
	preg_match("/facebook.com\/ical\/u.php\?uid=(\d+)&key=([a-f\d]+)/", $fb_url, $matches);
	if (count($matches) == 3)
	{
		$url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."?uid=".$matches[1]."&key=".$matches[2]."&timezone=".$timezone;
		print "Calendar URL is <a href=\"$url\">$url</a><br /><br />\n";
	}
	else
		 print "Bad matches! '$fb_url' is not a valid Facebook events URL!<br />\n";
}

if (!isset($key) || !isset($uid))
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
Facebook calendar export url (goto <a href="http://www.new.facebook.com/events.php">here</a>, click "Export events" and copy the URL):
<?
	
	print "<input type=\"text\" name=\"fb_url\" value=\"$fb_url\"><br />\n";
	print "<input type=\"submit\" name=\"blah\" value=\"Get calendar URL\">\n";
	print "</form>\n";
	exit;
}

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

