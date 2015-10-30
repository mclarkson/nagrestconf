#!/usr/bin/perl -w
#
# Nagios plugin to monitor vmware esx servers
#
# License: GPL
# Copyright (c) 2008 op5 AB
# Author: Kostyantyn Gushchyn <dev@op5.com>
# Contributor(s): Patrick Müller, Jeremy Martin, Eric Jonsson, stumpr, John Cavanaugh, Libor Klepac, maikmayers
#
# For direct contact with any of the op5 developers send a mail to
# dev@op5.com
# Discussions are directed to the mailing list op5-users@op5.com,
# see http://lists.op5.com/mailman/listinfo/op5-users
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License version 2 as
# published by the Free Software Foundation.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#

use strict;
use warnings;
use vars qw($PROGNAME $VERSION $output $values $result);
use Nagios::Plugin;
use File::Basename;
my $perl_module_instructions="
Download the latest version of Perl Toolkit from VMware support page. 
In this example we use VMware-vSphere-SDK-for-Perl-4.0.0-161974.x86_64.tar.gz,
but the instructions should apply to newer versions as well.
  
Upload the file to your op5 Monitor server's /root dir and execute:

    cd /root
    tar xvzf VMware-vSphere-SDK-for-Perl-4.0.0-161974.x86_64.tar.gz
    cd vmware-vsphere-cli-distrib/
    ./vmware-install.pl
  
Follow the on screen instructions, described below:

  \"Creating a new vSphere CLI installer database using the tar4 format.

  Installing vSphere CLI.

  Installing version 161974 of vSphere CLI

  You must read and accept the vSphere CLI End User License Agreement to
  continue.
  Press enter to display it.\" 
  
    <ENTER>

  \"Read through the License Agreement\" 
  \"Do you accept? (yes/no) 
  
    yes


  \"The following Perl modules were found on the system but may be too old to work
  with VIPerl:
  
  Crypt::SSLeay
  Compress::Zlib\"
  
  \"In which directory do you want to install the executable files? [/usr/bin]\"

    <ENTER>

  \"Please wait while copying vSphere CLI files...

  The installation of vSphere CLI 4.0.0 build-161974 for Linux completed
  successfully. You can decide to remove this software from your system at any
  time by invoking the following command:
  \"/usr/bin/vmware-uninstall-vSphere-CLI.pl\".
  
  This installer has successfully installed both vSphere CLI and the vSphere SDK
  for Perl.
  Enjoy,
  
  --the VMware team\"

Note: \"Crypt::SSLeay\" and \"Compress::Zlib\" are not needed for check_esx3 to work.  
";


eval { 
	require VMware::VIRuntime
} or Nagios::Plugin::Functions::nagios_exit('UNKNOWN', "Missing perl module VMware::VIRuntime. Download and install \'VMware Infrastructure (VI) Perl Toolkit\', available at http://www.vmware.com/download/sdk/\n $perl_module_instructions");

$PROGNAME = basename($0);
$VERSION = '0.2.0';

my $np = Nagios::Plugin->new(
  usage => "Usage: %s -D <data_center> | -H <host_name> [ -N <vm_name> ]\n"
    . "    -u <user> -p <pass> | -f <authfile>\n"
    . "    -l <command> [ -s <subcommand> ]\n"
    . "    [ -x <black list> ]\n"
    . "    [ -t <timeout> ] [ -w <warn_range> ] [ -c <crit_range> ]\n"
    . '    [ -V ] [ -h ]',
  version => $VERSION,
  plugin  => $PROGNAME,
  shortname => uc($PROGNAME),
  blurb => 'VMWare Infrastructure plugin',
  extra   => "Supported commands(^ means blank or not specified parameter) :\n"
    . "    Common options for VM, Host and DC :\n"
    . "        * cpu - shows cpu info\n"
    . "            + usage - CPU usage in percentage\n"
    . "            + usagemhz - CPU usage in MHz\n"
    . "            ^ all cpu info\n"
    . "        * mem - shows mem info\n"
    . "            + usage - mem usage in percentage\n"
    . "            + usagemb - mem usage in MB\n"
    . "            + swap - swap mem usage in MB\n"
    . "            + overhead - additional mem used by VM Server in MB\n"
    . "            + overall - overall mem used by VM Server in MB\n"
    . "            ^ all mem info\n"
    . "        * net - shows net info\n"
    . "            + usage - overall network usage in KBps(Kilobytes per Second) \n"
    . "            + receive - receive in KBps(Kilobytes per Second) \n"
    . "            + send - send in KBps(Kilobytes per Second) \n"
    . "            ^ all net info\n"
    . "        * io - shows disk io info\n"
    . "            + read - read latency in ms\n"
    . "            + write - write latency in ms\n"
    . "            ^ all disk io info\n"
    . "        * runtime - shows runtime info\n"
    . "            + status - overall host status (gray/green/red/yellow)\n"
    . "            + issues - all issues for the host\n"
    . "            ^ all runtime info\n"
    . "    VM specific :\n"
    . "        * cpu - shows cpu info\n"
    . "            + wait - CPU wait in ms\n"
    . "        * mem - shows mem info\n"
    . "            + swapin - swapin mem usage in MB\n"
    . "            + swapout - swapout mem usage in MB\n"
    . "            + active - active mem usage in MB\n"
    . "        * io - shows disk I/O info\n"
    . "            + usage - overall disk usage in MB/s\n"
    . "        * runtime - shows runtime info\n"
    . "            + con - connection state\n"
    . "            + cpu - allocated CPU in MHz\n"
    . "            + mem - allocated mem in MB\n"
    . "            + state - virtual machine state (UP, DOWN, SUSPENDED)\n"
    . "            + consoleconnections - console connections to VM\n"
    . "            + guest - guest OS status, needs VMware Tools\n"
    . "            + tools - VMWare Tools status\n"
    . "    Host specific :\n"
    . "        * net - shows net info\n"
    . "            + nic - makes sure all active NICs are plugged in\n"
    . "        * io - shows disk io info\n"
    . "            + aborted - aborted commands count\n"
    . "            + resets - bus resets count\n"
    . "            + kernel - kernel latency in ms\n"
    . "            + device - device latency in ms\n"
    . "            + queue - queue latency in ms\n"
    . "        * vmfs - shows Datastore info\n"
    . "            + (name) - info for datastore with name (name)\n"
    . "            ^ all datastore info\n"
    . "        * runtime - shows runtime info\n"
    . "            + con - connection state\n"
    . "            + health - checks cpu/storage/memory/sensor status\n"
    . "            + maintenance - shows whether host is in maintenance mode\n"
    . "            + list(vm) - list of VMWare machines and their statuses\n"
    . "        * service - shows Host service info\n"
    . "            + (names) - check the state of one or several services specified by (names), syntax for (names):<service1>,<service2>,...,<serviceN>\n"
    . "            ^ show all services\n"
    . "    DC specific :\n"
    . "        * io - shows disk io info\n"
    . "            + aborted - aborted commands count\n"
    . "            + resets - bus resets count\n"
    . "            + kernel - kernel latency in ms\n"
    . "            + device - device latency in ms\n"
    . "            + queue - queue latency in ms\n"
    . "        * vmfs - shows Datastore info\n"
    . "            + (name) - info for datastore with name (name)\n"
    . "            ^ all datastore info\n"
    . "        * runtime - shows runtime info\n"
    . "            + list(vm) - list of VMWare machines and their statuses\n"
    . "            + listhost - list of VMWare esx host servers and their statuses\n"
    . "        * recommendations - shows recommendations for cluster\n"
    . "            + (name) - recommendations for cluster with name (name)\n"
    . "            ^ all clusters recommendations\n"
    . "\n\nCopyright (c) 2008 op5",
  timeout => 30,
);

$np->add_arg(
  spec => 'host|H=s',
  help => "-H, --host=<hostname>\n"
    . '   ESX or ESXi hostname.',
  required => 0,
);

$np->add_arg(
  spec => 'datacenter|D=s',
  help => "-D, --datacenter=<DCname>\n"
    . '   Datacenter hostname.',
  required => 0,
);

$np->add_arg(
  spec => 'name|N=s',
  help => "-N, --name=<vmname>\n"
    . '   Virtual machine name.',
  required => 0,
);

$np->add_arg(
  spec => 'username|u=s',
  help => "-u, --username=<username>\n"
    . '   Username to connect with.',
  required => 0,
);

$np->add_arg(
  spec => 'password|p=s',
  help => "-p, --password=<password>\n"
    . '   Password to use with the username.',
  required => 0,
);

$np->add_arg(
  spec => 'authfile|f=s',
  help => "-f, --authfile=<path>\n"
    . "   Authentication file with login and password. File syntax :\n"
    . "   username=<login>\n"
    . '   password=<password>',
  required => 0,
);

$np->add_arg(
  spec => 'warning|w=s',
  help => "-w, --warning=THRESHOLD\n"
    . "   Warning threshold. See\n"
    . "   http://nagiosplug.sourceforge.net/developer-guidelines.html#THRESHOLDFORMAT\n"
    . '   for the threshold format.',
  required => 0,
);

$np->add_arg(
  spec => 'critical|c=s',
  help => "-c, --critical=THRESHOLD\n"
    . "   Critical threshold. See\n"
    . "   http://nagiosplug.sourceforge.net/developer-guidelines.html#THRESHOLDFORMAT\n"
    . '   for the threshold format.',
  required => 0,
);

$np->add_arg(
  spec => 'command|l=s',
  help => "-l, --command=COMMAND\n"
    . '   Specify command type (CPU, MEM, NET, IO, VMFS, RUNTIME, ...)',
  required => 1,
);

$np->add_arg(
  spec => 'subcommand|s=s',
  help => "-s, --subcommand=SUBCOMMAND\n"
    . '   Specify subcommand',
  required => 0,
);

$np->add_arg(
  spec => 'sessionfile|S=s',
  help => "-S, --sessionfile=SESSIONFILE\n"
    . '   Specify a filename to store sessions for faster authentication',
  required => 0,
);

$np->add_arg(
  spec => 'exclude|x=s',
  help => "-x, --exclude=<black list>\n"
    . '   Specify black list',
  required => 0,
);

$np->getopts;

my $host = $np->opts->host;
my $datacenter = $np->opts->datacenter;
my $vmname = $np->opts->name;
my $username = $np->opts->username;
my $password = $np->opts->password;
my $authfile = $np->opts->authfile;
my $warning = $np->opts->warning;
my $critical = $np->opts->critical;
my $command = $np->opts->command;
my $subcommand = $np->opts->subcommand;
my $sessionfile = $np->opts->sessionfile;
my $blacklist = $np->opts->exclude;
my $percw;
my $percc;
$output = "Unknown ERROR!";
$result = 'CRITICAL';

if (defined($subcommand))
{
	$subcommand = undef if ($subcommand eq '');
}

if (defined($critical))
{
	($percc, $critical) = check_percantage($critical);
	$critical = undef if ($critical eq '');
}

if (defined($warning))
{
	($percw, $warning) = check_percantage($warning);
	$warning = undef if ($warning eq '');
}

$np->set_thresholds(critical => $critical, warning => $warning);

eval
{
	die "Provide either Password/Username or Auth file\n" if ((!defined($password) || !defined($username) || defined($authfile)) && (defined($password) || defined($username) || !defined($authfile)));
	die "Both threshold values must be the same units\n" if (($percw && !$percc && defined($critical)) || (!$percw && $percc && defined($warning)));
	if (defined($authfile))
	{
		open (AUTH_FILE, $authfile) || die "Unable to open auth file \"$authfile\"\n";
		while( <AUTH_FILE> ) {
			if(s/^[ \t]*username[ \t]*=//){
				s/^\s+//;s/\s+$//;
				$username = $_;
			}
			if(s/^[ \t]*password[ \t]*=//){
				s/^\s+//;s/\s+$//;
				$password = $_;
			}
		}
		die "Auth file must contain both username and password\n" if (!(defined($username) && defined($password)));
	}

	if (defined($datacenter))
	{
		if (defined($sessionfile) and -e $sessionfile)
		{
			Vim::load_session(service_url => $datacenter, session_file => $sessionfile);
		}
		Util::connect("https://" . $datacenter . "/sdk/webService", $username, $password);
	}
	elsif (defined($host))
	{
		if (defined($sessionfile) and -e $sessionfile)
		{
			Vim::load_session(service_url => $host, session_file => $sessionfile);
		}
		Util::connect("https://" . $host . "/sdk/webService", $username, $password);
	}
	else
	{
		$np->nagios_exit('CRITICAL', "No Host or Datacenter specified");
	}
	if (defined($sessionfile))
	{
		Vim::save_session(session_file=>$sessionfile);
	}

	if (defined($vmname))
	{
		if (uc($command) eq "CPU")
		{
			($result, $output) = vm_cpu_info($vmname, $np, $subcommand);
		}
		elsif (uc($command) eq "MEM")
		{
			($result, $output) = vm_mem_info($vmname, $np, $subcommand);
		}
		elsif (uc($command) eq "NET")
		{
			($result, $output) = vm_net_info($vmname, $np, $subcommand);
		}
		elsif (uc($command) eq "IO")
		{
			($result, $output) = vm_disk_io_info($vmname, $np, $subcommand);
		}
		elsif (uc($command) eq "RUNTIME")
		{
			($result, $output) = vm_runtime_info($vmname, $np, $subcommand);
		}
		else
		{
			$output = "Unknown HOST-VM command\n" . $np->opts->_help;
			$result = 'CRITICAL';
		}
	}
	elsif (defined($host))
	{
		my $esx;
		$esx = {name => $host} if (defined($datacenter));
		if (uc($command) eq "CPU")
		{
			($result, $output) = host_cpu_info($esx, $np, $subcommand);
		}
		elsif (uc($command) eq "MEM")
		{
			($result, $output) = host_mem_info($esx, $np, $subcommand);
		}
		elsif (uc($command) eq "NET")
		{
			($result, $output) = host_net_info($esx, $np, $subcommand);
		}
		elsif (uc($command) eq "IO")
		{
			($result, $output) = host_disk_io_info($esx, $np, $subcommand);
		}
		elsif (uc($command) eq "VMFS")
		{
			($result, $output) = host_list_vm_volumes_info($esx, $np, $subcommand, $blacklist, $percc || $percw);
		}
		elsif (uc($command) eq "RUNTIME")
		{
			($result, $output) = host_runtime_info($esx, $np, $subcommand);
		}
		elsif (uc($command) eq "SERVICE")
		{
			($result, $output) = host_service_info($esx, $np, $subcommand);
		}
		else
		{
			$output = "Unknown HOST command\n" . $np->opts->_help;
			$result = 'CRITICAL';
		}
	}
	else
	{
		if (uc($command) eq "RECOMMENDATIONS")
		{
			my $cluster_name;
			$cluster_name = {name => $subcommand} if (defined($subcommand));
			($result, $output) = return_cluster_DRS_recommendations($np, $cluster_name);
		}
		elsif (uc($command) eq "CPU")
		{
			($result, $output) = dc_cpu_info($np, $subcommand);
		}
		elsif (uc($command) eq "MEM")
		{
			($result, $output) = dc_mem_info($np, $subcommand);
		}
		elsif (uc($command) eq "NET")
		{
			($result, $output) = dc_net_info($np, $subcommand);
		}
		elsif (uc($command) eq "IO")
		{
			($result, $output) = dc_disk_io_info($np, $subcommand);
		}
		elsif (uc($command) eq "VMFS")
		{
			($result, $output) = dc_list_vm_volumes_info($np, $subcommand, $blacklist, $percc || $percw);
		}
		elsif (uc($command) eq "RUNTIME")
		{
			($result, $output) = dc_runtime_info($np, $subcommand);
		}
		else
		{
			$output = "Unknown HOST command\n" . $np->opts->_help;
			$result = 'CRITICAL';
		}		
	}
};
if ($@)
{
	$output = $@;
	$result = 'CRITICAL';
}

Util::disconnect();
$np->nagios_exit($result, $output);

#######################################################################################################################################################################

sub get_key_metrices {
	my ($perfmgr_view, $group, @names) = @_;

	my $perfCounterInfo = $perfmgr_view->perfCounter;
	my @counters;

	foreach (@$perfCounterInfo) {
		if ($_->groupInfo->key eq $group) {
			my $cur_name = $_->nameInfo->key . "." . $_->rollupType->val;
			foreach my $index (0..@names-1)
			{
				if ($names[$index] =~ /$cur_name/)
				{
					$names[$index] =~ /(\w+).(\w+):*(.*)/;
					$counters[$index] = PerfMetricId->new(counterId => $_->key, instance => $3);
				}
			}
		}
	}

	return \@counters;
}

sub generic_performance_values {
	my ($views, $group, @list) = @_;
	my $counter = 0;
	my @values = ();
	eval
	{
		my $perfMgr = Vim::get_view(mo_ref => Vim::get_service_content()->perfManager, properties => [ 'perfCounter' ]);
		my $metrices = get_key_metrices($perfMgr, $group, @list);

		my @perf_query_spec = ();
		push(@perf_query_spec, PerfQuerySpec->new(entity => $_, metricId => $metrices, format => 'csv', intervalId => 20, maxSample => 1)) foreach (@$views);
		my $perf_data = $perfMgr->QueryPerf(querySpec => \@perf_query_spec);

		while (@$perf_data)
		{
			my $unsorted = shift(@$perf_data)->value;
			my @host_values = ();

			foreach my $id (@$unsorted)
			{
				foreach my $index (0..@$metrices-1)
				{
					if ($id->id->counterId == $$metrices[$index]->counterId)
					{
						$counter++ if (!defined($host_values[$index]));
						$host_values[$index] = $id;
					}
				}
			}
			push(@values, \@host_values);
		}
		
	};
	return undef if ($@ || $counter != (@list * @$views));
	return \@values;
}

sub return_host_performance_values {
	my $values;
	my $host_name = shift(@_);
	my $host_view = Vim::find_entity_views(view_type => 'HostSystem', filter => $host_name, properties => [ 'name' ]); # Added properties named argument.
	die "Runtime error\n" if (!defined($host_view));
	die "Host \"" . $$host_name{"name"} . "\" does not exist\n" if (!@$host_view);
	$values = generic_performance_values($host_view, @_);

	return undef if ($@);
	return $values;
}

sub return_host_vmware_performance_values {
	my $values;
	my $vmname = shift(@_);
	my $vm_view = Vim::find_entity_views(view_type => 'VirtualMachine', filter => {name => $vmname}, properties => [ 'name', 'runtime' ]);
	die "Runtime error\n" if (!defined($vm_view));
	die "VMware machine \"" . $vmname . "\" does not exist\n" if (!@$vm_view);
	die "VMware machine \"" . $vmname . "\" is not running. Current state is \"" . $$vm_view[0]->runtime->powerState->val . "\"\n" if ($$vm_view[0]->runtime->powerState->val ne "poweredOn");
	$values = generic_performance_values($vm_view, @_);

	return $@ if ($@);
	return $values;
}

sub return_dc_performance_values {
	my $values;
	my $host_views = Vim::find_entity_views(view_type => 'HostSystem', properties => [ 'name' ]);
	die "Runtime error\n" if (!defined($host_views));
	die "Datacenter does not contain any hosts\n" if (!@$host_views);
	$values = generic_performance_values($host_views, @_);

	return undef if ($@);
	return $values;
}

sub simplify_number
{
	my ($number, $cnt) = @_;
	$cnt = 2 if (!defined($cnt));
	return sprintf("%.${cnt}f", "$number");
}

sub convert_number
{
	my ($number) = shift(@_);
	$number =~ s/\,/\./;
	return $number;
}

sub check_percantage
{
	my ($number) = shift(@_);
	my $perc = $number =~ s/\%//;
	return ($perc, $number);
}

sub check_health_state
{
	my ($state) = shift(@_);
	my $res = 'CRITICAL';

	if (uc($state) eq "GREEN") {
		$res = 'OK'
	} elsif (uc($state) eq "YELLOW") {
		$res = 'WARNING';
	}
	
	return $res;
}

#=====================================================================| HOST |============================================================================#

sub host_cpu_info
{
	my ($host, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST CPU Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_performance_values($host, 'cpu', ('usage.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) * 0.01);
				$np->add_perfdata(label => "cpu_usage", value => $value, uom => '%', threshold => $np->threshold);
				$output = "cpu usage=" . $value . " %"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "USAGEMHZ")
		{
			$values = return_host_performance_values($host, 'cpu', ('usagemhz.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "cpu_usagemhz", value => $value, uom => 'Mhz', threshold => $np->threshold);
				$output = "cpu usagemhz=" . $value . " MHz";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST CPU - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_performance_values($host, 'cpu', ('usagemhz.average', 'usage.average'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value));
			my $value2 = simplify_number(convert_number($$values[0][1]->value) * 0.01);
			$np->add_perfdata(label => "cpu_usagemhz", value => $value1, uom => 'Mhz', threshold => $np->threshold);
			$np->add_perfdata(label => "cpu_usage", value => $value2, uom => '%', threshold => $np->threshold);
			$res = 'OK';
			$output = "cpu usage=" . $value1 . " MHz (" . $value2 . "%)";
		}
	}

	return ($res, $output);
}

sub host_mem_info
{
	my ($host, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST MEM Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_performance_values($host, 'mem', ('usage.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) * 0.01);
				$np->add_perfdata(label => "mem_usage", value => $value, uom => '%', threshold => $np->threshold);
				$output = "mem usage=" . $value . " %"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "USAGEMB")
		{
			$values = return_host_performance_values($host, 'mem', ('consumed.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_usagemb", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "mem usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SWAP")
		{
			$values = return_host_performance_values($host, 'mem', ('swapused.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_swap", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "swap usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "OVERHEAD")
		{
			$values = return_host_performance_values($host, 'mem', ('overhead.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_overhead", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "overhead=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "OVERALL")
		{
			$values = return_host_performance_values($host, 'mem', ('consumed.average', 'overhead.average'));
			if (defined($values))
			{
				my $value = simplify_number((convert_number($$values[0][0]->value) + convert_number($$values[1]->value)) / 1024);
				$np->add_perfdata(label => "mem_overhead", value =>  $value, uom => 'MB', threshold => $np->threshold);
				$output = "overall=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST MEM - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_performance_values($host, 'mem', ('consumed.average', 'usage.average', 'overhead.average', 'swapused.average'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value) / 1024);
			my $value2 = simplify_number(convert_number($$values[0][1]->value) * 0.01);
			my $value3 = simplify_number(convert_number($$values[0][2]->value) / 1024);
			my $value4 = simplify_number(convert_number($$values[0][3]->value) / 1024);
			$np->add_perfdata(label => "mem_usagemb", value => $value1, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_usage", value => $value2, uom => '%', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_overhead", value => $value3, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_swap", value => $value4, uom => 'MB', threshold => $np->threshold);
			$res = 'OK';
			$output = "mem usage=" . $value1 . " MB (" . $value2 . "%), overhead=" . $value3 . " MB, swapped=" . $value4 . " MB";
		}
	}

	return ($res, $output);
}

sub host_net_info
{
	my ($host, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST NET Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_performance_values($host, 'net', ('usage.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "net_usage", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "net usage=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "RECEIVE")
		{
			$values = return_host_performance_values($host, 'net', ('received.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "net_receive", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "net receive=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SEND")
		{
			$values = return_host_performance_values($host, 'net', ('transmitted.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "net_send", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "net send=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "NIC")
		{
			my $host_view = Vim::find_entity_view(view_type => 'HostSystem', filter => $host);
			die "Host \"" . $$host{"name"} . "\" does not exist\n" if (!defined($host_view));
			$host_view->update_view_data();
			my $network_config = $host_view->config->network;
			die "Host \"" . $$host{"name"} . "\" has no network info in the API.\n" if (!defined($network_config));

			$output = "";
			$res = "OK";
			my $OKCount = 0;
			my $BadCount = 0;

			# create a hash of NIC info to facilitate easy lookups
			my %NIC = ();
			foreach (@{$network_config->pnic})
			{
				$NIC{$_->key} = $_;
			}

			# see which NICs are actively part of a vswitch
			foreach (@{$network_config->vswitch})
			{
				# get list of physical nics
				if (defined($_->pnic)){
					foreach (@{$_->pnic})
					{
						my $nic_key = $_;
						my $nic_name = $NIC{$nic_key}->device;
						if (!defined($NIC{$nic_key}->linkSpeed))
						{
							$output .= ", " if ($output);
							$output .= "$nic_name is unplugged";
							$res = "CRITICAL";
							$BadCount++;
						}
						else
						{
							$OKCount++;
						}
					}
				}
			}

			if (!$output)
			{
				$output = "All $OKCount NICs are connected";
			}
			else
			{
				$output = $BadCount ."/" . ($BadCount + $OKCount) . " NICs are disconncted: " . $output;
			}
			$np->add_perfdata(label => "OK_NICs", value => $OKCount);
			$np->add_perfdata(label => "Bad_NICs", value => $BadCount);
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST NET - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_performance_values($host, 'net', ('received.average:*', 'transmitted.average:*'));
		$output = '';
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value));
			my $value2 = simplify_number(convert_number($$values[0][1]->value));
			$np->add_perfdata(label => "net_receive", value => $value1, uom => 'KBps', threshold => $np->threshold);
			$np->add_perfdata(label => "net_send", value => $value2, uom => 'KBps', threshold => $np->threshold);
			$res = 'OK';
			$output = "net receive=" . $value1 . " KBps, send=" . $value2 . " KBps, ";
		}

		my $host_view = Vim::find_entity_view(view_type => 'HostSystem', filter => $host);
		if (defined($host_view))
		{
			$host_view->update_view_data();
			my $network_config = $host_view->config->network;
			if (defined($network_config))
			{
				my $OKCount = 0;
				my $BadCount = 0;

				# create a hash of NIC info to facilitate easy lookups
				my %NIC = ();
				foreach (@{$network_config->pnic})
				{
					$NIC{$_->key} = $_;
				}

				# see which NICs are actively part of a vswitch
				foreach (@{$network_config->vswitch})
				{
					# get list of physical nics
					if (defined($_->pnic)){
						foreach (@{$_->pnic})
						{
							if (!defined($NIC{$_}->linkSpeed))
							{
								$BadCount++;
							}
							else
							{
								$OKCount++;
							}
						}
					}
				}
				
				if (!$BadCount)
				{
					$output .= "all $OKCount NICs are connected";
				}
				else
				{
					$output .= $BadCount ."/" . ($BadCount + $OKCount) . " NICs are disconncted";
				}
				$np->add_perfdata(label => "OK_NICs", value => $OKCount);
				$np->add_perfdata(label => "Bad_NICs", value => $BadCount);
			}
		}
	}

	return ($res, $output);
}

sub host_disk_io_info
{
	my ($host, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST IO Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "ABORTED")
		{
			$values = return_host_performance_values($host, 'disk', ('commandsAborted.summation:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_aborted", value => $value, threshold => $np->threshold);
				$output = "io commands aborted=" . $value;
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "RESETS")
		{
			$values = return_host_performance_values($host, 'disk', ('busResets.summation:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_busresets", value => $value, threshold => $np->threshold);
				$output = "io bus resets=" . $value;
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "READ")
		{
			$values = return_host_performance_values($host, 'disk', ('totalReadLatency.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_read", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io read latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "WRITE")
		{
			$values = return_host_performance_values($host, 'disk', ('totalWriteLatency.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_write", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io write latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "KERNEL")
		{
			$values = return_host_performance_values($host, 'disk', ('kernelLatency.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_kernel", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io kernel latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "DEVICE")
		{
			$values = return_host_performance_values($host, 'disk', ('deviceLatency.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_device", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io device latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "QUEUE")
		{
			$values = return_host_performance_values($host, 'disk', ('queueLatency.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value), 0);
				$np->add_perfdata(label => "io_queue", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io queue latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST IO - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_performance_values($host, 'disk', ('commandsAborted.summation:*', 'busResets.summation:*', 'totalReadLatency.average:*', 'totalWriteLatency.average:*', 'kernelLatency.average:*', 'deviceLatency.average:*', 'queueLatency.average:*'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value), 0);
			my $value2 = simplify_number(convert_number($$values[0][1]->value), 0);
			my $value3 = simplify_number(convert_number($$values[0][2]->value), 0);
			my $value4 = simplify_number(convert_number($$values[0][3]->value), 0);
			my $value5 = simplify_number(convert_number($$values[0][4]->value), 0);
			my $value6 = simplify_number(convert_number($$values[0][5]->value), 0);
			my $value7 = simplify_number(convert_number($$values[0][6]->value), 0);
			$np->add_perfdata(label => "io_aborted", value => $value1, threshold => $np->threshold);
			$np->add_perfdata(label => "io_busresets", value => $value2, threshold => $np->threshold);
			$np->add_perfdata(label => "io_read", value => $value3, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_write", value => $value4, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_kernel", value => $value5, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_device", value => $value6, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_queue", value => $value7, uom => 'ms', threshold => $np->threshold);
			$res = 'OK';
			$output = "io commands aborted=" . $value1 . ", io bus resets=" . $value2 . ", io read latency=" . $value3 . " ms, write latency=" . $value4 . " ms, kernel latency=" . $value5 . " ms, device latency=" . $value6 . " ms, queue latency=" . $value7 ." ms";
		}
	}

	return ($res, $output);
}

sub host_list_vm_volumes_info
{
	my ($host, $np, $subcommand, $blacklist, $perc) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST VM VOLUMES Unknown error';

	if (defined($subcommand))
	{
		$output = "No volume named \"$subcommand\" found";
		my $host_view = Vim::find_entity_view(view_type => 'HostSystem', filter => $host, properties => ['name', 'datastore']);
		die "Host \"" . $$host{"name"} . "\" does not exist\n" if (!defined($host_view));

		foreach my $ref_store (@{$host_view->datastore})
		{
			my $store = Vim::get_view(mo_ref => $ref_store, properties => ['summary', 'info']);
			if ($store->summary->name eq $subcommand)
			{
				$res = 'OK';
				my $value1 = simplify_number(convert_number($store->summary->freeSpace) / 1024 / 1024);
				my $value2 = simplify_number(convert_number($store->info->freeSpace) / convert_number($store->summary->capacity) * 100);
				if ($perc)
				{
					$res = $np->check_threshold(check => $value2);
				}
				else
				{
					$res = $np->check_threshold(check => $value1);
				}
				$np->add_perfdata(label => $store->summary->name, value => $perc?$value2:$value1, uom => $perc?'%':'MB', threshold => $np->threshold);
				$output = $store->summary->name . "=". $value1 . " MB (" . $value2 . "%)";
			}
		}
	}
	else
	{
		$res = OK;
		$output = '';
		my $host_view = Vim::find_entity_view(view_type => 'HostSystem', filter => $host, properties => ['name', 'datastore']);
		die "Host \"" . $$host{"name"} . "\" does not exist\n" if (!defined($host_view));
		foreach my $ref_store (@{$host_view->datastore})
		{
			my $store = Vim::get_view(mo_ref => $ref_store, properties => ['summary', 'info']);
			
			if (defined($blacklist))
			{
					my $name = $store->summary->name;
					next if ($blacklist =~ m/(^|\s|\t|,)\Q$name\E($|\s|\t|,)/);
			}
			
			my $value1 = simplify_number(convert_number($store->summary->freeSpace) / 1024 / 1024);
			my $value2 = simplify_number(convert_number($store->info->freeSpace) / convert_number($store->summary->capacity) * 100);

			if ($perc)
			{
				$res = Nagios::Plugin::Functions::max_state($res, $np->check_threshold(check => $value2));
			}
			else
			{
				$res = Nagios::Plugin::Functions::max_state($res, $np->check_threshold(check => $value1));
			}

			$np->add_perfdata(label => $store->summary->name, value => $perc?$value2:$value1, uom => $perc?'%':'MB', threshold => $np->threshold);
			$output .= $store->summary->name . "=". $value1 . " MB (" . $value2 . "%), ";
		}

		chop($output);
		chop($output);
		$output = "storages : " . $output;
	}

	return ($res, $output);
}

sub host_runtime_info
{
	my ($host, $np, $subcommand) = @_;

	my $res = 'CRITICAL';
	my $output = 'HOST RUNTIME Unknown error';
	my $runtime;
	my $host_view = Vim::find_entity_view(view_type => 'HostSystem', filter => $host, properties => ['name', 'runtime', 'overallStatus', 'configIssue']);
	die "Host \"" . $$host{"name"} . "\" does not exist\n" if (!defined($host_view));
	$host_view->update_view_data();
	$runtime = $host_view->runtime;

	if (defined($subcommand))
	{
		if (uc($subcommand) eq "CON")
		{
			$output =  "connection state=" . $runtime->connectionState->val;
			$res = 'OK' if ($runtime->connectionState->val eq "connected");
		}
		elsif (uc($subcommand) eq "HEALTH")
		{
			my $OKCount = 0;
			my $AlertCount = 0;
			my $cpuStatusInfo = $runtime->healthSystemRuntime->hardwareStatusInfo->cpuStatusInfo;
			my $storageStatusInfo = $runtime->healthSystemRuntime->hardwareStatusInfo->storageStatusInfo;
			my $memoryStatusInfo = $runtime->healthSystemRuntime->hardwareStatusInfo->memoryStatusInfo;
			my $numericSensorInfo = $runtime->healthSystemRuntime->systemHealthInfo->numericSensorInfo;

			if (!defined($cpuStatusInfo))
			{
				$res = 'CRITICAL';
				$output = "API error - unable to get cpuStatusInfo\n";
			}
			elsif (!defined($storageStatusInfo))
			{
				$res = 'CRITICAL';
				$output = "API error - unable to get storageStatusInfo\n";
			}
			elsif (!defined($memoryStatusInfo))
			{
				$res = 'CRITICAL';
				$output = "API error - unable to get memoryStatusInfo\n";
			}
			elsif (!defined($numericSensorInfo))
			{
				$res = 'CRITICAL';
				$output = "API error - unable to get numericSensorInfo\n";
			}
			else
			{
				$output = '';

				foreach (@$cpuStatusInfo)
				{
					# print "CPU Name = ". $_->name .", Label = ". $_->status->label . ", Summary = ". $_->status->summary . ", Key = ". $_->status->key . "\n";
					if (check_health_state($_->status->key) ne 'OK')
					{
						$output .= ", " if ($output);
						$output .= $_->name . ": " . $_->status->summary;
						$AlertCount++;
					}
					else
					{
						$OKCount++;
					}
				}

				foreach (@$storageStatusInfo)
				{
					# print "Storage Name = ". $_->name .", Label = ". $_->status->label . ", Summary = ". $_->status->summary . ", Key = ". $_->status->key . "\n";
					if (check_health_state($_->status->key) ne 'OK')
					{
						$output .= ", " if ($output);
						$output .= "Storage " . $_->name . ": " . $_->status->summary;
						$AlertCount++;
					}
					else
					{
						$OKCount++;
					}
				}

				foreach (@$memoryStatusInfo)
				{
					# print "Memory Name = ". $_->name .", Label = ". $_->status->label . ", Summary = ". $_->status->summary . ", Key = ". $_->status->key . "\n";
					if (check_health_state($_->status->key) ne 'OK')
					{
						$output .= ", " if ($output);
						$output .= "Memory: " . $_->status->summary;
						$AlertCount++;
					}
					else
					{
						$OKCount++;
					}
				}

				foreach (@$numericSensorInfo)
				{
					# print "Sensor Name = ". $_->name .", Type = ". $_->sensorType . ", Label = ". $_->healthState->label . ", Summary = ". $_->healthState->summary . ", Key = " . $_->healthState->key . "\n";
					if (check_health_state($_->healthState->key) ne 'OK')
					{
						$output .= ", " if ($output);
						$output .= $_->sensorType . " sensor " . $_->name . ": ".$_->healthState->summary;
						$AlertCount++;
					}
					else
					{
						$OKCount++;
					}
				}

				if ($output)
				{
					$output = "$AlertCount health issue(s) found: $output";
				}
				else
				{
					$output = "All $OKCount health checks are Green";
					$res = 'OK';
				}

				$np->add_perfdata(label => "Alerts", value => $AlertCount);
			}

		}
		elsif (uc($subcommand) eq "MAINTENANCE")
		{
			my %host_maintenance_state = (0 => "no", 1 => "yes");
			$output = "maintenance=" . $host_maintenance_state{$runtime->inMaintenanceMode};
			$res = 'OK';
		}
		elsif ((uc($subcommand) eq "LIST") || (uc($subcommand) eq "LISTVM"))
		{
			my %vm_state_strings = ("poweredOn" => "UP", "poweredOff" => "DOWN", "suspended" => "SUSPENDED");
			my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', begin_entity => $host_view, properties => ['name', 'runtime']);
			die "Runtime error\n" if (!defined($vm_views));
			die "There are no VMs.\n" if (!@$vm_views);
			my $up = 0;
			$output = '';

			foreach my $vm (@$vm_views) {
				my $vm_state = $vm->runtime->powerState->val;
				$up += $vm_state eq "poweredOn";
				$output .= $vm->name . "(" . $vm_state_strings{$vm_state} . "), ";
			}

			chop($output);
			chop($output);
			$res = 'OK';
			$output = $up .  "/" . @$vm_views . " VMs up: " . $output;
			$np->add_perfdata(label => "vmcount", value => $up, uom => 'units', threshold => $np->threshold);
			$res = $np->check_threshold(check => $up) if (defined($np->threshold));
		}
		elsif (uc($subcommand) eq "STATUS")
		{
			my $status = $host_view->overallStatus->val;
			$output =  "overall status=" . $status;
			$res = check_health_state($status);
		}
		elsif (uc($subcommand) eq "ISSUES")
		{
			my $issues = $host_view->configIssue;

			if (defined($issues))
			{
				$output = '';
				foreach (@$issues)
				{
					$output .= $_->vm->name . ": " . $_->fullFormattedMessage . "(caused by " . $_->userName . "); ";
				}
			}
			else
			{
				$res = 'OK';
				$output = 'No config issues';
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST RUNTIME - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		my %host_maintenance_state = (0 => "no", 1 => "yes");
		my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', begin_entity => $host_view, properties => ['name', 'runtime']);
		my $up = 0;

		die "Runtime error\n" if (!defined($vm_views));
		if (@$vm_views)
		{
			foreach my $vm (@$vm_views) {
				$up += $vm->runtime->powerState->val eq "poweredOn";
			}
			$np->add_perfdata(label => "vmcount", value => $up, uom => 'units', threshold => $np->threshold);
			$output = $up . "/" . @$vm_views . " VMs up";
		}
		else
		{
			$output = "No VMs installed";
		}

		my $AlertCount = 0;
		my $SensorCount = 0;
		my ($cpuStatusInfo, $storageStatusInfo, $memoryStatusInfo, $numericSensorInfo);
		if(defined($runtime->healthSystemRuntime))
		{
			$cpuStatusInfo = $runtime->healthSystemRuntime->hardwareStatusInfo->cpuStatusInfo;
			$storageStatusInfo = $runtime->healthSystemRuntime->hardwareStatusInfo->storageStatusInfo;
			$memoryStatusInfo = $runtime->healthSystemRuntime->hardwareStatusInfo->memoryStatusInfo;
			$numericSensorInfo = $runtime->healthSystemRuntime->systemHealthInfo->numericSensorInfo;
		}

		if (defined($cpuStatusInfo))
		{
			foreach (@$cpuStatusInfo)
			{
				$SensorCount++;
				$AlertCount++ if (check_health_state($_->status->key) ne 'OK');
			}
		}

		if (defined($storageStatusInfo))
		{
			foreach (@$storageStatusInfo)
			{
				$SensorCount++;
				$AlertCount++ if (check_health_state($_->status->key) ne 'OK');
			}
		}

		if (defined($memoryStatusInfo))
		{
			foreach (@$memoryStatusInfo)
			{
				$SensorCount++;
				$AlertCount++ if (check_health_state($_->status->key) ne 'OK');
			}
		}

		if (defined($numericSensorInfo))
		{
			foreach (@$numericSensorInfo)
			{
				$SensorCount++;
				$AlertCount++ if (check_health_state($_->healthState->key) ne 'OK');
			}
		}

		$res = 'OK';
		$output .= ", overall status=" . $host_view->overallStatus->val . ", connection state=" . $runtime->connectionState->val . ", maintenance=" . $host_maintenance_state{$runtime->inMaintenanceMode} . ", ";

		if ($AlertCount)
		{
			$output .= "$AlertCount health issue(s), ";
		}
		else
		{
			$output .= "All $SensorCount health checks are Green, ";
		}

		my $issues = $host_view->configIssue;
		if (defined($issues))
		{
			$output .= @$issues . " config issue(s)";
		}
		else
		{
			$output .= "no config issues";
		}
	}

	return ($res, $output);
}

sub host_service_info
{
	my ($host, $np, $subcommand) = @_;

	my $res = 'CRITICAL';
	my $output = 'HOST RUNTIME Unknown error';
	my $host_view = Vim::find_entity_view(view_type => 'HostSystem', filter => $host, properties => ['name', 'configManager']);
	die "Host \"" . $$host{"name"} . "\" does not exist\n" if (!defined($host_view));

	my $services = Vim::get_view(mo_ref => $host_view->configManager->serviceSystem)->serviceInfo->service;

	if (defined($subcommand))
	{
		$subcommand = ',' . $subcommand . ',';
		$output = '';
		foreach (@$services)
		{
			my $srvname = $_->key;
			if ($subcommand =~ s/,$srvname,/,/g)
			{
				while ($subcommand =~ s/,$srvname,/,/g){};
				$output .= $srvname . ", "  if (!$_->running);
			}
		}
		$subcommand =~ s/^,//;
		chop($subcommand);

		if ($subcommand ne '')
		{
			$res = 'UNKNOWN';
			$output = "unknown services : $subcommand";
		}
		elsif ($output eq '')
		{
			$res = 'OK';
			$output = "All services are in their apropriate state.";
		}
		else
		{
			chop($output);
			chop($output);
			$output .= " are down";
		}
	}
	else
	{
		my %service_state = (0 => "down", 1 => "up");
		$res = 'OK';
		$output = "services : ";
		$output .= $_->key . " (" . $service_state{$_->running} . "), " foreach (@$services);	
		chop($output);
		chop($output);
	}

	return ($res, $output);
}
#==========================================================================| VM |============================================================================#

sub vm_cpu_info
{
	my ($vmname, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST-VM CPU Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_vmware_performance_values($vmname, 'cpu', ('usage.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) * 0.01);
				$np->add_perfdata(label => "cpu_usage", value => $value, uom => '%', threshold => $np->threshold);
				$output = "\"$vmname\" cpu usage=" . $value . " %"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "USAGEMHZ")
		{
			$values = return_host_vmware_performance_values($vmname, 'cpu', ('usagemhz.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "cpu_usagemhz", value => $value, uom => 'Mhz', threshold => $np->threshold);
				$output = "\"$vmname\" cpu usage=" . $value . " MHz";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "WAIT")
		{
			$values = return_host_vmware_performance_values($vmname, 'cpu', ('wait.summation:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "cpu_wait", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "\"$vmname\" cpu wait=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST-VM CPU - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_vmware_performance_values($vmname, 'cpu', ('usagemhz.average', 'usage.average', 'wait.summation:*'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value));
			my $value2 = simplify_number(convert_number($$values[0][1]->value) * 0.01);
			my $value3 = simplify_number(convert_number($$values[0][2]->value));
			$np->add_perfdata(label => "cpu_usagemhz", value => $value1, uom => 'Mhz', threshold => $np->threshold);
			$np->add_perfdata(label => "cpu_usage", value => $value2, uom => '%', threshold => $np->threshold);
			$np->add_perfdata(label => "cpu_wait", value => $value3, uom => 'ms', threshold => $np->threshold);
			$res = 'OK';
			$output = "\"$vmname\" cpu usage=" . $value1 . " MHz(" . $value2 . "%) wait=" . $value3 . " ms";
		}
	}

	return ($res, $output);
}

sub vm_mem_info
{
	my ($vmname, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST-VM MEM Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('usage.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) * 0.01);
				$np->add_perfdata(label => "mem_usage", value => $value, uom => '%', threshold => $np->threshold);
				$output = "\"$vmname\" mem usage=" . $value . " %"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "USAGEMB")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('consumed.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_usagemb", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" mem usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SWAP")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('swapped.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_swap", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" swap usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SWAPIN")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('swapin.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_swapin", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" swapin=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SWAPOUT")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('swapout.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_swapout", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" swapout=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "OVERHEAD")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('overhead.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_overhead", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" mem overhead=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "OVERALL")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('consumed.average', 'overhead.average'));
			if (defined($values))
			{
				my $value = simplify_number((convert_number($$values[0][0]->value) + convert_number($$values[0][1]->value)) / 1024);
				$np->add_perfdata(label => "mem_overall", value =>  $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" mem overall=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "ACTIVE")
		{
			$values = return_host_vmware_performance_values($vmname, 'mem', ('active.average'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "mem_active", value =>  $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" mem active=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST-VM MEM - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_vmware_performance_values($vmname, 'mem', ('consumed.average', 'usage.average', 'overhead.average', 'active.average', 'swapped.average', 'swapin.average', 'swapout.average'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value) / 1024);
			my $value2 = simplify_number(convert_number($$values[0][1]->value) * 0.01);
			my $value3 = simplify_number(convert_number($$values[0][2]->value) / 1024);
			my $value4 = simplify_number(convert_number($$values[0][3]->value) / 1024);
			my $value5 = simplify_number(convert_number($$values[0][4]->value) / 1024);
			my $value6 = simplify_number(convert_number($$values[0][5]->value) / 1024);
			my $value7 = simplify_number(convert_number($$values[0][6]->value) / 1024);
			$np->add_perfdata(label => "mem_usagemb", value => $value1, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_usage", value => $value2, uom => '%', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_overhead", value => $value3, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_active", value => $value4, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_swap", value => $value5, uom => 'MB', threshold => $np->threshold);
			$res = 'OK';
			$output =  "\"$vmname\" mem usage=" . $value1 . " MB(" . $value2 . "%), overhead=" . $value3 . " MB, active=" . $value4 . " MB, swapped=" . $value5 . " MB, swapin=" . $value6 . " MB, swapout=" . $value7 . " MB";
		}
	}

	return ($res, $output);
}

sub vm_net_info
{
	my ($vmname, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST-VM NET Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_vmware_performance_values($vmname, 'net', ('usage.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "net_usage", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "\"$vmname\" net usage=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "RECEIVE")
		{
			$values = return_host_vmware_performance_values($vmname, 'net', ('received.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "net_receive", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "\"$vmname\" net receive=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SEND")
		{
			$values = return_host_vmware_performance_values($vmname, 'net', ('transmitted.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value));
				$np->add_perfdata(label => "net_send", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "\"$vmname\" net send=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST-VM NET - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_vmware_performance_values($vmname, 'net', ('received.average:*', 'transmitted.average:*'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value));
			my $value2 = simplify_number(convert_number($$values[0][1]->value));
			$np->add_perfdata(label => "net_receive", value => $value1, uom => 'KBps', threshold => $np->threshold);
			$np->add_perfdata(label => "net_send", value => $value2, uom => 'KBps', threshold => $np->threshold);
			$res = 'OK';
			$output = "\"$vmname\" net receive=" . $value1 . " KBps, send=" . $value2 . " KBps";
		}
	}

	return ($res, $output);
}

sub vm_disk_io_info
{
	my ($vmname, $np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'HOST-VM IO Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_host_vmware_performance_values($vmname, 'disk', ('usage.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "io_usage", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "\"$vmname\" io usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "READ")
		{
			$values = return_host_vmware_performance_values($vmname, 'disk', ('read.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "io_read", value => $value, uom => 'MB/s', threshold => $np->threshold);
				$output = "\"$vmname\" io read=" . $value . " MB/s";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "WRITE")
		{
			$values = return_host_vmware_performance_values($vmname, 'disk', ('write.average:*'));
			if (defined($values))
			{
				my $value = simplify_number(convert_number($$values[0][0]->value) / 1024);
				$np->add_perfdata(label => "io_write", value => $value, uom => 'MB/s', threshold => $np->threshold);
				$output = "\"$vmname\" io write=" . $value . " MB/s";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST IO - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_host_vmware_performance_values($vmname, 'disk', ('usage.average:*', 'read.average:*', 'write.average:*'));
		if (defined($values))
		{
			my $value1 = simplify_number(convert_number($$values[0][0]->value) / 1024);
			my $value2 = simplify_number(convert_number($$values[0][1]->value) / 1024);
			my $value3 = simplify_number(convert_number($$values[0][2]->value) / 1024);
			$np->add_perfdata(label => "io_usage", value => $value1, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "io_read", value => $value2, uom => 'MB/s', threshold => $np->threshold);
			$np->add_perfdata(label => "io_write", value => $value3, uom => 'MB/s', threshold => $np->threshold);
			$res = 'OK';
			$output = "\"$vmname\" io usage=" . $value1 . " MB, read=" . $value2 . " MB/s, write=" . $value3 . " MB/s";
		}
	}

	return ($res, $output);
}

sub vm_runtime_info
{
	my ($vmname, $np, $subcommand) = @_;

	my $res = 'CRITICAL';
	my $output = 'HOST-VM RUNTIME Unknown error';
	my $runtime;
	my $vm_view = Vim::find_entity_view(view_type => 'VirtualMachine', filter => {name => $vmname}, properties => ['name', 'runtime', 'overallStatus', 'guest', 'configIssue']);
	die "VMware machine \"" . $vmname . "\" does not exist\n" if (!defined($vm_view));
	$runtime = $vm_view->runtime;

	if (defined($subcommand))
	{
		if (uc($subcommand) eq "CON")
		{
			$output = "\"$vmname\" connection state=" . $runtime->connectionState->val;
			$res = 'OK' if ($runtime->connectionState->val eq "connected");
		}
		elsif (uc($subcommand) eq "CPU")
		{
			$output = "\"$vmname\" max cpu=" . $runtime->maxCpuUsage . " MHz";
			$res = 'OK';
		}
		elsif (uc($subcommand) eq "MEM")
		{
			$output = "\"$vmname\" max mem=" . $runtime->maxMemoryUsage . " MB";
			$res = 'OK';
		}
		elsif (uc($subcommand) eq "STATE")
		{
			my %vm_state_strings = ("poweredOn" => "UP", "poweredOff" => "DOWN", "suspended" => "SUSPENDED");
			$output = "\"$vmname\" run state=" . $vm_state_strings{$runtime->powerState->val};
			$res = 'OK' if ($runtime->powerState->val eq "poweredOn");
		}
		elsif (uc($subcommand) eq "STATUS")
		{
			my $status = $vm_view->overallStatus->val;
			$output = "\"$vmname\" overall status=" . $status;
			$res = check_health_state($status);
		}
		elsif (uc($subcommand) eq "CONSOLECONNECTIONS")
		{
			$output = "\"$vmname\" console connections=" . $runtime->numMksConnections;
			$res = $np->check_threshold(check => $runtime->numMksConnections);
		}
		elsif (uc($subcommand) eq "GUEST")
		{
			my %vm_guest_state = ("running" => "Running", "notRunning" => "Not running", "shuttingDown" => "Shutting down", "resetting" => "Resetting", "standby" => "Standby", "unknown" => "Unknown");
			$output = "\"$vmname\" guest state=" . $vm_guest_state{$vm_view->guest->guestState};
			$res = 'OK' if ($vm_view->guest->guestState eq "running");
		}
		elsif (uc($subcommand) eq "TOOLS")
		{
			my %vm_tools_status = ("toolsNotInstalled" => "Not installed", "toolsNotRunning" => "Not running", "toolsOk" => "OK", "toolsOld" => "Old");
			$output = "\"$vmname\" tools status=" . $vm_tools_status{$vm_view->guest->toolsStatus->val};
			$res = 'OK' if ($vm_view->guest->toolsStatus->val eq "toolsOk");
		}
		elsif (uc($subcommand) eq "ISSUES")
		{
			my $issues = $vm_view->configIssue;

			if (defined($issues))
			{
				$output = "\"$vmname\": ";
				foreach (@$issues)
				{
					$output .=  $_->fullFormattedMessage . "(caused by " . $_->userName . "); ";
				}
			}
			else
			{
				$res = 'OK';
				$output = "\"$vmname\" has no config issues";
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST-VM RUNTIME - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		my %vm_state_strings = ("poweredOn" => "UP", "poweredOff" => "DOWN", "suspended" => "SUSPENDED");
		my %vm_tools_status = ("toolsNotInstalled" => "Not installed", "toolsNotRunning" => "Not running", "toolsOk" => "OK", "toolsOld" => "Old");
		my %vm_guest_state = ("running" => "Running", "notRunning" => "Not running", "shuttingDown" => "Shutting down", "resetting" => "Resetting", "standby" => "Standby", "unknown" => "Unknown");
		$res = 'OK';
		$output = "\"$vmname\" status=" . $vm_view->overallStatus->val . ", run state=" . $vm_state_strings{$runtime->powerState->val} . ", guest state=" . $vm_guest_state{$vm_view->guest->guestState} . ", max cpu=" . $runtime->maxCpuUsage . " MHz, max mem=" . $runtime->maxMemoryUsage . " MB, console connections=" . $runtime->numMksConnections . ", tools status=" . $vm_tools_status{$vm_view->guest->toolsStatus->val} . ", ";
		my $issues = $vm_view->configIssue;
		if (defined($issues))
		{
			$output .= @$issues . " config issue(s)";
		}
		else
		{
			$output .= "has no config issues";
		}
	}

	return ($res, $output);
}

#==========================================================================| DC |============================================================================#

sub return_cluster_DRS_recommendations {
	my ($np, $cluster_name) = @_;
	my $res = 'OK';
	my $output;
	my @clusters;

	if (defined($cluster_name))
	{
		my $cluster = Vim::find_entity_view(view_type => 'ClusterComputeResource', filter => $cluster_name, properties => ['name']);
		die "cluster \"" . $$cluster_name{"name"} . "\" does not exist\n" if (!defined($cluster));
		push(@clusters, $cluster);
	}
	else
	{
		my $cluster = Vim::find_entity_views(view_type => 'ClusterComputeResource', properties => ['name', 'recommendation']);
		die "Runtime error\n" if (!defined($cluster));
		die "There are no clusters\n" if (!@$cluster);
		@clusters = @$cluster;
	}

	foreach my $cluster_view (@clusters)
	{
		my ($recommends) = $cluster_view->recommendation;
		if (defined($recommends))
		{
			my $value = 0;
			foreach my $recommend (@$recommends)
			{
				$value = $recommend->rating if ($recommend->rating > $value);
				$output .= "(" . $recommend->rating . ") " . $recommend->reason . " : " . $recommend->reasonText . "; ";
			}
			$res = $np->check_threshold(check => $value);
		}
	}

	if (defined($output))
	{
		$output = "Recommendations:" . $output;
	}
	else
	{
		$output = "No recommendations";
	}

	return ($res, $output);
}

sub dc_cpu_info
{
	my ($np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'DC CPU Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_dc_performance_values('cpu', ('usage.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value) * 0.01, @$values);
				$value = simplify_number($value / @$values);
				$np->add_perfdata(label => "cpu_usage", value => $value, uom => '%', threshold => $np->threshold);
				$output = "cpu usage=" . $value . " %"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "USAGEMHZ")
		{
			$values = return_dc_performance_values('cpu', ('usagemhz.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "cpu_usagemhz", value => $value, uom => 'Mhz', threshold => $np->threshold);
				$output = "cpu usagemhz=" . $value . " MHz";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "DC CPU - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_dc_performance_values('cpu', ('usagemhz.average', 'usage.average'));
		if (defined($values))
		{
			my $value1 = 0;
			my $value2 = 0;
			grep($value1 += convert_number($$_[0]->value), @$values);
			grep($value2 += convert_number($$_[1]->value) * 0.01, @$values);
			$value1 = simplify_number($value1);
			$value2 = simplify_number($value2 / @$values);
			$np->add_perfdata(label => "cpu_usagemhz", value => $value1, uom => 'Mhz', threshold => $np->threshold);
			$np->add_perfdata(label => "cpu_usage", value => $value2, uom => '%', threshold => $np->threshold);
			$res = 'OK';
			$output = "cpu usage=" . $value1 . " MHz (" . $value2 . "%)";
		}
	}

	return ($res, $output);
}

sub dc_mem_info
{
	my ($np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'DC MEM Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_dc_performance_values('mem', ('usage.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value) * 0.01, @$values);
				$value = simplify_number($value / @$values);
				$np->add_perfdata(label => "mem_usage", value => $value, uom => '%', threshold => $np->threshold);
				$output = "mem usage=" . $value . " %"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "USAGEMB")
		{
			$values = return_dc_performance_values('mem', ('consumed.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value) / 1024, @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "mem_usagemb", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "mem usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SWAP")
		{
			$values = return_dc_performance_values('mem', ('swapused.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value) / 1024, @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "mem_swap", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "swap usage=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "OVERHEAD")
		{
			$values = return_dc_performance_values('mem', ('overhead.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value) / 1024, @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "mem_overhead", value => $value, uom => 'MB', threshold => $np->threshold);
				$output = "overhead=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "OVERALL")
		{
			$values = return_dc_performance_values('mem', ('consumed.average', 'overhead.average'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += (convert_number($$_[0]->value) + convert_number($$_[1]->value)) / 1024, @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "mem_overhead", value =>  $value, uom => 'MB', threshold => $np->threshold);
				$output = "overall=" . $value . " MB";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "DC MEM - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_dc_performance_values('mem', ('consumed.average', 'usage.average', 'overhead.average', 'swapused.average'));
		if (defined($values))
		{
			my $value1 = 0;
			my $value2 = 0;
			my $value3 = 0;
			my $value4 = 0;
			grep($value1 += convert_number($$_[0]->value) / 1024, @$values);
			grep($value2 += convert_number($$_[1]->value) * 0.01, @$values);
			grep($value3 += convert_number($$_[2]->value) / 1024, @$values);
			grep($value4 += convert_number($$_[3]->value) / 1024, @$values);
			$value1 = simplify_number($value1);
			$value2 = simplify_number($value2 / @$values);
			$value3 = simplify_number($value3);
			$value4 = simplify_number($value4);
			$np->add_perfdata(label => "mem_usagemb", value => $value1, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_usage", value => $value2, uom => '%', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_overhead", value => $value3, uom => 'MB', threshold => $np->threshold);
			$np->add_perfdata(label => "mem_swap", value => $value4, uom => 'MB', threshold => $np->threshold);
			$res = 'OK';
			$output = "mem usage=" . $value1 . " MB (" . $value2 . "%), overhead=" . $value3 . " MB, swapped=" . $value4 . " MB";
		}
	}

	return ($res, $output);
}

sub dc_net_info
{
	my ($np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'DC NET Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "USAGE")
		{
			$values = return_dc_performance_values('net', ('usage.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "net_usage", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "net usage=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "RECEIVE")
		{
			$values = return_dc_performance_values('net', ('received.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "net_receive", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "net receive=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "SEND")
		{
			$values = return_dc_performance_values('net', ('transmitted.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value);
				$np->add_perfdata(label => "net_send", value => $value, uom => 'KBps', threshold => $np->threshold);
				$output = "net send=" . $value . " KBps"; 
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "DC NET - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_dc_performance_values('net', ('received.average:*', 'transmitted.average:*'));
		if (defined($values))
		{
			my $value1 = 0;
			my $value2 = 0;
			grep($value1 += convert_number($$_[0]->value), @$values);
			grep($value2 += convert_number($$_[1]->value), @$values);
			$value1 = simplify_number($value1);
			$value2 = simplify_number($value2);
			$np->add_perfdata(label => "net_receive", value => $value1, uom => 'KBps', threshold => $np->threshold);
			$np->add_perfdata(label => "net_send", value => $value2, uom => 'KBps', threshold => $np->threshold);
			$res = 'OK';
			$output = "net receive=" . $value1 . " KBps, send=" . $value2 . " KBps";
		}
	}

	return ($res, $output);
}

sub dc_list_vm_volumes_info
{
	my ($np, $subcommand, $blacklist, $perc) = @_;
	
	my $res = 'CRITICAL';
	my $output = 'DC VM VOLUMES Unknown error';

	if (defined($subcommand))
	{
		$output = "No volume named $subcommand found";
		my $host_views = Vim::find_entity_views(view_type => 'HostSystem', properties => ['name', 'datastore']);
		die "Runtime error\n" if (!defined($host_views));
		die "Datacenter does not contain any hosts\n" if (!@$host_views);

		HOSTITER: foreach my $host (@$host_views) {
			foreach my $ref_store (@{$host->datastore})
			{
				my $store = Vim::get_view(mo_ref => $ref_store, properties => ['summary', 'info']);
				if ($store->summary->name eq $subcommand)
				{
					$res = 'OK';
					my $value1 = simplify_number(convert_number($store->summary->freeSpace) / 1024 / 1024);
					my $value2 = simplify_number(convert_number($store->info->freeSpace) / convert_number($store->summary->capacity) * 100);
					if ($perc)
					{
						$res = $np->check_threshold(check => $value2);
					}
					else
					{
						$res = $np->check_threshold(check => $value1);
					}				
					$np->add_perfdata(label => $store->summary->name, value => $perc?$value2:$value1, uom => $perc?'%':'MB', threshold => $np->threshold);
					$output = $store->summary->name . "=". $value1 . " MB (" . $value2 . "%)";
					last HOSTITER;
				}
			}
		}
	}
	else
	{
		$res = OK;
		$output = '';
		my $host_views = Vim::find_entity_views(view_type => 'HostSystem', properties => ['name', 'datastore']);
		die "Runtime error\n" if (!defined($host_views));
		die "Datacenter does not contain any hosts\n" if (!@$host_views);

		foreach my $host (@$host_views) {
			foreach my $ref_store (@{$host->datastore})
			{
				my $store = Vim::get_view(mo_ref => $ref_store, properties => ['summary', 'info']);

				if (defined($blacklist))
				{
					my $name = $store->summary->name;
					next if ($blacklist =~ m/(^|\s|\t|,)\Q$name\E($|\s|\t|,)/);
				}

				my $value1 = simplify_number(convert_number($store->summary->freeSpace) / 1024 / 1024);
				my $value2 = simplify_number(convert_number($store->info->freeSpace) / convert_number($store->summary->capacity) * 100);

				if ($perc)
				{
					$res = Nagios::Plugin::Functions::max_state($res, $np->check_threshold(check => $value2));
				}
				else
				{
					$res = Nagios::Plugin::Functions::max_state($res, $np->check_threshold(check => $value1));
				}

				$np->add_perfdata(label => $store->summary->name, value => $perc?$value2:$value1, uom => $perc?'%':'MB', threshold => $np->threshold);
				$output .= $store->summary->name . "=". $value1 . " MB (" . $value2 . "%), ";
			}
		}
		chop($output);
		chop($output);
		$output = "storages : " . $output;
	}

	return ($res, $output);
}

sub dc_disk_io_info
{
	my ($np, $subcommand) = @_;
	 
	my $res = 'CRITICAL';
	my $output = 'DC IO Unknown error';
	
	if (defined($subcommand))
	{
		if (uc($subcommand) eq "ABORTED")
		{
			$values = return_dc_performance_values('disk', ('commandsAborted.summation:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_aborted", value => $value, threshold => $np->threshold);
				$output = "io commands aborted=" . $value;
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "RESETS")
		{
			$values = return_dc_performance_values('disk', ('busResets.summation:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_busresets", value => $value, threshold => $np->threshold);
				$output = "io bus resets=" . $value;
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "READ")
		{
			$values = return_dc_performance_values('disk', ('totalReadLatency.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_read", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io read latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "WRITE")
		{
			$values = return_dc_performance_values('disk', ('totalWriteLatency.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_write", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io write latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "KERNEL")
		{
			$values = return_dc_performance_values('disk', ('kernelLatency.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_kernel", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io kernel latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "DEVICE")
		{
			$values = return_dc_performance_values('disk', ('deviceLatency.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_device", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io device latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		elsif (uc($subcommand) eq "QUEUE")
		{
			$values = return_dc_performance_values('disk', ('queueLatency.average:*'));
			if (defined($values))
			{
				my $value = 0;
				grep($value += convert_number($$_[0]->value), @$values);
				$value = simplify_number($value, 0);
				$np->add_perfdata(label => "io_queue", value => $value, uom => 'ms', threshold => $np->threshold);
				$output = "io queue latency=" . $value . " ms";
				$res = $np->check_threshold(check => $value);
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "DC IO - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		$values = return_dc_performance_values('disk', ('commandsAborted.summation:*', 'busResets.summation:*', 'totalReadLatency.average:*', 'totalWriteLatency.average:*', 'kernelLatency.average:*', 'deviceLatency.average:*', 'queueLatency.average:*'));
		if (defined($values))
		{
			my $value1 = 0;
			my $value2 = 0;
			my $value3 = 0;
			my $value4 = 0;
			my $value5 = 0;
			my $value6 = 0;
			my $value7 = 0;
			grep($value1 += convert_number($$_[0]->value), @$values);
			grep($value2 += convert_number($$_[1]->value), @$values);
			grep($value3 += convert_number($$_[2]->value), @$values);
			grep($value4 += convert_number($$_[3]->value), @$values);
			grep($value5 += convert_number($$_[4]->value), @$values);
			grep($value6 += convert_number($$_[5]->value), @$values);
			grep($value7 += convert_number($$_[6]->value), @$values);
			$value1 = simplify_number($value1, 0);
			$value2 = simplify_number($value2, 0);
			$value3 = simplify_number($value3, 0);
			$value4 = simplify_number($value4, 0);
			$value5 = simplify_number($value5, 0);
			$value6 = simplify_number($value6, 0);
			$value7 = simplify_number($value7, 0);
			$np->add_perfdata(label => "io_aborted", value => $value1, threshold => $np->threshold);
			$np->add_perfdata(label => "io_busresets", value => $value2, threshold => $np->threshold);
			$np->add_perfdata(label => "io_read", value => $value3, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_write", value => $value4, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_kernel", value => $value5, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_device", value => $value6, uom => 'ms', threshold => $np->threshold);
			$np->add_perfdata(label => "io_queue", value => $value7, uom => 'ms', threshold => $np->threshold);
			$res = 'OK';
			$output = "io commands aborted=" . $value1 . ", io bus resets=" . $value2 . ", io read latency=" . $value3 . " ms, write latency=" . $value4 . " ms, kernel latency=" . $value5 . " ms, device latency=" . $value6 . " ms, queue latency=" . $value7 ." ms";
		}
	}

	return ($res, $output);
}

sub dc_runtime_info
{
	my ($np, $subcommand) = @_;

	my $res = 'CRITICAL';
	my $output = 'DC RUNTIME Unknown error';
	my $runtime;
	my $dc_view = Vim::find_entity_view(view_type => 'Datacenter', properties => ['name', 'overallStatus', 'configIssue']);

	die "There are no Datacenter\n" if (!defined($dc_view));

	if (defined($subcommand))
	{
		if ((uc($subcommand) eq "LIST") || (uc($subcommand) eq "LISTVM"))
		{
			my %vm_state_strings = ("poweredOn" => "UP", "poweredOff" => "DOWN", "suspended" => "SUSPENDED");
			my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', properties => ['name', 'runtime']);
			die "Runtime error\n" if (!defined($vm_views));
			die "There are no VMs.\n" if (!@$vm_views);
			my $up = 0;
			$output = '';

			foreach my $vm (@$vm_views) {
				my $vm_state = $vm->runtime->powerState->val;
				$up += $vm_state eq "poweredOn";
				$output .= $vm->name . "(" . $vm_state_strings{$vm_state} . "), ";
			}

			chop($output);
			chop($output);
			$res = 'OK';
			$output = $up .  "/" . @$vm_views . " VMs up: " . $output;
			$np->add_perfdata(label => "vmcount", value => $up, uom => 'units', threshold => $np->threshold);
			$res = $np->check_threshold(check => $up) if (defined($np->threshold));
		}
		elsif (uc($subcommand) eq "LISTHOST")
		{
			my %host_state_strings = ("poweredOn" => "UP", "poweredOff" => "DOWN", "suspended" => "SUSPENDED");
			my $host_views = Vim::find_entity_views(view_type => 'HostSystem');
			die "Runtime error\n" if (!defined($host_views));
			die "There are no VMs.\n" if (!@$host_views);
			my $up = 0;
			$output = '';

			foreach my $host (@$host_views) {
				$host->update_view_data();
				my $host_state = $host->runtime->powerState->val;
				$up += $host_state eq "poweredOn";
				$output .= $host->name . "(" . $host_state_strings{$host_state} . "), ";
			}

			chop($output);
			chop($output);
			$res = 'OK';
			$output = $up .  "/" . @$host_views . " Hosts up: " . $output;
			$np->add_perfdata(label => "hostcount", value => $up, uom => 'units', threshold => $np->threshold);
			$res = $np->check_threshold(check => $up) if (defined($np->threshold));
		}
		elsif (uc($subcommand) eq "STATUS")
		{
			my $status = $dc_view->overallStatus->val;
			$output =  "overall status=" . $status;
			$res = check_health_state($status);
		}
		elsif (uc($subcommand) eq "ISSUES")
		{
			my $issues = $dc_view->configIssue;

			if (defined($issues))
			{
				$output = '';
				foreach (@$issues)
				{
					$output .= $_->vm->name . ": " . $_->fullFormattedMessage . "(caused by " . $_->userName . "); ";
				}
			}
			else
			{
				$res = 'OK';
				$output = 'No config issues';
			}
		}
		else
		{
			$res = 'CRITICAL';
			$output = "HOST RUNTIME - unknown subcommand\n" . $np->opts->_help;
		}
	}
	else
	{
		my %host_maintenance_state = (0 => "no", 1 => "yes");
		my $vm_views = Vim::find_entity_views(view_type => 'VirtualMachine', properties => ['name', 'runtime']);
		my $up = 0;

		die "Runtime error\n" if (!defined($vm_views));
		
		if (@$vm_views)
		{
			foreach my $vm (@$vm_views) {
				$up += $vm->runtime->powerState->val eq "poweredOn";
			}
			$np->add_perfdata(label => "vmcount", value => $up, uom => 'units', threshold => $np->threshold);
			$output = $up . "/" . @$vm_views . " VMs up";
		}
		else
		{
			$output = "No VMs installed";
		}

		$res = 'OK';
		$output .= ", overall status=" . $dc_view->overallStatus->val . ", ";
		my $issues = $dc_view->configIssue;
		if (defined($issues))
		{
			$output .= @$issues . " config issue(s)";
		}
		else
		{
			$output .= "no config issues";
		}
	}

	return ($res, $output);
}

