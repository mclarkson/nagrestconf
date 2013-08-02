<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\services_bulktools_btn;

    define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');


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
    function add_services_bulktools_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Defines a jQuery dialog ready for showing later.
    # This is just a 'div' and a 'script'.

        # 'Add New Host' dialog box div
        print "<div id=\"bulkhsttooldlg\" ".
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
        print 'Any changes made here will apply to '.$size.' services.';
        #print 'Any changes made here will apply to ';
        print '</p><br />';
        #print '<script>$("#willaffect").append( $("#servicestable > p:nth-child(1)").html() );</script>';
        # Field to change
        print '<p>';
        print '<label for="field">Field to change *</label>';
        print '<select class="field" id="field" name="field" required="required">';
            print '<option value="command">command</option>';
            print '<option value="svcgroup">svcgroup</option>';
            print '<option value="contacts">contacts</option>';
            print '<option value="contactgroups">contactgroups</option>';
            print '<option value="disable">disable</option>';
        print '</select>';
        print '</p>';
        print '<script>';
        print '$("#field").bind("click", function(){';
        print 'var selected=$("#field").val();';
        print 'if( selected == "command" ){'.
              '  $("#chactionp").show();'.
              '  $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "svcgroup" ){'.
              '  $("#chactionp").show();'.
              '  $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "contacts" ){'.
              '  $("#chactionp").show();'.
              '  $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "contactgroups" ){'.
              '  $("#chactionp").show();'.
              '  $("#textp").show();'.
              '  $("#tristatep").hide();'.
              '}';
        print 'if( selected == "disable" ){'.
              '  $("#chactionp").hide();'.
              '  $("#textp").hide();'.
              '  $("#tristatep").show();'.
              '}';
        print '});';
        print '</script>';
        # Change action
        print '<p id="chactionp">';
        print '<label for="action">Change action *</label>';
        print '<select class="field" id="action" name="action" required="required">';
            print '<option value="replace">replace</option>';
            print '<option value="prepend">prepend</option>';
            print '<option value="append">append</option>';
            print '<option value="remove">remove</option>';
        print '</select>';
        print '</p>';
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
        print '<p><br />&nbsp;TODO - UNIMPLEMENTED</p>';
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
    function bulk_modify_services_using_REST( ) {
    # ------------------------------------------------------------------------

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        $retval = array();
        $retval["message"] = json_encode( $query_str );
        $retval["code"] = 200;

        sleep( 2 );
        print( json_encode( $retval ) );

        exit( 0 ); # <- Stop here - bypass any other actions.
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
    function initialize_plugin() {
    # ------------------------------------------------------------------------

        #
        # Don't do any more if this is not the 'Services' tab.
        #

        if( spi_get_tab_name( spi_get_tab_idx() ) != 'services' ) return;

        #
        # So, this is the 'Services' tab - add callbacks and
        # include the implementation using plugins_load().
        #

        #plugins_load( "plugins-lib/smorg_services_tab_impl.php" );

        spi_add_action( 'button', NS . 'button_html' );
        spi_add_action( 'dlgdiv', NS . 'add_services_bulktools_dlg_div' );
        spi_add_action( 'action', NS . 'services_bulktools_page_actions' );
    }
 
    /***********************************************************************
     *
     * SETUP CALLBACKS
     *
     ***********************************************************************
     */

    session_start();
    spi_add_action( 'init', NS . 'initialize_plugin' );

?>
