#!/usr/bin/perl

# ============================== SUMMARY =====================================
#
# Program : check_cisco_snmp.pl
# Version : 1.0
# Date    : Dec 02 2009
# Author  : Fabien Bizet - tokiess@gmail.com
# Summary : This is a nagios plugin that checks the status of objects
#           monitored Cisco  via SNMP
#
# Licence : GPL - summary below, full text at http://www.fsf.org/licenses/gpl.txt
#
# =========================== PROGRAM LICENSE =================================
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
#
# ===================== INFORMATION ABOUT THIS PLUGIN =========================
#
# This plugin checks the status of objects monitored cisco via SNMP
# and returns OK, WARNING, CRITICAL or UNKNOWN.  If a failure occurs it will
# describe the subsystem that failed and the failure code.
#
# This program is written and maintained by:
#   Fabien Bizet - tokiess(at)gmail.com
#
# It is based on check_snmp_temperature.pl plugin by:
#   William Leibzon - william(at)leibzon.org
#
# Using information from
# MIBs Cisco
# 
#
# System Types 

# "sensor" monitors the following OID's:
# Sensor Types OID 1.3.6.1.4.1.9.9.91.1.1.1.1.1
# 1:other
# 2:unknown
# 3:voltsAC
# 4:voltsDC
# 5:amperes
# 6:watts
# 7:hertz
# 8:celsius
# 9:percentRH
# 10:rpm
# 11:cmm
# 12:truthvalue
# 13:specialEnum
# 14:dBm

# entPhysicalModelName 1.3.6.1.2.1.47.1.1.1.1.13

# cefcModuleOperStatus 1.3.6.1.4.1.9.9.117.1.2.1.1.2
# 
# 1:unknown
# 2:ok
# 3:disabled
# 4:okButDiagFailed
# 5:boot
# 6:selfTest
# 7:failed
# 8:missing
# 9:mismatchWithParent
# 10:mismatchConfig
# 11:diagFailed
# 12:dormant
# 13:outOfServiceAdmin
# 14:outOfServiceEnvTemp
# 15:poweredDown
# 16:poweredUp
# 17:powerDenied
# 18:powerCycled
# 19:okButPowerOverWarning
# 20:okButPowerOverCritical


# entSensorValue 1.3.6.1.4.1.9.9.91.1.1.1.1.4
# entSensorPrecision 1.3.6.1.2.1.47.1.1.1.1.3
# entSensorStatus 1.3.6.1.2.1.47.1.1.1.1.5
# 1:ok
# 2:unaviable
# 3:nonoperational

# entSensorValueTimeStamp 1.3.6.1.4.1.9.9.91.1.1.1.1.6
# entSensorValueUpdateRate 1.3.6.1.4.1.9.9.91.1.1.1.1.7

# cswSwitchNumCurrent 1.3.6.1.4.1.9.9.500.1.2.1.1.1
# cswSwitchRole 1.3.6.1.4.1.9.9.500.1.2.1.1.3
# cswSwitchSwPriority 1.3.6.1.4.1.9.9.500.1.2.1.1.4
# cswSwitchHwPriority 1.3.6.1.4.1.9.9.500.1.2.1.1.5
# cswSwitchState 1.3.6.1.4.1.9.9.500.1.2.1.1.6
# 1 : waiting
# 2 : progressing
# 3 : added
# 4 : ready
# 5 : sdmMismatch
# 6 : verMismatch
# 7 : featureMismatch
# 8 : newMasterInit
# 9 : provisioned
# 10 : invalid

# cswRingRedundant 1.3.6.1.4.1.9.9.500.1.1.3
# 1:true
# 2:false

# cHsrpGrpStandbyState 1.3.6.1.4.1.9.9.106.1.2.1.1.15
# 1:initial
# 2:learn
# 3:listen
# 4:speak
# 5:standby
# 6:active
# cHsrpGrpVirtualIpAddr 1.3.6.1.4.1.9.9.106.1.2.1.1.11

# cospfRFC1583Compatibility 1.3.6.1.4.1.9.10.99.1.1
# 1:true
# 2:false
# cospfOpaqueLsaSupport 1.3.6.1.4.1.9.10.99.1.2
# 1:true
# 2:false

# cpmCPUTotalPhysicalIndex 1.3.6.1.4.1.9.9.109.1.1.1.1.2
# cpmCPUTotal5sec 1.3.6.1.4.1.9.9.109.1.1.1.1.3
# cpmCPUTotal1min 1.3.6.1.4.1.9.9.109.1.1.1.1.4
# cpmCPUTotal5min 1.3.6.1.4.1.9.9.109.1.1.1.1.5
 
# ciscoEnvMonFanStatusDescr 1.3.6.1.4.1.9.9.13.1.4.1.2
# ciscoEnvMonFanState 1.3.6.1.4.1.9.9.13.1.4.1.3
# 1:normal
# 2:warning
# 3:critical
# 4:shutdown
# 5:notPresent
# 6:notFunctioning

# ciscoEnvMonSupplyStatusDescr 1.3.6.1.4.1.9.9.13.1.5.1.2
# ciscoEnvMonSupplyState 1.3.6.1.4.1.9.9.13.1.5.1.3
# 1:normal
# 2:warning
# 3:critical
# 4:shutdown
# 5:notPresent
# 6:notFunctioning

# ciscoMemoryPoolName 1.3.6.1.4.1.9.9.48.1.1.1.2
# ciscoMemoryPoolValid 1.3.6.1.4.1.9.9.48.1.1.1.4
# 1:true
# 2:false
# ciscoMemoryPoolUsed 1.3.6.1.4.1.9.9.48.1.1.1.5
# ciscoMemoryPoolFree 1.3.6.1.4.1.9.9.48.1.1.1.6


# ironport
# perCentMemoryUtilization 1.3.6.1.4.1.15497.1.1.1.1
# perCentCPUUtilization 1.3.6.1.4.1.15497.1.1.1.2
# powerSupplyStatus 1.3.6.1.4.1.15497.1.1.1.8.1.2
# 1:NotInstalled
# 2:Healthy
# 3:NoAC
# 4:Faulty
# powerSupplyRedundancy 1.3.6.1.4.1.15497.1.1.1.8.1.3
# 1:ok
# 2:lost
# powerSupplyName 1.3.6.1.4.1.15497.1.1.1.8.1.4
# temperatureName 1.3.6.1.4.1.15497.1.1.1.9.1.3
# degreesCelsius 1.3.6.1.4.1.15497.1.1.1.9.1.2
# fanRPMs 1.3.6.1.4.1.15497.1.1.1.10.1.2
# fanName 1.3.6.1.4.1.15497.1.1.1.10.1.3
# raidStatus 1.3.6.1.4.1.15497.1.1.1.18.1.2
# 1:Healthy
# 2:Failure
# 3:Rebuild
# raidID 1.3.6.1.4.1.15497.1.1.1.18.1.3


#use strict;
use Data::Dumper;
use Getopt::Long;
my %sensor = ();
my %seuil =();
my %tmp =();
my %value_statuscode =();
my %relation_oid =();
my %seuil_oid =();
my %tmp_seuil =();
my %varlist = ();

# table of list name => oid
my %cisco_oids = (
	'entSensorType'=>'1.3.6.1.4.1.9.9.91.1.1.1.1.1',
	'entPhysicalModelName'=> '1.3.6.1.2.1.47.1.1.1.1.13',
	'entPhysicalDescr'=> '1.3.6.1.2.1.47.1.1.1.1.2',
	'cefcModuleOperStatus' => '1.3.6.1.4.1.9.9.117.1.2.1.1.2',
	'entSensorValue' => '1.3.6.1.4.1.9.9.91.1.1.1.1.4',
	'entSensorPrecision' => '1.3.6.1.4.1.9.9.91.1.1.1.1.3',
	'entSensorStatus' => '1.3.6.1.4.1.9.9.91.1.1.1.1.5',
	'entSensorValueTimeStamp' => '1.3.6.1.4.1.9.9.91.1.1.1.1.6',
	'entSensorValueUpdateRate' => '1.3.6.1.4.1.9.9.91.1.1.1.1.7',
	'cswSwitchNumCurrent'=>'1.3.6.1.4.1.9.9.500.1.2.1.1.1',
	'cswSwitchRole' => '1.3.6.1.4.1.9.9.500.1.2.1.1.3',
	'cswSwitchSwPriority' => '1.3.6.1.4.1.9.9.500.1.2.1.1.4',
	'cswSwitchHwPriority' => '1.3.6.1.4.1.9.9.500.1.2.1.1.5',
	'cswSwitchState' => '1.3.6.1.4.1.9.9.500.1.2.1.1.6',
	'cswRingRedundant' => '1.3.6.1.4.1.9.9.500.1.1.3',
	'cHsrpGrpStandbyState' => '1.3.6.1.4.1.9.9.106.1.2.1.1.15',
	'cHsrpGrpVirtualIpAddr' => '1.3.6.1.4.1.9.9.106.1.2.1.1.11',
	'cpmCPUTotalPhysicalIndex' => '1.3.6.1.4.1.9.9.109.1.1.1.1.2',
	'cpmCPUTotal5sec' => '1.3.6.1.4.1.9.9.109.1.1.1.1.3',
	'cpmCPUTotal1min' => '1.3.6.1.4.1.9.9.109.1.1.1.1.4',
	'cpmCPUTotal5min' => '1.3.6.1.4.1.9.9.109.1.1.1.1.5',
	'ciscoEnvMonFanStatusDescr' => '1.3.6.1.4.1.9.9.13.1.4.1.2',
	'ciscoEnvMonFanState' => '1.3.6.1.4.1.9.9.13.1.4.1.3',
	'ciscoEnvMonSupplyStatusDescr' => '1.3.6.1.4.1.9.9.13.1.5.1.2',
	'ciscoEnvMonSupplyState' => '1.3.6.1.4.1.9.9.13.1.5.1.3',
	'perCentMemoryUtilization' => '1.3.6.1.4.1.15497.1.1.1.1',
	'perCentCPUUtilization' => '1.3.6.1.4.1.15497.1.1.1.2',
	'powerSupplyStatus' => '1.3.6.1.4.1.15497.1.1.1.8.1.2',
	'powerSupplyRedundancy' => '1.3.6.1.4.1.15497.1.1.1.8.1.3',
	'powerSupplyName' => '1.3.6.1.4.1.15497.1.1.1.8.1.4',
	'temperatureName' => '1.3.6.1.4.1.15497.1.1.1.9.1.3',
	'degreesCelsius' => '1.3.6.1.4.1.15497.1.1.1.9.1.2',
	'fanRPMs' => '1.3.6.1.4.1.15497.1.1.1.10.1.2',
	'fanName' => '1.3.6.1.4.1.15497.1.1.1.10.1.3',
	'raidStatus' => '1.3.6.1.4.1.15497.1.1.1.18.1.2',
	'raidID' => '1.3.6.1.4.1.15497.1.1.1.18.1.3'
);


# init table name => tests snmp
my %system_types = (
	"module" => [
		'cefcModuleOperStatus',
	],
	"sensor" => [
		'entSensorType',
	],
	"stack" => [
	     'cswSwitchState',
	     'cswRingRedundant',
	],
	"hsrp" => [
		'cHsrpGrpStandbyState',
	],
	"env" => [
		'cpmCPUTotalPhysicalIndex',
		'ciscoEnvMonFanState',
		'ciscoEnvMonSupplyState',
	],

	"ironport" => [
		'perCentMemoryUtilization',
		'perCentCPUUtilization',
		'powerSupplyStatus',
		'powerSupplyRedundancy',
		'degreesCelsius',
		'fanRPMs',
		'raidStatus',
	],

);


# init table des seuils

$seuil{'perCentMemoryUtilization'}{'WARNING'}=90;
$seuil{'perCentMemoryUtilization'}{'CRITICAL'}=95;
$seuil{'perCentMemoryUtilization'}{'type'}='up';

$seuil{'perCentCPUUtilization'}{'WARNING'}=75;
$seuil{'perCentCPUUtilization'}{'CRITICAL'}=90;
$seuil{'perCentCPUUtilization'}{'type'}='up';

$seuil{'degreesCelsius'}{'WARNING'}=40;
$seuil{'degreesCelsius'}{'CRITICAL'}=50;
$seuil{'degreesCelsius'}{'type'}='up';

$seuil{'fanRPMs'}{'WARNING'}=2000;
$seuil{'dfanRPMs'}{'CRITICAL'}=1500;
$seuil{'fanRPMs'}{'type'}='down';



# init tail des seuil OID

%tmp=('value' => ['entSensorValue'], 'status' => ['entSensorStatus'], 'TimeStamp' => ['entSensorValueTimeStamp']);
%{$seuil_oid{'entSensorType'}} = %tmp; 

%tmp=(
   'value' =>['cpmCPUTotal5sec','cpmCPUTotal1min','cpmCPUTotal5min',],
   'warning' => [75,75,75],
   'critical' => [95,95,95],
   'type' => ['up','up','up'],

);
%{$seuil_oid{'cpmCPUTotalPhysicalIndex'}} = %tmp;

# init relation oid ex ( status => name)
my %relation_oid =(
	'cefcModuleOperStatus' =>['entPhysicalModelName'],
	'entSensorType' =>['entPhysicalDescr'],
	'cswSwitchState' =>['entPhysicalModelName'],
	'cHsrpGrpStandbyState' =>['cHsrpGrpVirtualIpAddr'],
	'cpmCPUTotalPhysicalIndex' => ['entPhysicalDescr'],
	'ciscoEnvMonFanState' => ['ciscoEnvMonFanStatusDescr'],
	'ciscoEnvMonSupplyState' => ['ciscoEnvMonSupplyStatusDescr'],
	'powerSupplyStatus' => ['powerSupplyName'],
	'powerSupplyRedundancy' => ['powerSupplyName'],
	'degreesCelsius' => ['temperatureName'],
	'fanRPMs' => ['fanName'],
	'raidStatus' => ['raidID'],
);




# init table value_statuscode translate snmp code => nagios code
%tmp = ('unknown' => 'UNKNOWN','batteryNormal' => 'OK','batteryLow' => 'CRITICAL');
%{$value_statuscode{'upsBasicBatteryStatus'}} = %tmp;
%tmp = ('unknown' => 'UNKNOWN',
        'ok' => 'OK',
        'disabled' => 'CRITICAL',
        'okButDiagFailed' => 'WARNING',
        'boot' => 'WARNING',
        'selfTest' => 'WARNING',
        'failed' => 'CRITICAL',
        'missing' =>  'WARNING',
        'mismatchWithParent' => 'CRITICAL',
        'mismatchConfig' => 'CRITICAL',
        'diagFailed' => 'CRITICAL',
	'dormant' => 'WARNING',
	'outOfServiceAdmin' => 'CRITICAL',
	'outOfServiceEnvTemp' => 'CRITICAL',
	'poweredDown' => 'CRITICAL',
	'poweredUp' => 'WARNING',
	'powerDenied' => 'CRITICAL',
	'powerCycled' => 'WARNING',
);
%{$value_statuscode{'cefcModuleOperStatus'}} = %tmp;
%tmp = ('nonoperational' => 'WARNING','ok' => 'OK','unaviable' => 'OK');
%{$value_statuscode{'entSensorStatus'}} = %tmp;

%tmp = ('true' => OK,'false'=>'CRITICAL');
%{$value_statuscode{'cswRingRedundant'}} = %tmp;

%tmp = ('waiting' => 'WARNING',
        'progressing' => 'WARNING',
	'added' => 'WARNING',
	'ready' => 'OK',
	'sdmMismatch' => 'CRITICAL',
	'verMismatch' => 'CRITICAL',
	'featureMismatch' => 'CRITICAL',
	'newMasterInit' => 'WARNING',
	'provisioned' => 'WARNING',
	'invalid' => 'CRITICAL',
);
%{$value_statuscode{'cswSwitchState'}} = %tmp;

%tmp = ('initial' => 'WARNING',
        'learn' => 'WARNING',
        'listen' => 'WARNING',
        'speak' => 'WARNING',
        'standby' => 'OK',
        'active' => 'OK',
);
%{$value_statuscode{'cHsrpGrpStandbyState'}} = %tmp;

%tmp = ('normal' => 'OK',
        'warning' => 'WARNING',
	'critical' => 'CRITICAL',
	'shutdown' => 'WARNING',
	'notPresent' => 'OK',
	'notFunctioning' => 'OK',
);
%{$value_statuscode{'ciscoEnvMonFanState'}} = %tmp;
%{$value_statuscode{'ciscoEnvMonSupplyState'}} = %tmp;

%tmp = ('NotInstalled' => 'WARNING',
        'Healthy' => 'OK',
        'NoAC' => 'CRITICAL',
        'Faulty' => 'CRITICAL',
);
%{$value_statuscode{'powerSupplyStatus'}} = %tmp;

%tmp = ('ok' => 'OK',
        'lost' => 'CRITICAL',
);
%{$value_statuscode{'powerSupplyRedundancy'}} = %tmp;

%tmp = ('Healthy' => 'OK',
        'Failure' => 'CRITICAL',
        'Rebuild' => 'WARNING',
);
%{$value_statuscode{'raidStatus'}} = %tmp;




# init table correspond value => text
my %correspond = (
        'cHsrpGrpStandbyState' => ['',
	   'initial',
	   'learn',
	   'listen',
	   'speak',
	   'standby',
	   'active',
	],
        'entSensorType' => ['',
           'other',
           'unknown',
           'voltsAC',
           'voltsDC',
           'amperes',
           'watts',
           'hertz',
           'celsius',
           'percentRH',
           'rpm',
	   'cmm',
	   'truthvalue',
	   'specialEnum',
	   'dBm',

       ],
       'cefcModuleOperStatus' => ['',
           'unknown',
           'ok',
           'disabled',
           'okButDiagFailed',
           'boot',
           'selfTest',
           'failed',
           'missing',
           'mismatchWithParent',
           'mismatchConfig',
           'diagFailed',
           'dormant',
           'outOfServiceAdmin',
           'outOfServiceEnvTemp',
	   'poweredDown',
	   'poweredUp',
	   'powerDenied',
	   'powerCycled',
	   'okButPowerOverWarning',
	   'okButPowerOverCritical',
       ],
       'entSensorStatus' => ['',
           'ok',
           'unaviable',
           'nonoperational',
	],
	'cswSwitchState' =>['',
	    'waiting',
	    'progressing',
	    'added',
	    'ready',
	    'sdmMismatch',
	    'verMismatch',
	    'featureMismatch',
	    'newMasterInit',
	    'provisioned',
	    'invalid',
	],
	'cswRingRedundant' => ['',
	   'true',
	   'false',
	],
	'ciscoEnvMonFanState' => ['',
	   'normal',
	   'warning',
	   'critical',
	   'shutdown',
	   'notPresent',
	   'notFunctioning',
	],
        'ciscoEnvMonSupplyState' => ['',
           'normal',
           'warning',
           'critical',
           'shutdown',
           'notPresent',
           'notFunctioning',
        ],
	'powerSupplyStatus' => ['',
	 'NotInstalled',
	 'Healthy',
	 'NoAC',
	 'Faulty',
	],
	'powerSupplyRedundancy' => ['',
	  'ok',
	  'lost',
	],
	'raidStatus' => ['',
	  'Healthy',
	  'Failure',
	  'Rebuild',
	],


);



my ($session,$error);
my %ERRORS=('OK'=>0,'WARNING'=>1,'CRITICAL'=>2,'UNKNOWN'=>3,'DEPENDENT'=>4);

my $Version='0.1';
my $o_host=     undef;          # hostname
my $o_community= undef;         # community
my $o_port=     161;            # SNMP port
my $o_help=     undef;          # help option
my $o_verb=     undef;          # verbose mode
my $o_version=  undef;          # version info option
my $o_warn=     undef;          # warning level option
my @o_warnL=    ();             # array for above list
my $o_crit=     undef;          # Critical level option
my @o_critL=    ();             # array for above list
my $o_timeout=  5;              # Default 5s Timeout
my $o_version2= undef;          # use snmp v2c
# SNMPv3 specific
my $o_login=    undef;          # Login for snmpv3
my $o_passwd=   undef;          # Pass for snmpv3
my $o_attr=     undef;          # What attribute(s) to check (specify more then one separated by '.')
my @o_attrL=    ();             # array for above list
my $o_unkdef=   2;              # Default value to report for unknown attributes
my $o_type=     undef;          # Type of system to check
my $o_type_sensor=undef;        # Type of system to check
my $o_debug=	undef;		# mode debug


sub print_version { print "$0: $Version\n" };

sub print_usage {
        print "Usage: $0 [-v] -H <host> -C <snmp_community> [-2] | (-l login -x passwd)  [-P <port>] -T test|module|sensor|stack|hsrp|chassis|ironport [-t <timeout>] [-V] [-u <unknown_default>]\n";
}

sub verb { my $t=shift; print $t,"\n" if defined($o_verb) ; }

# Get the alarm signal (just in case snmp timout screws up)
$SIG{'ALRM'} = sub {
     print ("ERROR: Alarm signal (Nagios time-out)\n");
          exit $ERRORS{"UNKNOWN"};
};


sub help {
        print "\nSNMP Cisco Monitor for Nagios version ",$Version,"\n";
	print " by Fabien Bizet - tokiess(at)gmail.com\n\n";
	print_usage();
        print <<EOD;
-v, --verbose
        print extra debugging information
-h, --help
        print this help message
-H, --hostname=HOST
        name or IP address of host to check
-C, --community=COMMUNITY NAME
        community name for the host's SNMP agent (implies v 1 protocol)
-2, --v2c
        use SNMP v2 (instead of SNMP v1)
-P, --port=PORT
        SNMPd port (Default 161)
-t, --timeout=INTEGER
        timeout for SNMP in seconds (Default: 5)
-V, --version
        prints version number
-u, --unknown_default=INT
        If attribute is not found then report the output as this number (i.e. -u 0)
-T, --type=test|module|sensor|stack|hsrp|chassis|ironport
        This allows to use pre-defined system type
        Currently support systems types are:
        test (tries all OID's in verbose mode can be used to generate new system type)
        module (module general status)
        sensor (sensor detailed)
        stack (stack chassis status)
        hsrp (only check the hsrp status)
        chassis (only check the system chassis health status)
        ironport (environnement cico IRONPORT)

--type-sensor=celsius|dBm|amperes|specialEnum|voltsAC|truthvalue
       This allows use with type sensor
EOD
}

sub calc_p100_mem($use,$free,$type){

  my $return=0;
  if($o_debug){print "calc mem de use => $use free => $free recupe \% type";}
  if($type eq "free" ){
	$return=round(($free*100)/($use+$free));
  }elsif($type eq "free" ){
 	$return=round(($use*100)/($use+$free));
  }
  return $return;
}

sub check_options {
    Getopt::Long::Configure ("bundling");
    GetOptions(
	        'v'     => \$o_verb,            'verbose'       => \$o_verb,
	        'h'     => \$o_help,            'help'          => \$o_help,
	        'H:s'   => \$o_host,            'hostname:s'    => \$o_host,
	        'P:i'   => \$o_port,            'port:i'        => \$o_port,
	        'C:s'   => \$o_community,       'community:s'   => \$o_community,
	        'l:s'   => \$o_login,           'login:s'       => \$o_login,
	        'x:s'   => \$o_passwd,          'passwd:s'      => \$o_passwd,
	        't:i'   => \$o_timeout,         'timeout:i'     => \$o_timeout,
	        'V'     => \$o_version,         'version'       => \$o_version,
	        '2'     => \$o_version2,        'v2c'           => \$o_version2,
	        'u:i'   => \$o_unkdef,          'unknown_default:i' => \$o_unkdef,
	        'T:s'   => \$o_type,            'type:s'        => \$o_type,
		'type-sensor:s' => \$o_type_sensor,
		'D'     => \$o_debug,         'debug'       => \$o_debug,
    );
    if (defined($o_help) ) { help(); exit $ERRORS{"UNKNOWN"}; }
    if (defined($o_version)) { print_version(); exit $ERRORS{"UNKNOWN"}; }
    if (! defined($o_host) ) # check host and filter
    { print "No host defined!\n";print_usage(); exit $ERRORS{"UNKNOWN"}; }
    # check snmp information
    if (!defined($o_community) && (!defined($o_login) || !defined($o_passwd)) )
    { print "Put snmp login info!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}; }
    if (!defined($o_type)) { print "Must define system type!\n"; print_usage(); exit $ERRORS{"UNKNOWN"}; }
    if (defined ($o_type)) {
	    if ($o_type eq "test"){
       		print "TEST MODE:\n";
    	    } #elsif (!defined($system_types{$o_type}))  {
      		#print "Unknown system type $o_type !\n"; print_usage(); exit $ERRORS{"UNKNOWN"};
    	    #}
    }
}



sub variable_status {
        my $return = '';
        my $nrm = '';
        my $type_nrm ='';
        my $val_max = undef;
        my $val_min = undef;
        if($o_debug){print "variable status $_[0] $_[1] ";}
        if(!defined($cisco_oids{$_[0]})){return "error variable";}
        elsif( defined($value_statuscode{$_[0]}) )
        {
          if($o_debug){print " value statuscode ".$value_statuscode{$_[0]}{$_[1]}."\n";}
          # print Dumper $value_statuscode{$_[0]} ;
          #print Dumper(%value_statuscode);
          #print Data::Dumper->Dump([%value_statuscode], [qw(value_statuscode)]);
          $return=$value_statuscode{$_[0]}{$_[1]};
        }
        elsif( defined($value_norme{$_[0]}) ){
          # for $attr (sort keys %varlist){
          for my $k_n (keys %{$value_norme{$_[0]}} ) {
            $nrm = $k_n;
            $type_nrm = $value_norme{$_[0]}{$k_n};
          }
          $val_max = $norme{$nrm}{$type_nrm.'_max'};
          $val_min = $norme{$nrm}{$type_nrm.'_min'};
          if($val_min < $_[1] && $val_max > $_[1]){$return='OK';}
          else{$return='WARNING';}
          if($o_debug){
            print " value $_[1] norme $nrm $type_nrm $val_max $val_min $return \n";
            print Dumper $value_norme{$_[0]} ;
          }
        }
        else{
          if(defined($seuil{$_[0]})){
            if($o_debug){print " seuil : ".$seuil{$_[0]}{'WARNING'}." ".$seuil{$_[0]}{'CRITICAL'};}
            if( $seuil{$_[0]}{'type'} eq 'up' ){
              if( $_[1] >= $seuil{$_[0]}{'WARNING'} ){
                $return='WARNING';
              }
              if( $_[1] >= $seuil{$_[0]}{'CRITICAL'} ){
                $return='CRITICAL';
              }else{$return='OK'; }
            }
         }
         if( $seuil{$_[0]}{'type'} eq 'down' ){
           if( $_[1] <= $seuil{$_[0]}{'WARNING'} ){
             $return='WARNING';
           }
           if( $_[1] <= $seuil{$_[0]}{'CRITICAL'} ){
             $return='CRITICAL';
           }else{$return='OK'; }
         }
       }

	if($o_debug){print " $return \n";}
	return $return;
}

sub affiche_resultat {
  print Dump $_;
  print " dump affichage \n";
  for $attr (sort keys %{$_[0]}){
    #print @{$_0{$attr}};
    foreach my $ligne (@{$_[0]{$attr}}){
      #print @{$ligne};
      #print "\n";
      print " \'${$ligne}[0]\'  \'${$ligne}[1]\'  \'${$ligne}[2]\'  \'${$ligne}[3]\' \'${$ligne}[4]\'\n";
    }
  }
}


sub seuil_relation {
# 
# arg 0 => attr
# arg 1 => index
# arg 2 => value attr
  my %return = ();
  %tmp_seuil = ();
  if($o_debug){print "test $_[0] $_[1] $_[2] <=> $o_type_sensor seuil relation OID\n";}
  # attr is in array seuil_oid ?
  if(defined($seuil_oid{$_[0]})){
    # filter sensor actif ?
    if(!$o_type_sensor || $o_type_sensor eq $_[2]){

      # snmpget value
      my $count_oid = @{$seuil_oid{$_[0]}{'value'}};

      for(my $num_oid=0;$num_oid < $count_oid;$num_oid++){
      
        $tmp_seuil{'name'}[$num_oid]=$seuil_oid{$_[0]}{'value'}[$num_oid];
        $oid_relation="$cisco_oids{$seuil_oid{$_[0]}{'value'}[$num_oid]}.$_[1]";
        $info_relation =  $session->get_request( -varbindlist => [$oid_relation]);
	if($o_debug){print "oid value => $oid_relation\n";}
        foreach $kr (keys(%{$info_relation}))
        {
          if($o_debug){
            print $info_relation->{$kr}." retour de num_oid $num_oid $kr value ".$seuil_oid{$_[0]}{'value'}[$num_oid]."\n";
	    print $seuil_oid{$_[0]}{'status'}[$num_oid]." relation status \n";
          }
          $return{'value'}[$num_oid]=$info_relation->{$kr};
          $tmp_seuil{'value'}[$num_oid]=$return{'value'}[$num_oid];
	  # if correspond value
          if(defined($correspond{$seuil_oid{$_[0]}{'value'}[$num_oid]})){
            $return{'value'}[$num] = ${$correspond{$seuil_oid{$_[0]}{'value'}[$num_oid]}}[$result->{$kr}];
	    $tmp_seuil{'value'}[$num]=$return{'value'}[$num_oid];
          }
        }
        if($o_debug){print " $_[0] $_[1] => $oid_relation = ".$return{'value'}[$num_oid]."\n";}

        # if snmpget status exist
        if(defined($seuil_oid{$_[0]}{'status'}[$num_oid])){
          if($o_debug){print "test status OID\n";}
	  # snmpget status
          $oid_relation="$cisco_oids{$seuil_oid{$_[0]}{'status'}[$num_oid]}.$_[1]";
          $info_relation =  $session->get_request( -varbindlist => [$oid_relation]);
          if($o_debug){print "$oid_relation OID pour ".$seuil_oid{$_[0]}{'status'}[$num_oid]."\n";}
          foreach $kr (keys(%{$info_relation}))
          {
            if($o_debug){print $info_relation->{$kr}." retour de $kr status ".$seuil_oid{$_[0]}{'status'}[$num_oid]."\n";}
            $return{'status'}[$num_oid]=$info_relation->{$kr};
	    $tmp_seuil{'status'}[$num_oid]=$return{'status'}[$num_oid];
	    if(defined($correspond{$seuil_oid{$_[0]}{'status'}[$num_oid]})){
	      if($o_debug){
	        print "correspond exist pour status ";
	        print $seuil_oid{$_[0]}{'status'}[$num_oid]." => ".${$correspond{$seuil_oid{$_[0]}{'status'}[$num_oid]}}[$info_relation->{$kr}]."\n";
	      }
	      $return{'status'}[$num_oid] = ${$correspond{$seuil_oid{$_[0]}{'status'}[$num_oid]}}[$info_relation->{$kr}];
	      $tmp_seuil{'status'}[$num_oid]=$return{'status'}[$num_oid];
	      $return{'statusnagios'}[$num_oid] = &variable_status($seuil_oid{$_[0]}{'status'}[$num_oid],$return{'status'}[$num_oid]);
	      $tmp_seuil{'statusnagios'}[$num_oid]=$return{'statusnagios'}[$num_oid];
	    }
	  }
	}
	if($seuil_oid{$_[0]}{'type'}[$num_oid] eq 'memory'){
	  if(defined($seuil_oid{$_[0]}{'mem_use'}[$num_oid])){
	    $oid_relation="$cisco_oids{$seuil_oid{$_[0]}{'mem_use'}[$num_oid]}.$_[1]";
	    $info_relation =  $session->get_request( -varbindlist => [$oid_relation]);
	    if($o_debug){print "$oid_relation OID pour ".$seuil_oid{$_[0]}{'mem_use'}[$num_oid]."\n";}
	    foreach $kr (keys(%{$info_relation}))
	    {
	      if($o_debug){print $info_relation->{$kr}." retour de $kr use \n";}
	      my $mem_use=$info_relation->{$kr};

	    }

	  }

	  if(defined($seuil_oid{$_[0]}{'mem_free'}[$num_oid])){
            $oid_relation="$cisco_oids{$seuil_oid{$_[0]}{'mem_free'}[$num_oid]}.$_[1]";
            $info_relation =  $session->get_request( -varbindlist => [$oid_relation]);
            if($o_debug){print "$oid_relation OID pour ".$seuil_oid{$_[0]}{'mem_free'}[$num_oid]."\n";}
            foreach $kr (keys(%{$info_relation}))
            {
              if($o_debug){print $info_relation->{$kr}." retour de $kr free \n";}
              my $mem_free=$info_relation->{$kr};
            }
          }

	  if(defined($seuil_oid{$_[0]}{'mem_total'}[$num_oid])){
            $oid_relation="$cisco_oids{$seuil_oid{$_[0]}{'mem_total'}[$num_oid]}.$_[1]";
            $info_relation =  $session->get_request( -varbindlist => [$oid_relation]);
            if($o_debug){print "$oid_relation OID pour ".$seuil_oid{$_[0]}{'mem_total'}[$num_oid]."\n";}
            foreach $kr (keys(%{$info_relation}))
            {
              if($o_debug){print $info_relation->{$kr}." retour de $kr total \n";}
              my $mem_total=$info_relation->{$kr};
            }
          }

	  my $mem_type=$seuil_oid{$_[0]}{'mem_type'}[$num_oid];

		
	}

	# test seuil
	if(defined($seuil_oid{$_[0]}{'warning'}[$num_oid])){
          if($o_debug){print " seuil : ".$seuil_oid{$_[0]}{'warning'}[$num_oid]." ".$seuil_oid{$_[0]}{'critical'}[$num_oid];}
          # test seuil up
	  if( $seuil_oid{$_[0]}{'type'}[$num_oid] eq 'up' ){
	    if( $tmp_seuil{'value'}[$num_oid] >= $seuil_oid{$_[0]}{'warning'}[$num_oid] ){
              $tmp_seuil{'statusnagios'}[$num_oid]=$return{'status'}[$num_oid]=$tmp_seuil{'status'}[$num_oid]='WARNING';
	      $tmp_seuil{'statusnagios'}[$num_oid]=$tmp_seuil{'status'}[$num_oid];
	    }
            if( $tmp_seuil{'value'}[$num_oid] >= $seuil_oid{$_[0]}{'critical'}[$num_oid] ){
              $return{'status'}[$num_oid]=$tmp_seuil{'status'}[$num_oid]='CRITICAL';
	      $tmp_seuil{'statusnagios'}[$num_oid]=$tmp_seuil{'status'}[$num_oid];
            }else{
	      $return{'status'}[$num_oid]=$tmp_seuil{'status'}[$num_oid]='OK'; 
	      $tmp_seuil{'statusnagios'}[$num_oid]=$tmp_seuil{'status'}[$num_oid];
	    }
          }

	  # test seuil down
	  if( $seuil_oid{$_[0]}{'type'}[$num_oid] eq 'down' ){
	    if( $tmp_seuil{'value'}[$num_oid] <= $seuil_oid{$_[0]}{'warning'}[$num_oid] ){
	        $return{'status'}[$num_oid]=$tmp_seuil{'status'}[$num_oid]='WARNING';
		$tmp_seuil{'statusnagios'}[$num_oid]=$tmp_seuil{'status'}[$num_oid];
	    }
	    if( $tmp_seuil{'value'}[$num_oid] <= $seuil_oid{$_[0]}{'critical'}[$num_oid] ){
	      $return{'status'}[$num_oid]=$tmp_seuil{'status'}[$num_oid]='CRITICAL';
	      $tmp_seuil{'statusnagios'}[$num_oid]=$tmp_seuil{'status'}[$num_oid];
	    }else{
	      $tmp_seuil{'status'}[$num_oid]=$return{'status'}[$num_oid]='OK'; 
	      $tmp_seuil{'statusnagios'}[$num_oid]=$tmp_seuil{'status'}[$num_oid];
	    }
	  }

        }
      }
    }
    else{
      $return{'error'} = 'pas de test';
      $tmp_seuil{'errorcode'}=1;
    }
  }else{$tmp_seuil{'errorcode'}=0;}
  return $return;
}

sub test_system_type {
  if($_[0]){
   for $attr ( @{ $system_types{$_[0]} } ) {
      $new_oid=$cisco_oids{$attr};
      $next=1;
      while($next)
      {
       # if($o_debug){print "test => $next\n";}
        if($result = $session->get_next_request( -varbindlist => [$new_oid])){$next=1;}
        else{$next=0;}
        if($new_oid =~ m/$cisco_oids{$attr}/ && $next){$next=1;}
        else{$next=0;}
        if($o_debug){print "test $_[0] sur $new_oid => $next\n";}
        foreach $k (keys(%{$result}))
        {
          $valtrans ='';
          $varstatus='';
          $valtest='';
          $new_oid=$k;
          $index=0;
          my $nosave=0;
          $pattern=$cisco_oids{$attr};
          if( $new_oid =~ s/$cisco_oids{$attr}.//g )
          {
            $index=$new_oid;
            $new_oid=$k;
            if (defined($relation_oid{$attr}))
            {
              foreach $relation (@{$relation_oid{$attr}}){
                if($o_debug){print "relation avec $relation\n";}
                $oid_relation="$cisco_oids{$relation}.$index";
                $info_relation =  $session->get_request( -varbindlist => [$oid_relation]);
                foreach $kr (keys(%{$info_relation}))
                {
                  if($o_debug){print $info_relation->{$kr}." retour de $kr \n";}
                  $retour_relation=$info_relation->{$kr};
                }
              }
            }
	     
	    if($o_debug){print "Clef=$k index : $index Valeur=".$result->{$k};}
	    if (defined($correspond{$attr}))
	    {
	      $valtrans =  ${$correspond{$attr}}[$result->{$k}];
	      if($o_debug){
	        print " correspond : ".$valtrans." ";
	        #print Dumper @{$correspond{$attr}};
	      }
	    }
	    if($o_debug){print "\n";}
	    if (defined($correspond_flag{$attr}))
	    {
	      @char=split(//,$result->{$k});
	      my $x=0;
	      my $val=$x+1;
	      foreach my $c (@char)
	      {
	        if($c){
	        #print "   ".$val.": ".${$correspond_flag{$attr}}[$x]."\n";
	          $valtrans.=${$correspond_flag{$attr}}[$x]."\n";
	        }
	        $x++;
	        $val++;
	      }
	    }
	
	    if($valtrans){$valtest=$valtrans;}
	    else{$valtest=$result->{$k};}
	    $varstatus= &variable_status($attr,$valtest);
	    $valget = $result->{$k};

            &seuil_relation($attr,$index,$valtest);
            if(!defined($tmp_seuil{'errorcode'}) && $tmp_seuil{'value'}){
              if($o_debug){print Dumper @{$tmp_seuil{'value'}};}
	      #foreach my $num_oid => $value_oid ( @{$tmp_seuil{'value'}}){
	      my $count_oid=@{$tmp_seuil{'value'}};
	      for($num_oid = 0; $num_oid < $count_oid ;$num_oid++){
	        if($o_debug){print " seuil relation $num_oid sur $count_oid : ".$tmp_seuil{'value'}[$num_oid]." ".$tmp_seuil{'status'}[$num_oid]." ".$tmp_seuil{'statusnagios'}[$num_oid]." ".$tmp_seuil{'name'}[$num_oid]."\n";}
	        $varstatus = $tmp_seuil{'statusnagios'}[$num_oid];
	        $valget=$tmp_seuil{'value'}[$num_oid];
	        my $count = @{$varlist{$attr}};
	        @{$varlist{$attr}[$count]}=($k, $valget,$valtrans,$varstatus,$retour_relation,$tmp_seuil{'name'}[$num_oid]);
	        if($o_debug){print Dumper @{$varlist{$attr}[$count]};}
	      }
	      $nosave=1;
	   }elsif($tmp_seuil{'errorcode'}==1){$nosave=1;}

           my $count = @{$varlist{$attr}};
           @{$varlist{$attr}[$count]}=($k, $valget,$valtrans,$varstatus,$retour_relation)if(!$nosave);
           if($o_debug){print $varlist{$attr}[$count][4]."  num $count $varlist{$attr}[$count][3]\n";}
           #print " $x \n";
         }
       }
     }
   }
  }
}

sub connect_snmp {

 eval "use Net::SNMP";
 if ($@) {
  verb("ERROR: You do NOT have the Net:".":SNMP library \n"
  . "  Install it by running: \n"
  . "  perl -MCPAN -e shell \n"
  . "  cpan[1]> install Net::SNMP \n");
  exit 1;
  } else {
    verb("The Net:".":SNMP library is available on your server \n");
  }

  # SNMP Connection to the host
 
  if (defined($o_login) && defined($o_passwd)) {
  # SNMPv3 login
	  verb("SNMPv3 login");
	  ($session, $error) = Net::SNMP->session(
	   -hostname         => $o_host,
	   -version          => '3',
	   -username         => $o_login,
	   -authpassword     => $o_passwd,
	   -authprotocol     => 'md5',
	   -privpassword     => $o_passwd,
	   -timeout          => $o_timeout
	   );
  } else {

   if (defined ($o_version2)) {
   # SNMPv2 Login
      ($session, $error) = Net::SNMP->session(
       -hostname  => $o_host,
       -version   => 2,
       -community => $o_community,
       -port      => $o_port,
       -timeout   => $o_timeout
       );
   } else {
   # SNMPV1 login
     ($session, $error) = Net::SNMP->session(
      -hostname  => $o_host,
      -community => $o_community,
      -port      => $o_port,
      -timeout   => $o_timeout
      );
   }
  }

}





# main prog

check_options();
connect_snmp();

my $i;
my $oid;
my $line;
my $resp;
my $attr;
my $key;
my $result;
my $new_oid;
my $valtrans;
my $varstatus;
my $valtest;
my $k;
my $next=1;
my @tmp;
my $index;

if ( $o_type eq "test" ) {
  print "Trying all preconfigured Cisco OID's against target...\n";
  for $system_test (sort keys %system_types){
    %varlist = undef;
	print "\ntest  TYPE $system_test ...\n";
    &test_system_type($system_test);
    for $attr (sort keys %varlist){
      #print "   $attr ...\n";
      foreach my $ligne (@{$varlist{$attr}}){
        print "     $attr   \'${$ligne}[1]\'  \'${$ligne}[2]\'  \'${$ligne}[3]\' \'${$ligne}[4]\' \'${$ligne}[5]\' \n";
      }
    }

  }
  
  $session->close();
    print "\nPlease email the results to Fabien Bizet - tokiess\@gmail.com\ if is a problem \n";
    print "\nTo add this system to check_cisco_snmp, use something like the following:\n\n";

 exit 0 ;
}
else{

  &test_system_type($o_type);
}

# part return screen
verb("\nCISCO Status to Nagios Status mapping...");
for $attr (sort keys %varlist){
  foreach my $ligne (@{$varlist{$attr}}){

    if($o_debug){print " \'${$ligne}[0]\'  \'${$ligne}[1]\'  \'${$ligne}[2]\'  \'${$ligne}[3]\' \'${$ligne}[4]\' \'${$ligne}[5]\' \n";}
    if (${$ligne}[3] eq "CRITICAL"){
       verb("\nStatus $attr CRITICAL...");
       $statuscritical = "1";
       $statuscode="CRITICAL";
       $statusinfo .= ", " if ($statusinfo);
       $statusinfo .= "$attr = Non-Recoverable : ${$ligne}[1] ${$ligne}[2] ${$ligne}[4] ${$ligne}[5]";
    }
    elsif (${$ligne}[3] eq "WARNING") {
      verb("\nStatus $attr WARNING...");
      $statuswarning = "1";
      $statuscode="WARNING";
      $statusinfo .= ", " if ($statusinfo);
      $statusinfo .= "$attr = Non-Critical : ${$ligne}[1] ${$ligne}[2] ${$ligne}[4] ${$ligne}[5]";
     }
     elsif (${$ligne}[3] eq "UNKNOWN") {
       verb("\nStatus $attr UNKNOWN...");
       $statusunknown = "1";
       $statuscode="UNKNOWN";
       $statusinfo .= ", " if ($statusinfo);
       $statusinfo .= "$attr = Other : ${$ligne}[1] ${$ligne}[2] ${$ligne}[4] ${$ligne}[5]";
    }
    elsif (${$ligne}[3] eq "OK") {
      verb("Status $attr  ${$ligne}[4] ${$ligne}[1] ${$ligne}[2] ${$ligne}[5] OK...");
      $statuscode="OK";
    }
#     else {
#           $statusunknown = "1";
#           $statuscode="UNKNOWN";
#           $statusinfo .= ", " if ($statusinfo);
#           $statusinfo .= "$attr=UKNOWN";
#     }
#    verb("$attr: statuscode = $statuscode");

  }
}

$statuscode="OK";

if ($statuscritical eq '1'){
  $statuscode="CRITICAL";
}
elsif ($statuswarning eq '1'){
  $statuscode="WARNING";
}
elsif ($statusunknown eq '1'){
  $statuscode="UNKNOWN";
}

if ($statuscode ne 'OK'){
    printf("$statuscode:$statusinfo");
}
else {
  printf("$statuscode");
}

print "\n";
verb("\nEXIT CODE: $ERRORS{$statuscode} STATUS CODE: $statuscode");
exit $ERRORS{$statuscode};


