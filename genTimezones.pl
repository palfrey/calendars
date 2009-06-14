# Timezone data generator
# copyright (c) 2009 Tom Parker
# timezones@tevp.net
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public
# License as published by the Free Software Foundation; either
# version 3 of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program; if not, see <http://www.gnu.org/licenses/>.

use Data::ICal::TimeZone;

print "BEGIN:VCALENDAR\n";
foreach (Data::ICal::TimeZone->zones)
{
	my $z = Data::ICal::TimeZone->new( timezone => $_ );
	print $z->definition->as_string;
}
print "END:VCALENDAR\n";

