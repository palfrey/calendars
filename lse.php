<?php
/**
 * LSE events feed
 * copyright (c) 2008 Tom Parker
 * lseevents@tevp.net
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

require_once('iCalcreator.class.php' );

$date = localtime(time(),true);
//print_r($date);
$month = $date['tm_mon']+1;
$year = $date['tm_year']+1900;

if ($month == 1)
{
	$prev_year = $year-1;
	$prev_month = 12;
}
else
{
	$prev_year = $year;
	$prev_month = $month-1;
}

if ($month == 12)
{
	$next_year = $year+1;
	$next_month = 1;
}
else
{
	$next_year = $year;
	$next_month = $month+1;
}

$fname = "lse-".$prev_month.".".$prev_year."-".$next_month.".".$next_year;
print $fname;

if (!file_exists($fname))
{
	$data = file_get_contents("http://www.lse.ac.uk/resources/search/events?eventtime=select&pagesize=0&sort=eventtime_asc&time_s_mon=".$prev_month."&time_s_year=".$prev_year."&time_e_mon=".$next_month."&time_e_year=".$next_year."&submit=Browse");
	file_put_contents($fname,$data);
}
else
{
	$data = file_get_contents($fname);
}

preg_match_all("|<li>(.*?)</li>|ms",$data,$items,PREG_SET_ORDER);
foreach ($items as $x)
{
	print(strip_tags($x[0]));
	print "<br/>\n";
}
print_r($items);
?>
