tz.php: genTz.php allzones.ics
	php genTz.php allzones.ics > tz.php

allzones.ics: genTimezones.pl
	perl genTimezones.pl > allzones.ics

