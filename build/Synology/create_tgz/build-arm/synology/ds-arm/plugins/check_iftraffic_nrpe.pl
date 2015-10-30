#!/usr/bin/perl -w
#
# nrpe plugin to monitor network bandwidth
# Created by Van Dyck Sven
# mobilesvenvd@gmail.com
#
# v0.91 Change performance data output to work together with pnp4nagios
# v0.9 Initial version
#
# Script based on check_iftraffic.pl


use strict;

use Getopt::Long;
&Getopt::Long::config('bundling');

use Data::Dumper;

my $iface_descr;
my $iface_speed;
my $opt_h;
my $units;
my $line;
my $error;

my @splitLine;


# Path to  tmp files
my $TRAFFIC_FILE = "/tmp/traffic";

# changes sos 20090717 UNKNOWN must bes 3
my %STATUS_CODE =
  ( 'UNKNOWN' => '3', 'OK' => '0', 'WARNING' => '1', 'CRITICAL' => '2' );

#default values;
my ( $in_bytes, $out_bytes ) = 0;
my $warn_usage = 85;
my $crit_usage = 98;
my $o_noreg    =  undef;  # Do not use Regexp for name


#added 20050614 by mw
my $max_value;
my $max_bytes;

#cosmetic changes 20050614 by mw, see old versions for detail
my $status = GetOptions(
	"h|help"        => \$opt_h,
	"w|warning=s"   => \$warn_usage,
	"c|critical=s"  => \$crit_usage,
	"b|bandwidth=i" => \$iface_speed,
        'r'             => \$o_noreg,           
        'noregexp'      => \$o_noreg,
	"u|units=s"     => \$units,
	"i|interface=s" => \$iface_descr,

	#added 20050614 by mw
	"M|max=i" => \$max_value
);

if ( $status == 0 ) {
	print_help();
	exit $STATUS_CODE{'OK'};
}

if ( ( !$iface_descr ) or ( !$iface_speed ) ) {
	print_usage();
}

#change 20050414 by mw
$iface_speed = bits2bytes( $iface_speed, $units ) / 1024;
if ( !$max_value ) {

	#if no -M Parameter was set, set it to 32Bit Overflow
	$max_bytes = 4194304 ;    # the value is (2^32/1024)
}
else {
	$max_bytes = unit2bytes( $max_value, $units );
}


#Parse /proc/net/dev on localhost to gather network statistics instead of querying SNMP
open F, "</proc/net/dev" or die "Can't open /proc/net/dev: $!";
my @f = <F>;
close F;

foreach (@f) {
        if ($_ =~ /${iface_descr}/){
                $line=$_;
                last;
                #Interface found, exiting loop
        }
}

$line =~ s/\s+/ /g;
@splitLine=split (/ /,$line);

(undef,$in_bytes)=split (/:/,$splitLine[1]);
$out_bytes=$splitLine[9];


$in_bytes  = $in_bytes / 1024;
$out_bytes = $out_bytes / 1024;


#end network statistics gathering

#Starting calculations

my $row;
my $last_check_time = time - 1;
my $last_in_bytes   = $in_bytes;
my $last_out_bytes  = $out_bytes;

if (
	open( FILE,
		"<" . $TRAFFIC_FILE . "_if" . $iface_descr
	)
  )
{
	while ( $row = <FILE> ) {

		#cosmetic change 20050416 by mw
		#Couldn't sustain;-)
##		chomp();
		( $last_check_time, $last_in_bytes, $last_out_bytes ) =
		  split( ":", $row );

### by sos 17.07.2009 check for last_bytes
if ( ! $last_in_bytes  ) { $last_in_bytes=$in_bytes;  }
if ( ! $last_out_bytes ) { $last_out_bytes=$out_bytes; }

if ($last_in_bytes !~ m/\d/) { $last_in_bytes=$in_bytes; }
if ($last_out_bytes !~ m/\d/) { $last_out_bytes=$out_bytes; }

	}
	close(FILE);
}

my $update_time = time;

open( FILE, ">" . $TRAFFIC_FILE . "_if" . $iface_descr )
  or die "Can't open $TRAFFIC_FILE for writing: $!";

printf FILE ( "%s:%.0ld:%.0ld\n", $update_time, $in_bytes, $out_bytes );
close(FILE);

my $db_file;

#added 20050614 by mw
#Check for and correct counter overflow (if possible).
#See function counter_overflow.
$in_bytes  = counter_overflow( $in_bytes,  $last_in_bytes,  $max_bytes );
$out_bytes = counter_overflow( $out_bytes, $last_out_bytes, $max_bytes );

my $in_traffic = sprintf( "%.2lf",
	( $in_bytes - $last_in_bytes ) / ( time - $last_check_time ) );
my $out_traffic = sprintf( "%.2lf",
	( $out_bytes - $last_out_bytes ) / ( time - $last_check_time ) );

# sos 20090717 changed  due to rrdtool needs bytes
#my $in_traffic_absolut  = sprintf( "%.0d", $last_in_bytes * 1024 );
#my $out_traffic_absolut = sprintf( "%.0d", $last_out_bytes * 1024 );
 my $in_traffic_absolut  = $in_bytes * 1024 ;
 my $out_traffic_absolut = $out_bytes * 1024;




my $in_usage  = sprintf( "%.1f", ( 1.0 * $in_traffic * 100 ) / $iface_speed );
my $out_usage = sprintf( "%.1f", ( 1.0 * $out_traffic * 100 ) / $iface_speed );

my $in_prefix  = "k";
my $out_prefix = "k";

if ( $in_traffic > 1024 ) {
	$in_traffic = sprintf( "%.2f", $in_traffic / 1024 );
	$in_prefix = "M";
}

if ( $out_traffic > 1024 ) {
	$out_traffic = sprintf( "%.2f", $out_traffic / 1024 );
	$out_prefix = "M";
}

$in_bytes  = sprintf( "%.2f", $in_bytes / 1024 );
$out_bytes = sprintf( "%.2f", $out_bytes / 1024 );

my $exit_status = "OK";

my $output = "Total RX Bytes: $in_bytes MB, Total TX Bytes: $out_bytes MB<br>";
$output .=
    "Average Traffic: $in_traffic "
  . $in_prefix . "B/s ("
  . $in_usage
  . "%) in, $out_traffic "
  . $out_prefix . "B/s ("
  . $out_usage
  . "%) out";

if ( ( $in_usage > $crit_usage ) or ( $out_usage > $crit_usage ) ) {
	$exit_status = "CRITICAL";
}

if (   ( $in_usage > $warn_usage )
	or ( $out_usage > $warn_usage ) && $exit_status eq "OK" )
{
	$exit_status = "WARNING";
}

$output .= "<br>$exit_status bandwidth utilization.\n"
  if ( $exit_status ne "OK" );

#$output .=
#"| inUsage=$in_usage;$warn_usage;$crit_usage outUsage=$out_usage;$warn_usage;$crit_usage "  . "inAbsolut=$in_traffic_absolut outAbsolut=$out_traffic_absolut\n";

$output .=
"| inUsage=$in_usage;$warn_usage;$crit_usage;; outUsage=$out_usage;$warn_usage;$crit_usage;;\n";


print $output;
exit( $STATUS_CODE{$exit_status} );

#added 20050416 by mw
#Converts an input value to value in bits
sub bits2bytes {
	return unit2bytes(@_) / 8;
}

#added 20050416 by mw
#Converts an input value to value in bytes
sub unit2bytes {
	my ( $value, $unit ) = @_;

	if ( $unit eq "g" ) {
		return $value * 1024 * 1024 * 1024;
	}
	elsif ( $unit eq "m" ) {
		return $value * 1024 * 1024;
	}
	elsif ( $unit eq "k" ) {
		return $value * 1024;
	}
	else {
		print "You have to supplie a supported unit\n";
		exit $STATUS_CODE{'UNKNOWN'};
	}
}

#added 20050414 by mw
#This function detects if an overflow occurs. If so, it returns
#a computed value for $bytes.
#If there is no counter overflow it simply returns the origin value of $bytes.
#IF there is a Counter reboot wrap, just use previous output.
sub counter_overflow {
	my ( $bytes, $last_bytes, $max_bytes ) = @_;

	$bytes += $max_bytes if ( $bytes < $last_bytes );
	$bytes = $last_bytes  if ( $bytes < $last_bytes );
	return $bytes;
}

#cosmetic changes 20050614 by mw
#Couldn't sustaine "HERE";-), either.
sub print_usage {
	print <<EOU;
    Usage: check_iftraffic.pl -i if_descr -b if_max_speed [ -w warn ] [ -c crit ]


    Options:

    -r, --noregexp
        Do not use regexp to match NAME in description OID
    -i --interface STRING
        Interface Name
    -b --bandwidth INTEGER
        Interface maximum speed in kilo/mega/giga/bits per second.
    -u --units STRING
        gigabits/s,m=megabits/s,k=kilobits/s,b=bits/s.
    -w --warning INTEGER
        % of bandwidth usage necessary to result in warning status (default: 85%)
    -c --critical INTEGER
        % of bandwidth usage necessary to result in critical status (default: 98%)
    -M --max INTEGER
	Max Counter Value of net devices in kilo/mega/giga/bytes.

EOU

	exit( $STATUS_CODE{"UNKNOWN"} );
}


