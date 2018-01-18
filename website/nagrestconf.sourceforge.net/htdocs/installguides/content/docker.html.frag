        <div class="col-xs-12 col-sm-9">
          <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
          </p>

        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>Docker Installation Guide</h1>
          <p>This page details how to install nagrestonf using <a href="http://www.docker.com">Docker</a>, which is available for most operating systems.</p>
          <h3>Before installation</h3>
          <p>The instructions on this page are for installing Nagios and Nagrestconf on a computer that already has Docker installed. Please search the Internet for the best/easiest way to install Docker on your OS.</p>
          <h3>Overview</h3>
          <p>Installation consists of the following steps:</p>
          <ul>
            <li>Install a compatible Nagios container from
                <a href="https://hub.docker.com/">Docker Hub</a>.</li>
            <li>Install the Nagrestconf container from Docker Hub.</li>
            <li>Test Nagrestconf and Nagios.</li>
            <li>Create an initial configuration.</li>
          </ul>
          <p>SEE ALSO: <a href="https://github.com/mclarkson/nagrestconf-docker">
              mclarkson/nagrestconf-docker</a> for alternative installation options and to find out
              how to change the default passwords.</p>

          <h3>Install a compatible Nagios container from Docker Hub.</h3>
          <p>Open a terminal window or ssh session on the target server then install
          Nagios using the following commands:</p>
          <pre>docker run -d --name nagios4 -p 8080:80 -v /opt/nagios jasonrivers/nagios:latest</pre>

          <h3>Install the Nagrestconf container from Docker Hub.</h3>
          <p>The following commands will get the custom configuration and start the nagrestconf container:</p>
          <pre>wget https://raw.githubusercontent.com/mclarkson/nagrestconf-docker/master/jasonrivers_docker-nagios.env

docker run -d -p 8880:8080 --name nagrestconf -v /tmp \
  --volumes-from nagios4 --env-file jasonrivers_docker-nagios.env \
  mclarkson/nagrestconf</pre>
          <p>Then start the nagrestconf-restarter container:</p>
          <pre>docker run -d --name nagrestconf-restarter \
    -e NAGIOSCMD=/opt/nagios/var/rw/nagios.cmd \
    --volumes-from nagrestconf mclarkson/nagrestconf-restarter</pre>

          <h3>Test Nagrestconf and Nagios</h3>
          <p>The nagrestsconf and nagios web interfaces should be accessible now.<p>
          <p>Log into nagrestconf with user 'nagrestconfadmin', and the password 'admin'.</p>
          <p>The nagrestconf interface, at 'http://server:8880/nagrestconf', will look like the following screen shot.</p>
          <a href="#img1" onClick="$('#img1').css('display','block'); return false;"><img src="/images/redhat1.png" class="img-thumbnail"></a></p><a id="img1" class="a-imgshow" onClick="$('#img1').css('display','none'); return false;"><img src="/images/redhat1.png" class="imgshow"></a>
          <p>Log into nagios with user 'nagiosadmin', and the password 'nagios'.</p>
          <p>The nagios interface, at 'http://server/nagios3', will look like the following screen shot.</p>
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
