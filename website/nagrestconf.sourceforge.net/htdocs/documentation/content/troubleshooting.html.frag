        <!-- Content -->

        <h1>Troubleshooting</h1>

        <ol>
          <li><a href="#1">Problem - Cannot write to ...</a></li>
          <li><a href="#2">Problem - Non-existent folder</a></li>
          <li><a href="#3">Problem - Nagios GUI is not updating</a></li>
          <li><a href="#4">Problem - Sorry, giving up on ...</a></li>
        </ol>

        <h3>Web Interface</h3>

        <h4 id="1">Problem - Cannot write to ...</h4>
        <a href="#img1" onClick="$('#img1').css('display','block'); return false;"><img src="/images/trouble_cannot_write.png" class="img-thumbnail"></a></p><a id="img1" class="a-imgshow" onClick="$('#img1').css('display','none'); return false;"><img src="/images/trouble_cannot_write.png" class="imgshow"></a>
        <tt>
            Could not execute query using REST.<br />
            Please check system settings.<br />
            REST return code: 400<br />
            Error was:<br />
            NAGCTL ERROR: Cannot write to /etc/nagios/objects/local/.<br />
        </tt>

        <h4>Resolution</h4>

        <ul>
          <li><p>Ensure selinux is off. Use <code>sestatus</code> to check.</p></li>
          <li><p>Check permissions of directories. They should be:</p>
          <pre>
drwxr-x---   root     nagios       /etc/nagios/objects/
drwxr-xr-x   apache   apache       /etc/nagios/objects/local
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/props
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/text-base
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/tmp
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/tmp/props
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/tmp/text-base
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/tmp/prop-base
drwxr-xr-x   root     root         /etc/nagios/objects/local/versions/.svn/prop-base
drwxr-xr-x   root     root         /etc/nagios/objects/local/local-nodes
drwxr-xr-x   root     root         /etc/nagios/objects/local/local-nodes/empty-hostgroup
drwxr-xr-x   root     root         /etc/nagios/objects/local/local-nodes/hostgroup1
drwxr-xr-x   root     root         /etc/nagios/objects/local/local-nodes/mgmt
drwxr-xr-x   apache   apache       /etc/nagios/objects/local/setup.known_good
drwxr-xr-x   apache   apache       /etc/nagios/objects/local/setup
</pre>
          <p>apache for rpm based systems, www-data for deb based systems</p>
          </li>
          <li><p>Check that apache (or www-data) is in the nagios group.</p></li>
        </ul>

        <hr />

        <h4 id="2">Problem - Non-existent folder</h4>
        <a href="#img3" onClick="$('#img3').css('display','block'); return false;"><img src="/images/trouble_wrong_folder.png" class="img-thumbnail"></a></p><a id="img3" class="a-imgshow" onClick="$('#img3').css('display','none'); return false;"><img src="/images/trouble_wrong_folder.png" class="imgshow"></a>
        <tt>
            Could not execute query using REST.<br />
            Please check system settings.<br />
            REST return code: 400<br />
            Error was:<br />
            NAGCTL ERROR: Non-existent folder (/etc/nagios/objects/locol).<br />
        </tt>

        <h4>Resolution</h4>

        <ul>
          <li><p>Check <tt>/etc/nagrestconf/nagrestconf.ini</tt>.<br />Check that
          <code>folder[]</code> has the correct folder name.</p></li>
        </ul>

        <hr />

        <h4 id="3">Problem - Nagios GUI is not updating</h4>

        <p>Configuration changes made in nagrestconf are not shown in the Nagios Web interface.</p>

        <h4>Resolution</h4>

        <ul>
          <li><p>Press 'Apply Changes' in the left pane on the main page then click 'Apply Configuration' and wait for a successful result.</p></li>
          <li><p>It can take up to one minute for changes to apply, or up to
          three minutes for distributed environments.</p></li>
          <li><p>Ensure the CRON job is active. See the CRON Jobs section in the <a href="/documentation/administration.php">Administration</a> page.</p></li>
        </ul>

        <hr />

        <h4 id="4">Problem - Sorry, giving up on ...</h4>
        <a href="#img4" onClick="$('#img4').css('display','block'); return false;"><img src="/images/trouble_giving_up.png" class="img-thumbnail"></a></p><a id="img4" class="a-imgshow" onClick="$('#img4').css('display','none'); return false;"><img src="/images/trouble_giving_up.png" class="imgshow"></a>
        <tt>
            Could not execute query using REST.<br />
            Please check system settings.<br />
            REST return code: 400<br />
            Error was:<br />
            lockfile: Sorry, giving up on "/etc/nagios/objects/local/setup/directory.lock"<br />
        </tt>

        <h4>Resolution</h4>

        <ul>
          <li><p>If the computer rebooted unexpectedly then simply delete the lock file.</p></li>
          <li><p>Ensure no one is applying a configuration then delete the lock file.</p></li>
        </ul>

        <!-- /Content -->
