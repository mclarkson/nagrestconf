<?php

    /***********************************************************************
     *
     * DEFINE THE NAMESPACE
     *
     ***********************************************************************
     */

    namespace Smorg\backup_btn;

    /***********************************************************************
     *
     * PLUGIN CALLBACK FUNCTIONS
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function backup_button_html( ) {
    # ------------------------------------------------------------------------
    # HTML code to show the 'Bulk Tools' button to the left pane.
    # Adds code to find the dialog div, backupdlg, add code to it, then
    # open the jQuery dialog.

        $id = spi_get_tab_idx();

        print '<input id="backuptool" type="button" value="Backup/Restore" />';
        print '<script>';
        print ' $("#backuptool").bind("click", function() {';
        print "$('#backupdlg').".
              "html('<p style=\"font-weight: bold;text-align: center;".
              "padding-top: 14px;\">".
              "<br />Loading data...<p>').".
              "load('/nagrestconf/".SCRIPTNAME."?tab=".$id."&sbackupbtns=1').".
              "dialog('open'); ";
        print '} );';
        print '</script>';
        print '<hr />';
    }

    # ------------------------------------------------------------------------
    function backup_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        # Show the dialog buttons

        if( isset( $query_str['sbackupbtns'] )) {
            send_backup_dlg_content( );
        }

        # Run the REST commands

        if( isset( $query_str['sbackupapply'] )) {
            backup_restore_using_REST( );
        }
    }

    # ------------------------------------------------------------------------
    function add_backup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Defines a jQuery dialog ready for showing later.
    # This is just a 'div' and a 'script'.

        # 'Add New Host' dialog box div
        print "<div id=\"backupdlg\" ".
              " title=\"Configuration Backup or Restore\"></div>";
        print '<script>';
        # Addtimeperiod button
        print 'var apply = function() { ';
        # Clear notification
        print ' $(".flash.error").hide();';
        print ' $(".flash.notice").hide();';
        # Disable button
        print '$( ".ui-button:contains(Apply)" )';
        print '.button( "option", "disabled", true );';

        # Do REST stuff
        print ' var active = $("#backuptabs").tabs("option","active");';
        print ' var form;';
        print ' function completeHandler(){alert("file uploaded");}';
        print ' function errorHandler(){alert("error");}';
        print ' function progressHandlingFunction(){alert("progress");}';
        print ' switch(active){'.
              '  case 0: form="#backupform1"; break;'.
              '  case 1: form="#backupform2"; break;'.
              ' };';
        print ' var query_string=$(form).serialize();';
        print ' query_string+="&active_tab="+active;';
        print ' query_string+="&filename="+$("#file2").val();';
        print ' $.getJSON( $(form).attr("action"), '; # <- url
        print ' query_string,';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  var filename = response.filename;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html("Success: "+message).show();';
        print '    if( filename != null )';
        print '    window.location.href = "/nagrestconf/download/"+filename;';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html("Fail: "+message).show();';
        print '  }';
        # Enable button
        print '$( ".ui-button:contains(Apply)" )';
        print '.button( "option", "disabled", false );';
        print ' });';

        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#backupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#backupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+20" }';
        print ', buttons : { "Apply": apply, "Close": cancel }';
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
    function send_backup_dlg_content( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to be added to jquery dialog.

        $id = spi_get_tab_idx();

        # START FIELDSET

        ###:TABS
        print '<div id="backuptabs">';
        print '<ul>';
        print '<li><a href="#backup"><span>Backup</span></a></li>';
        print '<li><a href="#restore"><span>Restore</span></a></li>';
        print '</ul>';

        ###:TAB1
        print '<div id="backup">';
        print '<form id="backupform1" '.
              'name="backupform1" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbackupapply=1';
        print '">';
        print '<fieldset>';
        print '<p style="font-weight: bold;text-align: center;">';
        print 'Choose which tables to BACKUP.';
        print '</p>';
        print '<p style="font-weight: normal;text-align: center;">';
        print 'The selected tables will be packaged and sent to your Browser.';
        print '</p><br />';
        # Hosts
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="hosts"';
        print ' id="hosts" style="display: inline;float: none;';
        print ' vertical-align: bottom;"/>';
        print '<label for="hosts" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">Hosts</label>';
        print '</p>';
        # Services
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="services"';
        print ' id="services" style="display: inline;float: none;';
        print ' vertical-align: bottom; margin-left:6px"/>';
        print '<label for="services" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">Services</label>';
        print '</p><br />';
        # Servicesets
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="servicesets"';
        print ' id="servicesets" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="servicesets" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">Service Sets</label>';
        print '</p><br />';
        # Host template
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="hosttemplates"';
        print ' id="hosttemplates" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="hosttemplates" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Host Templates</label>';
        print '</p>';
        # Service template
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="servicetemplates"';
        print ' id="servicetemplates" style="display: inline;float: none;';
        print ' vertical-align: bottom;"/ checked >';
        print '<label for="servicetemplates" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Service Templates</label>';
        print '</p>';
        # Contacts
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="contacts"';
        print ' id="contacts" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="contacts" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Contacts</label>';
        print '</p>';
        # Contact groups
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="contactgroups"';
        print ' id="contactgroups" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="contactgroups" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Contact Groups</label>';
        print '</p>';
        # Host groups
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="hostgroups"';
        print ' id="hostgroups" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="hostgroups" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Host Groups</label>';
        print '</p>';
        # Service groups
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="servicegroups"';
        print ' id="servicegroups" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="servicegroups" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Service Groups</label>';
        print '</p>';
        # Commands
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="commands"';
        print ' id="commands" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="commands" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Commands</label>';
        print '</p><br />';
        # Time periods
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="timeperiods"';
        print ' id="timeperiods" style="display: inline;float: none;';
        print ' vertical-align: bottom;" checked />';
        print '<label for="timeperiods" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Time Periods</label>';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '</div>';

        ###:TAB2
        print '<div id="restore">';
        print '<form id="uploadfileform2" action=""';
        print ' enctype="multipart/form-data" method="post" ';
        #print ' action="/nagrestconf/upload_file.php';
        print '">';
        print '<fieldset>';
        # Text
        print '<p style="font-weight: bold;text-align: center">';
        print 'Restore tables from a backup package.';
        print '</p>';
        print '<p style="text-align: center; padding-top: 8px;';
        print ' padding-bottom: 12px;">';
        print 'Upload the file package then choose the tables to restore.';
        print '</p>';
        print '<p>';
        print '<p style="display: inline; margin-left: 50px;"';
        print '>Backup file to upload *</p>';
        print '<input type="file" id="file2" name="file" required="required"';
        print ' style="height: auto;display: inline-block; margin-left: 10px;" />';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        # Backup Form
        print '<form id="backupform2" '.
              'name="backupform2" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab='.$id.'&sbackupapply=1';
        print '">';
        print '<fieldset>';
        # Hosts
        print '<p style="display: block; margin-left: 90px;';
        print ' padding-top: 6px; margin-top: -6px;">';
        #print ' padding-top: 0px; margin-top: -6px;">';
        #print '<input type="checkbox" name="overwrite"';
        #print ' id="overwrite" style="display: inline;float: none;';
        #print ' vertical-align: bottom;" />';
        #print '<label for="overwrite" style="display:inline;float:none;';
        #print ' vertical-align:bottom; margin-left:6px">';
        #print 'Overwrite existing entries.</label>';
        print 'No existing entries will be overwritten.';
        print '</p><br />';
        #
        print '<p style="display: inline; margin-left: 50px; padding-top:8px;"';
        print '>Tables to restore:</p><br />';
        # Hosts
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rhosts"';
        print ' id="rhosts" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rhosts" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">Hosts</label>';
        print '</p>';
        # Services
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="rservices"';
        print ' id="rservices" style="display: inline;float: none;';
        print ' vertical-align: bottom; margin-left:6px" disabled />';
        print '<label for="rservices" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">Services</label>';
        print '</p><br />';
        # Servicesets
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rservicesets"';
        print ' id="rservicesets" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rservicesets" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">Service Sets</label>';
        print '</p><br />';
        # Host template
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rhosttemplates"';
        print ' id="rhosttemplates" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rhosttemplates" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Host Templates</label>';
        print '</p>';
        # Service template
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="rservicetemplates"';
        print ' id="rservicetemplates" style="display: inline;float: none;';
        print ' vertical-align: bottom;"/ disabled >';
        print '<label for="rservicetemplates" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Service Templates</label>';
        print '</p>';
        # Contacts
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rcontacts"';
        print ' id="rcontacts" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rcontacts" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Contacts</label>';
        print '</p>';
        # Contact groups
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="rcontactgroups"';
        print ' id="rcontactgroups" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rcontactgroups" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Contact Groups</label>';
        print '</p>';
        # Host groups
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rhostgroups"';
        print ' id="rhostgroups" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rhostgroups" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Host Groups</label>';
        print '</p>';
        # Service groups
        print '<p style="display: inline-block; margin-left:14px;">';
        print '<input type="checkbox" name="rservicegroups"';
        print ' id="rservicegroups" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rservicegroups" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Service Groups</label>';
        print '</p>';
        # Commands
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rcommands"';
        print ' id="rcommands" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rcommands" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Commands</label>';
        print '</p><br />';
        # Time Periods
        print '<p style="display: inline-block; margin-left: 90px;">';
        print '<input type="checkbox" name="rtimeperiods"';
        print ' id="rtimeperiods" style="display: inline;float: none;';
        print ' vertical-align: bottom;" disabled />';
        print '<label for="rtimeperiods" style="display:inline;float:none;';
        print ' vertical-align:bottom; margin-left:6px">';
        print 'Time Periods</label>';
        print '</p>';
        print '<input type="hidden" name="runpack_dir" id="runpack_dir" />';

        ###:TABS DIV END
        print '</div>';

        print '<script>';
        print '$( "#backuptabs" ).tabs();';
        print '</script>';

        #print "<br /><p style=\"text-align:center;\">Click 'Apply' to start the operation";
        #print " or click 'Close' to cancel.</p>";
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus();
               $("#file2").AjaxFileUpload({
                   action: "/nagrestconf/scripts/restore.php",
                   onComplete: function(filename, response) {
                       if( response.code != 200 ){
                           var msg = "Fail: \'"+response.error+"\'";
                           $(".flash.notice").hide();
                           $(".flash.error").html(msg).show();
                       } else {
                           var msg = "Success: Uploaded \'"+response.name+"\'";
                           $("#runpack_dir").val(
                               response.unpack_dir );
                           $("#file2").attr("disabled",true)
                           for( tbl in response.tables )
                           {
                               var id="r"+response.tables[tbl];
                               $( "#"+id ).attr("disabled",false);
                           }
                           $(".flash.error").hide();
                           $(".flash.notice").html(msg).show();
                       }
                   }
               });'.
              '</script>';

        exit( 0 ); # <- Stop here - bypass any other actions.
    }

    # ------------------------------------------------------------------------
    function backup_table( $tbl_name, $tempdir, &$errstring ) {
    # ------------------------------------------------------------------------

        $request = new \RestRequest(
            RESTURL.'/show/'.$tbl_name.'?json={"folder":"'.FOLDER.'"}',
            'GET');
        set_request_options( $request );
        $request->execute();

        if( file_put_contents( $tempdir."/".$tbl_name,
            $request->getResponseBody() ) === FALSE )
        {
            $errstring = "Could not write to system storage.";
            return 1; /* error */
        }


        return 0;
    }

    # ------------------------------------------------------------------------
    function rest_backup( $query_str ) {
    # ------------------------------------------------------------------------

        $overwrite=0;
        $errstring="";
        $message="Backup completed.";

        if( isset( $query_str["overwrite"] ) ) {
            $overwrite = 1;
        }

        # Create a temp directory to store the files in
        $tempdir = tempnam(sys_get_temp_dir(),'');
        if( file_exists($tempdir) )
            unlink( $tempdir );
        mkdir( $tempdir );
        if( ! is_dir( $tempdir )) {
            $retval = array();
            $retval["message"] = "Could not create '$tempdir'";
            $retval["code"] = 400;
            print( json_encode($retval) );
            exit( 0 );
        }

        # Write the files into $tempdir

        $tbls = array( "hosts", "services", "servicesets",
                       "hosttemplates", "servicetemplates",
                       "contacts", "contactgroups", "hostgroups",
                       "servicegroups", "commands", "timeperiods" );

        foreach( $tbls as $tbl ) {
            if( isset( $query_str[$tbl] ) ) {
                if( backup_table( $tbl, $tempdir, $errstring ) > 0 ) {
                    $retval = array();
                    $retval["message"] = $errstring;
                    $retval["code"] = $resp["http_code"];
                    print( json_encode( $retval ) );
                    exit( 0 );
                }
            }
        }

        $d = date( "Ymd_His" );
        $filename = "nagcfgbak_$d.tgz";
        $cmd = "tar czf download/$filename -C $tempdir .";
        exec( $cmd . ' >/dev/stdout 2>&1', $output, $exit_status );

        # Delete the temporary directory
        foreach( new \RecursiveIteratorIterator(
                 new \RecursiveDirectoryIterator(
                     $tempdir, \FilesystemIterator::SKIP_DOTS),
                     \RecursiveIteratorIterator::CHILD_FIRST) as $path)
        {
            $path->isFile() ? unlink($path->getPathname())
                : rmdir($path->getPathname());
        }
        rmdir($tempdir);

        $retval = array();
        $retval["message"] = $message;
        $retval["filename"] = $filename;
        $retval["code"] = 200;
        print( json_encode( $retval ) );
    }

    # ------------------------------------------------------------------------
    function restore_table( $tblnam, $tempdir, &$errstring ) {
    # ------------------------------------------------------------------------

        $items = json_decode( file_get_contents($tempdir."/".$tblnam), true );

        #file_put_contents( "/tmp/bob", print_r($items,true) );

        foreach( $items as $moreitems ) {
            $new_qs = array();
            $new_qs["folder"] = FOLDER;
            foreach( $moreitems as $item ) {
                foreach( $item as $key => $val ) {
                    if( empty($val) ) continue;
                    $new_qs[$key] = $val;
                }
            }
            #file_put_contents("/tmp/bob", print_r($new_qs,true),FILE_APPEND);

            if( $tblnam == "hosts" )
            {
                $oldservicesets = $new_qs["servicesets"];
                $new_qs["servicesets"] = "";
            }
            elseif( $tblnam == "services" )
            {
                $new_qs["svcdesc"] = strtr( $new_qs["svcdesc"],
                    array( '"' => '\"',
                           '%22' => '%5C%22' ) );
                $new_qs["command"] = strtr( $new_qs["command"],
                    array( '"' => '\"',
                           '%22' => '%5C%22' ) );
            }
            elseif( $tblnam == "servicesets" )
            {
                $new_qs["svcdesc"] = strtr( $new_qs["svcdesc"],
                    array( '"' => '\"',
                           '%22' => '%5C%22' ) );
                $new_qs["command"] = strtr( $new_qs["command"],
                    array( '"' => '\"',
                           '%22' => '%5C%22' ) );
            }
            elseif( $tblnam == "commands" )
            {
                $new_qs["name"] = strtr( $new_qs["name"],
                    array( '"' => '\"',
                           '%22' => '%5C%22' ) );
                $new_qs["command"] = strtr( $new_qs["command"],
                    array( '"' => '\"',
                           '%22' => '%5C%22' ) );
            }

            $json = json_encode( $new_qs );
            $request = new \RestRequest(
              RESTURL.'/add/'.$tblnam,
              'POST',
              'json='.$json
            );
            set_request_options( $request );
            $request->execute();

            if( $tblnam == "hosts" )
            {
                $new_qs["servicesets"] = $oldservicesets;
                $json = json_encode( $new_qs );
                $request = new \RestRequest(
                  RESTURL.'/modify/'.$tblnam,
                  'POST',
                  'json='.$json
                );
                set_request_options( $request );
                $request->execute();
            }
        }
    }

    # ------------------------------------------------------------------------
    function rest_restore( $query_str ) {
    # ------------------------------------------------------------------------

        $overwrite = 0;
        $errstring = "";
        $message = "Restore completed for ";
        $tempdir = $query_str["runpack_dir"];
        $tbllist = "";

        if( isset( $query_str["overwrite"] ) ) {
            $overwrite = 1;
        }

        # The order of these does matter.
        $tbls = array( "commands", "timeperiods", "servicegroups",
                       "hostgroups", "contacts", "contactgroups",
                       "hosttemplates", "servicetemplates",
                       "servicesets", "hosts", "services",
                        );

        $comma="";
        foreach( $tbls as $tbl ) {
            if( isset( $query_str["r".$tbl] ) ) {
                $tbllist .= $comma.$tbl;
                $comma=", ";
                if( restore_table( $tbl, $tempdir, $errstring ) > 0 ) {
                    $retval = array();
                    $retval["message"] = $errstring;
                    $retval["code"] = $resp["http_code"];
                    print( json_encode( $retval ) );
                    exit( 0 );
                }
            }
        }

        $retval = array();
        $retval["message"] = $message.$tbllist;
        $retval["code"] = 200;
        print( json_encode( $retval ) );
    }

    # ------------------------------------------------------------------------
    function backup_restore_using_REST( ) {
    # ------------------------------------------------------------------------

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        if( $query_str["active_tab"] == 0 )
            rest_backup( $query_str );

        if( $query_str["active_tab"] == 1 )
            rest_restore( $query_str );

        exit( 0 ); # <- Stop here - bypass any other actions.
    }

?>


