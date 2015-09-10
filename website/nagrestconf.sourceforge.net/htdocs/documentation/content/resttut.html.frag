        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">

          <h1>REST Tutorial</h1>

          <p>The REST interface supports all Nagios options available for object files.</p>
          <p>This tutorial will guide the reader through setting up an additional host
          using the REST interface.</p>

          <h4>The Working Environment</h4>

          <p>Set up a Nagios and Nagrestconf server using one of the <a href="/installguide.php">Installation
            Guides</a> and be sure to <strong>import the backup configuration file</strong> as shown in the guide.</p>
          <p>Any platform can be used, as nagrestconf is the same for each, and using a virtual
          machine, container or chroot environment on a desktop computer would be good for testing purposes.</p>
          <p>This guide assumes that all commands will be run using the terminal console on the freshly
          installed environment so that 'localhost' can be used for the server name.</p>
          <p>Three tools are required:</p>
          <ul>
            <li>curl
            <p>A compiled program freely available for most operating systems.<br />
               Use the system's package manager to install curl.</p>
            </li>
            <li>JSON.sh
            <p>A shell script for 'pretty printing' <abbr title="JavaScript Object Notation">json</abbr> output.<br />
               Get it from <a href="https://github.com/dominictarr/JSON.sh">GitHub</a> or download directly in the test environment using,</p>
               <pre>curl -O https://raw.github.com/dominictarr/JSON.sh/master/JSON.sh</pre>
            </li>
            <li>Resty
            <p>A tiny script wrapper for curl<br />
               Get it from <a href="https://github.com/micha/resty">GitHub</a> or download directly in the test environment using,</p>
               <pre>curl -O https://raw.github.com/micha/resty/master/resty</pre>
            </li>
          </ul>
          <p>All work will be carried out from the home directory, so temporarily add the current directory to your PATH
          using <code>export PATH="$PATH:."</code>.</p>
          <p>It's not recommended to set your system up like this but it makes the tutorial easier read, and will only
          last as long as the login session.</p>
          <p>Install curl if it's not installed already, and get JSON.sh and resty.</p>
          <p>Ensure that JSON.sh and resty are executable with <code>chmod +x JSON.sh</code> and <code>chmod +x resty</code>.</p>
          <p>If the system was set up using the installation guide then no username or password is required to access
          the REST interface from localhost.</p>

          <h4>Test the Tools</h4>

          <p>Now that everything is set up it's time to test that the tools work, then the tutorial can begin.</p>
          <p><strong>Resty set up</strong></p>
          <p>Pull the resty functions into the current shell</p>
          <pre>source resty</pre>
          <p>Set the 'resty' url</p>
          <pre>resty 'http://127.0.0.1/rest'</pre>
          <p>Set four convenience shell variables, which are used for starting and ending json data that will be sent to nagrestconf.</p>
          <p>This is not necessary but saves some typing and will be used in the rest of the tutorial.</p>
          <pre>GS='json=\{"folder":"local"'
PS='json={"folder":"local"'
GE='\}'
PE='}'</pre>
          <p>GS - GET start, GE - GET end, PS - POST start, and PE - POST end.</p>
          <p>Then test that retrieving the list of hosts works.</p>
          <pre>GET /show/hosts -q $GS$GE</pre>
          <p>A lot of JSON data will be output. To tidy it up pipe it through JSON.sh (using the 'brief' option).</p>
          <pre>GET /show/hosts -q $GS$GE | JSON.sh -b</pre>
          <p>If everything is working then the output should be:</p>
          <pre>
[0,0,"name"]    "localhost"
[0,1,"alias"]   "localhost"
[0,2,"ipaddress"]       "127.0.0.1"
[0,3,"template"]        "hsttmpl-local"
[0,4,"hostgroup"]       "mgmt"
[0,7,"activechecks"]    "1"
[0,8,"servicesets"]     "local-checks"
</pre>
          <p>No more output will be shown for the rest of this tutorial.</p>
          <p>So, to recap, when logging into the terminal just type, or copy then paste, the following lines into the terminal
          first, then the GET/POST commands can be used for the rest of the session.</p>
          <pre>source resty
resty 'http://127.0.0.1/rest'
GS='json=\{"folder":"local"'
PS='json={"folder":"local"'
GE='\}'
PE='}'</pre>

          <h3>REST Commands</h3>

          <p>The REST interface is quite simple with only a few commands, but is split into two parts:
          GET requests and POST requests. GET requests are for commands that read the current state,
          and POST requests are for commands that can change the state.</p>
          <p>For GET requests use the general format, <code>GET /COMMAND/COMMANDARG -q $GS',JSONOPTIONS'$GE</code>,
          as used above, where COMMAND can be one of:</p>
          <ul>
            <li>check</li>
            <li>show</li>
          </ul>
          <p>For POST requests use the general format, <code>POST
            /COMMAND/COMMANDARG $PS',JSONOPTIONS'$PE</code>, as used in the rest
          of this tutorial, where COMMAND can be one of:</p>
          <ul>
            <li>add</li>
            <li>delete</li>
            <li>modify</li>
            <li>restart</li>
            <li>apply</li>
            <li>pipecmd</li>
          </ul>
          <p>What to use for COMMANDARG depends on the command but is quite intuitive. Refer to
          the <a href="/documentation/restreference.php#_rest_commands">REST Commands</a>
          section of the reference documentation to see all valid options.</p>

          <h3>Add a Host</h3>

          <p>To add a new host all required options must be supplied to the
          'add/hosts' command. The required options are marked with 'R' in the tables in
          the reference documentation. Refer to the <a href="/documentation/restreference.php#_hosts">
            hosts table</a> in the reference documentation to see which fields
          are required.</p>
          <p>Add a new host named 'newserver'.</p>
          <pre>POST /add/hosts $PS$PE</pre>
          <p>This will produce a descriptive error message since the required
          options weren't added. The correct command is:</p>
          <pre>
POST /add/hosts $PS',
     "name":"newserver",
     "alias":"newserver",
     "ipaddress":"1.2.3.4",
     "template":"hsttmpl-local"
'$PE</pre>
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
POST /delete/hosts $PS',"name":"newserver"'$PE
</pre>
          <p>Then add it again but use one of the existing service sets:</p>
          <pre>
POST /add/hosts $PS',
     "name":"newserver",
     "alias":"newserver",
     "ipaddress":"1.2.3.4",
     "template":"hsttmpl-local",
     "servicesets":"example-lin"
'$PE</pre>
          <p>Using the nagrestconf Web interface it can be seen that 'newserver' exists
          but this time with a bunch of services attached to it.</p>
          <p>The new host, 'newserver' won't appear in the Nagios Web interface until
          the changes are applied. Use the commands 'apply', 'check' then 'restart' to do this:</p>
          <pre>
POST /apply/nagiosconfig $PS$PE

GET /check/nagiosconfig -q $GS$GE

POST /restart/nagios $PS$PE</pre>
          <p>The previous commands produce minimal output. The 'apply' and 'check' commands can
          show more information, useful if there was an error, by using the 'verbose' option:</p>
          <pre>
POST /apply/nagiosconfig $PS',"verbose":"true"'$PE | JSON.sh -b

GET /check/nagiosconfig -q $GS',"verbose":"true"'$GE | JSON.sh -b

POST /restart/nagios $PS$PE</pre>
          <p>The output from the REST queries is also in JSON format, so in the previous example it is piped into JSON.sh.</p>

          <h3>Modify a Host</h3>

          <p>The host does not belong to a host group. The following command will add it to the
          'mgmt' host group:</p>
          <pre>
POST /modify/hosts $PS',
     "name":"newserver",
     "hostgroup":"mgmt"
'$PE
</pre>
          <p>This could have been added when the host was created by adding <code>"hostgroup":"mgmt"</code>
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
