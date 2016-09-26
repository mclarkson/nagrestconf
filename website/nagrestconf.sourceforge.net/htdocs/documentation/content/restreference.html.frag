        <!-- Content -->
        <div class="row" style="padding-left:10px;padding-right:20px;">
          <h1>REST Reference Documentation</h1>
<p>Common tables.</p>
<ul>
<li><a href="#_contacts">contacts</a></li>
<li><a href="#_contactgroups">contactgroups</a></li>
<li><a href="#_hosts">hosts</a></li>
<li><a href="#_hosttemplates">hosttemplates</a></li>
<li><a href="#_services">services</a></li>
<li><a href="#_servicesets">servicesets</a></li>
<li><a href="#_servicetemplates">servicetemplates</a></li>
<li><a href="#_hostgroups">hostgroups</a></li>
<li><a href="#_servicegroups">servicegroups</a></li>
<li><a href="#_commands">commands</a></li>
<li><a href="#_timeperiods">timeperiods</a></li>
</ul>
<p id="#lessused">Less used and deprecated tables.</p>
<ul>
<li><a href="#_hostdeps">hostdeps</a></li>
<li><a href="#_hostextinfo">hostextinfo</a></li>
<li><a href="#_hostescalation">hostescalation</a></li>
<li><a href="#_servicedeps">servicedeps</a></li>
<li><a href="#_serviceextinfo">serviceextinfo</a></li>
<li><a href="#_serviceescalation">serviceescalation</a></li>
</ul>

          <!-- INSERT REFERENCE MATERIAL -->
<div id="content">
<div class="sect1">
<h2 id="_reference">1. REFERENCE</h2>
<div class="sectionbody">
<div class="sect2">
<h3 id="_rest_commands">1.1. REST Commands</h3>
<div class="paragraph"><p>The URL is in the general form <em>https://&lt;HOST&gt;/rest/&lt;COMMAND&gt;/&lt;COMMANDARG&gt;</em>.</p></div>
<div class="paragraph"><p>Valid COMMANDS are check, show, add, delete, modify, restart, apply and
pipecmd.</p></div>
<div class="paragraph"><p>COMMAND options are added to the HTTP GET or POST query string in the form
<em>json={"option":"value"[,"option":"value"]&#8230;}</em>.</p></div>
<div class="paragraph"><p>GET requests are for operations that don&#8217;t modify data.</p></div>
<div class="ulist"><ul>
<li>
<p>
<code>https://&lt;HOST&gt;/rest/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
<code>check/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
nagiosconfig json={"folder":"&lt;name&gt;"[,"verbose":"true"]}
</p>
</li>
</ul></div>
</li>
<li>
<p>
<code>show/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
hosttemplates json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicetemplates json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hosts json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
services json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicesets json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicegroups json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostgroups json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contacts json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contactgroups json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
timeperiods json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
commands json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicedeps json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostdeps json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceesc json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostesc json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceextinfo json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostextinfo json={"folder":"&lt;name&gt;"[,"filter":"&lt;regex&gt;"][,"column":"&lt;integer&gt;"][,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
</ul></div>
</li>
</ul></div>
</li>
</ul></div>
<div class="paragraph"><p>POST requests are for operations that might modify data or state.</p></div>
<div class="ulist"><ul>
<li>
<p>
<code>https://&lt;HOST&gt;/rest/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
<code>add/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
hosttemplates json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicetemplates json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hosts json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
services json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicesets json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicegroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostgroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contacts json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contactgroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
timeperiods json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
commands json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicedeps json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostdeps json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceesc json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostesc json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceextinfo json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostextinfo json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
</ul></div>
</li>
<li>
<p>
<code>delete/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
hosttemplates json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicetemplates json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hosts json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
services json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicesets json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicegroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostgroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contacts json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contactgroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
timeperiods json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
commands json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicedeps json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostdeps json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceesc json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostesc json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceextinfo json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostextinfo json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
</ul></div>
</li>
<li>
<p>
<code>modify/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
hosttemplates json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicetemplates json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hosts json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
services json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicesets json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicegroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostgroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contacts json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
contactgroups json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
timeperiods json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
commands json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
servicedeps json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostdeps json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceesc json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostesc json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
serviceextinfo json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
<li>
<p>
hostextinfo json={"folder":"&lt;name&gt;"[,"&lt;option&gt;":"&lt;value&gt;"]*}
</p>
</li>
</ul></div>
</li>
<li>
<p>
<code>restart/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
nagios json={"folder":"&lt;name&gt;"}
</p>
</li>
</ul></div>
</li>
<li>
<p>
<code>apply/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
nagiosconfig json={"folder":"&lt;name&gt;"[,"verbose":"true"]}
</p>
</li>
<li>
<p>
nagioslastgoodconfig json={"folder":"&lt;name&gt;"}
</p>
</li>
</ul></div>
</li>
<li>
<p>
<code>pipecmd/</code>
</p>
<div class="ulist"><ul>
<li>
<p>
enablehostsvcchecks json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;}
</p>
</li>
<li>
<p>
disablehostsvcchecks json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
enablesvccheck json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;, "svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
disablesvccheck json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;" "svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
schedhstdowntime json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;,"starttime":"&lt;unixtime&gt;","endtime":"unixtime" [,"flexible":"&lt;0|1&gt;","duration":"&lt;minutes&gt;","author":"&lt;name&gt;","comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
delhstdowntime json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;,"svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
schedhstsvcdowntime json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;","svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
delhstsvcdowntime json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;","svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
schedsvcdowntime json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;","svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
<li>
<p>
delsvcdowntime json={"folder":"&lt;name&gt;","name":"&lt;hostname&gt;","svcdesc":"&lt;Service Description&gt;" [,"comment","&lt;comment&gt;"]}
</p>
</li>
</ul></div>
</li>
</ul></div>
</li>
</ul></div>
</div>
<div class="sect2">
<h3 id="_object_definitions_and_options">1.2. Object Definitions and Options</h3>
<div class="paragraph"><p>Refer to the Nagios object definitions documentation for more information about
individual options in the following tables. It can be found at the following
URL:</p></div>
<div class="paragraph"><p><a href="http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html">http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html</a></p></div>
<div class="sect3">
<h4 id="_listings_of_all_valid_rest_options">1.2.1. Listings of all Valid REST Options</h4>
<div class="paragraph"><p>The &#8216;Column&#8217; number in the following tables relate to the column number in the
database files on the nagios server. These are comma delimited files used by
<em>csv2nag</em> to create the nagios configuration files.</p></div>
<div class="paragraph"><p>Key for the &#8216;Flags&#8217; column:</p></div>
<div class="ulist"><ul>
<li>
<p>
'U' - The option is Unimplemented.
</p>
</li>
<li>
<p>
'E' - The field should be URL encoded.
</p>
</li>
<li>
<p>
'R' - A required field.
</p>
</li>
<li>
<p>
'K' - A key field. Required to uniquely identify an entry.
</p>
</li>
<li>
<p>
'L' - A list field. Lists consist of zero or more items separated by spaces.
</p>
</li>
<li>
<p>
'C' - A compound field: &lt;name&gt;|&lt;value&gt;[,&lt;name&gt;|&lt;value&gt;]&#8230;
</p>
</li>
<li>
<p>
'X' - Not available in the Web front-end.
</p>
</li>
<li>
<p>
'M' - Name mangling is applied to a passive-only nagios server. (Where the
          DCC variable is set to &#8216;1&#8217; in /etc/nagrestconf/csv2nag.conf.)
</p>
</li>
</ul></div>
<div class="paragraph"><p>The &#8216;REST variable name&#8217; column lists the option names that can be used in the
&#8216;json=&#8217; part of the query. These names are used in place of &#8216;&lt;option&gt;&#8217; shown
in the &#8216;Rest Commands&#8217; section above.</p></div>
<div class="admonitionblock">
<table><tr>
</tr></table>
</div>
</div>
<div class="sect3">
<h4 id="_contacts">1.2.2. contacts</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Contact name</p></td>
<td align="center" valign="top"><p class="table">RKM</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">contact_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Use</p></td>
<td align="center" valign="top"><p class="table">M</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Alias pretty name</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Email address</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">emailaddr</p></td>
<td align="left" valign="top"><p class="table">email</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Service notification period</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">svcnotifperiod</p></td>
<td align="left" valign="top"><p class="table">service_notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Service notification options</p></td>
<td align="center" valign="top"><p class="table">LR</p></td>
<td align="left" valign="top"><p class="table">svcnotifopts</p></td>
<td align="left" valign="top"><p class="table">service_notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Service notification commands</p></td>
<td align="center" valign="top"><p class="table">LRM</p></td>
<td align="left" valign="top"><p class="table">svcnotifcmds</p></td>
<td align="left" valign="top"><p class="table">service_notification_commands</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Host notification period</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">hstnotifperiod</p></td>
<td align="left" valign="top"><p class="table">host_notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Host notification options</p></td>
<td align="center" valign="top"><p class="table">LR</p></td>
<td align="left" valign="top"><p class="table">hstnotifopts</p></td>
<td align="left" valign="top"><p class="table">host_notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Host notification commands</p></td>
<td align="center" valign="top"><p class="table">LRM</p></td>
<td align="left" valign="top"><p class="table">hstnotifcmds</p></td>
<td align="left" valign="top"><p class="table">host_notification_commands</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Can submit commands</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">cansubmitcmds</p></td>
<td align="left" valign="top"><p class="table">can_submit_commands</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>12.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>13.</em></p></td>
<td align="left" valign="top"><p class="table">Service notification enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">svcnotifenabled</p></td>
<td align="left" valign="top"><p class="table">service_notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>14.</em></p></td>
<td align="left" valign="top"><p class="table">Host notification enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">hstnotifenabled</p></td>
<td align="left" valign="top"><p class="table">host_notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>15.</em></p></td>
<td align="left" valign="top"><p class="table">Pager</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">pager</p></td>
<td align="left" valign="top"><p class="table">pager</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>16.</em></p></td>
<td align="left" valign="top"><p class="table">Address1</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">address1</p></td>
<td align="left" valign="top"><p class="table">address1</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>17.</em></p></td>
<td align="left" valign="top"><p class="table">Address2</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">address2</p></td>
<td align="left" valign="top"><p class="table">address2</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>18.</em></p></td>
<td align="left" valign="top"><p class="table">Address3</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">address3</p></td>
<td align="left" valign="top"><p class="table">address3</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>19.</em></p></td>
<td align="left" valign="top"><p class="table">Address4</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">address4</p></td>
<td align="left" valign="top"><p class="table">address4</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>20.</em></p></td>
<td align="left" valign="top"><p class="table">Address5</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">address5</p></td>
<td align="left" valign="top"><p class="table">address5</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>21.</em></p></td>
<td align="left" valign="top"><p class="table">Address6</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">address6</p></td>
<td align="left" valign="top"><p class="table">address6</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>22.</em></p></td>
<td align="left" valign="top"><p class="table">Retain status info</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_status_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>23.</em></p></td>
<td align="left" valign="top"><p class="table">Retain non-status info</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainnonstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_nonstatus_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>24.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">XLM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_contactgroups">1.2.3. contactgroups</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Contact group name</p></td>
<td align="center" valign="top"><p class="table">RKM</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">contactgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Alias pretty name</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Members list</p></td>
<td align="center" valign="top"><p class="table">RLM</p></td>
<td align="left" valign="top"><p class="table">members</p></td>
<td align="left" valign="top"><p class="table">members</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_hosts">1.2.4. hosts</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RK</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Alias</p></td>
<td align="center" valign="top"><p class="table">RE</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">IP Address</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">ipaddress</p></td>
<td align="left" valign="top"><p class="table">address</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Host Template</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">template</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Shown Hostgroup</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">hostgroup</p></td>
<td align="left" valign="top"><p class="table">hostgroups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Contact Group</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Active checks</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">activechecks</p></td>
<td align="left" valign="top"><p class="table">active_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Service Set</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">servicesets</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Disable [0,1,2]</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Display name</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">displayname</p></td>
<td align="left" valign="top"><p class="table">display_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>12.</em></p></td>
<td align="left" valign="top"><p class="table">Parents</p></td>
<td align="center" valign="top"><p class="table">LXM</p></td>
<td align="left" valign="top"><p class="table">parents</p></td>
<td align="left" valign="top"><p class="table">parents</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>13.</em></p></td>
<td align="left" valign="top"><p class="table">Check command</p></td>
<td align="center" valign="top"><p class="table">ME</p></td>
<td align="left" valign="top"><p class="table">command</p></td>
<td align="left" valign="top"><p class="table">check_command</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>14.</em></p></td>
<td align="left" valign="top"><p class="table">Initial state</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">initialstate</p></td>
<td align="left" valign="top"><p class="table">initial_state</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>15.</em></p></td>
<td align="left" valign="top"><p class="table">Max check attempts</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">maxcheckattempts</p></td>
<td align="left" valign="top"><p class="table">max_check_attempts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>16.</em></p></td>
<td align="left" valign="top"><p class="table">Check interval</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">checkinterval</p></td>
<td align="left" valign="top"><p class="table">check_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>17.</em></p></td>
<td align="left" valign="top"><p class="table">Retry interval</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">retryinterval</p></td>
<td align="left" valign="top"><p class="table">retry_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>18.</em></p></td>
<td align="left" valign="top"><p class="table">Passive checks enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">passivechecks</p></td>
<td align="left" valign="top"><p class="table">passive_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>19.</em></p></td>
<td align="left" valign="top"><p class="table">Check period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">checkperiod</p></td>
<td align="left" valign="top"><p class="table">check_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>20.</em></p></td>
<td align="left" valign="top"><p class="table">Obsess over host</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">obsessoverhost</p></td>
<td align="left" valign="top"><p class="table">obsess_over_host</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>21.</em></p></td>
<td align="left" valign="top"><p class="table">Check freshness</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">checkfreshness</p></td>
<td align="left" valign="top"><p class="table">check_freshness</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>22.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">freshnessthresh</p></td>
<td align="left" valign="top"><p class="table">freshness_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>23.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandler</p></td>
<td align="left" valign="top"><p class="table">event_handler</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>24.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandlerenabled</p></td>
<td align="left" valign="top"><p class="table">event_handler_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>25.</em></p></td>
<td align="left" valign="top"><p class="table">Low flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">lowflapthresh</p></td>
<td align="left" valign="top"><p class="table">low_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>26.</em></p></td>
<td align="left" valign="top"><p class="table">High flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">highflapthresh</p></td>
<td align="left" valign="top"><p class="table">high_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>27.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">flapdetectionenabled</p></td>
<td align="left" valign="top"><p class="table">flap_detection_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>28.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">flapdetectionoptions</p></td>
<td align="left" valign="top"><p class="table">flap_detection_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>29.</em></p></td>
<td align="left" valign="top"><p class="table">Process perf data</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">processperfdata</p></td>
<td align="left" valign="top"><p class="table">process_perf_data</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>30.</em></p></td>
<td align="left" valign="top"><p class="table">Retain status information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_status_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>31.</em></p></td>
<td align="left" valign="top"><p class="table">Retain nonstatus information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainnonstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_nonstatus_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>32.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>33.</em></p></td>
<td align="left" valign="top"><p class="table">First notification delay</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">firstnotifdelay</p></td>
<td align="left" valign="top"><p class="table">first_notifdelay</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>34.</em></p></td>
<td align="left" valign="top"><p class="table">Notification period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">notifperiod</p></td>
<td align="left" valign="top"><p class="table">notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>35.</em></p></td>
<td align="left" valign="top"><p class="table">Notification opts</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">notifopts</p></td>
<td align="left" valign="top"><p class="table">notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>36.</em></p></td>
<td align="left" valign="top"><p class="table">Notifications enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>37.</em></p></td>
<td align="left" valign="top"><p class="table">Stalking options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">stalkingoptions</p></td>
<td align="left" valign="top"><p class="table">stalking_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>38.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>39.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>40.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>41.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>42.</em></p></td>
<td align="left" valign="top"><p class="table">Vrml image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>43.</em></p></td>
<td align="left" valign="top"><p class="table">Statusmap image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>44.</em></p></td>
<td align="left" valign="top"><p class="table">2d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords2d</p></td>
<td align="left" valign="top"><p class="table">2d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>45.</em></p></td>
<td align="left" valign="top"><p class="table">3d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords3d</p></td>
<td align="left" valign="top"><p class="table">3d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>46.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>47.</em></p></td>
<td align="left" valign="top"><p class="table">Custom variable</p></td>
<td align="center" valign="top"><p class="table">C</p></td>
<td align="left" valign="top"><p class="table">customvars</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
<div class="admonitionblock">
<table><tr>
<td class="icon">
<img alt="Note" src="data:image/png;base64,
" />
</td>
<td class="content">When using '/show/hosts' the contacts field is output as
'contact', without the 's', but set with 'contacts', with the 's' as shown
above.</td>
</tr></table>
</div>
</div>
<div class="sect3">
<h4 id="_hosttemplates">1.2.5. hosttemplates</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Name</p></td>
<td align="center" valign="top"><p class="table">RKM</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Use</p></td>
<td align="center" valign="top"><p class="table">M</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Normal check interval</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">normchecki</p></td>
<td align="left" valign="top"><p class="table">normchecki</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Check interval</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">checkinterval</p></td>
<td align="left" valign="top"><p class="table">check_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Retry interval</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">retryinterval</p></td>
<td align="left" valign="top"><p class="table">retry_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Notification period</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">notifperiod</p></td>
<td align="left" valign="top"><p class="table">notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Notification option</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">notifopts</p></td>
<td align="left" valign="top"><p class="table">notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Check period</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">checkperiod</p></td>
<td align="left" valign="top"><p class="table">check_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>12.</em></p></td>
<td align="left" valign="top"><p class="table">Max check attempts</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">maxcheckattempts</p></td>
<td align="left" valign="top"><p class="table">max_check_attempts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>13.</em></p></td>
<td align="left" valign="top"><p class="table">Check command</p></td>
<td align="center" valign="top"><p class="table">ME</p></td>
<td align="left" valign="top"><p class="table">checkcommand</p></td>
<td align="left" valign="top"><p class="table">check_command</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>14.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>15.</em></p></td>
<td align="left" valign="top"><p class="table">Passive checks enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">passivechecks</p></td>
<td align="left" valign="top"><p class="table">passive_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>16.</em></p></td>
<td align="left" valign="top"><p class="table">Obsess over host</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">obsessoverhost</p></td>
<td align="left" valign="top"><p class="table">obsess_over_host</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>17.</em></p></td>
<td align="left" valign="top"><p class="table">Check freshness</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">checkfreshness</p></td>
<td align="left" valign="top"><p class="table">check_freshness</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>18.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">freshnessthresh</p></td>
<td align="left" valign="top"><p class="table">freshness_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>19.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandler</p></td>
<td align="left" valign="top"><p class="table">event_handler</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>20.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandlerenabled</p></td>
<td align="left" valign="top"><p class="table">event_handler_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>21.</em></p></td>
<td align="left" valign="top"><p class="table">Low flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">lowflapthresh</p></td>
<td align="left" valign="top"><p class="table">low_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>22.</em></p></td>
<td align="left" valign="top"><p class="table">High flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">highflapthresh</p></td>
<td align="left" valign="top"><p class="table">high_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>23.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">flapdetectionenabled</p></td>
<td align="left" valign="top"><p class="table">flap_detection_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>24.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">flapdetectionoptions</p></td>
<td align="left" valign="top"><p class="table">flap_detection_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>25.</em></p></td>
<td align="left" valign="top"><p class="table">Process perf data</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">processperfdata</p></td>
<td align="left" valign="top"><p class="table">process_perf_data</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>26.</em></p></td>
<td align="left" valign="top"><p class="table">Retain status information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_status_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>27.</em></p></td>
<td align="left" valign="top"><p class="table">Retain nonstatus information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainnonstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_nonstatus_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>28.</em></p></td>
<td align="left" valign="top"><p class="table">First notification delay</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">firstnotifdelay</p></td>
<td align="left" valign="top"><p class="table">first_notifdelay</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>29.</em></p></td>
<td align="left" valign="top"><p class="table">Notifications enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>30.</em></p></td>
<td align="left" valign="top"><p class="table">Stalking options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">stalkingoptions</p></td>
<td align="left" valign="top"><p class="table">stalking_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>31.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>32.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>33.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>34.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>35.</em></p></td>
<td align="left" valign="top"><p class="table">Vrml image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>36.</em></p></td>
<td align="left" valign="top"><p class="table">Statusmap image</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>37.</em></p></td>
<td align="left" valign="top"><p class="table">2d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords2d</p></td>
<td align="left" valign="top"><p class="table">2d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>38.</em></p></td>
<td align="left" valign="top"><p class="table">3d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords3d</p></td>
<td align="left" valign="top"><p class="table">3d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>39.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">E</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>40.</em></p></td>
<td align="left" valign="top"><p class="table">Custom variable</p></td>
<td align="center" valign="top"><p class="table">C</p></td>
<td align="left" valign="top"><p class="table">customvars</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_services">1.2.6. services</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Name</p></td>
<td align="center" valign="top"><p class="table">RKE</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Service template</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">template</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Service command</p></td>
<td align="center" valign="top"><p class="table">RME</p></td>
<td align="left" valign="top"><p class="table">command</p></td>
<td align="left" valign="top"><p class="table">check_command</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">RKE</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">service_description</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Service groups</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">svcgroup</p></td>
<td align="left" valign="top"><p class="table">servicegroups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold (auto)*</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">freshnessthresh</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Active checks enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">activechecks</p></td>
<td align="left" valign="top"><p class="table">active_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Custom variables</p></td>
<td align="center" valign="top"><p class="table">C</p></td>
<td align="left" valign="top"><p class="table">customvars</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Disable[0,1,2]</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>12.</em></p></td>
<td align="left" valign="top"><p class="table">Display name</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">displayname</p></td>
<td align="left" valign="top"><p class="table">display_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>13.</em></p></td>
<td align="left" valign="top"><p class="table">Is volatile</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">isvolatile</p></td>
<td align="left" valign="top"><p class="table">is_volatile</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>14.</em></p></td>
<td align="left" valign="top"><p class="table">Initial state</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">initialstate</p></td>
<td align="left" valign="top"><p class="table">initial_state</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>15.</em></p></td>
<td align="left" valign="top"><p class="table">Max check attempts</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">maxcheckattempts</p></td>
<td align="left" valign="top"><p class="table">max_check_attempts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>16.</em></p></td>
<td align="left" valign="top"><p class="table">Check interval</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">checkinterval</p></td>
<td align="left" valign="top"><p class="table">check_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>17.</em></p></td>
<td align="left" valign="top"><p class="table">Retry interval</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retryinterval</p></td>
<td align="left" valign="top"><p class="table">retry_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>18.</em></p></td>
<td align="left" valign="top"><p class="table">Passive checks enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">passivechecks</p></td>
<td align="left" valign="top"><p class="table">passive_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>19.</em></p></td>
<td align="left" valign="top"><p class="table">Check period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">checkperiod</p></td>
<td align="left" valign="top"><p class="table">check_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>20.</em></p></td>
<td align="left" valign="top"><p class="table">Obsess over service</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">obsessoverservice</p></td>
<td align="left" valign="top"><p class="table">obsess_over_service</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>21.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold (manual)</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">manfreshnessthresh</p></td>
<td align="left" valign="top"><p class="table">freshness_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>22.</em></p></td>
<td align="left" valign="top"><p class="table">Check Freshness</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">checkfreshness</p></td>
<td align="left" valign="top"><p class="table">check_freshness</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>23.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandler</p></td>
<td align="left" valign="top"><p class="table">event_handler</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>24.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandlerenabled</p></td>
<td align="left" valign="top"><p class="table">event_handler_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>25.</em></p></td>
<td align="left" valign="top"><p class="table">Low flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">lowflapthresh</p></td>
<td align="left" valign="top"><p class="table">low_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>26.</em></p></td>
<td align="left" valign="top"><p class="table">High flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">highflapthresh</p></td>
<td align="left" valign="top"><p class="table">high_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>27.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">flapdetectionenabled</p></td>
<td align="left" valign="top"><p class="table">flap_detection_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>28.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">flapdetectionoptions</p></td>
<td align="left" valign="top"><p class="table">flap_detection_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>29.</em></p></td>
<td align="left" valign="top"><p class="table">Process perf data</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">processperfdata</p></td>
<td align="left" valign="top"><p class="table">process_perf_data</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>30.</em></p></td>
<td align="left" valign="top"><p class="table">Retain status information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_status_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>31.</em></p></td>
<td align="left" valign="top"><p class="table">Retain nonstatus information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainnonstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_nonstatus_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>32.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>33.</em></p></td>
<td align="left" valign="top"><p class="table">First notification delay</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">firstnotifdelay</p></td>
<td align="left" valign="top"><p class="table">first_notifdelay</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>34.</em></p></td>
<td align="left" valign="top"><p class="table">Notification period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">notifperiod</p></td>
<td align="left" valign="top"><p class="table">notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>35.</em></p></td>
<td align="left" valign="top"><p class="table">Notification opts</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">notifopts</p></td>
<td align="left" valign="top"><p class="table">notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>36.</em></p></td>
<td align="left" valign="top"><p class="table">Notifications enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>37.</em></p></td>
<td align="left" valign="top"><p class="table">Stalking options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">stalkingoptions</p></td>
<td align="left" valign="top"><p class="table">stalking_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>38.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>39.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>40.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>41.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>42.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>43.</em></p></td>
<td align="left" valign="top"><p class="table">Vrml image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>44.</em></p></td>
<td align="left" valign="top"><p class="table">Statusmap image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>45.</em></p></td>
<td align="left" valign="top"><p class="table">2d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords2d</p></td>
<td align="left" valign="top"><p class="table">2d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>46.</em></p></td>
<td align="left" valign="top"><p class="table">3d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords3d</p></td>
<td align="left" valign="top"><p class="table">3d_coords</p></td>
</tr>
</tbody>
</table>
</div>
<div class="paragraph"><p>* Freshness thresh (auto) also sets check_command to no-checks-received,
active_checks_enabled to 0 (depending on whether the host is a dcc or not),
passive_checks_enabled to 1 and check_freshness to 1. Use manfreshnessthresh
to restrict to only setting the freshness_threshold.</p></div>
</div>
<div class="sect3">
<h4 id="_servicesets">1.2.7. servicesets</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Serviceset name</p></td>
<td align="center" valign="top"><p class="table">RKE</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Service template</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">template</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Service command</p></td>
<td align="center" valign="top"><p class="table">RE</p></td>
<td align="left" valign="top"><p class="table">command</p></td>
<td align="left" valign="top"><p class="table">check_command</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">RK</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">service_description</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Service groups</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">svcgroup</p></td>
<td align="left" valign="top"><p class="table">servicegroups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold (auto)*</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">freshnessthresh</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Active checks</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">activechecks</p></td>
<td align="left" valign="top"><p class="table">active_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Custom variables</p></td>
<td align="center" valign="top"><p class="table">C</p></td>
<td align="left" valign="top"><p class="table">customvars</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>12.</em></p></td>
<td align="left" valign="top"><p class="table">Display name</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">displayname</p></td>
<td align="left" valign="top"><p class="table">display_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>13.</em></p></td>
<td align="left" valign="top"><p class="table">Is volatile</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">isvolatile</p></td>
<td align="left" valign="top"><p class="table">is_volatile</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>14.</em></p></td>
<td align="left" valign="top"><p class="table">Initial state</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">initialstate</p></td>
<td align="left" valign="top"><p class="table">initial_state</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>15.</em></p></td>
<td align="left" valign="top"><p class="table">Max check attempts</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">maxcheckattempts</p></td>
<td align="left" valign="top"><p class="table">max_check_attempts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>16.</em></p></td>
<td align="left" valign="top"><p class="table">Check interval</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">checkinterval</p></td>
<td align="left" valign="top"><p class="table">check_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>17.</em></p></td>
<td align="left" valign="top"><p class="table">Retry interval</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retryinterval</p></td>
<td align="left" valign="top"><p class="table">retry_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>18.</em></p></td>
<td align="left" valign="top"><p class="table">Passive checks enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">passivechecks</p></td>
<td align="left" valign="top"><p class="table">passive_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>19.</em></p></td>
<td align="left" valign="top"><p class="table">Check period</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">checkperiod</p></td>
<td align="left" valign="top"><p class="table">check_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>20.</em></p></td>
<td align="left" valign="top"><p class="table">Obsess over service</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">obsessoverservice</p></td>
<td align="left" valign="top"><p class="table">obsess_over_service</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>21.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold (manual)</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">manfreshnessthresh</p></td>
<td align="left" valign="top"><p class="table">freshness_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>22.</em></p></td>
<td align="left" valign="top"><p class="table">Check Freshness</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">checkfreshness</p></td>
<td align="left" valign="top"><p class="table">check_freshness</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>23.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandler</p></td>
<td align="left" valign="top"><p class="table">event_handler</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>24.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandlerenabled</p></td>
<td align="left" valign="top"><p class="table">event_handler_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>25.</em></p></td>
<td align="left" valign="top"><p class="table">Low flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">lowflapthresh</p></td>
<td align="left" valign="top"><p class="table">low_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>26.</em></p></td>
<td align="left" valign="top"><p class="table">High flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">highflapthresh</p></td>
<td align="left" valign="top"><p class="table">high_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>27.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">flapdetectionenabled</p></td>
<td align="left" valign="top"><p class="table">flap_detection_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>28.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">flapdetectionoptions</p></td>
<td align="left" valign="top"><p class="table">flap_detection_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>29.</em></p></td>
<td align="left" valign="top"><p class="table">Process perf data</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">processperfdata</p></td>
<td align="left" valign="top"><p class="table">process_perf_data</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>30.</em></p></td>
<td align="left" valign="top"><p class="table">Retain status information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_status_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>31.</em></p></td>
<td align="left" valign="top"><p class="table">Retain nonstatus information</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">retainnonstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_nonstatus_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>32.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>33.</em></p></td>
<td align="left" valign="top"><p class="table">First notification delay</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">firstnotifdelay</p></td>
<td align="left" valign="top"><p class="table">first_notifdelay</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>34.</em></p></td>
<td align="left" valign="top"><p class="table">Notification period</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifperiod</p></td>
<td align="left" valign="top"><p class="table">notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>35.</em></p></td>
<td align="left" valign="top"><p class="table">Notification opts</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">notifopts</p></td>
<td align="left" valign="top"><p class="table">notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>36.</em></p></td>
<td align="left" valign="top"><p class="table">Notifications enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>37.</em></p></td>
<td align="left" valign="top"><p class="table">Stalking options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">stalkingoptions</p></td>
<td align="left" valign="top"><p class="table">stalking_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>38.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>39.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>40.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>41.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>42.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>43.</em></p></td>
<td align="left" valign="top"><p class="table">Vrml image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>44.</em></p></td>
<td align="left" valign="top"><p class="table">Statusmap image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>45.</em></p></td>
<td align="left" valign="top"><p class="table">2d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords2d</p></td>
<td align="left" valign="top"><p class="table">2d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>46.</em></p></td>
<td align="left" valign="top"><p class="table">3d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords3d</p></td>
<td align="left" valign="top"><p class="table">3d_coords</p></td>
</tr>
</tbody>
</table>
</div>
<div class="paragraph"><p>* Freshness thresh (auto) also sets check_command to no-checks-received,
active_checks_enabled to 0 (depending on whether the host is a dcc or not),
passive_checks_enabled to 1 and check_freshness to 1. Use manfreshnessthresh
to restrict to only setting the freshness_threshold.</p></div>
</div>
<div class="sect3">
<h4 id="_servicetemplates">1.2.8. servicetemplates</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Name</p></td>
<td align="center" valign="top"><p class="table">RKM</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Use</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
<td align="left" valign="top"><p class="table">use</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Notification options</p></td>
<td align="center" valign="top"><p class="table">L</p></td>
<td align="left" valign="top"><p class="table">notifopts</p></td>
<td align="left" valign="top"><p class="table">notification_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Check interval</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">checkinterval</p></td>
<td align="left" valign="top"><p class="table">check_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Normal check interval</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">normchecki</p></td>
<td align="left" valign="top"><p class="table">normchecki</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Retry interval</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">retryinterval</p></td>
<td align="left" valign="top"><p class="table">retry_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Notification period</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">notifperiod</p></td>
<td align="left" valign="top"><p class="table">notification_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>12.</em></p></td>
<td align="left" valign="top"><p class="table">Check period</p></td>
<td align="center" valign="top"><p class="table">RM</p></td>
<td align="left" valign="top"><p class="table">checkperiod</p></td>
<td align="left" valign="top"><p class="table">check_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>13.</em></p></td>
<td align="left" valign="top"><p class="table">Max check attempts</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">maxcheckattempts</p></td>
<td align="left" valign="top"><p class="table">max_check_attempts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>14.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold (auto)*</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">freshnessthresh</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>15.</em></p></td>
<td align="left" valign="top"><p class="table">Active checks</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">activechecks</p></td>
<td align="left" valign="top"><p class="table">active_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>16.</em></p></td>
<td align="left" valign="top"><p class="table">Custom variables</p></td>
<td align="center" valign="top"><p class="table">C</p></td>
<td align="left" valign="top"><p class="table">customvars</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>17.</em></p></td>
<td align="left" valign="top"><p class="table">Is volatile</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">isvolatile</p></td>
<td align="left" valign="top"><p class="table">is_volatile</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>18.</em></p></td>
<td align="left" valign="top"><p class="table">Initial state</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">initialstate</p></td>
<td align="left" valign="top"><p class="table">initial_state</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>19.</em></p></td>
<td align="left" valign="top"><p class="table">Passive checks enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">passivechecks</p></td>
<td align="left" valign="top"><p class="table">passive_checks_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>20.</em></p></td>
<td align="left" valign="top"><p class="table">Obsess over service</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">obsessoverservice</p></td>
<td align="left" valign="top"><p class="table">obsess_over_service</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>21.</em></p></td>
<td align="left" valign="top"><p class="table">Freshness threshold (manual)</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">manfreshnessthresh</p></td>
<td align="left" valign="top"><p class="table">freshness_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>22.</em></p></td>
<td align="left" valign="top"><p class="table">Check Freshness</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">checkfreshness</p></td>
<td align="left" valign="top"><p class="table">check_freshness</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>23.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandler</p></td>
<td align="left" valign="top"><p class="table">event_handler</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>24.</em></p></td>
<td align="left" valign="top"><p class="table">Event handler enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">eventhandlerenabled</p></td>
<td align="left" valign="top"><p class="table">event_handler_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>25.</em></p></td>
<td align="left" valign="top"><p class="table">Low flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">lowflapthresh</p></td>
<td align="left" valign="top"><p class="table">low_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>26.</em></p></td>
<td align="left" valign="top"><p class="table">High flap threshold</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">highflapthresh</p></td>
<td align="left" valign="top"><p class="table">high_flap_threshold</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>27.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection enabled</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">flapdetectionenabled</p></td>
<td align="left" valign="top"><p class="table">flap_detection_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>28.</em></p></td>
<td align="left" valign="top"><p class="table">Flap detection options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">flapdetectionoptions</p></td>
<td align="left" valign="top"><p class="table">flap_detection_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>29.</em></p></td>
<td align="left" valign="top"><p class="table">Process perf data</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">processperfdata</p></td>
<td align="left" valign="top"><p class="table">process_perf_data</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>30.</em></p></td>
<td align="left" valign="top"><p class="table">Retain status information</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">retainstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_status_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>31.</em></p></td>
<td align="left" valign="top"><p class="table">Retain nonstatus information</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">retainnonstatusinfo</p></td>
<td align="left" valign="top"><p class="table">retain_nonstatus_information</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>32.</em></p></td>
<td align="left" valign="top"><p class="table">First notification delay</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">firstnotifdelay</p></td>
<td align="left" valign="top"><p class="table">first_notifdelay</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>33.</em></p></td>
<td align="left" valign="top"><p class="table">Notifications enabled</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
<td align="left" valign="top"><p class="table">notifications_enabled</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>34.</em></p></td>
<td align="left" valign="top"><p class="table">Stalking options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">stalkingoptions</p></td>
<td align="left" valign="top"><p class="table">stalking_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>35.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>36.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>37.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">E</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>38.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>39.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>40.</em></p></td>
<td align="left" valign="top"><p class="table">Vrml image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>41.</em></p></td>
<td align="left" valign="top"><p class="table">Statusmap image</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>42.</em></p></td>
<td align="left" valign="top"><p class="table">2d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords2d</p></td>
<td align="left" valign="top"><p class="table">2d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>43.</em></p></td>
<td align="left" valign="top"><p class="table">3d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords3d</p></td>
<td align="left" valign="top"><p class="table">3d_coords</p></td>
</tr>
</tbody>
</table>
</div>
<div class="paragraph"><p>* Freshness thresh (auto) also sets check_command to no-checks-received,
active_checks_enabled to 0 (depending on whether the host is a dcc or not),
passive_checks_enabled to 1 and check_freshness to 1. Use manfreshnessthresh
to restrict to only setting the freshness_threshold.</p></div>
</div>
<div class="sect3">
<h4 id="_commands">1.2.9. commands</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Command name</p></td>
<td align="center" valign="top"><p class="table">RKME</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">command_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Command line</p></td>
<td align="center" valign="top"><p class="table">RE</p></td>
<td align="left" valign="top"><p class="table">command</p></td>
<td align="left" valign="top"><p class="table">command_line</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_hostgroups">1.2.10. hostgroups</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Hostgroup name</p></td>
<td align="center" valign="top"><p class="table">RKM</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Alias</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table"></p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Members</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">members</p></td>
<td align="left" valign="top"><p class="table">members</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Hostgroup members</p></td>
<td align="center" valign="top"><p class="table">LXM</p></td>
<td align="left" valign="top"><p class="table">hostgroupmembers</p></td>
<td align="left" valign="top"><p class="table">hostgroup_members</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_servicegroups">1.2.11. servicegroups</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Servicegroup name</p></td>
<td align="center" valign="top"><p class="table">RK</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">servicegroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Alias</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Members</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">members</p></td>
<td align="left" valign="top"><p class="table">members</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Servicegroup members</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">servicegroupmembers</p></td>
<td align="left" valign="top"><p class="table">servicegroup_members</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_timeperiods">1.2.12. timeperiods</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Timeperiod name</p></td>
<td align="center" valign="top"><p class="table">RKM</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">timeperiod_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Alias</p></td>
<td align="center" valign="top"><p class="table">R</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
<td align="left" valign="top"><p class="table">alias</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Freestyle time definition</p></td>
<td align="center" valign="top"><p class="table">C</p></td>
<td align="left" valign="top"><p class="table">definition</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Timeperiod to exclude</p></td>
<td align="center" valign="top"><p class="table">LM</p></td>
<td align="left" valign="top"><p class="table">exclude</p></td>
<td align="left" valign="top"><p class="table">exclude</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">U</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Freestyle time exception</p></td>
<td align="center" valign="top"><p class="table">CXM</p></td>
<td align="left" valign="top"><p class="table">exception</p></td>
<td align="left" valign="top"><p class="table"></p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_servicedeps">1.2.13. servicedeps</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Dependent host name</p></td>
<td align="center" valign="top"><p class="table">RKX</p></td>
<td align="left" valign="top"><p class="table">dephostname</p></td>
<td align="left" valign="top"><p class="table">dependent_host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Dependent hostgroup name</p></td>
<td align="center" valign="top"><p class="table">KXM</p></td>
<td align="left" valign="top"><p class="table">dephostgroupname</p></td>
<td align="left" valign="top"><p class="table">dependent_hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Dependent service description</p></td>
<td align="center" valign="top"><p class="table">RKX</p></td>
<td align="left" valign="top"><p class="table">depsvcdesc</p></td>
<td align="left" valign="top"><p class="table">dependent_service_description</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RKX</p></td>
<td align="left" valign="top"><p class="table">hostname</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Hostgroup name</p></td>
<td align="center" valign="top"><p class="table">KXM</p></td>
<td align="left" valign="top"><p class="table">hostgroupname</p></td>
<td align="left" valign="top"><p class="table">hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">RKX</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">service_description</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Inherits parent</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">inheritsparent</p></td>
<td align="left" valign="top"><p class="table">inherits_parent</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Execution failure criteria</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">execfailcriteria</p></td>
<td align="left" valign="top"><p class="table">execution_failure_criteria</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Notification failure criteria</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">notiffailcriteria</p></td>
<td align="left" valign="top"><p class="table">notification_failure_criteria</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Dependency period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">period</p></td>
<td align="left" valign="top"><p class="table">dependency_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
<div class="admonitionblock">
<table><tr>
<td class="icon">
<img alt="Note" src="data:image/png;base64,
" />
</td>
<td class="content">Rows 1,2,4 and 5 are not lists in REST as they are in a
Nagios configuration file. One of rows 1 and 2 plus one of rows 4 and 5
are required.</td>
</tr></table>
</div>
</div>
<div class="sect3">
<h4 id="_hostdeps">1.2.14. hostdeps</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Dependent host name</p></td>
<td align="center" valign="top"><p class="table">RKX</p></td>
<td align="left" valign="top"><p class="table">dephostname</p></td>
<td align="left" valign="top"><p class="table">dependent_host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Dependent hostgroup name</p></td>
<td align="center" valign="top"><p class="table">KXM</p></td>
<td align="left" valign="top"><p class="table">dephostgroupname</p></td>
<td align="left" valign="top"><p class="table">dependent_hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RKX</p></td>
<td align="left" valign="top"><p class="table">hostname</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Hostgroup name</p></td>
<td align="center" valign="top"><p class="table">KXM</p></td>
<td align="left" valign="top"><p class="table">hostgroupname</p></td>
<td align="left" valign="top"><p class="table">hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Inherits parent</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">inheritsparent</p></td>
<td align="left" valign="top"><p class="table">inherits_parent</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Execution failure criteria</p></td>
<td align="center" valign="top"><p class="table">LRX</p></td>
<td align="left" valign="top"><p class="table">execfailcriteria</p></td>
<td align="left" valign="top"><p class="table">execution_failure_criteria</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Notification failure criteria</p></td>
<td align="center" valign="top"><p class="table">LRX</p></td>
<td align="left" valign="top"><p class="table">notiffailcriteria</p></td>
<td align="left" valign="top"><p class="table">notification_failure_criteria</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Dependency period</p></td>
<td align="center" valign="top"><p class="table">RXM</p></td>
<td align="left" valign="top"><p class="table">period</p></td>
<td align="left" valign="top"><p class="table">dependency_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">UX</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
<div class="admonitionblock">
<table><tr>
<td class="icon">
<img alt="Note" src="data:image/png;base64,
" />
</td>
<td class="content">Columns 1 and 2 are not lists in REST as they are in a
Nagios configuration file.</td>
</tr></table>
</div>
</div>
<div class="sect3">
<h4 id="_serviceescalation">1.2.15. serviceescalation</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">KRX</p></td>
<td align="left" valign="top"><p class="table">hostname</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Hostgroup name</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">hostgroupname</p></td>
<td align="left" valign="top"><p class="table">hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">KRX</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">service_description</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">LRXM</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">LRM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">First notification</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">firstnotif</p></td>
<td align="left" valign="top"><p class="table">first_notification</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Last notification</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">lastnotif</p></td>
<td align="left" valign="top"><p class="table">last_notification</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Escalation period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">period</p></td>
<td align="left" valign="top"><p class="table">escalation_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Escalation options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">escopts</p></td>
<td align="left" valign="top"><p class="table">escalation_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_hostescalation">1.2.16. hostescalation</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">KRX</p></td>
<td align="left" valign="top"><p class="table">hostname</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Hostgroup name</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">hostgroupname</p></td>
<td align="left" valign="top"><p class="table">hostgroup_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Contacts</p></td>
<td align="center" valign="top"><p class="table">LRXM</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
<td align="left" valign="top"><p class="table">contacts</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Contact groups</p></td>
<td align="center" valign="top"><p class="table">LRM</p></td>
<td align="left" valign="top"><p class="table">contactgroups</p></td>
<td align="left" valign="top"><p class="table">contact_groups</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">First notification</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">firstnotif</p></td>
<td align="left" valign="top"><p class="table">first_notification</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Last notification</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">lastnotif</p></td>
<td align="left" valign="top"><p class="table">last_notification</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Notification interval</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">notifinterval</p></td>
<td align="left" valign="top"><p class="table">notification_interval</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Escalation period</p></td>
<td align="center" valign="top"><p class="table">XM</p></td>
<td align="left" valign="top"><p class="table">period</p></td>
<td align="left" valign="top"><p class="table">escalation_period</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">Escalation options</p></td>
<td align="center" valign="top"><p class="table">LX</p></td>
<td align="left" valign="top"><p class="table">escopts</p></td>
<td align="left" valign="top"><p class="table">escalation_options</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_serviceextinfo">1.2.17. serviceextinfo</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">hostname</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">service_description</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_hostextinfo">1.2.18. hostextinfo</h4>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>1.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">hostname</p></td>
<td align="left" valign="top"><p class="table">host_name</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>2.</em></p></td>
<td align="left" valign="top"><p class="table">Notes</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
<td align="left" valign="top"><p class="table">notes</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>3.</em></p></td>
<td align="left" valign="top"><p class="table">Notes url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
<td align="left" valign="top"><p class="table">notes_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>4.</em></p></td>
<td align="left" valign="top"><p class="table">Action url</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
<td align="left" valign="top"><p class="table">action_url</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>5.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
<td align="left" valign="top"><p class="table">icon_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>6.</em></p></td>
<td align="left" valign="top"><p class="table">Icon image alt</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
<td align="left" valign="top"><p class="table">icon_image_alt</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>7.</em></p></td>
<td align="left" valign="top"><p class="table">Vrml image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
<td align="left" valign="top"><p class="table">vrml_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>8.</em></p></td>
<td align="left" valign="top"><p class="table">Statusmap image</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
<td align="left" valign="top"><p class="table">statusmap_image</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>9.</em></p></td>
<td align="left" valign="top"><p class="table">2d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords2d</p></td>
<td align="left" valign="top"><p class="table">2d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>10.</em></p></td>
<td align="left" valign="top"><p class="table">3d coords</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">coords3d</p></td>
<td align="left" valign="top"><p class="table">3d_coords</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>11.</em></p></td>
<td align="left" valign="top"><p class="table">Disable</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">disable</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_enablehostsvcchecks">1.2.19. enablehostsvcchecks</h4>
<div class="paragraph"><p>Enables active checks for the host then enables all passive and active service
checks for the host. The following nagios pipe commands are sent:</p></div>
<div class="literalblock">
<div class="content">
<pre><code>ENABLE_HOST_CHECK
ENABLE_PASSIVE_SVC_CHECKS
ENABLE_SVC_CHECK</code></pre>
</div></div>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_disablehostsvcchecks">1.2.20. disablehostsvcchecks</h4>
<div class="paragraph"><p>Disables active checks for the host then disables all passive and active
service checks for the host. Status is changed to green for the host and all
of its service checks and the comment is set. The following nagios pipe
commands are sent:</p></div>
<div class="literalblock">
<div class="content">
<pre><code>DISABLE_HOST_CHECK
DISABLE_HOST_SVC_CHECKS
PROCESS_SERVICE_CHECK_RESULT   &lt;-- Sets the comment and service status
... 10 second sleep ...
DISABLE_PASSIVE_SVC_CHECKS</code></pre>
</div></div>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Comment for the Nagios GUI</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">comment</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_enablesvccheck">1.2.21. enablesvccheck</h4>
<div class="paragraph"><p>Enables an individual service check and optionally sets a comment otherwise the
default comment will be used: "Un-disabled via REST. Check scheduled.". The
following nagios pipe commands are sent:</p></div>
<div class="literalblock">
<div class="content">
<pre><code>ENABLE_PASSIVE_SVC_CHECKS
ENABLE_SVC_CHECK
PROCESS_SERVICE_CHECK_RESULT</code></pre>
</div></div>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Comment for the Nagios GUI</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">comment</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_disablesvccheck">1.2.22. disablesvccheck</h4>
<div class="paragraph"><p>Disables an individual service check and optionally sets a comment otherwise
the default comment will be used: "Disabled via REST interface.". The following
nagios pipe commands are sent:</p></div>
<div class="literalblock">
<div class="content">
<pre><code>DISABLE_SVC_CHECK
PROCESS_SERVICE_CHECK_RESULT
DISABLE_PASSIVE_SVC_CHECKS</code></pre>
</div></div>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Service description</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">svcdesc</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Comment for the Nagios GUI</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">comment</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_schedhstdowntime">1.2.23. schedhstdowntime</h4>
<div class="paragraph"><p>Schedule fixed or flexible downtime for a host. The following nagios pipe
commands are sent:</p></div>
<div class="literalblock">
<div class="content">
<pre><code>SCHEDULE_HOST_DOWNTIME</code></pre>
</div></div>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Start time [unix time]</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">starttime</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">End time [unix time]</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">endtime</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Flexible downtime [<em>0</em>|1]</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">flexible</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Duration (flexible downtime in minutes)</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">duration</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Comment for the Nagios GUI*</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">comment</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Author</p></td>
<td align="center" valign="top"><p class="table">X</p></td>
<td align="left" valign="top"><p class="table">author</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
<div class="paragraph"><p>* If comment is not supplied then the default comment,
&#8220;Scheduled via the REST interface.&#8221;, is used.</p></div>
</div>
<div class="sect3">
<h4 id="_delhstdowntime">1.2.24. delhstdowntime</h4>
<div class="paragraph"><p>Delete all scheduled downtime for a host. The following nagios pipe commands
are sent:</p></div>
<div class="literalblock">
<div class="content">
<pre><code>DEL_HOST_DOWNTIME</code></pre>
</div></div>
<div class="tableblock">
<table rules="all"
frame="hsides"
cellspacing="0" cellpadding="4">
<col />
<col />
<col />
<col />
<col />
<thead>
<tr>
<th align="center" valign="top"> Column </th>
<th align="left" valign="top"> Description                   </th>
<th align="center" valign="top"> Flags   </th>
<th align="left" valign="top"> REST variable name        </th>
<th align="left" valign="top"> Nagios argument name</th>
</tr>
</thead>
<tbody>
<tr>
<td align="center" valign="top"><p class="table"><em>N/a.</em></p></td>
<td align="left" valign="top"><p class="table">Host name</p></td>
<td align="center" valign="top"><p class="table">RX</p></td>
<td align="left" valign="top"><p class="table">name</p></td>
<td align="left" valign="top"><p class="table">N/A</p></td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="sect3">
<h4 id="_schedulehostsvcdowntime">1.2.25. schedulehostsvcdowntime</h4>
<div class="paragraph"><p>Schedule fixed or flexible downtime for a host and all its services.</p></div>
</div>
<div class="sect3">
<h4 id="_delhostsvcdowntime">1.2.26. delhostsvcdowntime</h4>
<div class="paragraph"><p>Delete all scheduled downtime for a host and all its services.</p></div>
</div>
<div class="sect3">
<h4 id="_schedulesvcdowntime">1.2.27. schedulesvcdowntime</h4>
<div class="paragraph"><p>Schedule fixed or flexible downtime for a host.</p></div>
</div>
<div class="sect3">
<h4 id="_delsvcdowntime">1.2.28. delsvcdowntime</h4>
<div class="paragraph"><p>Delete all scheduled downtime for a host.</p></div>
</div>
</div>
</div>
</div>
</div>
          <!-- /INSERT REFERENCE MATERIAL -->
        </div>
