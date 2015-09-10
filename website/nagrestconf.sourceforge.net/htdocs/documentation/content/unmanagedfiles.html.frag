        <!-- Content -->

        <h1>Unmanaged Files</h1>

        <p>All Nagios configuration files are stored in the nagios
        configuration directory located at <code>/etc/nagios</code> for rpm
        based systems, or <code>/etc/nagios3</code> for debian based
        systems.</p>

        <p>Nagrestconf requires exclusive control of the following two
        directories contained within the nagios configuration directory:</p>

        <ul>
          <li><code>repos</code></li>
          <li><code>objects</code></li>
        </ul>

        <p>Files in the <code>objects</code> directory will be overwritten each
        time the configuration is applied.</p>

        <p>The <code>repos</code> directory contains one or more subversion
        repositories.</p>

        <p>All other nagios configuration files are not managed.</p>

        <p>This means that the following files, under the nagios configuration
        directory, are managed by the Administrator, not nagrestconf:</p>

        <ul>
          <li><code>cgi.cfg</code></li>
          <li><code>htpasswd</code> files</li>
          <li><code>nagios.cfg</code></li>
          <li><code>private/resource.cfg</code></li>
        </ul>

        <p>The unmanaged files generally change infrequently and it is unlikely that they
        will be added to the REST interface, although a configuration plugin tool for these
        files is planned.</p>

        <!-- /Content -->
