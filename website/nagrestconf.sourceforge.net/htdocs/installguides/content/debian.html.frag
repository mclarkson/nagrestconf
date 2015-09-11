        <div class="col-xs-12 col-sm-9">
          <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
          </p>

        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>Debian Installation Guide</h1>
          <p>This page details how to install nagrestonf on <a href="http://www.debian.org">Debian</a> Wheezy or Jessie.</p>
          <p>Packages that nagrestconf depends on, such as Nagios and Apache, will be installed
             automatically.</p>
          <h3>Before installation</h3>
          <p>The instructions on this page are for installing nagrestconf and its requirements on a new server,</p>
            <p class="text-danger">It is not recommended to follow these instructions on an existing server that is currently being used. It might break other applications on the server since packages might be upgraded.</p>

          <h3>Overview</h3>
          <p>Installation consists of the following steps:</p>
          <ul>
            <li>Install using the Deb packages.</li>
            <li>Configure the Operating System.</li>
            <li>Test nagrestconf and nagios.</li>
            <li>Create an initial configuration.</li>
          </ul>

          <h3>Install using the Deb packages.</h3>
          <p>Get the packages for Debian from the <a href="/downloads.php">download page</a> then copy them to the server.</p>
          <p>Open a terminal window or ssh session then install nagrestconf and all plugins:</p>
          <pre>sudo apt-get update
sudo apt-get install gdebi-core sudo tar cron
sudo gdebi nagrestconf_1.174.1_all.deb
sudo dpkg -i nagrestconf-services-plugin_1.174.1_all.deb \
      nagrestconf-services-bulktools-plugin_1.174.1_all.deb \
      nagrestconf-hosts-bulktools-plugin_1.174.1_all.deb \
      nagrestconf-backup-plugin_1.174.1_all.deb</pre>

          <h3>Configure the Operating System</h3>
          <p>Use the two helper scripts 'nagrestconf_install' and 'slc_configure'.</p>
          <pre>sudo nagrestconf_install -a
sudo slc_configure --folder=local</pre>
          <p>Change two variables in nagios.cfg</p>
          <pre>sudo sed -i 's/check_external_commands=0/check_external_commands=1/g' /etc/nagios3/nagios.cfg
sudo sed -i 's/enable_embedded_perl=1/enable_embedded_perl=0/g' /etc/nagios3/nagios.cfg</pre>
          <p>Relax permissions for the pipes</p>
          <pre>sudo chmod 770 /var/lib/nagios3/rw/</pre>
          <!--<p>Create a password for nagiosadmin - for GUI access to nagios.</p>
          <pre>htpasswd -bc /etc/nagios/htpasswd.users nagiosadmin a_password</pre>
          -->
          <p>Create a password for nagrestconfadmin - for GUI access to nagrestconf.</p>
          <pre>sudo htpasswd -bc /etc/nagios3/nagrestconf.users nagrestconfadmin a_password</pre>
          <p>Note that, by default, the nagrestconf GUI can only be reached from the host it was installed on, localhost. To enable connecting to nagrestconf from other hosts edit the apache configuration.</p>
          <p>For example, for Wheezy:</p>
          <!--<p>Edit /etc/apache2/conf.d/nagios.conf:</p>
          <pre>cp /etc/apache2/conf.d/nagios3.conf /tmp
sed -i 's#AuthUserFile .*#AuthUserFile /etc/nagios/htpasswd.users#i' \
    /etc/apache2/conf.d/nagios3.conf</pre>
          -->
          <p>Edit /etc/apache2/conf.d/nagrestconf.conf:</p>
          <pre>sudo cp /etc/apache2/conf.d/nagrestconf.conf /tmp
sudo sed -i 's#AuthUserFile .*#AuthUserFile /etc/nagios3/nagrestconf.users#i' \
    /etc/apache2/conf.d/nagrestconf.conf
sudo sed -i 's/allow from 127.0.0.1/allow from all/i' \
    /etc/apache2/conf.d/nagrestconf.conf
sudo sed -i 's/#Require/Require/i'     /etc/apache2/conf.d/nagrestconf.conf
sudo sed -i 's/#Auth/Auth/i'     /etc/apache2/conf.d/nagrestconf.conf</pre>
          <p>Or, for Jessie:</p>
          <!--<p>Edit /etc/apache2/conf-available/nagios.conf:</p>
          <pre>cp /etc/apache2/conf.d/nagios3.conf /tmp
sed -i 's#AuthUserFile .*#AuthUserFile /etc/nagios/htpasswd.users#i' \
    /etc/apache2/conf.d/nagios3.conf</pre>
          -->
          <p>Edit /etc/apache2/conf-available/nagrestconf.conf:</p>
          <pre>sudo cp /etc/apache2/conf-available/nagrestconf.conf /tmp
sudo sed -i 's#AuthUserFile .*#AuthUserFile /etc/nagios3/nagrestconf.users#i' \
    /etc/apache2/conf-available/nagrestconf.conf
sudo sed -i 's/allow from 127.0.0.1/allow from all/i' \
    /etc/apache2/conf-available/nagrestconf.conf
sudo sed -i 's/#Require/Require/i'     /etc/apache2/conf-available/nagrestconf.conf
sudo sed -i 's/#Auth/Auth/i'     /etc/apache2/conf-available/nagrestconf.conf</pre>
          <p>Restart apache and nagios</p>
          <pre>sudo service apache2 restart
sudo service nagios3 restart</pre>
          <p>The nagios restart will show errors since the configuration is empty.</p>

          <h3>Test nagrestconf and nagios</h3>
          <p>The nagrestsconf and nagios web interfaces should be accessible now.<p>
          <p>Log into nagrestconf with user 'nagrestconfadmin', and the password that was set above.</p>
          <p>The nagrestconf interface will look like the following screen shot.</p>
          <a href="#img1" onClick="$('#img1').css('display','block'); return false;"><img src="/images/redhat1.png" class="img-thumbnail"></a></p><a id="img1" class="a-imgshow" onClick="$('#img1').css('display','none'); return false;"><img src="/images/redhat1.png" class="imgshow"></a>
          <p>Log into nagios with user 'nagiosadmin', and the password that was set above.</p>
          <p>The nagios interface will look like the following screen shot.</p>
          <a href="#img2" onClick="$('#img2').css('display','block'); return false;"><img src="/images/redhat2.png" class="img-thumbnail"></a></p><a id="img2" class="a-imgshow" onClick="$('#img2').css('display','none'); return false;"><img src="/images/redhat2.png" class="imgshow"></a>

          <h3>Create an initial configuration</h3>
          <p>To create a simple test configuration use a script that makes REST calls, or use the 'Backup/Restore' button in the nagrestconf GUI. The latter method will be used in this guide.</p>

          <h4>Create an initial configuration using 'Backup/Restore'</h4>
          <p>An example configuration can be downloaded from <a href="http://sourceforge.net/projects/nagrestconf/files/example_configuration/nagcfgbak_example_v3.tgz/download">this link</a>, then log into nagrestconf and use the 'Backup/Restore' button.</p>
          <a href="#img3" onClick="$('#img3').css('display','block'); return false;"><img src="/images/redhat3.png" class="img-thumbnail"></a></p><a id="img3" class="a-imgshow" onClick="$('#img3').css('display','none'); return false;"><img src="/images/redhat3.png" class="imgshow"></a>
          <a href="#img12" onClick="$('#img12').css('display','block'); return false;"><img src="/images/initial-import.png" class="img-thumbnail"></a></p><a id="img12" class="a-imgshow" onClick="$('#img12').css('display','none'); return false;"><img src="/images/initial-import.gif" class="imgshow"></a>
          <p>Click 'Close' in the 'Backup/Restore' dialog then refresh the page.</p>
          <p>The new configuration will not appear in the Nagios Web interface until the 'Apply Changes' button is clicked, and then applied.</p>

          <p>That's it!</p>
          <p>&nbsp;</p>

            <!-- DISQUS -->

<div id="disqus_thread"></div>
<script type="text/javascript">
    var disqus_shortname = 'nagrestconf';
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
</div>
            <!-- DISQUS -->

        <!-- /Content -->

        <!-- /Content -->

        </div><!--/span-->
