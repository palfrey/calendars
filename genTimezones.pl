use Data::ICal::TimeZone;

print "BEGIN:VCALENDAR\n";
foreach (Data::ICal::TimeZone->zones)
{
	my $z = Data::ICal::TimeZone->new( timezone => $_ );
	print $z->definition->as_string;
}
print "END:VCALENDAR\n";

