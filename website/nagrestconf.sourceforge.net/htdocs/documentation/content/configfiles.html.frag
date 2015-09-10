        <!-- Content -->

        <h1>Configuration Files</h1>

        <p>All the configuration files are stored in /etc/nagrestconf and can
        be edited from the command line.</p>
        <p>More information is available in the comments in configuration file.</p>

        <h3>Files</h3>

        <ul>
          <li><a href="#csv2">csv2nag.conf</a></li>
          <li><a href="#nagc">nagctl.conf</a></li>
          <li><a href="#nagr">nagrestconf.ini</a></li>
          <li><a href="#rest">restart_nagios.conf</a></li>
        </ul>

        <h3 id="csv2">csv2nag.conf</h3>

        <table class="table table-bordered table-striped">
          <tr>
            <td>DCC</td>
            <td>Set DCC=1 if this is a data centre collector. E.g. DCC=0</td>
          </tr>
          <tr>
            <td>REMOTE_EXECUTOR</td>
            <td>pnp4nagios helper. E.g. "check_nrpe"</td>
          </tr>
          <tr>
            <td>FRESHNESS_CHECK_COMMAND</td>
            <td>The command used to check freshness on a collector. E.g. "no-checks-received"</td>
          </tr>
        </table>

        <h3 id="nagc">nagctl.conf</h3>

        <table class="table table-bordered table-striped">
          <tr>
            <td>NAG_DIR</td>
            <td>Nagios 'etc' directory. E.g. /etc/nagios</td>
          </tr>
          <tr>
            <td>NAG_OBJ_DIR</td>
            <td>Where the objects directory is. E.g. $NAG_DIR/objects</td>
          </tr>
          <tr>
            <td>NAG_CONFIG</td>
            <td>Where nagios.cfg file is. E.g. $NAG_DIR/nagios.cfg</td>
          </tr>
          <tr>
            <td>LIVESTATUSCOMMANDFILE</td>
            <td>Location of the live status unix pipe. E.g. '/var/log/nagios/rw/live'</td>
          </tr>
          <tr>
            <td>LIVESTATUSUNIXCAT</td>
            <td>Location of the unixcat command. E.g. '/usr/bin/unixcat'</td>
          </tr>
          <tr>
            <td>COMMANDFILE</td>
            <td>Where the nagios.cmd file is. E.g. '/var/log/nagios/rw/nagios.cmd'</td>
          </tr>
          <tr>
            <td>STATUSFILE</td>
            <td>Where the status.dat file is. E.g. '/var/log/nagios/status/status.dat'</td>
          </tr>
          <tr>
            <td>NAGIOSBIN</td>
            <td>Where nagios is. E.g. /usr/sbin/nagios</td>
          </tr>
          <tr>
            <td>CSV2NAG</td>
            <td>Where csv2nag is. E.g. "apache"</td>
          </tr>
          <tr>
            <td>WWWUSER</td>
            <td>The user apache runs as. E.g. "apache"</td>
          </tr>
        </table>

        <h3 id="nagr">nagrestconf.ini</h3>

        <table class="table table-bordered table-striped">
          <tr>
            <td>resturl</td>
            <td>The REST API url. E.g. "http://127.0.0.1/rest"</td>
          </tr>
          <tr>
            <td>folder[]</td>
            <td>The environment name. E.g. "local"</td>
          </tr>
          <tr>
            <td>restuser</td>
            <td>The REST API user name. E.g. "user"</td>
          </tr>
          <tr>
            <td>restpass</td>
            <td>The REST API password. E.g. "pass"</td>
          </tr>
          <tr>
            <td>sslkey</td>
            <td>Pasword-less ssl key. E.g. "/path/to/key"</td>
          </tr>
          <tr>
            <td>sslcert</td>
            <td>Certificate for the sslkey. E.g. "/path/to/cert"</td>
          </tr>
        </table>

        <h3 id="rest">restart_nagios.conf</h3>

        <table class="table table-bordered table-striped">
          <tr>
            <td>NAG_INITD</td>
            <td>Name of the nagios init.d script. E.g. nagios</td>
          </tr>
          <tr>
            <td colspan=2 align="center">Distributed Monitoring Options</td>
          </tr>
          <tr>
            <td>dcc</td>
            <td>IP address of the collector. E.g. 1.2.3.4</td>
          </tr>
          <tr>
            <td>SSH</td>
            <td>Alternative ssh tunnel command. E.g. tun1</td>
          </tr>
          <tr>
            <td>SSH_OPTS</td>
            <td>Supply extra options to ssh. E.g. "-p 2222"</td>
          </tr>
        </table>

        <!-- /Content -->
