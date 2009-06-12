<?
require_once( 'iCalcreator.class.php' );
$out = new vcalendar();
$v = new vcalendar(); // create a new calendar instance
$out->setXprop("X-WR-CALNAME","Facebook events");
$out->setConfig( 'unique_id', 'tevp.net' ); // set Your unique id, required if property UID is missing
$out->setProperty( 'method', 'PUBLISH' ); // required of some calendar software

$uid = "707610112";
$key = "7d5c604e1c";

$fname = "facebook-$uid-$key.ics";
$age = filemtime($fname);
if (!$age || time()-$age > 60*60)
#if (!$age)
{
	$data = file_get_contents("http://www.facebook.com/ical/u.php?uid=$uid&key=$key");
	if ($data == "")
		die("Can't read from Facebook!\n");
	file_put_contents($fname, $data);
}

$v->parse($fname);

$tz = new vtimezone();
$standard = new vtimezone("standard");
$daylight = new vtimezone("daylight");
$tz->addSubComponent($standard);
$tz->addSubComponent($daylight);
$out->addComponent($tz);

while( $vevent = $v->getComponent( 'vevent' )) {
	$description = $vevent->getProperty( 'description' );
	$description = str_replace("\r","",$description);
	$vevent->deleteProperty('description');
	$vevent->setProperty('description',$description);
	
	$start = $vevent->getProperty('DTSTART');
	$start['tz'] = "GMT-0";
	$vevent->setProperty('DTSTART',$start);

	$end = $vevent->getProperty('DTEND');
	$end['tz'] = "GMT-0";
	$vevent->setProperty('DTEND',$end);

	$out->addComponent($vevent);
}

$str = $out->createCalendar();                   // generate and get output in string, for testing?
echo $str;
?>

