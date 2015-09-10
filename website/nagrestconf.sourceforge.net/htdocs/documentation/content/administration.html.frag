        <!-- Content -->

        <h1>Administration</h1>

        <h3>Upload and Download Files</h3>

        <p>Upload and download directories store uploaded csv files, backups, and restore
        files. These files are not deleted after use and generally do not take up much space.</p>
        <p>These files can be safely deleted at any time or a cron job can be used to clear out
        these directories periodically.</p>
        <p><strong>NOTE:</strong> Do not delete the directories, only delete the contents.</p>
        <p>Using bash to delete the directory contents for all files over a month old:</p>
        <pre>
find /usr/share/nagrestconf/htdocs/nagrestconf/{upload,download} \
    -type f -mtime +30 -exec rm -f {} \;
</pre>

        <h3 id="cron">CRON Jobs</h3>

        <p>CRON, the periodic scheduler, is used to schedule nagios restarts
        and configuration imports from slave servers.</p>
        <p>Currently the cron jobs are added to root's crontab, but they
        will be moved to the system crontabs in the future.</p>
        <p>For standalone or slave servers ensure that the following crontab
        entry exists:</p>
        <pre>
* * * * * /usr/bin/test -e /tmp/nagios_restart_request &amp;&amp; ( /bin/rm /tmp/nagios_restart_request; /usr/bin/restart_nagios; )
</pre>
        <p>The frequency can be changed to reduce the number of restarts if the configuration changes often. This may be desirable for larger environments that make use of a lot of automation.</p>
        <p><strong>NOTE:</strong> the crontab is different for Fedora. Please see the
        <a href="/installguides/fedora.php">Fedora Installation Guide</a>.</p>
        <p>For collector nodes use:</p>
        <pre>
* * * * * /usr/bin/update_nagios
</pre>

        <p>For slave servers, in distributed environments, the following cron job
        may also be required:</p>

        <pre>*/10 * * * * /usr/bin/auto_reschedule_nagios_check</pre>

        <p>Refer to <a href="/documentation/toolscommands.php">Tools and Commands</a> for more information.</p>

        <h3>Plugins</h3>

        <p>Plugins can be enabled or disabled by installing or uninstalling the plugin
        package, or by adding/deleting symbolic links in:</p>
        
        <p><code>/usr/share/nagrestconf/htdocs/nagrestconf/plugins-enabled/</code></p>
        
        <p>The order that plugins are loaded does matter, for example, the Services Tab must be loaded
        before the Services Bulk Tool. Ordering is achieved by prefixing the plugin name with a
        number. The order is currently as follows:</p>
        
        <ul>
          <li>06_smorg_backup_btn.php</li>
          <li>10_smorg_services_tab.php</li>
          <li>50_smorg_hosts_bulktools_btn.php</li>
          <li>50_smorg_services_bulktools_btn.php</li>
        </ul>

        <p>No ongoing administration is required but if a link is removed from the plugins-enabled
        directory, be sure to recreate the link prefixed with the correct number.</p>

        <p>Example: Delete then recreate the Backup and Restore plugin link.</p>

        <p>Delete the plugin link:</p>
        <pre>
cd /usr/share/nagrestconf/htdocs/nagrestconf/plugins-enabled
rm 06_smorg_backup_btn.php
</pre>
        <p>Recreate the plugin link:</p>
        <pre>
cd /usr/share/nagrestconf/htdocs/nagrestconf/plugins-enabled
ln -s ../plugins/smorg_backup_btn.php 06_smorg_backup_btn.php
</pre>

        <!-- /Content -->
