<?php
error_reporting  (E_ALL);

$lines = file("gcal.ini");

$user = trim($lines[0]);
$pass = trim($lines[1]);

$authf = "auth-$user";
print $authf;
if (!file_exists($authf))
{
	$sock = fsockopen("ssl://www.google.com", 443, $errno, $errstr, 30);
	if (!$sock) die("$errstr ($errno)\n");

	$data = "Email=" . urlencode($user) . "&Passwd=" . urlencode($pass)."&source=tevp-gcalsync-1&service=cl";

	fwrite($sock, "POST /accounts/ClientLogin HTTP/1.0\r\n");
	fwrite($sock, "Host: www.google.com\r\n");
	fwrite($sock, "Content-type: application/x-www-form-urlencoded\r\n");
	fwrite($sock, "Content-length: " . strlen($data) . "\r\n");
	fwrite($sock, "Accept: */*\r\n");
	fwrite($sock, "\r\n");
	fwrite($sock, "$data\r\n");
	fwrite($sock, "\r\n");

	$headers = "";
	while ($str = trim(fgets($sock, 4096)))
	$headers .= "$str\n";

	echo "\n";

	$body = "";
	while (!feof($sock))
		$body .= fgets($sock, 4096);

	fclose($sock);
	file_put_contents($authf,$body);
}
else
	$body = file_get_contents($authf);

$lines = explode("\n", $body);
count($lines) == 4 or die("Wrong number of lines (".count($lines).")!");
$auth = $lines[2];
substr($auth,0,5) == "Auth=" or die("Wrong beginning string: '".substr($auth,0,5)."'");
$token = substr($auth, 5);
print "Token: $token\n";

function getAuth($url)
{
	$fname = md5($url);
	$age = filemtime($fname);
	if (!$age || time()-$age > 60*60)
	{
		global $token;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_VERBOSE, True);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, False);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: GoogleLogin auth=$token"));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, True);
		curl_setopt($ch, CURLOPT_AUTOREFERER, True);
		$ret = curl_exec($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($code == 200)
			file_put_contents($fname, $ret);
		else
			die("code for $url is $code\n");
	}
	else
		$ret = file_get_contents($fname);
	return $ret;
}
$callist = getAuth("http://www.google.com/calendar/feeds/default/allcalendars/full");

$doc = new DOMDocument();
$doc->loadXML($callist);
#$sels = $doc->getElementsByTagName("gCal:selected");
$sels = $doc->getElementsByTagName("entry");

print_r($sels->item(0));
die();

foreach ($doc->getElementsByTagName("gCal:selected") as $sel)
{
	print_r($sel);
}

print_r($doc->saveXML());

$allcals = simplexml_import_dom($doc);
#$allcals = new SimpleXMLElement($callist);
foreach ($allcals->entry as $entry)
{
	print_r($entry);
	if ((string)$entry->{'gCal:selected'}['value'] == 'true')
		print $entry->id." ".$entry->title."\n";
}
?>
