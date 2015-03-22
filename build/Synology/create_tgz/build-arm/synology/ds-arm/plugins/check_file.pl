#!/usr/bin/perl -w

############################## check_file.pl ##############
# Version : 0.1
# Date : Oct 15 2007
# Author  : Samuel Mutel
# Licence : GPL - http://www.fsf.org/licenses/gpl.txt
###########################################################

use strict;
use Getopt::Long;

my $Version='0.1';
my %ERRORS=('OK'=>0,'WARNING'=>1,'CRITICAL'=>2,'UNKNOWN'=>3,'DEPENDENT'=>4);

my $o_help=undef;          # wan't some help ?
my $o_version=undef;       # print version
my $o_exist=undef;         # Check if file exist
my $o_nexist=undef;        # Check if a file doesn't exist
my $o_empty=undef;         # Check if a file is empty
my $o_nempty=undef;        # Check if a file is not empty
my $o_paramok=undef;

sub print_version {
	print "check_file version : $Version\n";
}

sub print_usage {
    print "Usage: check_file.pl [-v] [-h] [-e|-n|-m|-t] <file>\n";
}

sub print_help {
  print "\nNagios Plugin to check if a file exist/doesn't exist.\n";
  print "It check too if a file is empty or not.\n\n";
  print_usage();
  print <<EOT;
-v, --version
  Print version of this plugin
-h, --help
   Print this help message
-e, --exist
   Check if a file exist
-n, --nexist
   Check if a file doesn't exist
-m, --empty
   Check if a file is empty
-t, --nempty
   Check if a file is not empty
EOT
}

sub check_options {
  Getopt::Long::Configure("bundling");
  GetOptions(
    'h'     => \$o_help,            'help'          => \$o_help,
    'v'     => \$o_version,         'version'       => \$o_version,
    'e'     => \$o_exist,           'exist'         => \$o_exist,
    'n'     => \$o_nexist,          'nexist'        => \$o_nexist,
    'm'     => \$o_empty,           'empty'         => \$o_empty,
    't'     => \$o_nempty,          'nempty'        => \$o_nempty,
  );
  
  if (defined ($o_help)) { print_help(); exit $ERRORS{"UNKNOWN"}};
  if (defined ($o_version)) { print_version(); exit $ERRORS{"UNKNOWN"}};
  if (!defined ($o_exist) && !defined ($o_nexist) && !defined ($o_empty) && !defined ($o_nempty)) { print_usage(); exit $ERRORS{"UNKNOWN"}};;
}

###### MAIN ######

check_options();

if (@ARGV != 1) {
	print_usage();
	exit $ERRORS{"UNKNOWN"};
}

my $file = $ARGV[0];

if (defined ($o_exist)) {
	if (-e $file) {
	  print "File " . $file . " exist.";
	  exit $ERRORS{"OK"}
	}
	else {
	  print "File " . $file . " does not exist.";
	  exit $ERRORS{"CRITICAL"}
	}
}

if (defined ($o_nexist)) {
  if (! -e $file) {
  	print "File " . $file . " does not exist.";
	  exit $ERRORS{"OK"}
	}
	else {
		print "File " . $file . " exist.";
	  exit $ERRORS{"CRITICAL"}
	}
}

if (defined ($o_empty)) {
  if (! -s $file) {
  	print "File " . $file . " is empty.";
	  exit $ERRORS{"OK"}
	}
	else {
		print "File " . $file . " is not empty.";
	  exit $ERRORS{"CRITICAL"}
	}
}

if (defined ($o_nempty)) {
  if (-s $file) {
  	print "File " . $file . " is not empty.";
	  exit $ERRORS{"OK"}
	}
	else {
		print "File " . $file . " is empty.";
	  exit $ERRORS{"CRITICAL"}
	}
}