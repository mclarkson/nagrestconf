        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">

          <h1>REST Tutorial</h1>

          <p>The REST interface supports all Nagios options available for object files.</p>
          <p>This tutorial will guide the reader through setting up an additional host
          using the REST interface.</p>

          <h4>The Working Environment</h4>

          <p>Set up a Nagios and Nagrestconf server using one of the <a href="/installguide.php">Installation
            Guides</a> and be sure to <strong>import the backup configuration file</strong> as shown in the guide.</p>

          <p>Download the tool, nrcq, from https://github.com/mclarkson/nrcq.</p>

          <p>Either copy nrcq to the Nagios server and use the tool there, or
            use the tool from your workstation to access nagrestconf remotely.</p>

          <p>If using nrcq on the Nagios server, ensure it is executable with '<code>chmod +x nrcq</code>',
           and copy it to the system PATH, for example, '<code>sudo cp nrcq /usr/local/bin/</code>'.</p>

          <p>If using your workstation to connect remotely, use 'ssh' to forward local packets to the remote
            Nagios server. For example, on a Linux or Mac workstation you would type something similar to:</p>

          <pre>sudo ssh -L 80:127.0.0.1:80 user@nagios-server</pre>

          <p>Then, in either case, the URL to use in nrcq will be http://localhost/rest.</p>

          <p>There are a few different ways to set up remote api access but using one of the previous methods
          allows this tutorial to always use the same URL.</p>

          <h4>Test the API</h4>

          <p>Now that everything is set up it's time to test that nrcq works, then the tutorial can begin.</p>

          <p>First we'll try retrieving a list of hosts. Type:</p>
          <pre>nrcq http://localhost/rest show/hosts</pre>
          <p>If everything is working then the output should be:</p>
          <pre>
    name:localhost
    alias:localhost
    ipaddress:127.0.0.1
    template:hsttmpl-local
    hostgroup:mgmt
    activechecks:1
    servicesets:local-checks
    disable:0
</pre>
          <p>No more output will be shown for the rest of this tutorial.</p>

          <h3>REST Commands</h3>

          <p>The REST interface is quite simple with only a few commands (or endpoints).</p>
          <p>To see a list of all commands type, 'nrcq -L'.</p>
          <p>Some entries will be shown in a condensed form, such as:</p>

<pre>
show|add|modify|delete/hosts
</pre>

          <p>That line means that the endpoints shown next are all valid:</p>

<pre>
show/hosts
add/hosts
modify/hosts
delete/hosts
</pre>

          <p>The nrcq tool also shows all the options for each endpoint along with the
          required fields, prefixed with a star.</p>
          <p>For example, to view the valid options for the hosts table, type:</p>

<pre>
nrcq -l hosts
</pre>

          <h3>Add a Host</h3>

          <p>To add a new host all required options must be supplied to the
          'add/hosts' command. The required options are marked with 'R' in the tables in
          the reference documentation, and prefixed with a star, '*', by '<code>nrcq -l ENDPOINT</code>'.</p>
          <p>To add a new host named 'newserver'.</p>
          <pre>nrcq http://localhost/rest add/hosts -d 'name:newserver'</pre>
          <p>This will produce a descriptive error message since the required
          options weren't added. The correct command is:</p>
          <pre>
nrcq http://localhost/rest add/hosts \
    -d name:newserver \
    -d alias:newserver \
    -d ipaddress:1.2.3.4 \
    -d template:hsttmpl-local
</pre>
          <p>If the previous command is run again an error message will be
          ouput because the host already exists, and having two hosts with the same name is
          an error.</p>
          <p>Look at the new host in the nagrestconf Web interface and it will have
          no services attached to it. The easiest way to add services is to
          create a service set first, then name the service set when adding the
          host. This will be shown next.</p>

          <h3>Delete Host</h3>

          <p>The host will need to be deleted before it can be added with services from a service set, since only newly created hosts get service sets applied to them. To delete the host:</p>
          <pre>
nrcq http://localhost/rest delete/hosts -d name:newserver
</pre>
          <p>Then add it again but use one of the existing service sets:</p>
          <pre>
nrcq http://localhost/rest add/hosts \
    -d name:newserver \
    -d alias:newserver \
    -d ipaddress:1.2.3.4 \
    -d template:hsttmpl-local \
    -d servicesets:example-lin
</pre>
          <p>Using the nagrestconf Web interface it can be seen that 'newserver' exists
          but this time with a bunch of services attached to it.</p>
          <p>The new host, 'newserver' won't appear in the Nagios Web interface until
          the changes are applied. Use the commands 'apply', 'check' then 'restart' to do this:</p>
          <pre>
nrcq http://localhost/rest apply/nagiosconfig

nrcq http://localhost/rest check/nagiosconfig

nrcq http://localhost/rest restart/nagios</pre>
          <p>The previous commands produce minimal output. The 'apply' and 'check' commands can
          show more information, useful if there was an error, by using the 'verbose' option:</p>
          <pre>
nrcq http://localhost/rest apply/nagiosconfig -d verbose:true

nrcq http://localhost/rest check/nagiosconfig -d verbose:true

nrcq http://localhost/rest restart/nagios</pre>

          <h3>Modify a Host</h3>

          <p>The host does not belong to a host group. The following command will add it to the
          'mgmt' host group:</p>
          <pre>
nrcq http://localhost/rest modify/hosts -d name:newserver -d hostgroup:mgmt
</pre>
          <p>This could have been added when the host was created by adding '<code>hostgroup:mgmt</code>'
          to the list of options in the 'add/hosts' command.</p>
          <p>The configuration can be applied as before by using 'apply', 'check', then 'restart', or
          by using the nagrestconf Web interface.</p>
</pre>
          <h3>That's It!</h3>

          <p>This tutorial has demonstrated adding, modifying, and deleting a host. Every configuration
          change follows the same format so that's pretty much all there is to know about the REST interface.
          It's all more of the same steps for each table (services, hostgroups, commands, etc) and will
          involve a bit of <a href="http://en.wikipedia.org/wiki/Trial_and_error">trial and error</a>.</p>

            <!-- DISQUS -->

<div id="disqus_thread" style="padding-top: 50px;"></div>
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

            <!-- DISQUS -->

        </div>

        <!-- /Content -->
