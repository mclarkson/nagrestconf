        <!-- Content -->

        <style>
          .img-responsive {
              border: 1px solid #CCCCCC;
              border-radius: 4px;
              display: block;
              height: auto;
              margin-bottom: 16px;
              max-width: 100%;
              padding: 8px;
          }
        </style>

        <h1>User Guide</h1>

        <ul>
          <li><a href="#user">User Interface Components</a></li>
          <li><a href="#list">Lists</a></li>
          <li><a href="#comp">Compound Fields</a></li>
          <li><a href="#filt">Filters</a></li>
          <li><a href="#serv">Service Sets</a></li>
        </ul>

        <h3 id="user">User Interface Components</h3>

        <img src="/images/ui-naming.png" class="img-responsive" alt="ui components image">

        <ol>
          <li><p>Result filter. Accepts a POSIX extended regular expression.</p></li>
          <li><p>Revert to the last-known-good configuration. This is the last configuration that was successfully applied.</p></li>
          <li><p>Apply all changes.</p></li>
          <li><p>Backup and Restore plugin.</p></li>
          <li><p>Bulk Tools plugin.</p></li>
          <li><p>Expandable object. Click to view extra linked items.</p></li>
          <li><p>Item actions. From left to right: Test mode, clone, edit, delete, disable.</p>
          <ul>
            <li><p>Test mode works in distributed environments only. It stops the item being shown in the collector node, but it still shows in the slave node.</p></li>
            <li><p>Clone makes a copy of the item.</p></li>
            <li><p>Edit opens an Edit dialog.</p></li>
            <li><p>Delete removes an item, and possibly all sub-items if the checkbox is ticked.</p></li>
            <li><p>Disable removes the item from the nagios configuration but keeps it in the nagrestconf configuration.</p></li>
          </ul>
          </li>
          <li><p>Tab pages. Easy access to the main tables.</p></li>
          <li><p>Add new item. Opens a dialog box form.</p></li>
          <li><p>Action Sidebar. Plugin components are added here.</p></li>
        </ol>

        <h3 id="list">Lists</h3>

        <p>Many text entry fields accept multiple values and in all cases
        the values should be separated by a single space. For example, to
        enter two contacts, 'bob' and 'jack', type <code>bob jack</code>
        into the text entry field.</p>

        <h3 id="comp">Compound Fields</h3>

        <p>A few text entry fields accept compound values, namely,
        'Custom Variables' in the Edit Service dialog, and timeperiod
        definitions.</p>

        <p>The format for entering custom variables is
        <code>&lt;name&gt;|&lt;value&gt;[,&lt;name&gt;|&lt;value&gt;]… </code>
        </p>

        <p>For example, to add a single custom variable, '_SNMP_community', with
        the value, 'public', enter the following in the text entry
        field:</p>
        <pre>_SNMP_community|public</pre>
        <p>To add an additional custom variable in the text entry field,
        '_TechContact' with the value, 'Jane Doe', then separate each pair with
        a comma, for example:</p>
        <pre>_SNMP_community|public,_TechContact|Jane Doe</pre>
        <p>This creates a service entry with the following entries:</p>
        <pre>define service {
   ...
   _SNMP_community   public
   _TechContact      Jane Doe
   ...
}</pre>

        <h3 id="filt">Filters</h3>

        <p>Filtering, available in the Hosts and Services tabs, accepts a POSIX
        extended regular expression. A terse but complete description of
        regular expressions might be available on your system using <code>man 7
          regex</code>, or refer to the <a
          href="http://man7.org/linux/man-pages/man7/regex.7.html">regex page
          at man7.org</a>.</p>

        <p>All fields in the result filter are logically AND'ed together. For
        example, if the Hosts tab results are filterd using the hostgroup
        dropdown box, then the filter regular expression will only apply to
        hosts in that hostgroup.</p>

        <p><strong>Examples</strong></p>

        <table class="table table-striped">
          <tr>
            <td><p>View all items containing 'abc' anywhere in the name.</p></td>
            <td><p>abc</p></td>
          </tr>
          <tr>
            <td><p>View all items starting with 'abc'.</p></td>
            <td><p>^abc</p></td>
          </tr>
          <tr>
            <td><p>View all items starting with 'abc' or 'def'.</p></td>
            <td><p>^(abc|def)</p></td>
          </tr>
          <tr>
            <td><p>View all items ending with '001'.</p></td>
            <td><p>001$</p></td>
          </tr>
          <tr>
            <td><p>View all items ending with '001','002' or '003'.</p></td>
            <td><p>00[1-3]$</p></td>
          </tr>
          <tr>
            <td><p>View all items starting with 'lon' followed by one of 'db2','mysql' or 'mssql'.</p></td>
            <td><p>^lon.*(db2|mysql|mssql)</p></td>
          </tr>
        </table>

        <h3 id="serv">Service Sets</h3>

        <p>Service Sets group a number of service checks together, which can then be applied to one or more
        hosts.</p>

        <p>One or more service sets can be applied to a host. When service sets are layered this way there
        may be duplicate services, and if there are duplicate services then the last service set containing
        the duplicate overrides any other. The last service set is the rightmost service set.</p>

        <p>Service sets can be specified in the Host add or edit dialogs, or can be applied to many hosts
        at once by using the Bulk Tools plugin.</p>

        <p>When using the Host edit dialog the service sets can be changed but the service sets will not be
        applied unless the 'Re-apply Service Sets' check box, locate next to the 'Apply Changes' button, is
        ticked before pressing 'Apply Changes' as shown below.</p>
          <a href="#img1" onClick="$('#img1').css('display','block'); return false;"><img src="/images/hosteditsvcset.png" class="img-thumbnail"></a></p><a id="img1" class="a-imgshow" onClick="$('#img1').css('display','none'); return false;"><img src="/images/hosteditsvcset.png" class="imgshow"></a>

        <p>When using Bulk Tools, new service sets can be applied to many hosts using the 'Modify hosts'
        tab in the 'Bulk Tools' dialog, but again they will not be applied. To apply the service sets to
        the hosts first make the service set changes in bulk, then switch to the 'Refresh Hosts' tab and
        apply the changes as shown below.</p>
          <a href="#img2" onClick="$('#img2').css('display','block'); return false;"><img src="/images/bulktoolssvcsetedit.png" class="img-thumbnail"></a></p><a id="img2" class="a-imgshow" onClick="$('#img2').css('display','none'); return false;"><img src="/images/bulktoolssvcsetedit.png" class="imgshow"></a>
          <a href="#img3" onClick="$('#img3').css('display','block'); return false;"><img src="/images/bulktoolssvcsetrefresh.png" class="img-thumbnail"></a></p><a id="img3" class="a-imgshow" onClick="$('#img3').css('display','none'); return false;"><img src="/images/bulktoolssvcsetrefresh.png" class="imgshow"></a>

        <!-- /Content -->
