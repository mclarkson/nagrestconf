        <!-- Content -->

        <h1>Tools and Commands</h1>

        <ul>
          <li><a href="#auto">auto_reschedule_nagios_check</a></li>
          <li><a href="#dcc_">dcc_configure</a></li>
          <li><a href="#nagr">nagrestconf_install</a></li>
          <li><a href="#rest">restart_nagios</a></li>
          <li><a href="#nagc">nagctl</a></li>
          <li><a href="#upgr">upgrade_setup_files.sh</a></li>
          <li><a href="#upda">update_nagios</a></li>
          <li><a href="#csv2">csv2nag</a></li>
          <li><a href="#slc_">slc_configure</a></li>
        </ul>

        <h3 id="auto">auto_reschedule_nagios_check</h3>

        <p>The script auto_reschedule_nagios_check is used on slave servers
        in distributed environments. It catches occasional border cases where
        service checks fall out of the scheduling loop and reschedules the
        check using the nagios command pipe.</p>

        <p>To configure this script ensure that the configuration file,
        <code>/etc/nagrestconf/nagctl.conf</code>, has the COMMANDFILE and
        STATUSFILE variables correctly set.</p>

        <p>This script takes no options and is typically run by the CRON daemon
        every 10 minutes. A typical crontab entry is shown in the CRON section
        of the <a href="/documentation/administration.php#cron">Administration</a>
        page.</p>

        <h3 id="dcc_">dcc_configure</h3>

        This script serves the same purpose as <a href="#slc_">slc_configure</a>
        but for a collector node.

        <h3 id="nagr">nagrestconf_install</h3>
        <pre>
NAGRESTCONF_INSTALL(8)  System Administration Utilities NAGRESTCONF_INSTALL(8)



NAME
       nagrestconf_install - Initial operating system setup for nagrestconf.

SYNOPSIS
       nagrestconf_install [-h] [-v] [-a] [-n] [-o] [-p] [-q] [-r]

DESCRIPTION
       nagrestconf_install - Modify the environment so nagrestconf will work.

       -h     :  Display this help text.

       -v     :  Display the program version string.

       -a     :  Try to do everything required.

       -n     :  Modify nagios.cfg only.

       -o     :  Modify system users only.

       -p     :  Modify crontab only.

       -q     :  Modify sudoers only.

       -r     :  Restart services only.



nagrestconf_install 0.10         January 2013           NAGRESTCONF_INSTALL(8)
</pre>

        <h3 id="rest">restart_nagios</h3>
        <pre>
RESTART_NAGIOS(8)       System Administration Utilities      RESTART_NAGIOS(8)



NAME
       restart_nagios - Nagios restarter and subversion updater.

SYNOPSIS
       restart_nagios


DESCRIPTION
       Restarts  the  nagios  daemon,  checks in the changes to subversion and
       mirrors the subversion repo.

       This script will usually be run from cron, but it can be run directly.

       This   program   uses   the   configuration   files   nagctl.conf   and
       restart_nagios.conf in /etc/nagrestconf/.



restart_nagios 1.0               January 2013                RESTART_NAGIOS(8)
</pre>

        <h3 id="nagc">nagctl</h3>
        <p>This tool is intended for system use and is not intended to be used
        directly.</p>
        <pre>
NAGCTL(8)               System Administration Utilities              NAGCTL(8)



NAME
       nagctl - Manipulate csv (.setup) files.

SYNOPSIS
       nagctl [-h] <SERVICE> <ACTION> <FILE|ITEM> <CSV_LINE|QUERY>

DESCRIPTION
       -h     :  Display this help text.

       ACTION :  One of four ACTIONS are understood:

       show   -  Query a csv FILE.

       add    -  Add a line to a csv FILE

       delete -  Delete a csv entry from a FILE.

       modify -  Modify an existing csv entry.

       check  -  check an ITEM.

       pipecmd
              -  Send text to the Nagios cmd pipe.

       propagate
              -  Propagate changes to master.

       restart
              -  Restart an ITEM.

       SERVICE
              :  The directory holding the configuration.

       ITEM   :  For things that are not add, delete, show or modify.

       FILE   :  The file to ACTION. E.g. hosts, services.

       CSV_LINE :
              A line of CSV in the same format as the target FILE.

       QUERY  :  A query to 'show'.



nagctl 1.0                       January 2013                        NAGCTL(8)
</pre>

        <h3 id="upgr">upgrade_setup_files.sh</h3>

        <p>This tool is only used when upgrading nagrestconf, but only when indicated
        to do so.</p>

        <p>This tool shouldn't be used unless told to do so. It creates extra columns
        in the csv files when new REST functionality has been added.</p>

        <h3 id="upda">update_nagios</h3>

        <p>This tool is run by cron on a collector node in distributed environments.
        It updates environment folders in <code>/etc/nagios/objects</code> that have
        been copied over by slave nodes using svnsync.</p>
        
        <p>A typical crontab entry is shown in the CRON section
        of the <a href="/documentation/administration.php#cron">Administration</a>
        page.</p>

        <h3 id="csv2">csv2nag</h3>
        <pre>
CSV2NAG(8)              System Administration Utilities             CSV2NAG(8)



NAME
       csv2nag  -  Create  nagios (.cfg) configuration files from csv (.setup)
       files.

SYNOPSIS
       csv2nag [-yhs] TYPE

DESCRIPTION
       -y     :  Answer 'yes' to any questions.

       -h     :  Display this help text.

       -s NAME
              :  Use NAME for service-line name instead of using the

              parent directory as the service-line name.

       TYPE   :  One of six TYPES are understood:

       hosts  -  Create hosts files.

              One host file is created per  host  in  the  nagrestconf-1-nodes
              directory.   The  nagrestconf-1-hostgroups.cfg file is also cre‐
              ated in the top level directory.

       services
              -  Add services to hosts files in

              the nagrestconf-1-nodes directory.  This  script  should  previ‐
              ously  have  been  run  with  the  'hosts'  TYPE.   The nagrest‐
              conf-1-servicegroups.cfg file will also be created.

       hosttemplates
              -  Create a hosttemplates file.

       svctemplates
              -  Create a servicetemplates file.

       contacts
              -  Create a contacts file.

       commands
              -  Create a commands file.

       timeperiods
              -  Create a timeperiods file.

       servicedeps
              -  Create a servicedeps file.

       hostdeps
              -  Create a hostdeps file.

       serviceesc
              -  Create a serviceesc file.

       hostesc
              -  Create a hostesc file.

       serviceext
              -  Create a serviceext file.

       hostext
              -  Create a hostext file.

       all    -  Create all TYPEs in one go.

       csv2nag should be run from the directory containing the 'setup'  direc‐
       tory and the following files must exist:

              setup/&lt;FOLDER_NAME&gt;-hosts.setup
              setup/&lt;FOLDER_NAME&gt;-hostgroups.setup
              setup/&lt;FOLDER_NAME&gt;-hosttemplates.setup
              setup/&lt;FOLDER_NAME&gt;-services.setup
              setup/&lt;FOLDER_NAME&gt;-servicegroups.setup
              setup/&lt;FOLDER_NAME&gt;-servicetemplates.setup
              setup/&lt;FOLDER_NAME&gt;-contacts.setup
              setup/&lt;FOLDER_NAME&gt;-contact‐groups.setup
              setup/&lt;FOLDER_NAME&gt;-commands.setup

EXAMPLES
              Create the entire config from scratch, wiping  out  the  current
              one:

              csv2nag all



csv2nag 1.0                      January 2013                       CSV2NAG(8)
</pre>

        <h3 id="slc_">slc_configure</h3>
        <pre>
SLC_CONFIGURE(8)        System Administration Utilities       SLC_CONFIGURE(8)



NAME
       slc_configure  -  Initial setup of nagios object directories for use by
       nagrestconf programs.

DESCRIPTION
       Configure a Data Centre Collector:

              slc_configure [-h] [--folder=<folder_name>]

       -h            - this help text.

       --folder      - the service line folder name to add.

       --dccip       - IP address of the DCC.

       Any omitted options will be prompted for.



slc_configure 1.0                January 2013                 SLC_CONFIGURE(8)
</pre>

        <!-- /Content -->
