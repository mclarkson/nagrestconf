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
          <p>All the recipes on this page have been created using Nrcq as shown in
          the <a href="/documentation/resttut.php">REST Tutorial</a>.</p>
          <p>Before trying a recipe install nrcq.</p>

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
URL="http://127.0.0.1/rest"
nrcq $URL show/services -f name:server1 \
    | grep svcdesc \
    | while IFS=":" read a b; do \
        nrcq $URL modify/services -d name:server1 -d "svcdesc:$b" -d disable:1
done

nrcq $URL modify/hosts -d name:server1 -d disable:1
</pre>
                  <p>Enabling the host and all of its services needs to be done the other way round and the disable
                  property should be changed from a '1' to a '0':</p>
                  <pre>
URL="http://127.0.0.1/rest"
nrcq $URL modify/hosts -d name:server1 -d disable:0

nrcq $URL show/services -f name:server1 \
    | grep svcdesc \
    | while IFS=":" read a b; do \
        nrcq $URL modify/services -d name:server1 -d "svcdesc:$b" -d disable:0
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
nrcq http://localhost/rest delete/services -d name:server1 -d "svcdesc:.*"
nrcq http://localhost/rest delete/hosts -d name:server1
</pre>
                  <p>That's all that is required to delete a host and its services.</p>
                  <!-- LIST CONTENT -->
                </div>
              </li>

              <li><a href="#_">Delete the entire configuration.</a>
                <div style="display: none">
                  <!-- LIST CONTENT -->
                  <p>There is no way to quickly delete the entire configuration using the Web interface. The following snippet will completely delete the nagrestconf configuration.</p>
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
nrcq http://localhost/rest delete/hosts -d "name:.*" -d "svcdesc:.*"
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
