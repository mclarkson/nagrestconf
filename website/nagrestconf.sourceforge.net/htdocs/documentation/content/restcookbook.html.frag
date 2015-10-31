        <!-- Content -->
        <style>
          ul#cookbook li > div > p:first-child {
            padding-top: 8px;
            padding-bottom: 2px;
          }
          ul#cookbook li:last-child {
            padding-bottom: 8px;
          }
        </style>
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>REST Cookbook</h1>
          <p>All the recipes on this page have been created using Resty as shown in
          the <a href="/documentation/resttut.php">REST Tutorial</a>.</p>
          <p>Before trying a recipe start a terminal session and initialise Resty using the following snippet as an example.</p>
          <pre>source resty
resty 'http://127.0.0.1/rest'
GS='json=\{"folder":"local"'
PS='json={"folder":"local"'
GE='\}'
PE='}'</pre>

          <h2>Recipes</h2>
          <ul id="cookbook">
            <li><p>How to remove things.</p></li>
            <ol>

              <li><a href="#_">Disable a host.</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>Often disabling a host is preferable to deleting it. For example, hosts might be brought up and taken down based on system load, in which case any custom changes, made directly to the host or services, will be lost if they are deleted then later added again.</p>
                  <p>A host and its services can be easily disabled in the nagrestconf web interface, but to disable a host using the REST interface the services must be disabled before the host can be disabled.</p>
                  <p>The 'disable' property accepts 0, 1 or 2, which means enabled, disabled, or testing mode, respectively. Wildcards can not be used for modifying properties so the 'filter' option is used to show all of the services for a host.</p>
                  <p>The following snippet shows how to disable a host, 'server1', and all of its services:</p>
                  <pre>
GET /show/services -q $GS',"filter":"server1"'$GE \
    | JSON.sh -b \
    | sed -n '/svcdesc/{ s/.*"\([^"]*\)"$/\1/p }' \
    | while read a; do
        POST /modify/services $PS',
            "name":"server1",
            "svcdesc":"'$a'",
            "disable":"1"
            '$PE;
    done

POST /modify/hosts $PS',"name":"server1","disable":"1"'$PE
</pre>
                  <p>So what do all of those lines do?</p>
                  <p><code>GET /show/services -q $GS',"filter":"server1"'$GE</code></p>
                  <p>This gets all of the services for host 'server1' then pipes the output to,</p>
                  <p><code>JSON.sh -b</code></p>
                  <p>Cleans up the output and removes empty properties, then pipes the output to,</p>
                  <p><code>sed -n '/svcdesc/{ s/.*"\([^"]*\)"$/\1/p }'</code></p>
                  <p>Sed is used here to find lines containing 'svcdesc' then to show only show the second column of ouput,
                  then this is output to the 'read' bash builtin function.</p>
                  <p><code> while read a; do</code></p>
                  <p>This reads a line of input into variable 'a' and puts 'a', the service description (svcdesc), inside the POST query.</p>
                  <p>The 'read' shell builtin keeps on reading lines until there are no lines left to read so all services are disabled.</p>
                  <p>The final POST command disables the host itself.</p>
                  <p>Enabling the host and all of its services needs to be done the other way round and the disable
                  property should be changed from a '1' to a '0':</p>
                  <pre>
POST /modify/hosts $PS',"name":"server1","disable":"0"'$PE

GET /show/services -q $GS',"filter":"server1"'$GE \
    | JSON.sh -b \
    | sed -n '/svcdesc/{ s/.*"\([^"]*\)"$/\1/p }' \
    | while read a; do
        POST /modify/services $PS',
            "name":"server1",
            "svcdesc":"'$a'",
            "disable":"0"
            '$PE;
    done
</pre>
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">Delete a host.</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>A host and its services can be easily deleted in the nagrestconf web interface, but to delete a host using the REST interface the services must be deleted before the host can be deleted.</p>
                  <p>The following snippet shows how to delete a host, 'server1', and all of its services:</p>
                  <pre>
POST /delete/services $PS',"name":"server1","svcdesc":".*"'$PE
POST /delete/hosts $PS',"name":"server1"'$PE
</pre>
                  <p>That's all that is required to delete a host and its services.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">Delete the entire configuration.</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>There is no way to quickly delete the entire configuration using the Web interface. The following snippet will completely delete the nagrestconf configuration.</p>
                  <pre>
for i in services hosts servicesets hosttemplates \
         servicetemplates contactgroups contacts  \
         hostgroups servicegroups timeperiods commands
do
   POST /delete/$i $PS',"name":".*","svcdesc":".*"'$PE
done
</pre>
Or with nrcq:
<pre>
for i in services hosts servicesets hosttemplates \
         servicetemplates contactgroups contacts  \
         hostgroups servicegroups timeperiods commands
do
   nrcq http://127.0.0.1/rest delete/$i -d "name:.*" -d "svcdesc:.*"
done
</pre>
                  <p>NOTE: This will probably not work if the '<a href="/documentation/restreference.php#lessused">
                  Less used and deprecated tables</a>' have been used as they will need to be deleted first.
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">Delete an entire table.</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>A bunch of hosts or services can be deleted from the hosts or services tables by using the Web interface, but bulk deletion is not possible on the other tables.</p>
                  <p>Use the following snippet to delete the hosts table.</p>
                  <pre>
POST /delete/hosts $PS',"name":".*","svcdesc":".*"'$PE
</pre>
                  <p>Change 'hosts' in 'delete/hosts' for another table name to delete that instead.</p>
                  <p>You may have noticed that the 'svcdesc' property is used for 'delete/hosts' when that property does not exist in the hosts table. When a property is used, but does not exist, then the REST interface silently ignores it, so the previous delete command will work for tables that requires the 'svcdesc' property and for those that don't.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>

            </ol>


          </ul>

        </div>
        <!-- /Content -->
