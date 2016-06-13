        <div class="col-xs-12 col-sm-9">
          <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
          </p>

        <!-- Content -->

        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>Synology Diskstation Installation Guide</h1>
          <h3>Overview</h3>
          <p>Installation consists of the following steps:</p>
          <ul>
            <li>Identify your Diskstation architecture.</li>
            <li>Download the '.spk' file from sourceforge.</li>
            <li>Log into the Diskstation GUI.</li>
            <li>Open the Package Manager.</li>
            <li>Choose manual install.</li>
            <li>'Browse' to the downloaded '.spk' file.</li>
            <li>Set the port.</li>
            <li>Set passwords.</li>
            <li>Wait for a 'Success' message</li>
            <li>[optional] Restore the sample backup configuration.</li>
          </ul>
          <h3>Identify your Diskstation architecture.</h3>
          <p>Find your Diskstation in <a href="https://web.archive.org/web/20160323065142/http://forum.synology.com/wiki/index.php/What_kind_of_CPU_does_my_NAS_have">this archived list</a>, and look for the words, 'x86' or 'arm', next to the model of your diskstation to identify the CPU architecture. If you can't find your Synology device in the list then try both packages - the one that doesn't work will refuse to install.</p>
          <h3>Download the '.spk' file from sourceforge.</h3>
          <p>Get the correct '.spk' file, according to your Diskstation CPU architecture, from the <a href="/downloads.php">download page</a>.
          <h3>Log into the Diskstation GUI.</h3>
          <a href="#img1" onClick="$('#img1').css('display','block'); return false;"><img src="/images/synstep1.png" class="img-thumbnail"></a></p><a id="img1" class="a-imgshow" onClick="$('#img1').css('display','none'); return false;"><img src="/images/synstep1.png" class="imgshow"></a>
          <h3>Open the Package Manager.</h3>
          <a href="#img2" onClick="$('#img2').css('display','block'); return false;"><img src="/images/synstep2.png" class="img-thumbnail"></a></p><a id="img2" class="a-imgshow" onClick="$('#img2').css('display','none'); return false;"><img src="/images/synstep2.png" class="imgshow"></a>
          <h3>Choose manual install.</h3>
          <a href="#img3" onClick="$('#img3').css('display','block'); return false;"><img src="/images/synstep3.png" class="img-thumbnail"></a></p><a id="img3" class="a-imgshow" onClick="$('#img3').css('display','none'); return false;"><img src="/images/synstep3.png" class="imgshow"></a>
          <h3>'Browse' to the downloaded '.spk' file.</h3>
          <p>Click 'Browse' and choose the '.spk' file downloaded earlier, then click 'Next'.</p>
          <p>The '.spk' package will be checked which can take a while.</p>
          <a href="#img4" onClick="$('#img4').css('display','block'); return false;"><img src="/images/synstep4.png" class="img-thumbnail"></a></p><a id="img4" class="a-imgshow" onClick="$('#img4').css('display','none'); return false;"><img src="/images/synstep4.png" class="imgshow"></a>
          <h3>Set the port.</h3>
          <p>The installer will check that the port is available for synagios to use just before copying files.</p>
          <a href="#img13" onClick="$('#img13').css('display','block'); return false;"><img src="/images/synstep4-2.png" class="img-thumbnail"></a></p><a id="img13" class="a-imgshow" onClick="$('#img13').css('display','none'); return false;"><img src="/images/synstep4-2.png" class="imgshow"></a>
          <h3>Set passwords.</h3>
          <a href="#img5" onClick="$('#img5').css('display','block'); return false;"><img src="/images/synstep5.png" class="img-thumbnail"></a></p><a id="img5" class="a-imgshow" onClick="$('#img5').css('display','none'); return false;"><img src="/images/synstep5.png" class="imgshow"></a>
          <h3>Wait for a 'Success' message</h3>
          <p>Ensure 'Run after installation' is checked then click 'Apply'.</p>
          <p>Installation will take a while then a success message, 'All services started successfully', should be displayed.</p>
          <a href="#img6" onClick="$('#img6').css('display','block'); return false;"><img src="/images/synstep6.png" class="img-thumbnail"></a></p><a id="img6" class="a-imgshow" onClick="$('#img6').css('display','none'); return false;"><img src="/images/synstep6.png" class="imgshow"></a>
          <p>At this point Nagios and Nagrestconf are fully functional but contain an empty configuration. Check that Nagrestconf is available by clicking the 'Synagios' Meerkat icon and log in with user, 'nagrestconfadmin', and the password that was set when the package was installed.</p>
          <a href="#img7" onClick="$('#img7').css('display','block'); return false;"><img src="/images/synstep7.png" class="img-thumbnail"></a></p><a id="img7" class="a-imgshow" onClick="$('#img7').css('display','none'); return false;"><img src="/images/synstep7.png" class="imgshow"></a>
          <h3>[optional] Restore the sample backup configuration.</h3>
          <p>A fully working example configuration can be downloaded from the same place that the '.spk' package was downloaded from, or follow <a href="http://sourceforge.net/projects/nagrestconf/files/example_configuration/nagcfgbak_synology_v2.tgz/download">this link</a>.</p>
          <p>Download the 'nagcfgbak_synology_v2.tgz' file and restore it using the nagrestconf GUI.</p>
          <p>If you receive a 'Fail: undefined' message in Firefox, then stop Firefox blocking the unsecured content:</p>
          <a href="#img8" onClick="$('#img8').css('display','block'); return false;"><img src="/images/synstep8.png" class="img-thumbnail"></a></p><a id="img8" class="a-imgshow" onClick="$('#img8').css('display','none'); return false;"><img src="/images/synstep8.png" class="imgshow"></a>
          <p>Then restore:</p>
          <a href="#img9" onClick="$('#img9').css('display','block'); return false;"><img src="/images/synstep9.png" class="img-thumbnail"></a></p><a id="img9" class="a-imgshow" onClick="$('#img9').css('display','none'); return false;"><img src="/images/synstep9.png" class="imgshow"></a>
          <a href="#img12" onClick="$('#img12').css('display','block'); return false;"><img src="/images/initial-import.png" class="img-thumbnail"></a></p><a id="img12" class="a-imgshow" onClick="$('#img12').css('display','none'); return false;"><img src="/images/initial-import.gif" class="imgshow"></a>
          <p>Click on the page tabs to view the configuration.</p>
          <p>Optionally set an email address for the 'ds-admin' user on the 'Contacts' page.</p>
          <p>Click 'Apply Changes' in the left side bar then view the Nagios GUI using the user name 'nagiosadmin' and the password that was set earlier.</p>
          <a href="#img10" onClick="$('#img10').css('display','block'); return false;"><img src="/images/synstep10.png" class="img-thumbnail"></a></p><a id="img10" class="a-imgshow" onClick="$('#img10').css('display','none'); return false;"><img src="/images/synstep10.png" class="imgshow"></a>
          <a href="#img11" onClick="$('#img11').css('display','block'); return false;"><img src="/images/synstep11.png" class="img-thumbnail"></a></p><a id="img11" class="a-imgshow" onClick="$('#img11').css('display','none'); return false;"><img src="/images/synstep11.png" class="imgshow"></a>
          <p>The nagios GUI runs on port 8888 at, http://diskstation:8888/nagios3.</p>
          <p>Outside of the Diskstation Desktop, the nagrestconf GUI runs on port 8888 at, http://diskstation:8888/nagrestconf</p>
          <p>Thats it!</p>

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
