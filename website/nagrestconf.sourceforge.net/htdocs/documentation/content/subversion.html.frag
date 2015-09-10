        <!-- Content -->

        <h1>Subversion</h1>

        <p>Configurations are saved to a <a href="http://subversion.apache.org">subversion</a>
        repository when a configuration is successfully applied, so the repository only
        contains valid configurations.</p>

        <p>Reverting to a previous configuration is not possible using the REST interface
        or the Web interface, although this feature is planned for inclusion, but
        it is possible using the command line.</p>
        
        <h3>Overview</h3>

        <p>The following are steps required to revert to a previous configuration:</p>

        <ol>
          <li>Check out the repository.</li>
          <li>Locate the revision to revert to.</li>
          <li>Check out that revision.</li>
          <li>Lock REST access.</li>
          <li>Copy the reverted configuration.</li>
          <li>Unlock REST access.</li>
          <li>Verify the new configuration in the Nagrestconf Web interface.</li>
          <li>Apply the configuration.</li>
        </ol>

        <h3>Example</h3>

        <p>This example assumes that the environment is named 'local' and the nagios
        configuration directory is in <code>/etc/nagios</code>.</p>
        <p><strong>NOTE</strong> that the repository should only be changed on the
        slave node for distributed environments.</p>

        <p>Log into the nagios server then,</p>

        <h4>Check out the repository.</h4>

        <pre>
cd
svn co file:///etc/nagios/repos/local
</pre>

        <h4>Locate the revision to revert to.</h4>

        <pre>
cd local/
svn log | less
</pre>
        <p>Only choose revisions with the comment, 'Changes saved'.</p>
        <p>View the differences with <code>svn diff -r REVISION</code>.</p>

        <h4>Check out that revision.</h4>

        <pre>
svn up -r REVISION
</pre>

        <h4>Lock REST access.</h4>

        <pre>
lockfile -! -1 -r 120 /etc/nagios/objects/local/setup/directory.lock
</pre>
        <p>The Web interface should now be completely unresposive when the page is
        refreshed. If the Web interface still works then check the path in the
        previous command.</p>

        <h4>Copy the reverted configuration.</h4>

        <p>List the directory contents before copying and ensure the permissions
        remain the same.</p>

        <pre>
ls -l /etc/nagios/objects/local/setup/
cp *.setup /etc/nagios/objects/local/setup/
ls -l /etc/nagios/objects/local/setup/
</pre>

        <h4>Unlock REST access.</h4>

        <pre>
rm /etc/nagios/objects/local/setup/directory.lock
</pre>

        <h4>Verify the new configuration in the Nagrestconf Web interface.</h4>

        <p>The Nagrestconf Web interface will be responsive again. Visually check the configuration.</p>

        <h4>Apply the configuration.</h4>

        <p>Either 'Revert Changes', to back out, or 'Apply Changes' to accept the new configuration</p>

        <!-- /Content -->
