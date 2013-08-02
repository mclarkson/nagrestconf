<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\services_tab;

    /***********************************************************************
     *
     * PLUGIN FUNCTIONS
     *
     ***********************************************************************
     */
 
    # ------------------------------------------------------------------------
    function services_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort;

        if( isset( $query_str['servicestable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }

            # Shows the hosts table html fragment
            show_services_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['delservicesdialog'] )) {

            show_delservicedialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['editservicesdialog'] )) {

            show_editservicedialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        # Configure the server using REST

        } else if( isset( $query_str['delservice'] )) {

            delete_service_using_REST( );

        } else if( isset( $query_str['editservice'] )) {

            edit_service_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function create_url( ) {
    # ------------------------------------------------------------------------
    # Allow the globals to override the QUERY_STRING. The globals are
    # cleared if they were found to be set.
    
        global $g_tab_new, $g_sort_new, $g_sfilter, $g_hfilter;

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        $url="/nagrestconf/".SCRIPTNAME."?";

        if( ! empty( $g_sort_new ) ) {
            $url .= "&sort=".$g_sort_new;
            $g_sort_new="";
        } else if( isset( $query_str['sort'] ) ) {
            $url .= "&sort=".$query_str['sort'];
        }
        if( ! empty( $g_tab_new ) ) {
            $url .= "&tab=".$g_tab_new;
            $g_tab_new="";
        } else if( isset( $query_str['tab'] ) ) {
            $url .= "&tab=".$query_str['tab'];
        }
        if( $g_sfilter !== 0 ) {
            if( ! empty( $g_sfilter ) ) {
                $url .= "&sfilter=".urlencode($g_sfilter);
            } else if( isset( $query_str['sfilter'] ) ) {
                $url .= "&sfilter=".urlencode($query_str['sfilter']);
            }
            $g_sfilter="";
        } else {
            $g_sfilter="";
        }
        if( $g_hfilter !== 0 ) {
            if( ! empty( $g_hfilter ) ) {
                $url .= "&hfilter=".urlencode($g_hfilter);
            } else if( isset( $query_str['hfilter'] ) ) {
                $url .= "&hfilter=".urlencode($query_str['hfilter']);
            }
            $g_hfilter="";
        } else {
            $g_hfilter="";
        }

        return $url;
    }

    # ------------------------------------------------------------------------
    function show_services_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        print "<script>".
              "$(document).ready(function() {".
        #      "  $(document).keydown(function(event){".
        #      "      if(event.keyCode == 13) {".
        #      "        event.preventDefault();".
        #      "      return false;".
        #      "      }".
        #      "    });".
              # Load the right pane
              #'$("#servicestable").html("").'.
              '$("#servicestable").'.
              'load("'.$url.'&servicestable=true");'.
              "  });".
              "</script>";

        print "<div id=pageheader>";
        show_pageheader();
        print "</div>";

        # To find out how the layout works see:
        # http://matthewjamestaylor.com/blog/equal-height-columns-cross-
        # browser-css-no-hacks

        print "<div class=\"colmask leftmenu\">";
        print "<div class=\"colright\">";
        print "<div class=\"col1wrap\">";
        print "<div class=\"col1\">";
        print '<div id="servicestable">'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_services_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_services_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        global $g_tab, $g_sfilter, $g_hfilter, $g_folders;

        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        $hfilter="";
        if( isset( $query_str['hfilter'] ) ) {
            $hfilter=$query_str['hfilter'];
        }

        $sfilter="";
        if( isset( $query_str['sfilter'] ) ) {
            $sfilter=$query_str['sfilter'];
        }

        $g_hfilter = 0; # <-- don't include hfilter
        $g_sfilter = 0; # <-- don't include sfilter
        $url = create_url( );

        print "<p style='margin-bottom:10px'>Filter by Name regex:<br>".
              "<input class='filtermain' id='hregex' name='hregex' type='text'".
              " style='width:100px;'".
              " value='".$hfilter."'".
              " />".
              "</p>";
        print "<p style='margin-bottom:10px'>Filter by Service regex:<br>".
              "<input class='filtermain' id='sregex' name='sregex' type='text'".
              " style='width:100px;'".
              " value='".$sfilter."'".
              " /><span class='btn ui-corner-all' ".
              " onClick='".
              "var a=encodeURIComponent($(\"#sregex\").val());".
              "var b=encodeURIComponent($(\"#hregex\").val());".
              'window.location="'.$url.
              '"+"&amp;sfilter="+a+"&amp;hfilter="+b;'.
              "'>go</span>".
              "</p>";
        print "<script>";
        print "$('.filtermain').keypress(function (e) {";
        print "  if (e.which == 13) {";
        print "    var a=encodeURIComponent($(\"#sregex\").val());".
              "    var b=encodeURIComponent($(\"#hregex\").val());".
              '     window.location="'.$url.
              '"+"&sfilter="+a+"&hfilter="+b;'.
              "    return true;";
        print "  }";
        print "});";
        print "</script>";

        print "<hr />";

        show_revert_and_apply_buttons();
    }

    # ------------------------------------------------------------------------
    function get_and_sort_services( $name, $sort="svcdesc" ) {
    # ------------------------------------------------------------------------

        $request = new \RestRequest(
          RESTURL.'/show/services?json='.
          '{"folder":"'.FOLDER.'","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        if( isset( $query_str['hfilter'] ) ) {
            $hostregex = urldecode($query_str['hfilter']);
        } else {
            $hostregex = ".*";
        }
        if( isset( $query_str['sfilter'] ) ) {
            $svcregex = urldecode($query_str['sfilter']);
        } else {
            $svcregex = ".*";
        }

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            if( preg_match("/$hostregex/i",$name) == 0 ) continue;
            if( preg_match("/$svcregex/i",urldecode($svcdesc)) == 0 ) continue;
            $d['name']=$name;
            $d['svcdesc']=$svcdesc;
            $d['command']=$command;
            $d['template']=$template;
            $d['disable']=$disable;
            $c[]=$d;
        }
        define( 'SORT1', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT1]>$d[SORT1];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function show_services_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        $a = get_and_sort_services( '.*', $g_sort );

        print "<p>".count($a)." services.</p>";

        print "<table style=\"float:right;width:95%;margin-right:30px;\">";
        print "<thead><tr style='font-weight: normal;'>";

        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        $g_sort_new = "svcdesc";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Service </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        $g_sort_new = "template";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Template </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        $g_sort_new = "command";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Command </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        print "<td style=\"text-align: right\">";
        print "</td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            if( $num > 1000 ) {
                print "<tr><td>...</td>";
                print "<td>...</td>";
                print "<td>...</td>";
                print "<td>...</td>";
                print "<td></td></tr>";
                break;
            }
            $style="";
            if( $item['disable'] == "1" ) {
                $style = ' style="background-color: #F7DCC6;"';
            } elseif( $item['disable'] == "2" ) {
                $style = ' style="background-color: #FFFC9E;"';
            } 

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            print "<td>".urldecode($item['name'])."</td>";
            print "<td>".urldecode($item['svcdesc'])."</td>";
            print "<td>".$item['template']."</td>";
            #print "<td>".urldecode($item['command'])."</td>";
            print "<td>".urldecode(substr($item['command'],0,100));
            if( strlen($item['command'])>100 ) print "...";
            print "</td>";
            // Actions
            print "<td style=\"float:right;\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Service\"".
                  " onClick=\"$('#editsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?editsvcdialog=true".
                  "&amp;hostname=".$item['name']."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Service\"".
                  " onClick=\"$('#delsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?delsvcdialog=true".
                  "&amp;hostname=".$item['name']."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "</td>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    /***********************************************************************
     *
     * DELETE SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delservicedialog_buttons( $name, $svcdesc ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delsvcform" name="delsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?delsvc=1';
        print '">';
        print '<h2>About to delete service:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">';
        print " &quot;".urldecode($svcdesc)."&quot; on <br>$name</h2>";
        print "<h2>Click 'Delete Service' to confirm or 'Close' to cancel.</h2>";
        # Hostname
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '<input type="hidden" name="svcdesc" value="';
        print $svcdesc;
        print '"/>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_service_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delsvcdlg\" title=\"Delete Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var delsvc = function() { ';
        print ' $.getJSON( $("#delsvcform").attr("action"), '; # <- url
        print ' $("#delsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#servicestable").html("").';
        print '      load("'.$url.'&servicestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#delsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Delete Service": delsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_service_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["delsvc"] );
        $query_str["folder"] = FOLDER;

        if( ! isset( $query_str["name"] ) && ! isset( $query_str["svcdesc"] ) ) {
            $retval["message"] = "Internal error: name or svcdesc empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        if( isset( $query_str["svcdesc"] ) ) {
            $query_str["svcdesc"] = strtr( $query_str["svcdesc"], 
                                           array( '"' => '\"',) );
            #$query_str["svcdesc"] = urlencode($query_str["svcdesc"]);
        }
        $a = array();
        $a["name"] = $query_str["name"];
        $a["svcdesc"] = $query_str["svcdesc"];
        $a["folder"] = FOLDER;
        $json = json_encode( $a );
        $request = new RestRequest(
          RESTURL.'/delete/services',
          'POST',
          'json='.$json
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $resp = $request->getResponseInfo();
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit( 0 );
    }

    /***********************************************************************
     *
     * EDIT SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_edit_service_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to edit the service.

        # 'Add New Host' dialog box div
        print "<div id=\"editsvcdlg\" title=\"Edit Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var editsvc = function() { ';
        print ' $.getJSON( $("#editsvcform").attr("action"), '; # <- url
        print ' $("#editsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#servicestable").html("").';
        print '      load("'.$url.'&servicestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Apply Changes": editsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_editservicedialog_buttons( $name, $svcdesc ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to edit a host

        $svcdesc_copy = $svcdesc;

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/services?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        # Can't search for specific service check using the REST interface.
        # Have to ask for all services for the host (above) and search it:
        foreach( $hlist as $svc ) {
            foreach( $svc as $item ) extract( $item );
            if( $svcdesc == $svcdesc_copy ) break;
        }

        print '<form id="editsvcform" name="editsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?editsvc=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="editservicetabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Disabled
        print '<p>';
        print '<label for="sdisabled">Status</label>';
        $checked="";
        $checked1="";
        $checked2="";
        if( $disable == "2" ) {
            $checked2="checked";
        } elseif( $disable == "1" ) {
            $checked1="checked";
        } else {
            $checked="checked";
        }
        print '<input type="radio" name="disable"';
        print ' value="0" '.$checked.' />Enabled &nbsp;';
        print '<input type="radio" name="disable"';
        print ' value="1" '.$checked1.' />Disabled &nbsp;';
        print '<input type="radio" name="disable"';
        print ' value="2" '.$checked2.' />Testing';
        print '</p>';

        # Hostname
        print '<p>';
        print '<label for="hostname">Host name</label>';
        print '<input class="field" type="text" id="hostname" name="name"';
        print ' value="'.$name.'" readonly="readonly" />';
        print '</p>';
        # Service Template
        $st = get_and_sort_servicetemplates( );
        print '<p>';
        print '<label for="svctemplate">Service Template *</label>';
        print '<select class="field" id="svctemplate" name="template"';
        print ' required="required">';
        foreach( $st as $item ) {
            $selected = "";
            if( $item["name"] == $template ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>'
              .$item["name"].'</option>';
        }
        print '</select>';
        print '</p>';

        # Command
        # Allow both types of speech marks as input value
        $newcmd = urldecode( $command );
        $newcmd = strtr( $newcmd, array("\""=>"\\\"") );
        print '<p>';
        print '<label for="escommand">Command *</label>';
        print '<input class="field" type="text" id="escommand" name="command"';
              # Using <.. value="\"" ..> does not work so...
        print ' required="required" />';
              # ...have to use javascript to set the value:
        print '<script>$("#escommand").val("'.$newcmd.'");</script>';
        print '</p>';

        # Service Description
        print '<p>';
        print '<label for="svcdesc">Description</label>';
        print '<input class="field" type="text" id="svcdesc" name="svcdesc"';
        print ' value="'.urldecode($svcdesc).'" readonly="readonly" />';
        print '</p>';
        # Service Groups
        print '<p>';
        print '<label for="svcgroup">Service Groups</label>';
        print '<input class="field" type="text" id="svcgroup"';
        print ' value="'.$svcgroup.'" name="svcgroup">';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="contacts">Contacts</label>';
        print '<input class="field" type="text" id="contacts"';
        print ' value="'.$contacts.'" name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="contactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' value="'.$contactgroups.'" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="customvars">Custom Variables</label>';
        print '<input class="field" type="text" id="customvars"';
        print ' value="'.$customvars.'" name="customvars">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' value="'.$freshnessthresh.'" name="freshnessthresh">';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        $checked="checked";
        if( $activechecks == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" '.$checked.' />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Check interval
        print '<p>';
        print '<label for="echeckinterval">Check Interval</label>';
        print '<input class="field" type="text" id="echeckinterval"';
        print ' value="'.$checkinterval.'" name="checkinterval">';
        print '</p>';
        # Retry interval
        print '<p>';
        print '<label for="eretryinterval">Retry Interval</label>';
        print '<input class="field" type="text" id="eretryinterval"';
        print ' value="'.$retryinterval.'" name="retryinterval">';
        print '</p>';
        # Max check attempts
        print '<p>';
        print '<label for="emaxcheckattempts">Max Check Attempts</label>';
        print '<input class="field" type="text" id="emaxcheckattempts"';
        print ' value="'.$maxcheckattempts.'" name="maxcheckattempts">';
        print '</p>';
        # Freshness threshold manual
        print '<p>';
        print '<label for="emfta">Freshness threshold (manual)</label>';
        print '<input class="field" type="text" id="emfta"';
        print ' value="'.$manfreshnessthresh.'" name="manfreshnessthresh">';
        print '</p>';
        # Passive Checks
        print '<p style="margin-top: 12px;">';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        $selected=""; if( ! strlen($passivechecks) ) $selected="selected";
        print '<option value="" '.$selected.'>From template</option>';
        $selected=""; if( $passivechecks == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $passivechecks == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        # Check Freshness
        print '<p>';
        print '<label for="scheckfreshness">Check Freshness</label>';
        print '<select name="checkfreshness" id="scheckfreshness" class="field">';
        $selected=""; if( ! strlen($checkfreshness) ) $selected="selected";
        print '<option value="" '.$selected.'>From template</option>';
        $selected=""; if( $checkfreshness == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $checkfreshness == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        $selected=""; if( ! strlen($retainstatusinfo) ) $selected="selected";
        print '<option value="" '.$selected.'>From template</option>';
        $selected=""; if( $retainstatusinfo == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $retainstatusinfo == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        $selected=""; if( ! strlen($retainnonstatusinfo) ) $selected="selected";
        print '<option value="" '.$selected.'>From template</option>';
        $selected=""; if( $retainnonstatusinfo == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $retainnonstatusinfo == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';
        print '</div>';
        print '<script>';
        print '$( "#editservicetabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function edit_service_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["editsvc"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["disable"] ) ) {
            if( $query_str["disable"] == "2" ) $query_str["disable"] = "2";
            elseif( $query_str["disable"] == "1" ) $query_str["disable"] = "1";
            else $query_str["disable"] = "0";
        }
        if( isset( $query_str["command"] ) ) {
            $query_str["command"] = strtr( $query_str["command"], 
                                           array( '"' => '\"',) );
            $query_str["command"] = urlencode($query_str["command"]);
        }
        if( isset( $query_str["activechecks"] ) )
            $query_str["activechecks"] = "1";
        else
            $query_str["activechecks"] = "0";
        # Handle deleting fields
        if( ! strlen( $query_str["retainstatusinfo"] ) )
            $query_str["retainstatusinfo"] = "-";
        if( ! strlen( $query_str["retainnonstatusinfo"] ) )
            $query_str["retainnonstatusinfo"] = "-";
        if( ! strlen( $query_str["passivechecks"] ) )
            $query_str["passivechecks"] = "-";
        if( ! strlen( $query_str["checkfreshness"] ) )
            $query_str["checkfreshness"] = "-";
        if( empty( $query_str["retryinterval"] ) )
            $query_str["retryinterval"] = "-";
        if( empty( $query_str["checkinterval"] ) )
            $query_str["checkinterval"] = "-";
        if( empty( $query_str["maxcheckattempts"] ) )
            $query_str["maxcheckattempts"] = "-";
        if( empty( $query_str["manfreshnessthresh"] ) )
            $query_str["manfreshnessthresh"] = "-";
        if( empty( $query_str["freshnessthresh"] ) )
            $query_str["freshnessthresh"] = "-";
        if( empty( $query_str["contacts"] ) )
            $query_str["contacts"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
        if( empty( $query_str["customvars"] ) )
            $query_str["customvars"] = "-";
        if( empty( $query_str["freshnessthresh"] ) )
            $query_str["freshnessthresh"] = "-";
        if( empty( $query_str["svcgroup"] ) )
            $query_str["svcgroup"] = "-";
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/modify/services',
          'POST',
          'json='.$json
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $resp = $request->getResponseInfo();
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit( 0 );
    }

?>
