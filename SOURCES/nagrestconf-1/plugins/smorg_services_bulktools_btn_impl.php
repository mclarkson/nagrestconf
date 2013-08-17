<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\services_bulktools_btn;

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
    # Adds code to find the dialog div, bulksvctooldlg, add code to it, then
    # open the jQuery dialog.

        $id = spi_get_tab_idx();

        print '<input id="bulksvctool" type="button" value="Bulk Tools" />';
        print '<script>';
        print ' $("#bulksvctool").bind("click", function() {';
        print "$('#bulksvctooldlg').".
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
    function services_bulktools_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort;

        # Show the dialog buttons

        if( isset( $query_str['sbulkbtns'] )) {
            send_services_bulktools_dlg_content( );
        }

        # Run the REST commands

        if( isset( $query_str['sbulkapply'] )) {
            bulk_modify_services_using_REST( );
        }

        # Save the query_string
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        $_SESSION['query_str'] = $query_str;
    }

    # ------------------------------------------------------------------------
    function add_services_bulktools_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Defines a jQuery dialog ready for showing later.
    # This is just a 'div' and a 'script'.

        # 'Add New Host' dialog box div
        print "<div id=\"bulksvctooldlg\" ".
              " title=\"Apply Service Changes in Bulk\"></div>";
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
              #'  case 0: form="#svcbulkform1"; break;'.
              '  case 0: form="#svcbulkform1"; break;'.
              '  case 1: form="#svcbulkform2"; break;'.
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
        $url = \Smorg\services_tab\create_url( );
        print '    $("#servicestable").html("").';
        print '      load("'.$url.'&servicestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html("Fail").show();';
        print '    $("#sbulktextarea").val(message);';
        print '    $("#sbulktextarea").show();';
        print '    $("#servicestable").html("").';
        print '      load("'.$url.'&servicestable=true");';
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
              '$("#bulksvctooldlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#bulksvctooldlg" ).dialog( { ';
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
    function get_and_sort_services( $name, $sort="svcdesc" ) {
    # ------------------------------------------------------------------------
    # This function is copied from plugins-lib/smorg_services_tab_impl.php
    # then one line removed and one line added as shown below:

        $request = new \RestRequest(
          RESTURL.'/show/services?json='.
          '{"folder":"'.FOLDER.'","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        # Next line commented out from the original
        #parse_str( $_SERVER['QUERY_STRING'], $query_str );
        # Next line added
        $query_str = $_SESSION['query_str'];
        # No other changes

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
    function send_services_bulktools_dlg_content( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to be added to jquery dialog.

        #global $g_sort;
        #$a = \Smorg\services_tab\get_and_sort_services( '.*', $g_sort );
        $a = get_and_sort_services( '.*' );
        $size = count($a);

        $id = spi_get_tab_idx();

        # START FIELDSET

        ###:TABS
        print '<div id="bulktoolstabs">';
        print '<ul>';
        #print '<li><a href="#svcadd"><span>Add Services</span></a></li>';
        print '<li><a href="#svcmodify"><span>Modify Services</span></a></li>';
        print '<li><a href="#svcdelete"><span>Delete Services</span></a></li>';
        print '</ul>';

        ###:TAB1
        #print '<div id="svcadd">';
        #print '<form id="svcbulkform1" '.
        #      'name="svcbulkform1" method="get"';
        #print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        #print '">';
        #print '<fieldset>';
        #print '<p><br />&nbsp;TODO - UNIMPLEMENTED</p>';
        #print '</fieldset>';
        #print '</form>';
        #print '</div>';

        ###:TAB2
        print '<div id="svcmodify">';
        print '<form id="svcbulkform1" '.
              'name="svcbulkform1" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        print '">';
        print '<fieldset>';
        print '<p style="font-weight: bold;text-align: center;" id="willaffect">';
        $s=""; if( $size != 1 ) $s="s";
        print 'Any changes made here will apply to '.$size." service$s.";
        #print 'Any changes made here will apply to ';
        print '</p><br />';
        #print '<script>$("#willaffect").append( $("#servicestable > p:nth-child(1)").html() );</script>';
        # Field to change
        print '<p>';
        print '<label for="field">Field to change *</label>';
        print '<select class="field" id="field" name="field" required="required">';
            print '<option value="command">command</option>';
            print '<option value="template">template</option>';
            print '<option value="svcgroup">svcgroup</option>';
            print '<option value="contacts">contacts</option>';
            print '<option value="contactgroups">contactgroups</option>';
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
        print 'if( selected == "svcgroup" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "contacts" ){'.
              '  $("#chactionp").show();'.
              '  if( selectedaction != "remove" ) $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "contactgroups" ){'.
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

        ###:TAB3
        print '<div id="svcdelete">';
        print '<form id="svcbulkform2" '.
              'name="svcbulkform2" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbulkapply=1';
        print '">';
        print '<fieldset>';
        print '<p style="font-weight: bold;text-align: center;" id="willaffect">';
        $s=""; if( $size != 1 ) $s="s";
        print 'Pressing \'Apply Changes\' will DELETE '.$size." service$s.";
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '</div>';

        print '<script>';
        print '$( "#bulktoolstabs" ).tabs();';
        print '</script>';

        # END FIELDSET
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

        if( $field == "command" ||
            $field == "svcgroup" ||
            $field == "contacts" ||
            $field == "template" ||
            $field == "contactgroups"
        ) {

            $a = get_and_sort_services( '.*' );
            $n = 0;

            foreach( $a as $item ) {
                $n++;
                unset( $options );
                $options["folder"] = FOLDER;
                $options["name"] = $item["name"];
                $options["svcdesc"] = $item["svcdesc"];
                $options[$field] = $query_str["text"];

                $json = json_encode( $options );

                # Do the REST edit svcgroup request
                $request = new \RestRequest(
                  RESTURL.'/modify/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $list = json_decode( $request->getResponseBody(), true );

                foreach( $list as $slist_item )
                    $slist .= "$n. Modifying " . $item["name"] . " -> " . 
                              $item["svcdesc"] . " : " . $slist_item . "\n";

                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;
            }

        } elseif ( $query_str["field"] == "disable") {

            // $query_str["disable"] == 0,1 or 2

            $a = get_and_sort_services( '.*' );
            $n = 0;

            foreach( $a as $item ) {
                $n++;
                unset( $options );
                $options["folder"] = FOLDER;
                $options["name"] = $item["name"];
                $options["svcdesc"] = $item["svcdesc"];
                $options[$field] = $query_str["disable"];

                $json = json_encode( $options );

                # Do the REST edit svcgroup request
                $request = new \RestRequest(
                  RESTURL.'/modify/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $list = json_decode( $request->getResponseBody(), true );

                foreach( $list as $slist_item )
                    $slist .= "$n. Modifying " . $item["name"] . " -> " . 
                              $item["svcdesc"] . " : " . $slist_item . "\n";

                $resp = $request->getResponseInfo();
                if( $resp["http_code"] != 200 ) break;
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

        if( $field == "command" ||
            $field == "svcgroup" ||
            $field == "contacts" ||
            $field == "template" ||
            $field == "contactgroups"
        ) {

            $a = get_and_sort_services( '.*' );
            $n = 0;

            foreach( $a as $item ) {
                $n++;
                unset( $options );
                $options["folder"] = FOLDER;
                $options["name"] = $item["name"];
                $options["svcdesc"] = $item["svcdesc"];
                $options[$field] = "-";

                $json = json_encode( $options );

                # Do the REST edit svcgroup request
                $request = new \RestRequest(
                  RESTURL.'/modify/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
                $list = json_decode( $request->getResponseBody(), true );

                foreach( $list as $slist_item )
                    $slist .= "$n. Modifying " . $item["name"] . " -> " . 
                              $item["svcdesc"] . " : " . $slist_item . "\n";

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
    function rest_modify_services( $query_str ) {
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
    function rest_delete_services( $query_str ) {
    # ------------------------------------------------------------------------

        $a = get_and_sort_services( '.*' );
        $n = 0;

        foreach( $a as $item ) {
            $n++;
            unset( $options );
            $options["folder"] = FOLDER;
            $options["name"] = $item["name"];
            $options["svcdesc"] = $item["svcdesc"];

            $json = json_encode( $options );

            # Do the REST edit svcgroup request
            $request = new \RestRequest(
              RESTURL.'/delete/services',
              'POST',
              'json='.$json
            );
            set_request_options( $request );
            $request->execute();
            $list = json_decode( $request->getResponseBody(), true );

            foreach( $list as $slist_item )
                $slist .= "$n. Deleting " . $item["name"] . " -> " . 
                          $item["svcdesc"] . " : " . $slist_item . "\n";

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
    function bulk_modify_services_using_REST( ) {
    # ------------------------------------------------------------------------

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        if( $query_str["active_tab"] == 0 )
            rest_modify_services( $query_str );

        if( $query_str["active_tab"] == 1 )
            rest_delete_services( $query_str );

        exit( 0 ); # <- Stop here - bypass any other actions.
    }

?>

