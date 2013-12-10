<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\hosts_bulktools_btn;

    /***********************************************************************
     *
     * PLUGIN CALLBACK FUNCTIONS
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function button_html( ) {
    # ------------------------------------------------------------------------
    # HTML code to show the 'Bulk Tools' button to the left pane.
    # Adds code to find the dialog div, bulkhsttooldlg, add code to it, then
    # open the jQuery dialog.

        $id = spi_get_tab_idx();

        print '<input id="bulkhsttool" type="button" value="Bulk Tools" />';
        print '<script>';
        print ' $("#bulkhsttool").bind("click", function() {';
        print "$('#bulkhsttooldlg').".
              "html('<p style=\"font-weight: bold;text-align: center;".
              "padding-top: 14px;\">".
              "<br />Loading filtered data...<p>').".
              "load('/nagrestconf/".SCRIPTNAME."?tab=".$id."&sbulkbtns=1').".
              "dialog('open'); ";
        print '} );';
        print '</script>';
        print '<hr />';
    }

    # ------------------------------------------------------------------------
    function hosts_bulktools_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort;

        # Show the dialog buttons

        if( isset( $query_str['sbulkbtns'] )) {
            send_hosts_bulktools_dlg_content( );
        }

        # Run the REST commands

        if( isset( $query_str['sbulkapply'] )) {
            bulk_modify_hosts_using_REST( );
        }

        # Save the query_string
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        $_SESSION['query_str'] = $query_str;
    }

    # ------------------------------------------------------------------------
    function add_hosts_bulktools_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Defines a jQuery dialog ready for showing later.
    # This is just a 'div' and a 'script'.

        # 'Add New Host' dialog box div
        print "<div id=\"bulkhsttooldlg\" ".
              " title=\"Apply Host Changes in Bulk\"></div>";
        print '<script>';
        # Addtimeperiod button
        print 'var applychanges = function() { ';
        # Clear notification
        print ' $(".flash.error").hide();';
        print ' $(".flash.notice").hide();';
        # Disable button
        print '$( ".ui-button:contains(Apply Changes)" )';
        print '.button( "option", "disabled", true );';

        # Show spinner
        print '$("#sbulktextarea").css("background-image",';
        print "\"url('images/working.gif')\");";

        # Do REST stuff
        print ' var active = $("#bulktoolstabs").tabs("option","active");';
        print ' var form;';
        print ' switch(active){'.
              '  case 0: form="#hstbulkform1"; break;'.
              '  case 1: form="#hstbulkform2"; break;'.
              '  case 2: form="#hstbulkform3"; break;'.
              '  case 3: form="#hstbulkform4"; break;'.
              ' };';
        print ' var query_string=$(form).serialize();';
        print ' query_string+="&active_tab="+active;';
        print ' $.getJSON( $(form).attr("action"), '; # <- url
        print ' query_string,';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html("Success").show();';
        print '    $("#sbulktextarea").val(message);';
        print '    $("#sbulktextarea").show();';
        $url = \create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html("Fail").show();';
        print '    $("#sbulktextarea").val(message);';
        print '    $("#sbulktextarea").show();';
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  }';
        # Enable button
        print '$( ".ui-button:contains(Apply Changes)" )';
        print '.button( "option", "disabled", false );';
        # Disable spinner
        print ' $("#sbulktextarea").css("background-image","none");';
        # Scroll to bottom
        print ' var a = $("#sbulktextarea");';
        print ' a.scrollTop( a[0].scrollHeight - a.height() );';
        print ' });';

        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#bulkhsttooldlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#bulkhsttooldlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Apply Changes": applychanges, "Close": cancel }';
        print ', modal : true';
        # TODO print ', resizable : true';
        print ', resizable : false';
        print ' } );';
        print '</script>';
    }

    /***********************************************************************
     *
     * PLUGIN FUNCTIONS
     *
     ***********************************************************************
     */
 
    # ------------------------------------------------------------------------
    function get_and_sort_hosts( $sort="hostgroup", $filter="", $column=1 ) {
    # ------------------------------------------------------------------------
    # This function is copied from index.php
    # then one line removed and one line added as shown below:

        $request2 = new \RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"'.$column.'","filter":"'.urlencode($filter).'"}', 'GET');
        set_request_options( $request2 );
        $request2->execute();
        $hlist = json_decode( $request2->getResponseBody(), true );

        # Next line commented out from the original
        #parse_str( $_SERVER['QUERY_STRING'], $query_str );
        # Next line added
        $query_str = $_SESSION['query_str'];
        # No other changes

        if( isset( $query_str['hfilter'] ) ) {
            $hostregex = $query_str['hfilter'];
        } else {
            $hostregex = ".*";
        }

        $a=array();
        foreach( $hlist as $list ) {
            foreach( $list as $line ) {
                extract( $line );
            }
            if( preg_match("/$hostregex/i",$name) == 0 ) continue;
            $b['name']=$name;
            $b['alias']=$alias;
            $b['ipaddress']=$ipaddress;
            $b['template']=$template;
            $b['hostgroup']=$hostgroup;
            $b['contact']=$contact;
            $b['contactgroups']=$contactgroups;
            $b['activechecks']=$activechecks;
            $b['servicesets']=$servicesets;
            $b['disable']=$disable;
            $a[]=$b;
        }
        define( 'SORT4', $sort );
        if( $sort == "ipaddress" ) {
            usort($a, function ($a1,$a2) {
                $n = sscanf( $a1[SORT4], "%d.%d.%d.%d",$a,$b,$c,$d );
                $a3 = sprintf("%03d%03d%03d%03d",$a,$b,$c,$d);
                $n = sscanf( $a2[SORT4], "%d.%d.%d.%d",$a,$b,$c,$d );
                $a4 = sprintf("%03d%03d%03d%03d",$a,$b,$c,$d);
                return $a3>$a4;
                } );
        } else {
            usort($a, function ($a1,$a2) { return $a1[SORT4]>$a2[SORT4]; } );
        }

        return $a;
    }

    # ------------------------------------------------------------------------
    function send_hosts_bulktools_dlg_content( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to be added to jquery dialog.

        $qs = $_SESSION['query_str'];
        $hgfilter = $qs['hgfilter'];
        if( empty( $hgfilter ) || $hgfilter == "all" ) {
            $a = get_and_sort_hosts( $sort="name" );
        } else {
            $a = get_and_sort_hosts( $sort="name", $filter=$hgfilter, $column=5 );
        }

        $size = count($a);

        $id = spi_get_tab_idx();

        # START FIELDSET

        ###:TABS
        print '<div id="bulktoolstabs">';
        print '<ul>';
        print '<li><a href="#hstmodify"><span>Modify Hosts</span></a></li>';
        print '<li><a href="#hstdelete"><span>Delete Hosts</span></a></li>';
        print '<li><a href="#hstadd"><span>Add Hosts</span></a></li>';
        print '<li><a href="#hstrefresh"><span>Refresh Hosts</span></a></li>';
        print '</ul>';

        ###:TAB1
        print '<div id="hstmodify">';
        print '<form id="hstbulkform1" '.
              'name="hstbulkform1" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        print '">';
        print '<fieldset>';
        print '<p style="font-weight: bold;text-align: center;" id="willaffect">';
        $s=""; if( $size != 1 ) $s="s";
        print 'Any changes made here will apply to '.$size." host$s.";
        #print 'Any changes made here will apply to ';
        print '</p><br />';
        #print '<script>$("#willaffect").append( $("#hoststable > p:nth-child(1)").html() );</script>';
        # Field to change
        print '<p>';
        print '<label for="field">Field to change *</label>';
        print '<select class="field" id="field" name="field" required="required">';
            print '<option value="command">command</option>';
            print '<option value="template">template</option>';
            print '<option value="hostgroup">hostgroup</option>';
            print '<option value="contacts">contacts</option>';
            print '<option value="contactgroups">contactgroups</option>';
            print '<option value="ipaddress">ipaddress</option>';
            #print '<option value="activechecks">activechecks</option>';
            print '<option value="maxcheckattempts">maxcheckattempts</option>';
            print '<option value="servicesets">servicesets</option>';
            print '<option value="disable">disable</option>';
        print '</select>';
        print '</p>';
        print '<script>';
        print '$("#field").bind("click", function(){';
        print 'var selected=$("#field").val();';
        print 'var selectedaction=$("#action").val();';
        print 'if( selected == "command" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "template" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "hostgroup" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "contacts" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "ipaddress" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        #print 'if( selected == "activechecks" ){'.
        #      '  $("#chactionp").show();'.
        #      '  if( selectedaction != "remove" ) $("#textp").show();'.
        #      '  $("#tristatep").hide();'.
        #      '}';
        print 'if( selected == "servicesets" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "maxcheckattempts" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "disable" ){'.
              '  $("#chactionp").hide();'.
              '  $("#textp").hide();'.
              '  $("#tristatep").show();'.
              '  $("#action").val("replace");'.
              '}';
        print '});';
        print '</script>';
        # Change action
        print '<p id="chactionp">';
        print '<label for="action">Change action *</label>';
        print '<select class="field" id="action" name="action" required="required">';
            print '<option value="replace">replace</option>';
        #    print '<option value="prepend">prepend</option>';
        #    print '<option value="append">append</option>';
        #    print '<option value="append">regex</option>';
            print '<option value="remove">remove</option>';
        print '</select>';
        print '</p>';
        print '<script>';
        print '$("#action").bind("click", function(){';
        print 'var selected=$("#action").val();';
        print 'if( selected == "remove" ){'.
              '  $("#textp").hide();'.
              '} else {'.
              '  $("#textp").show();'.
              '}});';
        print '</script>';
        # Tristate
        print '<p style="display: none;" id="tristatep">';
        print '<label for="disable">Status *</label>';
        print '<select class="field" id="disable" name="disable" required="required">';
            print '<option value="0">0: enabled</option>';
            print '<option value="1">1: disabled</option>';
            print '<option value="2">2: testing</option>';
        print '</select>';
        print '</p>';
        # Text
        print '<p id="textp">';
        print '<label for="text">Text *</label>';
        print '<input class="field" type="text" id="text" name="text" required="required" />';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '</div>';

        ###:TAB2
        print '<div id="hstdelete">';
        print '<form id="hstbulkform2" '.
              'name="hstbulkform2" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        print '">';
        print '<fieldset>';
        print '<p style="font-weight: bold;text-align: center;" id="willaffect">';
        $s=""; if( $size != 1 ) $s="s";
        print 'Pressing \'Apply Changes\' will DELETE '.$size." host$s.";
        print '</p>';
        print '<p style="text-align: center; padding-top: 14px;" >';
        print '<input type="checkbox" id="bulkdelhosts"';
        print ' name="bulkdelhosts" ';
        print ' style="display: inline-block; vertical-align: middle;';
        print ' margin: 0 0 0 0; padding-top: 0px;" />';
        print '<label for="bulkdelhosts" style="display: inline-block;';
        print ' vertical-align: middle; float: none; width: auto;';
        print ' padding-left: 6px; margin: 0 0 0 0;';
        print ' padding-top: 0px;">Click to confirm deletion of ';
        print ' '.$size.' host'.$s.'.</label>';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '</div>';

        ###:TAB3
        print '<div id="hstadd">';
        print '<form id="hstbulkform3" '.
              'name="hstbulkform3" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        print '">';
        print '<fieldset>';
        print '<p><br />&nbsp;TODO - UNIMPLEMENTED</p>';
        print '</fieldset>';
        print '</form>';
        print '</div>';

        ###:TAB4
        print '<div id="hstrefresh">';
        print '<form id="hstbulkform4" '.
              'name="hstbulkform4" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        print '">';
        print '<fieldset>';
        print '<p style="font-weight: bold;text-align: center;" id="willaffect">';
        $s=""; if( $size != 1 ) $s="s";
        print 'Pressing \'Apply Changes\' will refresh '.$size." host$s.";
        print '</p>';
        print '<p style="padding-top: 10px; text-align: center;">';
        print 'Service sets will be reapplied to the selected hosts.';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '</div>';

        ###:TABS DIV END
        print '</div>';

        print '<script>';
        print '$( "#bulktoolstabs" ).tabs();';
        print '</script>';

        print '<hr />';
        print '<p>Log output:</p>';
        print '<textarea id="sbulktextarea" wrap="logical"';
        print 'readonly="true" >';
        print '</textarea>';
        print '<hr />';
        print "<p>Click 'Apply Changes' to make the bulk changes "; 
        print " or click 'Close' to cancel.</p>";
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 ); # <- Stop here - bypass any other actions.
    }

    # ------------------------------------------------------------------------
    function rest_replace( $query_str ) {
    # ------------------------------------------------------------------------

        $resp = array();
        $slist="";
        $field = $query_str["field"];
        $options = array();

        if( $field == "command" ||
            $field == "template" ||
            $field == "hostgroup" ||
            $field == "contacts" ||
            $field == "ipaddress" ||
            $field == "maxcheckattempts" ||
            $field == "servicesets" ||
            $field == "contactgroups"
        ) {

            $qs = $_SESSION['query_str'];
            $hgfilter = $qs['hgfilter'];
            if( empty( $hgfilter ) || $hgfilter == "all" ) {
                $a = get_and_sort_hosts( $sort="name" );
            } else {
                $a = get_and_sort_hosts( $sort="name", $filter=$hgfilter,
                    $column=5 );
            }

            $n = 0;

            foreach( $a as $item ) {
                $n++;
                unset( $options );
                $options["folder"] = FOLDER;
                $options["name"] = $item["name"];
                $options[$field] = $query_str["text"];

                $json = json_encode( $options );

                # Do the REST edit hosts request
                $request = new \RestRequest(
                  RESTURL.'/modify/hosts',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $list = json_decode( $request->getResponseBody(), true );

                foreach( $list as $slist_item )
                    $slist .= "$n. Modifying " . $item["name"] .
                              " : " . $slist_item . "\n";

                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;
            }

        } elseif ( $query_str["field"] == "disable") {

            // $query_str["disable"] == 0,1 or 2

            $qs = $_SESSION['query_str'];
            $hgfilter = $qs['hgfilter'];
            if( empty( $hgfilter ) || $hgfilter == "all" ) {
                $a = get_and_sort_hosts( $sort="name" );
            } else {
                $a = get_and_sort_hosts( $sort="name", $filter=$hgfilter,
                    $column=5 );
            }

            $n = 0;

            foreach( $a as $item ) {

                # Disable Services attached to this host

                $list = \get_and_sort_services( $item["name"] );

                $options = array();

                if( $query_str["disable"] != 0 ) {
                    # If disabling, disable services first
                    $options = array();
                    $options["name"] = $item["name"];
                    $options["folder"] = FOLDER;
                    $options[$field] = $query_str["disable"];

                    foreach( $list as $item2 ) {
                        $options["svcdesc"] = $item2['svcdesc'];
                        $json = json_encode( $options );
                        $request = new \RestRequest(
                          RESTURL.'/modify/services',
                          'POST',
                          'json='.$json
                        );
                        set_request_options( $request );
                        $request->execute();
                        $output = json_decode( $request->getResponseBody(),
                                               true );

                        foreach( $output as $slist_item )
                            $slist .= "    Modifying " . $item["name"] .
                                      " -> " . urldecode($item2['svcdesc']) .
                                      " : " .  $slist_item . "\n";

                        $resp = $request->getResponseInfo();
                        if( $resp["http_code"] != 200 ) break;
                        ### Check $slist->http_code ###
                    }
                }

                # Disable Host

                $n++;
                unset( $options );
                $options["folder"] = FOLDER;
                $options["name"] = $item["name"];
                $options[$field] = $query_str["disable"];

                $json = json_encode( $options );

                # Do the REST edit hosts request
                $request = new \RestRequest(
                  RESTURL.'/modify/hosts',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $output = json_decode( $request->getResponseBody(), true );

                foreach( $output as $slist_item )
                    $slist .= "$n. Modifying " . $item["name"] .
                              " : " . $slist_item . "\n";

                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;

                if( $query_str["disable"] == 0 ) {
                    # If enabling, enable services last
                    $options = array();
                    $options["name"] = $item["name"];
                    $options["folder"] = FOLDER;
                    $options[$field] = $query_str["disable"];

                    foreach( $list as $item2 ) {
                        $options["svcdesc"] = $item2['svcdesc'];
                        $json = json_encode( $options );
                        $request = new \RestRequest(
                          RESTURL.'/modify/services',
                          'POST',
                          'json='.$json
                        );
                        set_request_options( $request );
                        $request->execute();
                        $output = json_decode( $request->getResponseBody(),
                                               true );

                        foreach( $output as $slist_item )
                            $slist .= "    Modifying " . $item["name"] .
                                      " -> " . urldecode($item2['svcdesc']) .
                                      " : " .  $slist_item . "\n";

                        $resp = $request->getResponseInfo();
                        if( $resp["http_code"] != 200 ) break;
                        ### Check $slist->http_code ###
                    }
                }

            }
        }

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit(0);

    }

    # ------------------------------------------------------------------------
    function rest_remove( $query_str ) {
    # ------------------------------------------------------------------------

        $resp = array();
        $slist="";
        $field = $query_str["field"];
        $options = array();

        if( $field == "command" ||
            $field == "template" ||
            $field == "hostgroup" ||
            $field == "contacts" ||
            $field == "ipaddress" ||
            $field == "maxcheckattempts" ||
            $field == "servicesets" ||
            $field == "contactgroups"
        ) {

            $qs = $_SESSION['query_str'];
            $hgfilter = $qs['hgfilter'];
            if( empty( $hgfilter ) || $hgfilter == "all" ) {
                $a = get_and_sort_hosts( $sort="name" );
            } else {
                $a = get_and_sort_hosts( $sort="name", $filter=$hgfilter,
                    $column=5 );
            }

            $n = 0;

            foreach( $a as $item ) {
                $n++;
                unset( $options );
                $options["folder"] = FOLDER;
                $options["name"] = $item["name"];
                $options[$field] = "-";

                $json = json_encode( $options );

                # Do the REST edit hosts request
                $request = new \RestRequest(
                  RESTURL.'/modify/hosts',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $list = json_decode( $request->getResponseBody(), true );

                foreach( $list as $slist_item )
                    $slist .= "$n. Modifying " . $item["name"] .
                              " : " . $slist_item . "\n";

                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;
            }

        } else {
            $retval = array();
            $retval["message"] = "Cannot remove data from this field";
            $retval["code"] = 400;
            print( json_encode( $retval ) );
            exit(0);
        }

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit(0);

    }

    # ------------------------------------------------------------------------
    function rest_modify_hosts( $query_str ) {
    # ------------------------------------------------------------------------

        /*
        $retval = array();
        $retval["message"] = json_encode( $query_str );
        $retval["code"] = 200;
        sleep( 2 );
        print( json_encode( $retval ) );
        exit(0);
        */

        if( $query_str["action"] == "replace") {

            rest_replace( $query_str );

        } elseif ( $query_str["action"] == "prepend") {

        } elseif ( $query_str["action"] == "append") {

        } elseif ( $query_str["action"] == "regex") {

        } elseif ( $query_str["action"] == "remove") {

            rest_remove( $query_str );

        }

        $retval = array();
        $retval["message"] = "Sorry, this action is not implemented.";
        $retval["code"] = 200;
        print( json_encode( $retval ) );
        exit(0);
    }

    # ------------------------------------------------------------------------
    function rest_delete_hosts( $query_str ) {
    # ------------------------------------------------------------------------

        if( ! isset( $query_str["bulkdelhosts"] ) ) {
            $retval["message"] = "Error: Deletion was not confirmed.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        $qs = $_SESSION['query_str'];
        $hgfilter = $qs['hgfilter'];
        if( empty( $hgfilter ) || $hgfilter == "all" ) {
            $a = get_and_sort_hosts( $sort="name" );
        } else {
            $a = get_and_sort_hosts( $sort="name", $filter=$hgfilter,
                $column=5 );
        }

        $n = 0;

        foreach( $a as $item ) {
            $n++;
            $svcs = get_and_sort_services( $item["name"] );

            if( sizeof($svcs) > 0 ) { 
                # Delete Services attached to this host
                $options = array();
                $options["name"] = $item["name"];
                $options["svcdesc"] = '.*';
                $options["folder"] = FOLDER;
                $json = json_encode( $options );
                $request = new \RestRequest(
                  RESTURL.'/delete/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();

                $list = json_decode( $request->getResponseBody(), true );
                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;
            }

            #$slist = json_decode( $request->getResponseBody(), true );
            ### Check $slist->http_code ###

            # Delete Host

            unset( $resp );
            unset( $request );
            unset( $options );

            $options["folder"] = FOLDER;
            $options["name"] = $item["name"];

            $json = json_encode( $options );

            # Do the REST edit svcgroup request
            $request = new \RestRequest(
              RESTURL.'/delete/hosts',
              'POST',
              'json='.$json
            );
            set_request_options( $request );
            $request->execute();
            $list = json_decode( $request->getResponseBody(), true );

            foreach( $list as $slist_item )
                $slist .= "$n. Deleting " . $item["name"] . 
                          " : " . $slist_item . "\n";

            $resp = $request->getResponseInfo();
            if( $resp["http_code"] != 200 ) break;
        }

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit(0);

    }

    # ------------------------------------------------------------------------
    function rest_refresh_hosts( $query_str ) {
    # ------------------------------------------------------------------------

        $qs = $_SESSION['query_str'];
        $hgfilter = $qs['hgfilter'];
        if( empty( $hgfilter ) || $hgfilter == "all" ) {
            $a = get_and_sort_hosts( $sort="name" );
        } else {
            $a = get_and_sort_hosts( $sort="name", $filter=$hgfilter,
                $column=5 );
        }

        $n = 0;

        foreach( $a as $item ) {
            $n++;
            $svcs = get_and_sort_services( $item["name"] );

            # Save host details
            $request = new \RestRequest(
            RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
            '"column":"1","filter":"'.$item["name"].'"}', 'GET');
            set_request_options( $request );
            $request->execute();
            $slist = json_decode( $request->getResponseBody(), true );

            $new_qs = array();
            $new_qs["folder"] = FOLDER;
            foreach( $slist[0] as $item2 ) {
                foreach( $item2 as $key => $val ) {
                    $new_qs[$key] = $val; 
                }
            }
            $newhostjson = json_encode( $new_qs );

            # Delete host
            if( sizeof($svcs) > 0 ) { 
                $option = array();
                $option["name"] = $item["name"];
                $option["svcdesc"] = '.*';
                $option["folder"] = FOLDER;
                $json = json_encode( $option );
                $request = new \RestRequest(
                  RESTURL.'/delete/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $slist = json_decode( $request->getResponseBody(), true );
                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;
                ### Check $slist->http_code ###
            }

            $option = array();
            $option["name"] = $item["name"];
            $option["folder"] = FOLDER;
            $json = json_encode( $option );
            # Do the REST add host request
            $request = new \RestRequest(
              RESTURL.'/delete/hosts',
              'POST',
              'json='.$json
            );
            set_request_options( $request );
            $request->execute();
            $slist = json_decode( $request->getResponseBody(), true );
            $resp = $request->getResponseInfo();
            if( $resp["http_code"] != 200 ) break;

            # Add host

            # Do the REST add host request
            $request2 = new \RestRequest(
              RESTURL.'/add/hosts',
              'POST',
              'json='.$newhostjson
            );
            set_request_options( $request2 );
            $request2->execute();
            $slist = json_decode( $request2->getResponseBody(), true );

            foreach( $slist as $slist_item )
                $list .= "$n. Refreshing " . $item["name"] . 
                          " : " . $slist_item . "\n";

            $resp = $request->getResponseInfo();
            if( $resp["http_code"] != 200 ) break;
        }

        # Return json
        $retval = array();
        $retval["message"] = $list;
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit(0);
    }

    # ------------------------------------------------------------------------
    function bulk_modify_hosts_using_REST( ) {
    # ------------------------------------------------------------------------

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        if( $query_str["active_tab"] == 0 )
            rest_modify_hosts( $query_str );

        if( $query_str["active_tab"] == 1 )
            rest_delete_hosts( $query_str );

        #if( $query_str["active_tab"] == 2 )
        #    rest_delete_hosts( $query_str );

        if( $query_str["active_tab"] == 3 )
            rest_refresh_hosts( $query_str );

        exit( 0 ); # <- Stop here - bypass any other actions.
    }

?>


