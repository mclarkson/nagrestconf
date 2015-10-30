#!/usr/bin/perl -w

# check_ilo2_health.pl
# based on check_stuff.pl and locfg.pl
#
# Nagios plugin using the Nagios::Plugin module and the
# HP Lights-Out XML PERL Scripting Sample
# see http://h18013.www1.hp.com/support/files/lights-out/us/download/25057.html
# checks if all sensors are ok, returns warning on high temperatures and 
# fan failures and critical on overall health failure
#
# Alexander Greiner-Baer <alexander.greiner-baer@web.de> 2007 
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#
# Changelog:
# 1.41		Thu, 26 Jul 2007 17:42:36 +0200
#   perfdata label ist now quoted
#   --
# 1.4		Mon, 25 Jun 2007 09:45:52 +0200
#   check vrm and power supply
#   
#   new option "--notemperatures"
#   
#   new option "--perfdata"
#   
#   some minor changes
#   --
# 1.3beta	Wed, 20 Jun 2007 09:57:46 +0200
#   do some error checking
#   
#   new option "--inputfile"
#   read bmc output from file
#   --
# 1.2	Mon, 18 Jun 2007 09:33:17 +0200
#   new option "--skipsyntaxerrors"
#   ignores syntax errors in the xml output, maybe required by older firmwares
#   
#   introduce a date to the changelog ;)
#   --
# 1.1	do not return warning if temperature status is n/a
#
#   add "<LOCFG VERSION="2.21" />" to get rid of the
#   "<INFORM>Scripting utility should be updated to the latest version.</INFORM>"
#   message
#   --
# 1	initial release


use strict;
use warnings;

use Nagios::Plugin;
use IO::Socket::SSL;
use XML::Simple;

use vars qw($VERSION $PROGNAME  $verbose $warn $critical $timeout $result);
$VERSION = 1.4;

$PROGNAME = "check_ilo2_health";

# instantiate Nagios::Plugin
my $p = Nagios::Plugin->new(
	usage => "Usage: %s [ -v|--verbose ]  [-H <host>] [-t <timeout>]
	[ -u|--user=<USERNAME> ] [ -p|--password=<PASSWORD> ]
	[ -e|--skipsyntaxerrors=1 ] [ -f|--inputfile=<filename> ]
	[ -n|--notemperatures=1 ] [ -d|--perfdata=1 ]",
	version => $VERSION,
	blurb => 'This plugin checks the health status on a remote iLO2 device
and will return OK, WARNING or CRITICAL. iLO2 (integrated Lights-Out 2)
can be found on HP ProLiant servers.'
);

# add all arguments
$p->add_arg(
	spec => 'user|u=s',
	help => 
	qq{-u, --user=STRING
	Specify the username on the command line.},
);

$p->add_arg(
	spec => 'password|p=s',
	help => 
	qq{-p, --password=STRING
	Specify the password on the command line.},
);

$p->add_arg(
	spec => 'host|H=s',
	help => 
	qq{-H, --host=STRING
	Specify the host on the command line.},
);

$p->add_arg(
	spec => 'skipsyntaxerrors|e=i',
	help => 
	qq{-e, --skipsyntaxerrors=INTEGER
	Setting to 1 skips syntax errrors on older firmwares. Default off.},
);

$p->add_arg(
	spec => 'notemperatures|n=i',
	help => 
	qq{-n, --notemperatures=INTEGER
	Setting to 1 gives output without temperature listing. Default off.},
);

$p->add_arg(
	spec => 'perfdata|d=i',
	help => 
	qq{-d, --perfdata=INTEGER
	Setting to 1 adds perfdata to the output. Default off.},
);

$p->add_arg(
	spec => 'inputfile|f=s',
	help => 
	qq{-f, --inputfile=STRING
	Do not query the BMC. Read input from file.},
);

# parse arguments
$p->getopts;

my $return = "OK";
my $message;
my $xmlinput;
my $isinput = 0;
my $client;
my $host = $p->opts->host;
my $username = $p->opts->user;
my $password = $p->opts->password;
my $inputfile = $p->opts->inputfile;
my $skipsyntaxerrors = 0;
my $notemperatures = 0;
my $perfdata = 0;

# perform checking on command line options

if ( defined($p->opts->skipsyntaxerrors) ) {
	if ( ( $p->opts->skipsyntaxerrors != 1 ) && ( $p->opts->skipsyntaxerrors != 0 ) )  {
		$p->nagios_die( "ERROR: Invalid option supplied for the -e option. Use 0 or 1." );
	}
	if ( $p->opts->skipsyntaxerrors == 1 ) {
		$skipsyntaxerrors = 1;
	}
}

if ( defined($p->opts->notemperatures) ) {
	if ( ( $p->opts->notemperatures != 1 ) && ( $p->opts->notemperatures != 0 ) )  {
		$p->nagios_die( "ERROR: Invalid option supplied for the -n option. Use 0 or 1." );
	}
	if ( $p->opts->notemperatures == 1 ) {
		$notemperatures = 1;
	}
}

if ( defined($p->opts->perfdata) ) {
	if ( ( $p->opts->perfdata != 1 ) && ( $p->opts->perfdata != 0 ) )  {
		$p->nagios_die( "ERROR: Invalid option supplied for the -n option. Use 0 or 1." );
	}
	if ( $p->opts->perfdata == 1 ) {
		$perfdata = 1;
	}
}

unless ( (defined($inputfile) ) || ( defined($host) && defined($username) && defined($password) ) ) {
	$p->nagios_die("ERROR: Missing host, password and user.");
}

unless ( defined($inputfile) ) {
    # query code from locfg.pl	
    # Set the default SSL port number if no port is specified	
    $host .= ":443" unless ($host =~ m/:/);
    #
    # Open the SSL connection and the input file
    $client = new IO::Socket::SSL->new(PeerAddr => $host);
    if (!$client) {
	$p->nagios_exit(
		return_code => "UNKNOWN",
		message => "ERROR: Failed to establish SSL connection with $host."
		);
    }

    # send xml to BMC
    print $client '<?xml version="1.0"?>' . "\r\n";
    print $client '<LOCFG VERSION="2.21" />' . "\r\n";
    print $client '<RIBCL VERSION="2.21">' . "\r\n";
    print $client '<LOGIN USER_LOGIN="'.$username.'" PASSWORD="'.$password.'">' . "\r\n";
    print $client '<SERVER_INFO MODE="read">' . "\r\n";
    print $client '<GET_EMBEDDED_HEALTH />' . "\r\n";
    print $client '</SERVER_INFO>' . "\r\n";
    print $client '</LOGIN>' . "\r\n";
    print $client '</RIBCL>' . "\r\n";
}
else {
	open($client, $inputfile) or $p->nagios_die("ERROR: $inputfile not found");
}

# retrieve data
while (my $line = <$client>) {
	print $line if ( $p->opts->verbose );
# thrash all unnecessary lines
	if ( $line =~ m/<GET_EMBEDDED_HEALTH_DATA>/ ) {
		$isinput=1;
	}
	if ( $line =~ m/<\/GET_EMBEDDED_HEALTH_DATA>/ ) {
		$isinput=0;
		$xmlinput .= $line;
	}
	if ( $isinput ) {
		$xmlinput .= $line;
	}
	if ( $line =~ m/MESSAGE='/) {
		my ($msg) = ( $line =~ m/MESSAGE='(.*)'/);

		if ( $msg !~ m/No error/ ) {
			if ( $msg =~ m/Syntax error/ ) {
				unless ( $skipsyntaxerrors ) {
					close $client;
					$p->nagios_exit(
							return_code => "UNKNOWN",
							message => "ERROR: $msg."
							);
				}
			}
			else {
				# message could be "User login name was not found"
				close $client;
				$p->nagios_exit(
						return_code => "UNKNOWN",
						message => "ERROR: $msg."
						);
			}
		}
	}	
}
close $client;

# parse with XML::Simple
my $xml;
	if ( $xmlinput ) {
		$xml = XMLin($xmlinput, ForceArray => 1) 
	}
else { 
	$p->nagios_exit(
			return_code => "UNKNOWN",
			message => "ERROR: No parseable output."
			);
}

my $temperatures = $xml->{'TEMPERATURE'}[0]->{'TEMP'};
my @checks;
push(@checks,$xml->{'FANS'}[0]->{'FAN'});
push(@checks,$xml->{'VRM'}[0]->{'MODULE'});
push(@checks,$xml->{'POWER_SUPPLIES'}[0]->{'SUPPLY'});
my $health = $xml->{'HEALTH_AT_A_GLANCE'}[0];
my $location;
my $status;
my $temperature;
my $cautiontemp;
my $criticaltemp;

## check overall health status
my $vrmstatus = $health->{'VRM'}[0]->{'STATUS'};
if ( defined($vrmstatus) && ( $vrmstatus !~ m/^Ok$/i ) ) {
	$return = "CRITICAL";
	$message .= "VRM $vrmstatus, ";
}

my $temperaturestatus = $health->{'TEMPERATURE'}[0]->{'STATUS'};
if ( defined($temperaturestatus) && ( $temperaturestatus !~ m/^Ok$/i ) ) {
	$return = "CRITICAL";
	$message .= "Temperature $temperaturestatus, ";
}

my $powerstatus = $health->{'POWER_SUPPLIES'}[0]->{'STATUS'};
if ( defined($powerstatus) && ( $powerstatus !~ m/^Ok$/i ) ) {
    $return = "CRITICAL";
    $message .= "Power supply $powerstatus, ";
}

my $fanstatus = $health->{'FANS'}[0]->{'STATUS'};
if ( defined($fanstatus) && ( $fanstatus !~ m/^Ok$/i ) ) {
	$return = "CRITICAL";
	$message .= "Fans $fanstatus, ";
}

if ( ! $message ) {
	$message .= "Overall Health Ok, ";
}

# check fans, vrm and power supplies
foreach my $check (@checks) {
	if (ref($check)) {
		foreach my $item (@$check) {
			$location=$item->{'LABEL'}[0]->{'VALUE'};
			$status=$item->{'STATUS'}[0]->{'VALUE'};
			if ( defined($location) && defined($status) ) {
				if ( ( $status !~ m/^(Ok)$|^(n\/a)$|^(Not Installed)$/i ) ) {
					# do not override previous return value from overall health
					unless ( $return eq "CRITICAL" ) {
						$return = "WARNING";
					}
					$message .= "$location: $status, ";
				}
			}
		}
	}
}

# check temperatures
if (ref($temperatures) ) {
	unless ( $notemperatures ) {
		$message .= "Temperatures: ";
	}
	foreach my $temp (@$temperatures) {
		$location=$temp->{'LOCATION'}[0]->{'VALUE'};
		$status=$temp->{'STATUS'}[0]->{'VALUE'};
		$temperature=$temp->{'CURRENTREADING'}[0]->{'VALUE'};
		if ( defined($location) && defined($status) && defined($temperature) ) {
			if ( ( $status !~ m/^(Ok)$|^(n\/a)$|^(Not Installed)$/i ) ) {
				# do not override previous return value from overall health
				unless ( $return eq "CRITICAL" ) {
					$return = "WARNING";
				}
				if ( $notemperatures ) {
					$message .= "$location ($status): $temperature, ";
				}
			}
			unless ( ( $status =~ m/^(n\/a)$|^(Not Installed)$/i ) || ( $notemperatures ) )  {
				$message .= "$location ($status): $temperature, ";
				if ( $perfdata ) {
					$cautiontemp=$temp->{'CAUTION'}[0]->{'VALUE'};
					$criticaltemp=$temp->{'CRITICAL'}[0]->{'VALUE'};
					if ( defined($cautiontemp) && defined($criticaltemp) ) {
						$p->set_thresholds(
							warning  => $cautiontemp,
							critical => $criticaltemp,
						);
						my $threshold = $p->threshold;
						# add perfdata
						$p->add_perfdata(
							label   => "'".$location."'",
							value   => $temperature,
							uom     => "",
							threshold => $threshold,
						);
					}
				}
			}
		}
		else {
			$message .= "no reading, ";
		}
	}
}

# strip trailing ","
$message =~ s/, $//;

$p->nagios_exit( 
	return_code => $return, 
	message => $message 
);

