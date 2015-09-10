        <!-- Content -->
        <style>
          ul#cookbook li > div > p:first-child {
            padding-top: 8px;
            padding-bottom: 0px;
          }
          ul#cookbook li:last-child {
            padding-bottom: 4px;
          }
          ul#cookbook li {
            padding-bottom: 6px;
          }
        </style>
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>File System Layout</h1>
          <ul id="cookbook">

              <li><a href="#_">/usr/bin</a>
                <div style="display: none">
                  <ul>
                    <li><a href="#_">auto_reschedule_nagios_check</a>
                      <div style="display: none">
                        <p>Checks for any service checks that have stopped running and
                        tries to restart them. Used for distributed setups.</p>
                      </div>
                    </li>
                    <li><a href="#_">dcc_configure</a>
                      <div style="display: none">
                        <p>System configurator for a collector node.</p>
                      </div>
                    </li>
                    <li><a href="#_">nagrestconf_install</a>
                      <div style="display: none">
                        <p>System configurator for a collector or slave node.</p>
                      </div>
                    </li>
                    <li><a href="#_">restart_nagios</a>
                      <div style="display: none">
                        <p>Nagios restarter. Cron will run this tool.</p>
                      </div>
                    </li>
                    <li><a href="#_">nagctl</a>
                      <div style="display: none">
                        <p>Writes csv files to be versioned by subversion and ensures that each REST command is valid.</p>
                      </div>
                    </li>
                    <li><a href="#_">upgrade_setup_files.sh</a>
                      <div style="display: none">
                        <p>Only used for upgrade. There has been no need to use this for a couple of years now.</p>
                      </div>
                    </li>
                    <li><a href="#_">update_nagios</a>
                      <div style="display: none">
                        <p>Applies configurations sent by slaves. Called by cron.</p>
                      </div>
                    </li>
                    <li><a href="#_">csv2nag</a>
                      <div style="display: none">
                        <p>Nagios configuration writer.</p>
                      </div>
                    </li>
                    <li><a href="#_">slc_configure</a>
                      <div style="display: none">
                        <p>System configurator for a standalone or slave node. Initialises the subversion repository.</p>
                      </div>
                    </li>
                  </ul>
                </div>
              </li>

              <li><a href="#_">/usr/share/doc/nagrestconf/</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>Documentation and samples.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">/usr/share/man/man8/</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>Manual pages for the tools.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">/usr/share/nagrestconf/htdocs/</a>

                <div style="display: none">
                  <p>Files served by the Web server are stored in this directory.</p>
                  <ul>
                    <li><a href="#_">rest/</a>
                      <div style="display: none">
                        <!-- LIST CONTENT -->
                        <p>PHP files for the REST interface are stored here.</p>
                        <!-- LIST CONTENT -->
                      </div>
                    </li>

                    <li><a href="#_">nagrestconf/</a>
                      <div style="display: none">
                        <!-- LIST CONTENT -->
                        <p>PHP files for the Web interface are stored here.</p>
                        <ul>
                          <li><a href="#_">plugins/</a>
                            <div style="display: none">
                              <!-- LIST CONTENT -->
                              <p>Plugin PHP files are stored here.</p>
                              <!-- LIST CONTENT -->
                            </div>
                          </li>

                          <li><a href="#_">plugins-enabled/</a>
                            <div style="display: none">
                              <!-- LIST CONTENT -->
                              <p>Plugins will be shown if links to files in the plugins directory are placed in this directory.</p>
                              <!-- LIST CONTENT -->
                            </div>
                          </li>
                        </ul>
                        <!-- LIST CONTENT -->
                      </div>
                    </li>
                  </ul>
                </div>

              <li><a href="#_">/etc/apache2/conf.d/</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>The installer puts Apache Web configuration files in this directory.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">/etc/nagrestconf/</a>
                <div style="display: none">
                  <p>Configuration files for Nagrestconf tools are stored here.</p>
                  <ul>
                    <li><a href="#_">nagrestconf.ini</a>
                      <div style="display: none">
                        <p>Web interface configuration file.</p>
                      </div>
                    </li>
                    <li><a href="#_">csv2nag.conf</a>
                      <div style="display: none">
                        <p>Configuration file for the nagios configuration writer.</p>
                      </div>
                    </li>
                    <li><a href="#_">nagctl.conf</a>
                      <div style="display: none">
                        <p>Configuration file for the csv file writer.</p>
                      </div>
                    </li>
                    <li><a href="#_">restart_nagios.conf</a>
                      <div style="display: none">
                        <p>Configuration file for the restart_nagios script.</p>
                      </div>
                    </li>
                  </ul>
                </div>
              </li>

              <li><a href="#_">/etc/nagios/objects/</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>Contains directories for each environment, named 'folders' in the REST API.</p>
                  <p>For a standalone server this will often contain a directory named 'local'.</p>
                  <p>For a collector node this will contain many directories, one for each slave node.</p>
                  <!-- LIST CONTENT -->
                  <ul>
                    <li><a href="#_">setup/</a>
                      <div style="display: none">
                        <p>Current in-progress configuration before being applied.</p>
                      </div>
                    </li>
                    <li><a href="#_">versions/</a>
                      <div style="display: none">
                        <p>Checked out subversion repository containing the configuration.</p>
                      </div>
                    </li>
                    <li><a href="#_">setup.known_good/</a>
                      <div style="display: none">
                        <p>The last configuration that worked in nagios is stored in this directory.</p>
                      </div>
                    </li>
                  </ul>
                </div>
              </li>

              <li><a href="#_">/etc/nagios/repos/</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>Subversion repository containing the configuration.</p>
                  <p>If this is a slave node then this will be subversion synced to the collector node
                  when a configuration is applied.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>
              </li>

          </ul>

        </div>
        <!-- /Content -->
