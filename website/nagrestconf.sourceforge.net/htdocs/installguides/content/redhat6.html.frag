        <div class="col-xs-12 col-sm-9">
          <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
          </p>

        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>Redhat 6 and Centos 6 Installation Guide</h1>
          <p>This page details how to install nagrestonf on <a href="http://www.redhat.com/products/enterprise-linux/">Redhat 6</a> or <a href="http://www.centos.org/">Centos 6</a>.</p>
          <p>Packages that nagrestconf depends on, such as Nagios and Apache, will be installed
             automatically.</p>
          <h3>Before installation</h3>
          <p>The instructions on this page are for installing nagrestconf and its requirements on a new server,</p>
            <p class="text-danger">It is not recommended to follow these instructions on an existing server that is currently being used. It might break other applications on the server since packages might be upgraded.</p>

          <h3>Overview</h3>
          <p>Installation consists of the following steps:</p>
          <ul>
            <li>Install using the RPM packages.</li>
            <li>Configure the Operating System.</li>
            <li>Test nagrestconf and nagios.</li>
            <li>Create an initial configuration.</li>
            <li>Add Plugins.</li>
          </ul>

          <h3>Install using the RPM packages.</h3>
          <p>Get the RPM packages for Centos/Redhat 6 from the <a href="/downloads.php">download page</a> then copy them to the server.</p>
          <p>Open a terminal window or ssh session then add the  <a href="http://fedoraproject.org/wiki/EPEL">EPEL</a> repository to satisfy dependencies later on.</p>
          <pre>sudo rpm -ivh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm</pre>
          <p>Open a terminal window or ssh session then install nagrestconf and all plugins:</p>
          <pre>sudo yum --nogpg install nagrestconf-1.174.4-1.noarch.rpm \
    nagrestconf-services-tab-plugin-1.174.4-1.noarch.rpm \
    nagrestconf-services-bulktools-plugin-1.174.4-1.noarch.rpm \
    nagrestconf-hosts-bulktools-plugin-1.174.4-1.noarch.rpm \
    nagrestconf-backup-plugin-1.174.4-1.noarch.rpm</pre>

          <h3>Configure the Operating System</h3>
          <p> <span class="text-danger">Ensure selinux is disabled</span>, instructions <a href="https://www.centos.org/docs/5/html/5.1/Deployment_Guide/sec-sel-enable-disable.html">here</a>.</p>
          <p>Use the two helper scripts 'nagrestconf_install' and 'slc_configure'.</p>
          <pre>sudo nagrestconf_install -a
sudo slc_configure --folder=local</pre>
          <p>Create a password for nagiosadmin - for GUI access to nagios.</p>
          <pre>sudo htpasswd -bc /etc/nagios/htpasswd.users nagiosadmin a_password</pre>
          <p>Create a password for nagrestconfadmin - for GUI access to nagrestconf.</p>
          <pre>sudo htpasswd -bc /etc/nagios/nagrestconf.users nagrestconfadmin a_password</pre>
          <p>Note that, by default, the nagrestconf GUI can only be reached from the host it was installed on, localhost. To enable connecting to nagrestconf from other hosts edit the apache configuration.</p>
          <p>For example,</p>
          <p>Edit /etc/httpd/conf.d/nagios.conf:</p>
          <pre>sudo cp /etc/httpd/conf.d/nagios.conf /tmp
sudo sed -i 's#AuthUserFile .*#AuthUserFile /etc/nagios/htpasswd.users#i' \
    /etc/httpd/conf.d/nagios.conf</pre>
          <p>Edit /etc/httpd/conf.d/nagrestconf.conf:</p>
          <pre>sudo cp /etc/httpd/conf.d/nagrestconf.conf /tmp
sudo sed -i 's#AuthUserFile .*#AuthUserFile /etc/nagios/nagrestconf.users#i' \
    /etc/httpd/conf.d/nagrestconf.conf
sudo sed -i 's/allow from 127.0.0.1/allow from all/i' \
    /etc/httpd/conf.d/nagrestconf.conf
sudo sed -i 's/#Require/Require/i'     /etc/httpd/conf.d/nagrestconf.conf
sudo sed -i 's/#Auth/Auth/i'     /etc/httpd/conf.d/nagrestconf.conf</pre>
          <p>Restart apache</p>
          <pre>sudo service httpd restart</pre>

          <h3>Test nagrestconf and nagios</h3>
          <p>The nagrestsconf and nagios web interfaces should be accessible now.<p>
          <p>Log into nagrestconf with user 'nagrestconfadmin', and the password that was set above.</p>
          <p>The nagrestconf interface, at 'http://server/nagrestconf', will look like the following screen shot.</p>
          <a href="#img1" onClick="$('#img1').css('display','block'); return false;"><img src="/images/redhat1.png" class="img-thumbnail"></a></p><a id="img1" class="a-imgshow" onClick="$('#img1').css('display','none'); return false;"><img src="/images/redhat1.png" class="imgshow"></a>
          <p>Log into nagios with user 'nagiosadmin', and the password that was set above.</p>
          <p>The nagios interface, at 'http://server/nagios', will look like the following screen shot.</p>
          <a href="#img2" onClick="$('#img2').css('display','block'); return false;"><img src="/images/redhat2.png" class="img-thumbnail"></a></p><a id="img2" class="a-imgshow" onClick="$('#img2').css('display','none'); return false;"><img src="/images/redhat2.png" class="imgshow"></a>

          <h3>Create an initial configuration</h3>
          <p>To create a simple test configuration use a script that makes REST calls, or use the 'Backup/Restore' button in the nagrestconf GUI. The latter method will be used in this guide.</p>

          <h4>Create an initial configuration using 'Backup/Restore'</h4>
          <p>An example configuration can be downloaded from <a href="http://sourceforge.net/projects/nagrestconf/files/example_configuration/nagcfgbak_example_v3.tgz/download">this link</a>, then log into nagrestconf and use the 'Backup/Restore' button.</p>
          <a href="#img3" onClick="$('#img3').css('display','block'); return false;"><img src="/images/redhat3.png" class="img-thumbnail"></a></p><a id="img3" class="a-imgshow" onClick="$('#img3').css('display','none'); return false;"><img src="/images/redhat3.png" class="imgshow"></a>
          <a href="#img12" onClick="$('#img12').css('display','block'); return false;"><img src="/images/initial-import.png" class="img-thumbnail"></a></p><a id="img12" class="a-imgshow" onClick="$('#img12').css('display','none'); return false;"><img src="/images/initial-import.gif" class="imgshow"></a>
          <p>Click 'Close' in the 'Backup/Restore' dialog then refresh the page.</p>
          <p>The new configuration will not appear in the Nagios Web interface until the 'Apply Changes' button is clicked, and then applied.</p>

          <h4>Add Plugins</h4>
          <p>Nagios and Nagrestconf should be installed and working, however,
          the plugins are probably not installed so Nagios will show errors
          trying to run the host and service checks.</p>
          <p>Install the plugins your distribution provides.</p>
          <p>Choose the required plugins or install them all as below.</p>
          <pre>sudo yum install nagios-plugins-all nagios-plugins-nrpe</pre>

          <p>That's it!</p>

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


        </div><!--/span-->
