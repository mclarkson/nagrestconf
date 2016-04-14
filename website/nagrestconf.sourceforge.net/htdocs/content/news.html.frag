        <div class="col-xs-12 col-sm-9">
          <p class="pull-right visible-xs">
            <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
          </p>

        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>News</h1>
          <p><strong>14 April 2016 - Software updates.</strong></p>
            <blockquote>
              Synagios version 0.14.3 released. Notable changes:
              <ul>
                <li>More fixes for DSM 6</li>
                <ul>
                  <li>New mailsender binary<br />- Reported by Thomas Rosin</li>
                  <ul>
                    <li>Statically built binary that runs on DSM6 x86</li>
                    <li>Now accepts lowercase 'data' SMTP command</li>
                  </ul>
                  <li>Adds system host name to chroot's /etc/hosts file.<br />- Reported by Thomas Rosin</li>
                  <li>Log shows the processes running inside the chroot again.</li>
                  <li>Fix ownership details for /var/mail.<br />- Reported by Thomas Rosin</li>
                </ul>
              </ul>
            </blockquote>
          <p><strong>26 March 2016 - Software updates.</strong></p>
            <blockquote>
              Synagios version 0.14.2 released. Notable changes:
              <ul>
                <li>Fixes for DSM 6</li>
                <li>Updated to Nagrestconf 1.174.5.</li>
              </ul>
            </blockquote>
          <p><strong>8 February 2016 - Software updates.</strong></p>
            <blockquote>
              Nagrestconf version 1.174.5 released. Notable changes:
              <ul>
                <li>Allow special characters in Host Alias field. Closes #55.</li>
                <li>GUI usability enhancements.</li>
                <li>Multiple parents fix. Fixes issue #53.</li>
              </ul>
              Nrcq version 0.1.2 released:
              <ul>
                <li>Added extra encoding for Host's Alias field.</li>
              </ul>
              This version of nrcq should be used with nagrestconf 1.174.5.
            </blockquote>
          <p><strong>9 November 2015 - Software updates.</strong></p>
            <blockquote>
              Nagrestconf version 1.174.4 released with fix for Centos 6. Notable changes:
              <ul>
                <li>PHP 5.3.3 Centos 6 - GUI only shows error message #49.</li>
              </ul>
            </blockquote>
          <p><strong>30 October 2015 - Software updates.</strong></p>
            <blockquote>
              Nagrestconf version 1.174.3 released. Notable changes:
              <ul>
                <li>Bug Fixes.</li>
                <li>Allow service notication option 's'. Closes #46</li>
                <li>Fix host update via GUI. Closes #44</li>
                <li>Fix first_notification_delay. Closes #47</li>
              </ul>
            </blockquote>
          <p><strong>29 October 2015 - New Tool.</strong></p>
            <blockquote>
              New tool, nrcq, released:
                <blockquote>
                Command line tool for accessing the rest api either locally or remotely.
                </blockquote>
            </blockquote>
          <p><strong>13 September 2015 - Software updates.</strong></p>
            <blockquote>
              Nagrestconf version 1.174.1 released. Notable changes:
              <ul>
                <li>Make synology log output useful. Closes #2.</li>
                <li>Refresh hosts page after restore. Closes #20.</li>
                <li>Status Map Image fields added for templates. Closes #22.</li>
                <li>Added 'parents' field to hosts dialog. Closes #17.</li>
                <li>Allow hostnames, not just ip addresses. Closes #26.</li>
                <li>Alias field added to clone host dialog. Closes #29.</li>
                <li>Added Host Custom Variables and Notes fields to REST and UI. Closes #38.</li>
                <li>Added extra dependencies for really minimal systems.</li>
              </ul>
              Synagios version 0.14 released. Notable changes:
              <ul>
                <li>Includes Nagrestconf 1.174.1.</li>
                <li>Base Operating System updated from Debian Wheezy to Debian Jessie.</li>
                <li>Nagios updated to 3.5.1.</li>
                <li>Pnp4nagios updated to 0.6.24.</li>
                <li>Installed nagios_nrpe_plugin. Closes #21.</li>
              </ul>
            </blockquote>
          <p><strong>11 March 2014 - Synagios update.</strong></p>
            <blockquote>
              Synagios version 0.13 beta released with updates for the newly released DSM 5.0.
            </blockquote>
          <p><strong>09 March 2014 - Software updates.</strong></p>
            <blockquote>
              Nagrestconf version 1.173 beta released. Notable changes:
              <ul>
                <li>Visual changes to the sidebar.</li>
                <li>Bug fixes, see the <a href="https://github.com/mclarkson/nagrestconf/issues?milestone=1&page=1&state=closed" target="_blank">
                    Issues</a> page on GitHub for more information.</li>
              </ul>
              Synagios version 0.12 beta released. Notable changes:
              <ul>
                <li>Includes Nagrestconf 1.173.</li>
                <li>The port can now be selected when installing the package. Thanks to patmtp35 and InsomniacSoftware
                    for pointing this out.</li>
                <li>Bug fixes, see the <a href="https://github.com/mclarkson/nagrestconf/issues?milestone=1&page=1&state=closed" target="_blank">
                    Issues</a> page on GitHub for more information.</li>
              </ul>
            </blockquote>
          <p><strong>03 February 2014 - SyNagios on ARM working.</strong></p>
            <blockquote>
              SyNagios for ARM devices is now working. It has been tested on the smaller
              Synology DS112 NAS device with 256MB of RAM and means that SyNagios should
              work on a large range of Synology devices.
            </blockquote>
          <p><strong>24 January 2014 - Web site goes live.</strong></p>
            <blockquote>
            The Nagrestconf web site is now available. Content is being added and it is far
            from complete but it's a start!
            </blockquote>
          <p><strong>16 January 2014 - SyNagios package released.</strong></p>
            <blockquote>
            Nagrestconf has been packaged up with Nagios and PNP4Nagios for Synology Diskstation
            storage devices. Both x86 and arm packages have been released but only the x86 pacakge
            has been tested so far. Setting up Nagios, PNP4Nagios and Nagrestconf on Synology is
            really simple, taking just a few clicks, and provides monitoring, graphing, and
            configuration in a single downloadable package.
            </blockquote>
          <p><strong>13 January 2014 - Nagrestconf goes from Alpha to Beta.</strong></p>
            <blockquote>
            After many years in alpha, Nagrestconf has finally moved to Beta. Most bugs have been
            fixed and no more features will be added to this version, although bug fixes and
            plugins may still be added.
            </blockquote>
        </div>
        <!-- /Content -->

        </div><!--/span-->
