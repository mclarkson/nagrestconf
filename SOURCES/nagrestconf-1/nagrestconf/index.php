<?php
/*
 * Copyright(C) 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
 *
 *    This software is provided under the terms of the GNU
 *    General Public License (GPL), as published at: 
 *    http://www.gnu.org/licenses/gpl.html .
 *
 * File:    index.php
 * Author:  Mark Clarkson
 * Date:    23 Jan 2012
 *
 */
    # ------------------------------------------------------------------------
    # USER MODIFIABLE GLOBALS
    # ------------------------------------------------------------------------

    define( "SCRIPTNAME", "index.php" );
    define( "VERSION", "v1.174.3" );
    define( "LIBDIR", "/usr/share/nagrestconf/htdocs/nagrestconf/" );

    # ------------------------------------------------------------------------
    # DON'T TOUCH ANYTHING BELOW
    # ------------------------------------------------------------------------

    # ------------------------------------------------------------------------
    # GLOBALS
    # ------------------------------------------------------------------------

    define( "OK", 1 );
    define( "NOT_OK", 2 );
    define( "ERROR", 3 );

    /***********************************************************************
     *
     * SUPPORT CLASSES
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    class RestRequest
    # ------------------------------------------------------------------------
    {
        protected $url;
        protected $verb;
        protected $requestBody;
        protected $requestLength;
        protected $username;
        protected $password;
        protected $acceptType;
        protected $responseBody;
        protected $responseInfo;
        protected $sslkey;
        protected $sslcert;

        public function __construct ($url = null, $verb = 'GET', $requestBody = null)
        {
            $this->url              = $url;
            $this->verb             = $verb;
            $this->requestBody      = $requestBody;
            $this->requestLength    = 0;
            $this->username         = null;
            $this->password         = null;
            $this->acceptType       = 'application/json';
            $this->responseBody     = null;
            $this->responseInfo     = null;

            /*
             * Don't buildPostBody on construction. Allow arbitrary data
             * to be stored in this case.
             *
            if ($this->requestBody !== null)
            {
                $this->buildPostBody();
            }
            */
        }

        # --------------------------------------------------------------------
        public function flush ()
        # --------------------------------------------------------------------
        {
            $this->requestBody      = null;
            $this->requestLength    = 0;
            $this->verb             = 'GET';
            $this->responseBody     = null;
            $this->responseInfo     = null;
        }

        # --------------------------------------------------------------------
        public function execute ()
        # --------------------------------------------------------------------
        {
            $ch = curl_init();
            $this->setAuth($ch);

            try
            {
                switch (strtoupper($this->verb))
                {
                    case 'GET':
                        $this->executeGet($ch);
                        break;
                    case 'POST':
                        $this->executePost($ch);
                        break;
                    case 'PUT':
                        $this->executePut($ch);
                        break;
                    case 'DELETE':
                        $this->executeDelete($ch);
                        break;
                    default:
                        throw new InvalidArgumentException('Current verb ('
                                . $this->verb . ') is an invalid REST verb.');
                }
            }
            catch (InvalidArgumentException $e)
            {
                curl_close($ch);
                throw $e;
            }
            catch (Exception $e)
            {
                curl_close($ch);
                throw $e;
            }

        }

        # --------------------------------------------------------------------
        public function buildPostBody ($data = null)
        # --------------------------------------------------------------------
        {
            $data = ($data !== null) ? $data : $this->requestBody;

            if (!is_array($data))
            {
                throw new InvalidArgumentException('Invalid data input for postBody.  Array expected');
            }

            $data = http_build_query($data, '', '&');
            $this->requestBody = $data;
        }

        # --------------------------------------------------------------------
        protected function executeGet ($ch)
        # --------------------------------------------------------------------
        {       
            $this->doExecute($ch);  
        }

        # --------------------------------------------------------------------
        protected function executePost ($ch)
        # --------------------------------------------------------------------
        {
            if (!is_string($this->requestBody))
            {
                $this->buildPostBody();
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
            curl_setopt($ch, CURLOPT_POST, 1);

            $this->doExecute($ch);  
        }

        # --------------------------------------------------------------------
        protected function executePut ($ch)
        # --------------------------------------------------------------------
        {
            if (!is_string($this->requestBody))
            {
                $this->buildPostBody();
            }

            $this->requestLength = strlen($this->requestBody);

            $fh = fopen('php://memory', 'rw');
            fwrite($fh, $this->requestBody);
            rewind($fh);

            curl_setopt($ch, CURLOPT_INFILE, $fh);
            curl_setopt($ch, CURLOPT_INFILESIZE, $this->requestLength);
            curl_setopt($ch, CURLOPT_PUT, true);

            $this->doExecute($ch);

            fclose($fh);
        }

        # --------------------------------------------------------------------
        protected function executeDelete ($ch)
        # --------------------------------------------------------------------
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

            $this->doExecute($ch);
        }

        # --------------------------------------------------------------------
        protected function doExecute (&$curlHandle)
        # --------------------------------------------------------------------
        {
            $this->setCurlOpts($curlHandle);
            $this->responseBody = curl_exec($curlHandle);
            $this->responseInfo = curl_getinfo($curlHandle);

            curl_close($curlHandle);
        }

        # --------------------------------------------------------------------
        protected function setCurlOpts (&$curlHandle)
        # --------------------------------------------------------------------
        {
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 300);
            curl_setopt($curlHandle, CURLOPT_PROXY, "");
            curl_setopt($curlHandle, CURLOPT_URL, $this->url);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array ('Accept: ' . $this->acceptType));
        }

        # --------------------------------------------------------------------
        protected function setAuth (&$curlHandle)
        # --------------------------------------------------------------------
        {
            if ($this->username !== null && $this->password !== null)
            {
                curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($curlHandle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
                # SSL key and cert
                if( ! empty( $this->sslkey ) ) {
                    curl_setopt($curlHandle, CURLOPT_SSLKEY, $this->sslkey);
                }
                if( ! empty( $this->sslcert ) ) {
                    curl_setopt($curlHandle, CURLOPT_SSLCERT, $this->sslcert);
                }
            }
        }

        # --------------------------------------------------------------------
        public function getAcceptType ()
        # --------------------------------------------------------------------
        {
            return $this->acceptType;
        } 

        # --------------------------------------------------------------------
        public function setAcceptType ($acceptType)
        # --------------------------------------------------------------------
        {
            $this->acceptType = $acceptType;
        } 

        # --------------------------------------------------------------------
        public function getPassword ()
        # --------------------------------------------------------------------
        {
            return $this->password;
        } 

        # --------------------------------------------------------------------
        public function setPassword ($password)
        # --------------------------------------------------------------------
        {
            $this->password = $password;
        } 

        # --------------------------------------------------------------------
        public function setSSLKey ($key)
        # --------------------------------------------------------------------
        {
            $this->sslkey = $key;
        } 

        # --------------------------------------------------------------------
        public function setSSLCert ($cert)
        # --------------------------------------------------------------------
        {
            $this->sslcert = $cert;
        } 

        # --------------------------------------------------------------------
        public function getResponseBody ()
        # --------------------------------------------------------------------
        {
            return $this->responseBody;
        } 

        # --------------------------------------------------------------------
        public function getResponseInfo ()
        # --------------------------------------------------------------------
        {
            return $this->responseInfo;
        } 

        # --------------------------------------------------------------------
        public function getUrl ()
        # --------------------------------------------------------------------
        {
            return $this->url;
        } 

        # --------------------------------------------------------------------
        public function setUrl ($url)
        # --------------------------------------------------------------------
        {
            $this->url = $url;
        } 

        # --------------------------------------------------------------------
        public function getUsername ()
        # --------------------------------------------------------------------
        {
            return $this->username;
        } 

        # --------------------------------------------------------------------
        public function setUsername ($username)
        # --------------------------------------------------------------------
        {
            $this->username = $username;
        } 

        # --------------------------------------------------------------------
        public function getVerb ()
        # --------------------------------------------------------------------
        {
            return $this->verb;
        } 

        # --------------------------------------------------------------------
        public function setVerb ($verb)
        # --------------------------------------------------------------------
        {
            $this->verb = $verb;
        } 
    }

    /***********************************************************************
     *
     * PAGE CODE STARTS
     *
     ***********************************************************************
     */

    /***********************************************************************
     *
     * SUPPORT FUNCS
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function autocomplete_svcnotifopts( $id ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # d u r f s n
         print '$(function() {
         var '.$id.' = [
         {
             value: "w",
             label: "warning",
             desc: "Notify on WARNING service states."
         },
         {
             value: "u",
             label: "unknown",
             desc: "notify on UNKNOWN service states."
         },
         {
             value: "c",
             label: "critical",
             desc: "Notify on CRITICAL service states."
         },
         {
             value: "r",
             label: "recovery",
             desc: "Notify on service recoveries (OK states)."
         },
         {
             value: "f",
             label: "flapping",
             desc: "Notify when flapping starts/stops."
         },
         {
             value: "s",
             label: "scheduled downtime",
             desc: "Notify when scheduled downtime starts/ends."
         },
         {
             value: "n",
             label: "none",
             desc: "Disable all host notifications."
         }
         ];';
        print 'function split( val ) {';
        print ' return val.split( / \s*/ );';
        print '}';
        print 'function extractLast( term ) {';
        print ' return split( term ).pop();';
        print '}';
        print "$( \"#$id\" )";
        print '.bind( "keydown", function( event ) {';
        print ' if ( event.keyCode === $.ui.keyCode.TAB &&';
        print ' $( this ).data( "ui-autocomplete" ).menu.active ) {';
        print '  event.preventDefault();';
        print ' }';
        print '})';
        print '.autocomplete({';
        print ' minLength: 0,';
        print ' source: function( request, response ) {';
        print ' response( $.ui.autocomplete.filter(';
        print "  $id, extractLast( request.term ) ) );";
        print '},';
        print 'focus: function() {';
        print ' return false;';
        print '},';
        print 'select: function( event, ui ) {';
        print ' var terms = split( this.value );';
        print ' terms.pop();';
        print ' terms.push( ui.item.value );';
        print ' terms.push( "" );';
        print ' this.value = terms.join( " " );';
        print ' return false;';
        print '}';
        print '})';
        print '.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                 return $( "<li>" )
                 .append( "<a><b>" + item.value + "</b> : " + item.desc + "</a>" )
                 .appendTo( ul );
             };';
        print '});';
    }

    # ------------------------------------------------------------------------
    function autocomplete_hstnotifopts( $id ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # d u r f s n
         print '$(function() {
         var '.$id.' = [
         {
             value: "d",
             label: "down",
             desc: "Notify on DOWN host states."
         },
         {
             value: "u",
             label: "unreachable",
             desc: "Notify on UNREACHABLE host states."
         },
         {
             value: "r",
             label: "recovery",
             desc: "Notify on host recoveries (UP states)."
         },
         {
             value: "f",
             label: "flapping",
             desc: "Notify when flapping starts/stops."
         },
         {
             value: "s",
             label: "scheduled downtime",
             desc: "Notify when scheduled downtime starts/ends."
         },
         {
             value: "n",
             label: "none",
             desc: "Disable all host notifications."
         }
         ];';
        print 'function split( val ) {';
        print ' return val.split( / \s*/ );';
        print '}';
        print 'function extractLast( term ) {';
        print ' return split( term ).pop();';
        print '}';
        print "$( \"#$id\" )";
        print '.bind( "keydown", function( event ) {';
        print ' if ( event.keyCode === $.ui.keyCode.TAB &&';
        print ' $( this ).data( "ui-autocomplete" ).menu.active ) {';
        print '  event.preventDefault();';
        print ' }';
        print '})';
        print '.autocomplete({';
        print ' minLength: 0,';
        print ' source: function( request, response ) {';
        print ' response( $.ui.autocomplete.filter(';
        print "  $id, extractLast( request.term ) ) );";
        print '},';
        print 'focus: function() {';
        print ' return false;';
        print '},';
        print 'select: function( event, ui ) {';
        print ' var terms = split( this.value );';
        print ' terms.pop();';
        print ' terms.push( ui.item.value );';
        print ' terms.push( "" );';
        print ' this.value = terms.join( " " );';
        print ' return false;';
        print '}';
        print '})';
        print '.data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                 return $( "<li>" )
                 .append( "<a><b>" + item.value + "</b> : " + item.desc + "</a>" )
                 .appendTo( ul );
             };';
        print '});';
    }

    # ------------------------------------------------------------------------
    function autocomplete_jscript_single( $id ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print "$( \"#$id\" )";
        print '.bind( "keydown", function( event ) {';
        print ' if ( event.keyCode === $.ui.keyCode.TAB &&';
        print ' $( this ).data( "ui-autocomplete" ).menu.active ) {';
        print '  event.preventDefault();';
        print ' }';
        print '})';
        print '.autocomplete({';
        print ' minLength: 0,';
        print ' source: '.$id;
        print '});';
        print '});';
    }

    # ------------------------------------------------------------------------
    function autocomplete_jscript( $id ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print 'function split( val ) {';
        print ' return val.split( / \s*/ );';
        print '}';
        print 'function extractLast( term ) {';
        print ' return split( term ).pop();';
        print '}';
        print "$( \"#$id\" )";
        print '.bind( "keydown", function( event ) {';
        print ' if ( event.keyCode === $.ui.keyCode.TAB &&';
        print ' $( this ).data( "ui-autocomplete" ).menu.active ) {';
        print '  event.preventDefault();';
        print ' }';
        print '})';
        print '.autocomplete({';
        print ' minLength: 0,';
        print ' source: function( request, response ) {';
        print ' response( $.ui.autocomplete.filter(';
        print "  $id, extractLast( request.term ) ) );";
        print '},';
        print 'focus: function() {';
        print ' return false;';
        print '},';
        print 'select: function( event, ui ) {';
        print ' var terms = split( this.value );';
        print ' terms.pop();';
        print ' terms.push( ui.item.value );';
        print ' terms.push( "" );';
        print ' this.value = terms.join( " " );';
        print ' return false;';
        print '}';
        print '});';
        print '});';
    }

    # ------------------------------------------------------------------------
    function debug_print( $data ) {
    # ------------------------------------------------------------------------
        #print "$data\n";
    }

    # ------------------------------------------------------------------------
    function show_env_array( ) {
    # ------------------------------------------------------------------------
        echo "\n\$_ENV ARRAY\n\n";
        print_r( $_ENV );
    }

    # ------------------------------------------------------------------------
    function show_server_array( ) {
    # ------------------------------------------------------------------------
        echo "<pre>\n\$_SERVER ARRAY\n\n";
         print_r( $_SERVER );
        echo "</pre>";
    }

    # ------------------------------------------------------------------------
    function show_session_array( ) {
    # ------------------------------------------------------------------------
        echo "\n\$_SESSION ARRAY\n\n";
        print_r( $_SESSION );
    }

    # ------------------------------------------------------------------------
    function clean_query_str( &$query_str ) {
    # ------------------------------------------------------------------------
    # TODO: UNUSED
        foreach( $query_str as &$item ) {
           $item = strtr( $item, array(
                          "," => "`"
                          ) );
        }
    }

    # ------------------------------------------------------------------------
    function get_and_sort_servicesets( $name ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/servicesets?json='.
          '{"folder":"'.FOLDER.'","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['svcdesc']=$svcdesc;
            $d['command']=$command;
            $d['template']=$template;
            $d['disable']=$disable;
            $d["name"] = $name;
            $d["svcgroup"] = $svcgroup;
            $d["contacts"] = $contacts;
            $d["contactgroups"] = $contactgroups;
            $d["freshnessthresh"] = $freshnessthresh;
            $d["activechecks"] = $activechecks;
            $d["customvars"] = $customvars;
            $c[]=$d;
        }
        usort($c, function ($c,$d) {
            return $c['svcdesc']>$d['svcdesc'];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_servicesets_unique( ) {
    # ------------------------------------------------------------------------

        $request = new RestRequest(
          RESTURL.'/show/servicesets?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $c[]=$name;
        }
        $c = array_unique( $c );
        usort($c, function ($c,$d) {
            return $c>$d;
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_contactgroups( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/contactgroups?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['alias']=$alias;
            $d['members']=$members;
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
    function get_and_sort_contacts( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/contacts?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['use']=$alias;
            $d['alias']=$alias;
            $d['emailaddr']=$emailaddr;
            $d['svcnotifperiod']=$svcnotifperiod;
            $d['svcnotifopts']=$svcnotifopts;
            $d['svcnotifcmds']=$svcnotifcmds;
            $d['hstnotifperiod']=$hstnotifperiod;
            $d['hstnotifopts']=$hstnotifopts;
            $d['hstnotifcmds']=$hstnotifcmds;
            $d['cansubmitcmds']=$cansubmitcmds;
            $c[]=$d;
        }
        define( 'SORT2', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT2]>$d[SORT2];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_hostgroups( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/hostgroups?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['alias']=$alias;
            $c[]=$d;
        }
        define( 'SORT3', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT3]>$d[SORT3];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_hosttemplates( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/hosttemplates?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['use']=$use;
            $d['contacts']=$contacts;
            $d['contactgroups']=$contactgroups;
            $d['checkinterval']=$checkinterval;
            $d['retryinterval']=$retryinterval;
            $d['notifperiod']=$notifperiod;
            $d['notifopts']=$notifopts;
            $d['checkperiod']=$checkperiod;
            $d['maxcheckattempts']=$maxcheckattempts;
            $d['checkcommand']=$checkcommand;
            $d['notifinterval']=$notifinterval;
            $c[]=$d;
        }
        define( 'SORT5', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT5]>$d[SORT5];
        }
        );

        return $c;
    }


    # ------------------------------------------------------------------------
    function get_and_sort_servicetemplates( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/servicetemplates?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['use']=$use;
            $d['contacts']=$contacts;
            $d['contactgroups']=$contactgroups;
            $d['checkinterval']=$checkinterval;
            $d['retryinterval']=$retryinterval;
            $d['notifperiod']=$notifperiod;
            $d['notifopts']=$notifopts;
            $d['notifinterval']=$notifinterval;
            $d['disable']=$disable;
            $d['checkperiod']=$checkperiod;
            $d['maxcheckattempts']=$maxcheckattempts;
            $c[]=$d;
        }
        define( 'SORT6', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT6]>$d[SORT6];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_services( $name ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/services?json='.
          '{"folder":"'.FOLDER.'","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['svcdesc']=$svcdesc;
            $d['command']=$command;
            $d['template']=$template;
            $d['disable']=$disable;
            $c[]=$d;
        }
        usort($c, function ($c,$d) {
            return $c['svcdesc']>$d['svcdesc'];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_hosts( $sort="hostgroup", $filter="", $column=1 ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"'.$column.'","filter":"'.urlencode($filter).'"}', 'GET');
        set_request_options( $request2 );
        $request2->execute();
        $hlist = json_decode( $request2->getResponseBody(), true );

        parse_str( $_SERVER['QUERY_STRING'], $query_str );
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
            $b['command']=$alias;
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
    function get_and_sort_svcgroups( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/servicegroups?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['alias']=$alias;
            $c[]=$d;
        }
        define( 'SORT7', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT7]>$d[SORT7];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_timeperiods( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/timeperiods?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['alias']=$alias;
            $d['exclude']=$exclude;
            $c[]=$d;
        }
        define( 'SORT8', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT8]>$d[SORT8];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function get_and_sort_commands( $sort="name" ) {
    # ------------------------------------------------------------------------

        $request2 = new RestRequest(
          RESTURL.'/show/commands?json='.
          '{"folder":"'.FOLDER.'"}',
          'GET');
        set_request_options( $request2 );
        $request2->execute();
        $slist = json_decode( $request2->getResponseBody(), true );

        $c=array();
        foreach( $slist as $slistitem ) {
            foreach( $slistitem as $line2 ) {
                extract( $line2 );
            }
            $d['name']=$name;
            $d['command']=$command;
            $c[]=$d;
        }
        define( 'SORT9', $sort );
        usort($c, function ($c,$d) {
            return $c[SORT9]>$d[SORT9];
        }
        );

        return $c;
    }

    # ------------------------------------------------------------------------
    function create_url( ) {
    # ------------------------------------------------------------------------
    # Allow the globals to override the QUERY_STRING. The globals are
    # cleared if they were found to be set.

        global $g_tab_new, $g_sort_new, $g_hgfilter, $g_hfilter;

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
        if( $g_hgfilter !== 0 ) {
            if( ! empty( $g_hgfilter ) ) {
                $url .= "&hgfilter=".$g_hgfilter;
            } else if( isset( $query_str['hgfilter'] ) ) {
                $url .= "&hgfilter=".$query_str['hgfilter'];
            }
            $g_hgfilter="";
        } else {
            $g_hgfilter="";
        }
        if( $g_hfilter !== 0 ) {
            if( ! empty( $g_hfilter ) ) {
                $url .= "&hfilter=".$g_hfilter;
            } else if( isset( $query_str['hfilter'] ) ) {
                $url .= "&hfilter=".$query_str['hfilter'];
            }
            $g_hfilter="";
        } else {
            $g_hfilter="";
        }

        return $url;
    }

    /***********************************************************************
     *
     * THE WEB PAGE
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_html_header( ) {
    # ------------------------------------------------------------------------
        print'<!DOCTYPE html>
<HTML>
<HEAD>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<TITLE>Nagios REST Configurator</TITLE>
<meta name="author" content="Mark Clarkson">
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="main.css">
<link rel="stylesheet" type="text/css" href="css/ionicons.css">
<script src = js/jquery-1.9.1.js></script>
<script src = js/jquery.ajaxfileupload.js></script>
<script src = js/jquery-ui-1.10.3.custom.min.js></script>
<link rel=stylesheet type=text/css
    href=css/redmond/jquery-ui.css />
<link rel="icon"
      type="image/png"
      href="/nagrestconf/images/meerkat_16x16.png">
</HEAD>
<BODY>
        ';
#<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
#<meta name="date" content="2012-01-09T08:40:55+0100">
#<meta name="copyright" content="">
#<meta http-equiv="content-style-type" content="text/css">
#<meta http-equiv="expires" content="0">
#<meta http-equiv="pragma" content="no-cache">
    }

    # ------------------------------------------------------------------------
    function show_pageheader( ) {
    # ------------------------------------------------------------------------

        global $g_tab, $g_tab_names;

        print '<div id="nagrestconf"';
        print ' style="margin-top: 0px; padding-top: 0px; height: 53px;';
        print ' top: 0px;">';
        print '<a href="http://nagrestconf.smorg.co.uk/" target="_blank">';
        print '<img src="images/meerkat_50x51_sinc.png" alt="Meerkat"';
        print ' style="padding-top: 3px;"></a>';
        print '<span style="vertical-align: top; padding-top: 9px;';
        print ' display: inline-block; padding-left: 6px;';
        print ' text-align: left;">Nagrestconf<br /><span style="';
        print ' font-weight: normal; font-size: 12px;"><i>'.VERSION.'</i></span>';
        print '</div>';
        print '<div id="pagetabs">';
        print '<ul>';

        krsort( $g_tab_names );

        $active=0; # The active tab
        $i=0;

        foreach( $g_tab_names as $num => $name ) {
            if( $g_tab == $name[2] ) $active=$i;
            $i++;
            print '<li style="float:right;">';
            print '<a href="#none">';
            print '<span style="cursor: pointer;" id="'.$name[0];
            print '-tab">'.$name[1].'</span>';
            print '</a></li>';
            print '<script>';
            print '$("#'.$name[0].'-tab").bind("click", function() ';
            print '{ window.location="index.php?tab='.$name[2].'"; } )';
            print '</script>';
        }

        print '</ul>';
        print '</div>';
        print '<script>';
        print '$( "#pagetabs" ).tabs( { active: '.($active).' } );';
        print '</script>';

    }

    # ------------------------------------------------------------------------
    function show_revert_button( ) {
    # ------------------------------------------------------------------------
        global $g_tab;

        $url = create_url( );

        print '<style>#revert:hover{font-weight: bold;}</style>';
        print '<p style="padding: 4px 0px 4px 6px;"><a href="#" id="revert">';
        print '<span class="ion-ios7-undo-outline" style="font-size:16px"></span>';
        print '&nbsp; Revert Changes</a></p>';
        print '<script>';
        print ' $("#revert").bind("click", function() {';
        print ' var ans = confirm( "Reverting to Last Known Good';
        print ' configuration\nAll changes will be lost - Really Revert?" );';
        print ' if( ans ) window.location="'.$url.'&revert=true";';
        print '} );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_apply_button( ) {
    # ------------------------------------------------------------------------
        global $g_tab;

        $url = create_url( );

        print '<div style="margin-right: 4px;">';
        print '<input id="apply" type="button" value="Apply Changes"';
        print ' style="width: 100%; margin: 8px 0 10px 0;" />';
        print '</div>';
        print '<script>';
        print ' $("#apply").bind("click", function() {';
        #print ' var ans = confirm( "Apply the current configuration?" );';
        #print ' if( ans ) window.location="'.$url.'&apply=true";';
        #
        print "$('#applyconfigdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?applyconfig=true').".
              "dialog('open'); ";
        #
        print '} );';
        print '</script>';
    }

    /***********************************************************************
     *
     * HOSTGROUPS (GROUPS) TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_hostgroups_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        print "<script>".
              "$(document).ready(function() {".
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
              # Load the right pane
              #'$("#hostgroupstable").html("").'.
              '$("#hostgroupstable").'.
              'load("'.$url.'&hostgroupstable=true");'.
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
        #show_hosts_tab_right_pane( );
        print '<div id="hostgroupstable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_hostgroups_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_hostgroups_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        show_apply_button( );

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_groups_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        show_hostgroups_list( );

        print "<p>&nbsp;</p>";

        show_svcgroups_list( );
    }

    # ------------------------------------------------------------------------
    function show_hostgroups_list( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_hostgroups( $sort=$g_sort );
        } else {
            $a = get_and_sort_hostgroups( );
        }

        print "<p>".count($a)." host groups.</p>";
        print "<table><thead><tr>";

        # Sort by host name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by ip address
        $g_sort_new = "alias";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Alias </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" title=\"Add New Hostgroup\" onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newhostgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=3&newhostgroupdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // IP ADDRESS
            print "<td>".$item['alias']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Hostgroup\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#edithostgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=3&edithostgroupdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Hostgroup\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delhostgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=3&delhostgroupdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    # ------------------------------------------------------------------------
    function show_svcgroups_list( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_svcgroups( $sort=$g_sort );
        } else {
            $a = get_and_sort_svcgroups( );
        }

        print "<p>".count($a)." service groups.</p>";
        print "<table><thead><tr>";

        # Sort by host name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by ip address
        $g_sort_new = "alias";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Alias </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" title=\"Add New Servicegroup\" onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newsvcgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=3&newsvcgroupdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // IP ADDRESS
            print "<td>".$item['alias']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Hostgroup\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#editsvcgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=3&editsvcgroupdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Hostgroup\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delsvcgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=3&delsvcgroupdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    /***********************************************************************
     *
     * EDIT SERVICE GROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_editsvcgroupdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/servicegroups?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="editsvcgroupform" name="editsvcgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=3&editsvcgroup=1';
        print '">';
        print '<fieldset>';
        # Hostname
        print '<p>';
        print '<label for="svcgroupname">Servicegroup name *</label>';
        print '<input class="field" type="text" id="svcgroupname" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" ';
        print ' value="'.$alias.'" required="required" />';
        print '</p>';
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
    function show_edit_svcgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"editsvcgroupdlg\" title=\"Add New Hostgroup\"></div>";
        print '<script>';
        # Addsvc button
        print 'var editsvcgroup = function() { ';
        print ' $.getJSON( $("#editsvcgroupform").attr("action"), '; # <- url
        print ' $("#editsvcgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hostgroupstable").html("").';
        print '      load("'.$url.'&hostgroupstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editsvcgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editsvcgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Servicegroup": editsvcgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_svcgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["editsvcgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST edit svcgroup request
        $request = new RestRequest(
          RESTURL.'/modify/servicegroups',
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
     * ADD NEW SERVICE GROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newsvcgroupdialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newsvcgroupform" name="newsvcgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=3&newsvcgroup=1';
        print '">';
        print '<fieldset>';
        # Hostname
        print '<p>';
        print '<label for="svcgroupname">Servicegroup name *</label>';
        print '<input class="field" type="text" id="svcgroupname" name="name" required="required" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" required="required" />';
        print '</p>';
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
    function show_new_svcgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newsvcgroupdlg\" title=\"Add New Hostgroup\"></div>";
        print '<script>';
        # Addsvc button
        print 'var addsvcgroup = function() { ';
        print ' $.getJSON( $("#newsvcgroupform").attr("action"), '; # <- url
        print ' $("#newsvcgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hostgroupstable").html("").';
        print '      load("'.$url.'&hostgroupstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#newsvcgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newsvcgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Servicegroup": addsvcgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_svcgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newsvcgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add svcgroup request
        $request = new RestRequest(
          RESTURL.'/add/servicegroups',
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
     * DELETE SERVICE GROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delsvcgroupdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delsvcgroupform" name="delsvcgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=3&delsvcgroup=1';
        print '">';
        print '<h2>About to <b>DELETE</b> service group:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Hostgroup' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_svcgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delsvcgroupdlg\" title=\"Delete Hostgroup\"></div>";
        print '<script>';
        # Addsvc button
        print 'var delsvcgroup = function() { ';
        print ' $.getJSON( $("#delsvcgroupform").attr("action"), '; # <- url
        print ' $("#delsvcgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hostgroupstable").html("").';
        print '      load("'.$url.'&hostgroupstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#delsvcgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delsvcgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Delete Servicegroup": delsvcgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_svcgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delsvcgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add svc request
        $request = new RestRequest(
          RESTURL.'/delete/servicegroups',
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
     * EDIT HOSTGROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_edithostgroupdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/hostgroups?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="edithostgroupform" name="edithostgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=3&edithostgroup=1';
        print '">';
        print '<fieldset>';
        # Hostname
        print '<p>';
        print '<label for="hostgroupname">Hostgroup name *</label>';
        print '<input class="field" type="text" id="hostgroupname" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" ';
        print ' value="'.$alias.'" required="required" />';
        print '</p>';
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
    function show_edit_hostgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"edithostgroupdlg\" title=\"Edit Hostgroup\"></div>";
        print '<script>';
        # Addhost button
        print 'var edithostgroup = function() { ';
        print ' $.getJSON( $("#edithostgroupform").attr("action"), '; # <- url
        print ' $("#edithostgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hostgroupstable").html("").';
        print '      load("'.$url.'&hostgroupstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#edithostgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#edithostgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Hostgroup": edithostgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_hostgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["edithostgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST edit hostgroup request
        $request = new RestRequest(
          RESTURL.'/modify/hostgroups',
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
     * ADD NEW HOSTGROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newhostgroupdialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newhostgroupform" name="newhostgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=3&newhostgroup=1';
        print '">';
        print '<fieldset>';
        # Hostname
        print '<p>';
        print '<label for="hostgroupname">Hostgroup name *</label>';
        print '<input class="field" type="text" id="hostgroupname" name="name" required="required" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" required="required" />';
        print '</p>';
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
    function show_new_hostgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newhostgroupdlg\" title=\"Add New Hostgroup\"></div>";
        print '<script>';
        # Addhost button
        print 'var addhostgroup = function() { ';
        print ' $.getJSON( $("#newhostgroupform").attr("action"), '; # <- url
        print ' $("#newhostgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hostgroupstable").html("").';
        print '      load("'.$url.'&hostgroupstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#newhostgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newhostgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Hostgroup": addhostgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_hostgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newhostgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add hostgroup request
        $request = new RestRequest(
          RESTURL.'/add/hostgroups',
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
     * DELETE HOSTGROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delhostgroupdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delhostgroupform" name="delhostgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=3&delhostgroup=1';
        print '">';
        print '<h2>About to <b>DELETE</b> hostgroup:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Hostgroup' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_hostgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delhostgroupdlg\" title=\"Delete Hostgroup\"></div>";
        print '<script>';
        # Addhost button
        print 'var delhostgroup = function() { ';
        print ' $.getJSON( $("#delhostgroupform").attr("action"), '; # <- url
        print ' $("#delhostgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hostgroupstable").html("").';
        print '      load("'.$url.'&hostgroupstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#delhostgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delhostgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Delete Hostgroup": delhostgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_hostgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delhostgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/delete/hostgroups',
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

    /*

       ===================================================================

                              END OF GROUPS TAB

       ===================================================================

     */

    /***********************************************************************
     *
     * COMMANDS TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_commands_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        #/*
        print "<script>".
              "$(document).ready(function() {".
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
              # Load the right pane
              #'$("#commandstable").html("").'.
              '$("#commandstable").'.
              'load("'.$url.'&commandstable=true");'.
              "  });".
              "</script>";
        #*/

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
        #show_hosts_tab_right_pane( );
        print '<div id="commandstable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_commands_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_commands_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        show_apply_button();

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_commands_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_commands( $sort=$g_sort );
        } else {
            $a = get_and_sort_commands( );
        }

        print "<p>".count($a)." commands.</p>";
        print "<table><thead><tr>";

        # Sort by name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by alias
        $g_sort_new = "command";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Command </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" ".
              " title=\"Add New Command\" onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newcommanddlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=7&newcommandsdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".urldecode($item['name'])."</td>";
            // COMMAND
            print "<td>".substr(urldecode($item['command']),0,100);
            if( strlen(urldecode($item['command']))>100 ) print "...";
            print "</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Command\"";
            print " onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#editcommanddlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=7&editcommandsdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" ".
                  " title=\"Delete Command\"";
            print " onClick=\"".
            #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delcommanddlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=7&delcommandsdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    /***********************************************************************
     *
     * NEW COMMAND DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newcommanddialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newcommandform" '.
              'name="newcommandform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=7&newcommand=1';
        print '">';
        print '<fieldset>';
        # Command name
        print '<p>';
        print '<label for="name">Command Name *</label>';
        print '<input class="field" type="text" id="name" ';
        print ' name="name" required="required" ';
        print ' />';
        print '</p>';
        # Command
        print '<p>';
        print '<label for="command">Command</label>';
        print '<input class="field" type="text" id="command" name="command"';
        print ' required="required" />';
        print '</p>';

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
    function show_new_command_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newcommanddlg\" ".
              " title=\"New Command\"></div>";
        print '<script>';
        # Addcommand button
        print 'var newcommand = function() { ';
        print ' $.getJSON( $("#newcommandform").attr("action"), '; # <- url
        print ' $("#newcommandform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#commandstable").html("").';
        print '      load("'.$url.'&commandstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#newcommanddlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newcommanddlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Command": newcommand, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_command_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newcommand"] );
        $query_str["folder"] = FOLDER;
        if( ! empty( $query_str["name"] ) )
        {
            $query_str["name"] = strtr( $query_str["name"], 
                                           array( '"' => '\"',) );
            $query_str["name"] = urlencode($query_str["name"]);
        }
        if( ! empty( $query_str["command"] ) )
        {
            $query_str["command"] = strtr( $query_str["command"], 
                                           array( '"' => '\"',) );
            $query_str["command"] = urlencode($query_str["command"]);
        }
        $json = json_encode( $query_str );

        # Do the REST new command request
        $request = new RestRequest(
          RESTURL.'/add/commands',
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
     * DELETE COMMAND DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delcommanddialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delcommandform" '.
              'name="delcommandform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=7&delcommand=1';
        print '">';
        print '<h2>About to <b>DELETE</b> Command:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">';
        print urldecode($name).'</h2>';
        print "<h2>Click 'Delete Command' to confirm ".
              "or 'Close' to cancel.</h2>";
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_command_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delcommanddlg\" ".
              "title=\"Delete Command\"></div>";
        print '<script>';
        # Addcommand button
        print 'var delcommand = function() { ';
        print ' $.getJSON( $("#delcommandform").attr("action"), '; #<- url
        print ' $("#delcommandform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#commandstable").html("").';
        print '      load("'.$url.'&commandstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() '.
              '{ $("#delcommanddlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delcommanddlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : '.
              '{ "Delete Command": delcommand, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_command_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delcommand"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add command request
        $request = new RestRequest(
          RESTURL.'/delete/commands',
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
     * EDIT COMMAND DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_editcommanddialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/commands?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="editcommandform" name="editcommandform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=7&editcommand=1';
        print '">';
        print '<fieldset>';
        # Command name
        print '<p>';
        print '<label for="name">Command Name *</label>';
        print '<input class="field" type="text" id="name" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.urldecode($name).'" />';
        print '</p>';
        # Command
        $newcmd = urldecode($command);
        $newcmd = strtr( $newcmd, array("\""=>"\\\"","\\"=>"\\\\") );
        print '<p>';
        print '<label for="ecommand">Command</label>';
        print '<input class="field" type="text" id="ecommand" name="command"';
              # Using <.. value="\"" ..> does not work so...
        print ' required="required" value="'.$newcmd.'" />';
              # ...have to use javascript to set the value:
        print '<script>$("#ecommand").val("'.$newcmd.'");</script>';
        print '</p>';

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
    function show_edit_command_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"editcommanddlg\" title=\"Edit Command\"></div>";
        print '<script>';
        # Addcommand button
        print 'var editcommand = function() { ';
        print ' $.getJSON( $("#editcommandform").attr("action"), '; # <- url
        print ' $("#editcommandform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#commandstable").html("").';
        print '      load("'.$url.'&commandstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editcommanddlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editcommanddlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Command": editcommand, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_command_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["editcommand"] );
        $query_str["folder"] = FOLDER;
        if( ! empty( $query_str["name"] ) )
        {
            $query_str["name"] = strtr( $query_str["name"], 
                                           array( '"' => '\"',) );
            $query_str["name"] = urlencode($query_str["name"]);
        }
        if( ! empty( $query_str["command"] ) )
        {
            $query_str["command"] = strtr( $query_str["command"], 
                                           array( '"' => '\"',) );
            $query_str["command"] = urlencode($query_str["command"]);
        }
        # Handle deleting fields
        if( empty( $query_str["exclude"] ) )
            $query_str["exclude"] = "-";
        if( empty( $query_str["definition"] ) )
            $query_str["definition"] = "-";
        $json = json_encode( $query_str );

        # Do the REST edit command request
        $request = new RestRequest(
          RESTURL.'/modify/commands',
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

    /*

       ===================================================================

                            END OF COMMANDS TAB

       ===================================================================

     */

    /***********************************************************************
     *
     * TIMEPERIODS TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_timeperiods_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        #/*
        print "<script>".
              "$(document).ready(function() {".
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
              # Load the right pane
              #'$("#timeperiodstable").html("").'.
              '$("#timeperiodstable").'.
              'load("'.$url.'&timeperiodstable=true");'.
              "  });".
              "</script>";
        #*/

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
        #show_hosts_tab_right_pane( );
        print '<div id="timeperiodstable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_timeperiods_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_timeperiods_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        show_apply_button();

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_timeperiods_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_timeperiods( $sort=$g_sort );
        } else {
            $a = get_and_sort_timeperiods( );
        }

        print "<p>".count($a)." timeperiods.</p>";
        print "<table><thead><tr>";

        # Sort by name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by alias
        $g_sort_new = "alias";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Alias </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by exclude
        $g_sort_new = "exclude";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Exclude </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" ".
              " title=\"Add New Timeperiod\" onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newtimeperioddlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=6&newtimeperiodsdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // ALIAS
            print "<td>".$item['alias']."</td>";
            // EXCLUDE
            print "<td>".$item['exclude']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Timeperiod\"";
            print " onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#edittimeperioddlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=6&edittimeperiodsdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" ".
                  " title=\"Delete Timeperiod\"";
            print " onClick=\"".
            #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#deltimeperioddlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=6&deltimeperiodsdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    /***********************************************************************
     *
     * NEW TIMEPERIOD DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newtimeperioddialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newtimeperiodform" '.
              'name="newtimeperiodform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=6&newtimeperiod=1';
        print '">';
        print '<fieldset>';
        # Timeperiod name
        print '<p>';
        print '<label for="name">Timeperiod Name *</label>';
        print '<input class="field" type="text" id="name" ';
        print ' name="name" required="required" ';
        print ' />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias</label>';
        print '<input class="field" type="text" id="alias" name="alias"';
        print ' required="required" />';
        print '</p>';
        # Definition
        print '<p>';
        print '<label for="definition">Timeperiod Definition</label>';
        print '<input class="field" type="text" id="definition" name="definition"';
        print ' />';
        print '</p>';
        # Exclude
        print '<p>';
        print '<label for="exclude">Exclude *</label>';
        print '<input class="field" type="text" id="exclude" name="exclude"';
        print ' />';
        print '</p>';

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
    function show_new_timeperiod_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newtimeperioddlg\" ".
              " title=\"New Timeperiod\"></div>";
        print '<script>';
        # Addtimeperiod button
        print 'var newtimeperiod = function() { ';
        print ' $.getJSON( $("#newtimeperiodform").attr("action"), '; # <- url
        print ' $("#newtimeperiodform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#timeperiodstable").html("").';
        print '      load("'.$url.'&timeperiodstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#newtimeperioddlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newtimeperioddlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Timeperiod": newtimeperiod, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_timeperiod_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newtimeperiod"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST new timeperiod request
        $request = new RestRequest(
          RESTURL.'/add/timeperiods',
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
     * DELETE TIMEPERIOD DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_deltimeperioddialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="deltimeperiodform" '.
              'name="deltimeperiodform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=6&deltimeperiod=1';
        print '">';
        print '<h2>About to <b>DELETE</b> Timeperiod:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Timeperiod' to confirm ".
              "or 'Close' to cancel.</h2>";
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_timeperiod_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"deltimeperioddlg\" ".
              "title=\"Delete Timeperiod\"></div>";
        print '<script>';
        # Addtimeperiod button
        print 'var deltimeperiod = function() { ';
        print ' $.getJSON( $("#deltimeperiodform").attr("action"), '; #<- url
        print ' $("#deltimeperiodform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#timeperiodstable").html("").';
        print '      load("'.$url.'&timeperiodstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() '.
              '{ $("#deltimeperioddlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#deltimeperioddlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : '.
              '{ "Delete Timeperiod": deltimeperiod, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_timeperiod_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["deltimeperiod"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add timeperiod request
        $request = new RestRequest(
          RESTURL.'/delete/timeperiods',
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
     * EDIT TIMEPERIOD DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_edittimeperioddialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/timeperiods?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="edittimeperiodform" name="edittimeperiodform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=6&edittimeperiod=1';
        print '">';
        print '<fieldset>';
        # Timeperiod name
        print '<p>';
        print '<label for="name">Timeperiod Name *</label>';
        print '<input class="field" type="text" id="name" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias</label>';
        print '<input class="field" type="text" id="alias" name="alias"';
        print ' value="'.$alias.'" />';
        print '</p>';
        # Definition
        print '<p>';
        print '<label for="definition">Timeperiod Definition</label>';
        print '<input class="field" type="text" id="definition" name="definition"';
        print ' value="'.$definition.'" />';
        print '</p>';
        # Exclude
        print '<p>';
        print '<label for="exclude">Exclude *</label>';
        print '<input class="field" type="text" id="exclude" name="exclude"';
        print ' value="'.$exclude.'" required="required" />';
        print '</p>';

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
    function show_edit_timeperiod_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"edittimeperioddlg\" title=\"Edit Timeperiod\"></div>";
        print '<script>';
        # Addtimeperiod button
        print 'var edittimeperiod = function() { ';
        print ' $.getJSON( $("#edittimeperiodform").attr("action"), '; # <- url
        print ' $("#edittimeperiodform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#timeperiodstable").html("").';
        print '      load("'.$url.'&timeperiodstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#edittimeperioddlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#edittimeperioddlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Timeperiod": edittimeperiod, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_timeperiod_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["edittimeperiod"] );
        $query_str["folder"] = FOLDER;
        # Handle deleting fields
        if( empty( $query_str["exclude"] ) )
            $query_str["exclude"] = "-";
        if( empty( $query_str["definition"] ) )
            $query_str["definition"] = "-";
        $json = json_encode( $query_str );

        # Do the REST edit timeperiod request
        $request = new RestRequest(
          RESTURL.'/modify/timeperiods',
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

    /*

       ===================================================================

                            END OF TIMEPERIODS TAB

       ===================================================================

     */

    /***********************************************************************
     *
     * TEMPLATES TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_templates_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        #/*
        print "<script>".
              "$(document).ready(function() {".
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
              # Load the right pane
              #'$("#templatestable").html("").'.
              '$("#templatestable").'.
              'load("'.$url.'&templatestable=true");'.
              "  });".
              "</script>";
        #*/

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
        #show_hosts_tab_right_pane( );
        print '<div id="templatestable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_templates_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_templates_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        show_apply_button();

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_templates_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        show_hosttemplates_list( );

        print "<p>&nbsp;</p>";

        show_svctemplates_list( );
    }

    # ------------------------------------------------------------------------
    function show_hosttemplates_list( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_hosttemplates( $sort=$g_sort );
        } else {
            $a = get_and_sort_hosttemplates( );
        }

        print "<p>".count($a)." host templates.</p>";
        print "<table><thead><tr>";

        # Sort by name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by use
        $g_sort_new = "use";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Parent </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by contacts
        $g_sort_new = "contacts";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Contacts </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by contact groups
        $g_sort_new = "contactgroups";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Contact Groups </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by maxcheckattempts
        $g_sort_new = "maxcheckattempts";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Max Checks </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by checkinterval
        $g_sort_new = "checkinterval";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Check Interval </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by Notif Period
        #$g_sort_new = "notifperiod";
        #$url = create_url( );
        #print "<td><a href='".$url."'><span class=black>Notif Period</span>";
        #print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
        #      " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" ".
              " title=\"Add New Host Template\" onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newhosttemplatedlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=5&newhosttemplatedialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // USE (Parent template)
            print "<td>".$item['use']."</td>";
            // CONTACTS
            print "<td>".$item['contacts']."</td>";
            // CONTACT GROUPS
            print "<td>".$item['contactgroups']."</td>";
            // maxcheckattempts
            print "<td>".$item['maxcheckattempts']."</td>";
            // checkinterval
            print "<td>".$item['checkinterval']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Host Template\"";
            print " onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#edithosttemplatedlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=5&edithosttemplatedialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" ".
                  " title=\"Delete Host Template\"";
            print " onClick=\"".
            #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delhosttemplatedlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=5&delhosttemplatedialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    # ------------------------------------------------------------------------
    function show_svctemplates_list( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_servicetemplates( $sort=$g_sort );
        } else {
            $a = get_and_sort_servicetemplates( );
        }

        print "<p>".count($a)." service templates.</p>";
        print "<table><thead><tr>";

        # Sort by name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by use
        $g_sort_new = "use";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Parent </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by contacts
        $g_sort_new = "contacts";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Contacts </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by contact groups
        $g_sort_new = "contactgroups";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Contact Groups </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by maxcheckattempts
        $g_sort_new = "maxcheckattempts";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Max Checks </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by checkinterval
        $g_sort_new = "checkinterval";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Check Interval </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" ".
              " title=\"Add New Service Template\" onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newsvctemplatedlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=5&newsvctemplatedialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // USE (Parent template)
            print "<td>".$item['use']."</td>";
            // CONTACTS
            print "<td>".$item['contacts']."</td>";
            // CONTACT GROUPS
            print "<td>".$item['contactgroups']."</td>";
            // maxcheckattempts
            print "<td>".$item['maxcheckattempts']."</td>";
            // checkinterval
            print "<td>".$item['checkinterval']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Service Template\"";
            print " onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#editsvctemplatedlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=5&editsvctemplatedialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" ".
                  " title=\"Delete Service Template\"";
            print " onClick=\"".
            #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delsvctemplatedlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=5&delsvctemplatedialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    /***********************************************************************
     *
     * NEW HOST TEMPLATE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newhosttemplatedialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newhosttemplateform" '.
              'name="newhosttemplateform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=5&newhosttemplate=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="newhosttmpltabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # name
        print '<p>';
        print '<label for="hosttemplatename">Template Name *</label>';
        print '<input class="field" type="text" id="hosttemplatename" ';
        print ' name="name" required="required" ';
        print '/>';
        print '</p>';
        # Use
        $hts = get_and_sort_hosttemplates( );
        print '<p>';
        print '<label for="use">Parent Template *</label>';
        print '<select class="field" id="use" name="use" required="required">';
        $selected = "selected";
        print '<option value="" selected>None</option>';
        foreach( $hts as $item ) {
            print '<option value="'.$item["name"].'">';
            print $item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contacts
        print '<p>';
        print '<label for="acontacts">Contacts</label>';
        print '<input class="field" type="text" id="acontacts" name="contacts" />';
        print '</p>';
        # Contact Groups
        print '<p>';
        print '<label for="acontactgroups">Contact Groups</label>';
        print '<input class="field" type="text" id="acontactgroups" name="contactgroups" />';
        print '</p>';
        # Check Interval
        print '<p>';
        print '<label for="checkinterval">Check Interval *</label>';
        print '<input class="field" type="text" id="checkinterval" name="checkinterval"';
        print ' value="5" required="required" />';
        print '</p>';
        # Retry Interval
        print '<p>';
        print '<label for="retryinterval">Retry Interval *</label>';
        print '<input class="field" type="text" id="retryinterval" name="retryinterval"';
        print ' value="1" required="required" />';
        print '</p>';
        # Max Check Attempts
        print '<p>';
        print '<label for="maxcheckattempts">Max Check Attempts *</label>';
        print '<input class="field" type="text" id="maxcheckattempts" '.
              'value="3" name="maxcheckattempts" required="required" />';
        print '</p>';
        # Check Period
        print '<p>';
        print '<label for="vcheckperiod">Check Period *</label>';
        print '<input class="field" type="text" id="vcheckperiod" '.
              'value="24x7" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="vnotifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="vnotifperiod" '.
              'value="24x7" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="60" name="notifinterval" required="required" />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Check Command
        print '<p>';
        print '<label for="checkcommand">Check Command</label>';
        print '<input class="field" type="text" id="checkcommand" '.
              'value="check-host-alive" name="checkcommand" />';
        print '</p>';
        # Notification Options
        print '<p>';
        print '<label for="vnotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="vnotifopts" '.
              'value="d u r" name="notifopts" />';
        print '</p>';
        # Passive Checks
        print '<p>';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        print '<label for="actionurl">Action URL</label>';
        print '<input class="field" type="text" id="action_url" '.
              'value="" name="action_url" />';
        print '</p>';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="siconimg">Icon Image</label>';
        print '<input class="field" type="text" id="icon_image" '.
              'value="" name="icon_image" />';
        print '</p>';
        print '<p>';
        print '<p>';
        print '<label for="siconimgalt">Icon Image Alt</label>';
        print '<input class="field" type="text" id="icon_image_alt" '.
              'value="" name="icon_image_alt" />';
        print '</p>';
        print '<p>';
        print '<label for="sstatusmapimage">Status Map Image</label>';
        print '<input class="field" type="text" id="statusmap_image" '.
              'value="" name="statusmap_image" />';
        print '</p>';
        print '</div>';

        print '</div>';
        print '<script>';
        print '$( "#newhosttmpltabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var acontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "acontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var acontactgroups = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "acontactgroups" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var checkcommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "checkcommand" );
        print '</script>';

        # Auto-complete for checkperiod
        $hgs = get_and_sort_timeperiods( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var vcheckperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "vcheckperiod" );
        print '</script>';
        #
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var vnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "vnotifperiod" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_hstnotifopts( "vnotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_new_hosttemplate_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newhosttemplatedlg\" ".
              " title=\"New Host Template\"></div>";
        print '<script>';
        # Addhosttemplate button
        print 'var newhosttemplate = function() { ';
        print ' $.getJSON( $("#newhosttemplateform").attr("action"), '; # <- url
        print ' $("#newhosttemplateform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#templatestable").html("").';
        print '      load("'.$url.'&templatestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#newhosttemplatedlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newhosttemplatedlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Host Template": newhosttemplate, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_hosttemplate_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newhosttemplate"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["action_url"] ) ) {
            $query_str["action_url"] = strtr( $query_str["action_url"], 
                                           array( '"' => '\"',) );
            $query_str["action_url"] = urlencode($query_str["action_url"]);
        }
        $json = json_encode( $query_str );

        # Do the REST new hosttemplate request
        $request = new RestRequest(
          RESTURL.'/add/hosttemplates',
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
     * DELETE HOST TEMPLATE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delhosttemplatedialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delhosttemplateform" '.
              'name="delhosttemplateform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=5&delhosttemplate=1';
        print '">';
        print '<h2>About to <b>DELETE</b> host template:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Host Template' to confirm ".
              "or 'Close' to cancel.</h2>";
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_hosttemplate_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delhosttemplatedlg\" ".
              "title=\"Delete Host Template\"></div>";
        print '<script>';
        # Addhosttemplate button
        print 'var delhosttemplate = function() { ';
        print ' $.getJSON( $("#delhosttemplateform").attr("action"), '; #<- url
        print ' $("#delhosttemplateform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#templatestable").html("").';
        print '      load("'.$url.'&templatestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() '.
              '{ $("#delhosttemplatedlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delhosttemplatedlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : '.
              '{ "Delete Host Template": delhosttemplate, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_hosttemplate_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delhosttemplate"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add hosttemplate request
        $request = new RestRequest(
          RESTURL.'/delete/hosttemplates',
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
     * EDIT HOST TEMPLATE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_edithosttemplatedialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/hosttemplates?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="edithosttemplateform" name="edithosttemplateform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=5&edithosttemplate=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="edithosttmpltabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Hostname
        print '<p>';
        print '<label for="hosttemplatename">Template Name *</label>';
        print '<input class="field" type="text" id="hosttemplatename" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Use
        $hts = get_and_sort_hosttemplates( );
        print '<p>';
        print '<label for="use">Parent Template *</label>';
        print '<select class="field" id="use" name="use" required="required">';
        $selected = "";
        if( ! isset( $use ) ) $selected = " selected";
        print '<option value="-"'.$selected.'>None</option>';
        foreach( $hts as $item ) {
            $selected = "";
            if( $item["name"] == $name ) continue;
            if( $item["name"] == $use ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>';
            print $item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contacts
        print '<p>';
        print '<label for="contacts">Contacts</label>';
        print '<input class="field" type="text" id="contacts" name="contacts"';
        print ' value="'.$contacts.'" />';
        print '</p>';
        # Contact Groups
        print '<p>';
        print '<label for="contactgroups">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroups" name="contactgroups"';
        print ' value="'.$contactgroups.'" />';
        print '</p>';
        # Check Interval
        print '<p>';
        print '<label for="checkinterval">Check Interval *</label>';
        print '<input class="field" type="text" id="checkinterval" name="checkinterval"';
        print ' value="'.$checkinterval.'" required="required" />';
        print '</p>';
        # Retry Interval
        print '<p>';
        print '<label for="retryinterval">Retry Interval *</label>';
        print '<input class="field" type="text" id="retryinterval" name="retryinterval"';
        print ' value="'.$retryinterval.'" required="required" />';
        print '</p>';
        # Max Check Attempts
        print '<p>';
        print '<label for="maxcheckattempts">Max Check Attempts *</label>';
        print '<input class="field" type="text" id="maxcheckattempts" '.
              'value="'.$maxcheckattempts.'" name="maxcheckattempts" '.
              'required="required" />';
        print '</p>';
        # Check Period
        print '<p>';
        print '<label for="wcheckperiod">Check Period *</label>';
        print '<input class="field" type="text" id="wcheckperiod" '.
              'value="'.$checkperiod.'" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="wnotifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="wnotifperiod" '.
              'value="'.$notifperiod.'" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="'.$notifinterval.'" name="notifinterval" required="required" />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Check Command
        print '<p>';
        print '<label for="checkcommand">Check Command</label>';
        print '<input class="field" type="text" id="acheckcommand" '.
              'value="'.$checkcommand.'" name="checkcommand" />';
        print '</p>';
        # Notification Options
        print '<p>';
        print '<label for="wnotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="wnotifopts" '.
              'value="'.$notifopts.'" name="notifopts" />';
        print '</p>';
        # Passive Checks
        print '<p>';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        $selected=""; if( ! strlen($passivechecks) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $passivechecks == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $passivechecks == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        $selected=""; if( ! strlen($notifications_enabled) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $notifications_enabled == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $notifications_enabled == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        $new_actionurl = urldecode( $action_url );
        $new_actionurl = strtr( $new_actionurl, array("\""=>"\\\"","\\"=>"\\\\") );
        print '<label for="actionurl">Action URL</label>';
        print '<input class="field" type="text" id="action_url" '.
              'value="'.$new_actionurl.'" name="action_url" />';
        print '</p>';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        $selected=""; if( ! strlen($retainstatusinfo) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
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
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $retainnonstatusinfo == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $retainnonstatusinfo == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="siconimg">Icon Image</label>';
        print '<input class="field" type="text" id="icon_image" '.
              'value="'.$icon_image.'" name="icon_image" />';
        print '</p>';
        print '<p>';
        print '<p>';
        print '<label for="siconimgalt">Icon Image Alt</label>';
        print '<input class="field" type="text" id="icon_image_alt" '.
              'value="'.$icon_image_alt.'" name="icon_image_alt" />';
        print '</p>';
        print '<p>';
        print '<label for="sstatusmapimage">Status Map Image</label>';
        print '<input class="field" type="text" id="statusmap_image" '.
              'value="'.$statusmap_image.'" name="statusmap_image" />';
        print '</p>';
        print '</div>';

        print '</div>';
        print '<script>';
        print '$( "#edithosttmpltabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var contacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "contacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var contactgroups = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "contactgroups" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var acheckcommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "acheckcommand" );
        print '</script>';

        # Auto-complete for checkperiod
        $hgs = get_and_sort_timeperiods( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var wcheckperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "wcheckperiod" );
        print '</script>';
        #
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var wnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "wnotifperiod" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_hstnotifopts( "wnotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_edit_hosttemplate_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"edithosttemplatedlg\" title=\"Edit Host Template\"></div>";
        print '<script>';
        # Addhosttemplate button
        print 'var edithosttemplate = function() { ';
        print ' $.getJSON( $("#edithosttemplateform").attr("action"), '; # <- url
        print ' $("#edithosttemplateform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#templatestable").html("").';
        print '      load("'.$url.'&templatestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#edithosttemplatedlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#edithosttemplatedlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Host Template": edithosttemplate, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_hosttemplate_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["edithosttemplate"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["action_url"] ) ) {
            $query_str["action_url"] = strtr( $query_str["action_url"], 
                                           array( '"' => '\"',) );
            $query_str["action_url"] = urlencode($query_str["action_url"]);
        }
        # Handle deleting fields
        if( empty( $query_str["contacts"] ) )
            $query_str["contacts"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
        if( empty( $query_str["notifopts"] ) )
            $query_str["notifopts"] = "-";
        if( ! strlen( $query_str["retainstatusinfo"] ) )
            $query_str["retainstatusinfo"] = "-";
        if( ! strlen( $query_str["retainnonstatusinfo"] ) )
            $query_str["retainnonstatusinfo"] = "-";
        if( ! strlen( $query_str["action_url"] ) )
            $query_str["action_url"] = "-";
        if( ! strlen( $query_str["passivechecks"] ) )
            $query_str["passivechecks"] = "-";
        if( ! strlen( $query_str["notifications_enabled"] ) )
            $query_str["notifications_enabled"] = "-";
        if( ! strlen( $query_str["icon_image"] ) )
            $query_str["icon_image"] = "-";
        if( ! strlen( $query_str["icon_image_alt"] ) )
            $query_str["icon_image_alt"] = "-";
        if( ! strlen( $query_str["statusmap_image"] ) )
            $query_str["statusmap_image"] = "-";
        $json = json_encode( $query_str );

        # Do the REST edit hosttemplate request
        $request = new RestRequest(
          RESTURL.'/modify/hosttemplates',
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
     * NEW SERVICE TEMPLATE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newsvctemplatedialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newsvctemplateform" '.
              'name="newsvctemplateform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=5&newsvctemplate=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="newsvctmpltabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # name
        print '<p>';
        print '<label for="svctemplatename">Template Name *</label>';
        print '<input class="field" type="text" id="svctemplatename" ';
        print ' name="name" required="required" ';
        print '/>';
        print '</p>';
        # Use
        $hts = get_and_sort_servicetemplates( );
        print '<p>';
        print '<label for="use">Parent Template *</label>';
        print '<select class="field" id="use" name="use" required="required">';
        $selected = "selected";
        print '<option value="" selected>None</option>';
        foreach( $hts as $item ) {
            print '<option value="'.$item["name"].'">';
            print $item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contacts
        print '<p>';
        print '<label for="bcontacts">Contacts</label>';
        print '<input class="field" type="text" id="bcontacts" name="contacts" />';
        print '</p>';
        # Contact Groups
        print '<p>';
        print '<label for="bcontactgroups">Contact Groups</label>';
        print '<input class="field" type="text" id="bcontactgroups" name="contactgroups" />';
        print '</p>';
        # Check Interval
        print '<p>';
        print '<label for="checkinterval">Check Interval *</label>';
        print '<input class="field" type="text" id="checkinterval" name="checkinterval"';
        print ' value="5" required="required" />';
        print '</p>';
        # Retry Interval
        print '<p>';
        print '<label for="retryinterval">Retry Interval *</label>';
        print '<input class="field" type="text" id="retryinterval" name="retryinterval"';
        print ' value="1" required="required" />';
        print '</p>';
        # Max Check Attempts
        print '<p>';
        print '<label for="maxcheckattempts">Max Check Attempts *</label>';
        print '<input class="field" type="text" id="maxcheckattempts" '.
              'value="3" name="maxcheckattempts" required="required" />';
        print '</p>';
        # Check Period
        print '<p>';
        print '<label for="xcheckperiod">Check Period *</label>';
        print '<input class="field" type="text" id="xcheckperiod" '.
              'value="24x7" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="xnotifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="xnotifperiod" '.
              'value="24x7" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="60" name="notifinterval" required="required" />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Notification Options
        print '<p>';
        print '<label for="xnotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="xnotifopts" '.
              'value="w u c r" name="notifopts" />';
        print '</p>';
        # Passive Checks
        print '<p>';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        # Notifications Enabled
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        print '<label for="actionurl">Action URL</label>';
        print '<input class="field" type="text" id="action_url" '.
              'value="" name="action_url" />';
        print '</p>';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="siconimg">Icon Image</label>';
        print '<input class="field" type="text" id="icon_image" '.
              'value="" name="icon_image" />';
        print '</p>';
        print '<p>';
        print '<p>';
        print '<label for="siconimgalt">Icon Image Alt</label>';
        print '<input class="field" type="text" id="icon_image_alt" '.
              'value="" name="icon_image_alt" />';
        print '</p>';
        print '<p>';
        print '<label for="sstatusmapimage">Status Map Image</label>';
        print '<input class="field" type="text" id="statusmap_image" '.
              'value="" name="statusmap_image" />';
        print '</p>';
        print '</div>';

        print '</div>';
        print '<script>';
        print '$( "#newsvctmpltabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var bcontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "bcontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var bcontactgroups = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "bcontactgroups" );
        print '</script>';

        # Auto-complete for checkperiod
        $hgs = get_and_sort_timeperiods( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var xcheckperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "xcheckperiod" );
        print '</script>';
        #
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var xnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "xnotifperiod" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_svcnotifopts( "xnotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_new_svctemplate_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newsvctemplatedlg\" ".
              " title=\"New Service Template\"></div>";
        print '<script>';
        # Addsvctemplate button
        print 'var newsvctemplate = function() { ';
        print ' $.getJSON( $("#newsvctemplateform").attr("action"), '; # <- url
        print ' $("#newsvctemplateform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#templatestable").html("").';
        print '      load("'.$url.'&templatestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#newsvctemplatedlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newsvctemplatedlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Service Template": newsvctemplate, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_svctemplate_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newsvctemplate"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["action_url"] ) ) {
            $query_str["action_url"] = strtr( $query_str["action_url"], 
                                           array( '"' => '\"',) );
            $query_str["action_url"] = urlencode($query_str["action_url"]);
        }
        $json = json_encode( $query_str );

        # Do the REST new svctemplate request
        $request = new RestRequest(
          RESTURL.'/add/servicetemplates',
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
     * DELETE SERVICE TEMPLATE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delsvctemplatedialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delsvctemplateform" '.
              'name="delsvctemplateform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=5&delsvctemplate=1';
        print '">';
        print '<h2>About to <b>DELETE</b> svc template:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Host Template' to confirm ".
              "or 'Close' to cancel.</h2>";
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_svctemplate_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delsvctemplatedlg\" ".
              "title=\"Delete Host Template\"></div>";
        print '<script>';
        # Addsvctemplate button
        print 'var delsvctemplate = function() { ';
        print ' $.getJSON( $("#delsvctemplateform").attr("action"), '; #<- url
        print ' $("#delsvctemplateform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#templatestable").html("").';
        print '      load("'.$url.'&templatestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() '.
              '{ $("#delsvctemplatedlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delsvctemplatedlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : '.
              '{ "Delete Service Template": delsvctemplate, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_svctemplate_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delsvctemplate"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add svctemplate request
        $request = new RestRequest(
          RESTURL.'/delete/servicetemplates',
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
     * EDIT SERVICE TEMPLATE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_editsvctemplatedialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/servicetemplates?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="editsvctemplateform" name="editsvctemplateform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=5&editsvctemplate=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="editsvctmpltabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Hostname
        print '<p>';
        print '<label for="svctemplatename">Contact Name *</label>';
        print '<input class="field" type="text" id="svctemplatename" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Use
        $hts = get_and_sort_servicetemplates( );
        print '<p>';
        print '<label for="use">Parent Template *</label>';
        print '<select class="field" id="use" name="use" required="required">';
        $selected = "";
        if( ! isset( $use ) ) $selected = " selected";
        print '<option value="-"'.$selected.'>None</option>';
        foreach( $hts as $item ) {
            $selected = "";
            if( $item["name"] == $name ) continue;
            if( $item["name"] == $use ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>';
            print $item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contacts
        print '<p>';
        print '<label for="ccontacts">Contacts</label>';
        print '<input class="field" type="text" id="ccontacts" name="contacts"';
        print ' value="'.$contacts.'" />';
        print '</p>';
        # Contact Groups
        print '<p>';
        print '<label for="ccontactgroups">Contact Groups</label>';
        print '<input class="field" type="text" id="ccontactgroups" name="contactgroups"';
        print ' value="'.$contactgroups.'" />';
        print '</p>';
        # Check Interval
        print '<p>';
        print '<label for="checkinterval">Check Interval *</label>';
        print '<input class="field" type="text" id="checkinterval" name="checkinterval"';
        print ' value="'.$checkinterval.'" required="required" />';
        print '</p>';
        # Retry Interval
        print '<p>';
        print '<label for="retryinterval">Retry Interval *</label>';
        print '<input class="field" type="text" id="retryinterval" name="retryinterval"';
        print ' value="'.$retryinterval.'" required="required" />';
        print '</p>';
        # Max Check Attempts
        print '<p>';
        print '<label for="maxcheckattempts">Max Check Attempts *</label>';
        print '<input class="field" type="text" id="maxcheckattempts" '.
              'value="'.$maxcheckattempts.'" name="maxcheckattempts" required="required" />';
        print '</p>';
        # Check Period
        print '<p>';
        print '<label for="ycheckperiod">Check Period *</label>';
        print '<input class="field" type="text" id="ycheckperiod" '.
              'value="'.$checkperiod.'" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="ynotifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="ynotifperiod" '.
              'value="'.$notifperiod.'" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="'.$notifinterval.'" name="notifinterval" required="required" />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Notification Options
        print '<p>';
        print '<label for="ynotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="ynotifopts" '.
              'value="'.$notifopts.'" name="notifopts" />';
        print '</p>';
        # Passive Checks
        print '<p>';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        $selected=""; if( ! strlen($passivechecks) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $passivechecks == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $passivechecks == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        $selected=""; if( ! strlen($notifications_enabled) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $notifications_enabled == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $notifications_enabled == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        $new_actionurl = urldecode( $action_url );
        $new_actionurl = strtr( $new_actionurl, array("\""=>"\\\"","\\"=>"\\\\") );
        print '<label for="actionurl">Action URL</label>';
        print '<input class="field" type="text" id="action_url" '.
              'value="'.$new_actionurl.'" name="action_url" />';
        print '</p>';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        $selected=""; if( ! strlen($retainstatusinfo) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
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
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $retainnonstatusinfo == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $retainnonstatusinfo == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="siconimg">Icon Image</label>';
        print '<input class="field" type="text" id="icon_image" '.
              'value="'.$icon_image.'" name="icon_image" />';
        print '</p>';
        print '<p>';
        print '<p>';
        print '<label for="siconimgalt">Icon Image Alt</label>';
        print '<input class="field" type="text" id="icon_image_alt" '.
              'value="'.$icon_image_alt.'" name="icon_image_alt" />';
        print '</p>';
        print '<p>';
        print '<label for="sstatusmapimage">Status Map Image</label>';
        print '<input class="field" type="text" id="statusmap_image" '.
              'value="" name="statusmap_image" />';
        print '</p>';
        print '</div>';

        print '</div>';
        print '<script>';
        print '$( "#editsvctmpltabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var ccontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "ccontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var ccontactgroups = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "ccontactgroups" );
        print '</script>';

        # Auto-complete for checkperiod
        $hgs = get_and_sort_timeperiods( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var ycheckperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "ycheckperiod" );
        print '</script>';
        #
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var ynotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "ynotifperiod" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_svcnotifopts( "ynotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_edit_svctemplate_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"editsvctemplatedlg\" title=\"Edit Service Template\"></div>";
        print '<script>';
        # Addsvctemplate button
        print 'var editsvctemplate = function() { ';
        print ' $.getJSON( $("#editsvctemplateform").attr("action"), '; # <- url
        print ' $("#editsvctemplateform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#templatestable").html("").';
        print '      load("'.$url.'&templatestable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editsvctemplatedlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editsvctemplatedlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Service Template": editsvctemplate, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_svctemplate_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["editsvctemplate"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["action_url"] ) ) {
            $query_str["action_url"] = strtr( $query_str["action_url"], 
                                           array( '"' => '\"',) );
            $query_str["action_url"] = urlencode($query_str["action_url"]);
        }
        # Handle deleting fields
        if( empty( $query_str["contacts"] ) )
            $query_str["contacts"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
        if( empty( $query_str["notifopts"] ) )
            $query_str["notifopts"] = "-";
        if( ! strlen( $query_str["retainstatusinfo"] ) )
            $query_str["retainstatusinfo"] = "-";
        if( ! strlen( $query_str["retainnonstatusinfo"] ) )
            $query_str["retainnonstatusinfo"] = "-";
        if( ! strlen( $query_str["action_url"] ) )
            $query_str["action_url"] = "-";
        if( ! strlen( $query_str["passivechecks"] ) )
            $query_str["passivechecks"] = "-";
        if( ! strlen( $query_str["notifications_enabled"] ) )
            $query_str["notifications_enabled"] = "-";
        if( ! strlen( $query_str["icon_image"] ) )
            $query_str["icon_image"] = "-";
        if( ! strlen( $query_str["icon_image_alt"] ) )
            $query_str["icon_image_alt"] = "-";
        if( ! strlen( $query_str["statusmap_image"] ) )
            $query_str["statusmap_image"] = "-";
        $json = json_encode( $query_str );

        # Do the REST edit svctemplate request
        $request = new RestRequest(
          RESTURL.'/modify/servicetemplates',
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

    /*

       ===================================================================

                              END OF TEMPLATES TAB

       ===================================================================

     */

    /***********************************************************************
     *
     * CONTACTS TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_contacts_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        #/*
        print "<script>".
              "$(document).ready(function() {".
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
              # Load the right pane
              #'$("#contactstable").html("").'.
              '$("#contactstable").'.
              'load("'.$url.'&contactstable=true");'.
              "  });".
              "</script>";
        #*/

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
        #show_hosts_tab_right_pane( );
        print '<div id="contactstable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_contacts_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_contacts_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        show_apply_button();

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_contacts_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        show_contactgroups_list( );

        print "<p>&nbsp;</p>";

        show_contacts_list( );
    }

    # ------------------------------------------------------------------------
    function show_contactgroups_list( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_contactgroups( $sort=$g_sort );
        } else {
            $a = get_and_sort_contactgroups( );
        }

        print "<p>".count($a)." contact groups.</p>";
        print "<table><thead><tr>";

        # Sort by name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by full name
        $g_sort_new = "alias";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Alias </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by email address
        $g_sort_new = "members";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Members </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" ".
              " title=\"Add New Contact Group\" onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newcontactgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=4&newcontactgroupdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // FULL NAME
            print "<td>".$item['alias']."</td>";
            // EMAIL ADDRESS
            print "<td>".$item['members']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Contact Group\"";
            print " onClick=\"".
              #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#editcontactgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=4&editcontactgroupdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" ".
                  " title=\"Delete Contact Group\"";
            print " onClick=\"".
            #"if( confirm('sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delcontactgroupdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=4&delcontactgroupdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    # ------------------------------------------------------------------------
    function show_contacts_list( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new;

        if( isset($g_sort) ) {
            $a = get_and_sort_contacts( $sort=$g_sort );
        } else {
            $a = get_and_sort_contacts( );
        }

        print "<p>".count($a)." contacts.</p>";
        print "<table><thead><tr>";

        # Sort by name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by full name
        $g_sort_new = "alias";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Alias </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by email address
        $g_sort_new = "emailaddr";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Email Address </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" title=\"Add New Contact\"".
              " onClick=\"".
        #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newcontactdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=4&newcontactdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td>".$item['name']."</td>";
            // FULL NAME
            print "<td>".$item['alias']."</td>";
            // EMAIL ADDRESS
            print "<td>".$item['emailaddr']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-edit\" title=\"Edit Contact\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#editcontactdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=4&editcontactdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" ".
                  "title=\"Delete Contact\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delcontactdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME.
              "?tab=4&delcontactdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            ++$num;
        }
        print "</tbody>";
        print "</table>";
    }

    /***********************************************************************
     *
     * NEW CONTACT DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newcontactdialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newcontactform" '.
              'name="newcontactform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=4&newcontact=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="newcontactabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # name
        print '<p>';
        print '<label for="contactname">Contact Name *</label>';
        print '<input class="field" type="text" id="contactname" ';
        print ' name="name" required="required" ';
        print '/>';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias</label>';
        print '<input class="field" type="text" id="alias" name="alias" ';
        print ' required="required" />';
        print '</p>';
        # Email Address
        print '<p>';
        print '<label for="emailaddr">Email Address</label>';
        print '<input class="field" type="text" id="emailaddr" name="emailaddr" />';
        print '</p>';
        # Service Notification Period
        print '<p>';
        print '<label for="xsvcnotifperiod">Service Notif Period *</label>';
        print '<input class="field" type="text" id="xsvcnotifperiod" '.
              'value="24x7" name="svcnotifperiod" />';
        print '</p>';
        # Service Notification Options
        print '<p>';
        print '<label for="svcnotifopts">Service Notif Opts *</label>';
        print '<input class="field" type="text" id="svcnotifopts" '.
              'value="w u c r" name="svcnotifopts" />';
        print '</p>';
        # Service Notification Commands
        print '<p>';
        print '<label for="svcnotifcmds">Service Notif Cmd *</label>';
        print '<input class="field" type="text" id="svcnotifcmds" '.
              'value="notify-service-by-email" name="svcnotifcmds" />';
        print '</p>';
        # Host Notification Period
        print '<p>';
        print '<label for="xhstnotifperiod">Host Notif Period *</label>';
        print '<input class="field" type="text" id="xhstnotifperiod" '.
              'value="24x7" name="hstnotifperiod" />';
        print '</p>';
        # Host Notification Options
        print '<p>';
        print '<label for="hstnotifopts">Host Notif Opts *</label>';
        print '<input class="field" type="text" id="hstnotifopts" '.
              'value="d u r" name="hstnotifopts" />';
        print '</p>';
        # Host Notification Commands
        print '<p>';
        print '<label for="hstnotifcmds">Host Notif Cmd *</label>';
        print '<input class="field" type="text" id="hstnotifcmds" '.
              'value="notify-host-by-email" name="hstnotifcmds" />';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="cansubmitcmds">Can Submit Cmds</label>';
        #$checked="checked";
        #if( $activechecks == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="cansubmitcmds"';
        print ' name="cansubmitcmds" />';
        #print ' name="activechecks" '.$checked.' />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        print '<p>';
        print '<label for="ssvcnotifenabled">Service Notifications</label>';
        print '<input class="field" type="checkbox" id="svcnotifenabled"';
        print ' name="svcnotifenabled" checked />';
        print '</p>';
        print '<p>';
        print '<label for="shstnotifenabled">Host Notifications</label>';
        print '<input class="field" type="checkbox" id="hstnotifenabled"';
        print ' name="hstnotifenabled" checked />';
        print '</p>';
        print '<p>';
        print '<label for="pager">Pager</label>';
        print '<input class="field" type="text" id="pager" name="pager"';
        print ' value="" />';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        print '<option value="" selected>Nagios default</option>';
        print '<option value="1" >Enabled</option>';
        print '<option value="0" >Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        print '<option value="" selected>Nagios default</option>';
        print '<option value="1" >Enabled</option>';
        print '<option value="0" >Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        print '</div>';
        print '<script>';
        print '$( "#newcontactabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';


        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_hstnotifopts( "hstnotifopts" );
        autocomplete_svcnotifopts( "svcnotifopts" );
        print '});';
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var hstnotifcmds = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "hstnotifcmds" );
        print '</script>';

        # Auto-complete for commands
        #$hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var svcnotifcmds = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "svcnotifcmds" );
        print '</script>';

        # Auto-complete for checkperiod
        $hgs = get_and_sort_timeperiods( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var xhstnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "xhstnotifperiod" );
        print '</script>';
        #
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var xsvcnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "xsvcnotifperiod" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_new_contact_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newcontactdlg\" ".
              " title=\"New Contact\"></div>";
        print '<script>';
        # Addcontact button
        print 'var newcontact = function() { ';
        print ' $.getJSON( $("#newcontactform").attr("action"), '; # <- url
        print ' $("#newcontactform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#contactstable").html("").';
        print '      load("'.$url.'&contactstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#newcontactdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newcontactdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Contact": newcontact, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_contact_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newcontact"] );
        $query_str["folder"] = FOLDER;
        # Handle check box
        if( isset( $query_str["cansubmitcmds"] ) )
            $query_str["cansubmitcmds"] = "1";
        else
            $query_str["cansubmitcmds"] = "0";
        if( isset( $query_str["svcnotifenabled"] ) )
            $query_str["svcnotifenabled"] = "1";
        else
            $query_str["svcnotifenabled"] = "0";
        if( isset( $query_str["hstnotifenabled"] ) )
            $query_str["hstnotifenabled"] = "1";
        else
            $query_str["hstnotifenabled"] = "0";
        #clean_query_str( $query_str );
        $json = json_encode( $query_str );

        # Do the REST new contact request
        $request = new RestRequest(
          RESTURL.'/add/contacts',
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
     * DELETE CONTACT DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delcontactdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delcontactform" '.
              'name="delcontactform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=4&delcontact=1';
        print '">';
        print '<h2>About to <b>DELETE</b> contact:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Contact Group' to confirm ".
              "or 'Close' to cancel.</h2>";
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_contact_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delcontactdlg\" ".
              "title=\"Delete Contact\"></div>";
        print '<script>';
        # Addcontact button
        print 'var delcontact = function() { ';
        print ' $.getJSON( $("#delcontactform").attr("action"), '; #<- url
        print ' $("#delcontactform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#contactstable").html("").';
        print '      load("'.$url.'&contactstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() '.
              '{ $("#delcontactdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delcontactdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : '.
              '{ "Delete Contact": delcontact, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_contact_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delcontact"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add contact request
        $request = new RestRequest(
          RESTURL.'/delete/contacts',
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
     * EDIT CONTACT DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_editcontactdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/contacts?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="editcontactform" name="editcontactform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=4&editcontact=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="editcontactabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # name
        print '<p>';
        print '<label for="contactname">Contact Name *</label>';
        print '<input class="field" type="text" id="contactname" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # use
        #print '<p>';
        #print '<label for="use">Use *</label>';
        #print '<input class="field" type="text" id="use" ';
        #print ' name="use" required="required" ';
        #print ' value="'.$use.'" />';
        #print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" ';
        print ' value="'.$alias.'" required="required" />';
        print '</p>';
        # Email Address
        print '<p>';
        print '<label for="emailaddr">Email Address</label>';
        print '<input class="field" type="text" id="emailaddr" name="emailaddr"';
        print ' value="'.$emailaddr.'" />';
        print '</p>';
        # Service Notification Period
        print '<p>';
        print '<label for="wsvcnotifperiod">Service Notif Period</label>';
        print '<input class="field" type="text" id="wsvcnotifperiod" '.
              'name="svcnotifperiod"';
        print ' value="'.$svcnotifperiod.'" />';
        print '</p>';
        # Service Notification Options
        print '<p>';
        print '<label for="asvcnotifopts">Service Notif Opts</label>';
        print '<input class="field" type="text" id="asvcnotifopts" '.
              'name="svcnotifopts"';
        print ' value="'.$svcnotifopts.'" />';
        print '</p>';
        # Service Notification Commands
        print '<p>';
        print '<label for="svcnotifcmds">Service Notif Cmd</label>';
        print '<input class="field" type="text" id="asvcnotifcmds" '.
              'name="svcnotifcmds"';
        print ' value="'.$svcnotifcmds.'" />';
        print '</p>';
        # Host Notification Period
        print '<p>';
        print '<label for="whstnotifperiod">Host Notif Period</label>';
        print '<input class="field" type="text" id="whstnotifperiod" '.
              'name="hstnotifperiod"';
        print ' value="'.$hstnotifperiod.'" />';
        print '</p>';
        # Host Notification Options
        print '<p>';
        print '<label for="ahstnotifopts">Host Notif Opts</label>';
        print '<input class="field" type="text" id="ahstnotifopts" '.
              'name="hstnotifopts"';
        print ' value="'.$hstnotifopts.'" />';
        print '</p>';
        # Host Notification Commands
        print '<p>';
        print '<label for="hstnotifcmds">Host Notif Cmd</label>';
        print '<input class="field" type="text" id="ahstnotifcmds" '.
              'name="hstnotifcmds"';
        print ' value="'.$hstnotifcmds.'" />';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="cansubmitcmds">Can Submit Cmds</label>';
        $checked="checked";
        if( $cansubmitcmds == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="cansubmitcmds"';
        print ' name="cansubmitcmds" '.$checked.' />';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        print '<p>';
        print '<label for="ssvcnotifenabled">Service Notifications</label>';
        $checked="checked";
        if( $svcnotifenabled == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="svcnotifenabled"';
        print ' name="svcnotifenabled" '.$checked.' />';
        print '</p>';
        print '<p>';
        print '<label for="shstnotifenabled">Host Notifications</label>';
        $checked="checked";
        if( $hstnotifenabled == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="hstnotifenabled"';
        print ' name="hstnotifenabled" '.$checked.' />';
        print '</p>';
        print '<p>';
        print '<label for="pager">Pager</label>';
        print '<input class="field" type="text" id="pager" name="pager"';
        print ' value="'.$pager.'" />';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        $selected=""; if( ! strlen($retainstatusinfo) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
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
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $retainnonstatusinfo == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $retainnonstatusinfo == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';

        print '</div>';
        print '<script>';
        print '$( "#editcontactabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_hstnotifopts( "ahstnotifopts" );
        autocomplete_svcnotifopts( "asvcnotifopts" );
        print '});';
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var ahstnotifcmds = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "ahstnotifcmds" );
        print '</script>';

        # Auto-complete for commands
        #$hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var asvcnotifcmds = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "asvcnotifcmds" );
        print '</script>';

        # Auto-complete for checkperiod
        $hgs = get_and_sort_timeperiods( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var whstnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "whstnotifperiod" );
        print '</script>';
        #
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var wsvcnotifperiod = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "wsvcnotifperiod" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_edit_contact_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"editcontactdlg\" title=\"Edit Contact\"></div>";
        print '<script>';
        # Addcontact button
        print 'var editcontact = function() { ';
        print ' $.getJSON( $("#editcontactform").attr("action"), '; # <- url
        print ' $("#editcontactform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#contactstable").html("").';
        print '      load("'.$url.'&contactstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editcontactdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editcontactdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Contact": editcontact, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_contact_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["editcontact"] );
        $query_str["folder"] = FOLDER;
        # Handle check box
        if( isset( $query_str["cansubmitcmds"] ) )
            $query_str["cansubmitcmds"] = "1";
        else
            $query_str["cansubmitcmds"] = "0";
        if( isset( $query_str["svcnotifenabled"] ) )
            $query_str["svcnotifenabled"] = "1";
        else
            $query_str["svcnotifenabled"] = "0";
        if( isset( $query_str["hstnotifenabled"] ) )
            $query_str["hstnotifenabled"] = "1";
        else
            $query_str["hstnotifenabled"] = "0";
        # Handle deleting fields
        if( ! strlen( $query_str["retainstatusinfo"] ) )
            $query_str["retainstatusinfo"] = "-";
        if( ! strlen( $query_str["retainnonstatusinfo"] ) )
            $query_str["retainnonstatusinfo"] = "-";
        if( empty( $query_str["svcnotifperiod"] ) )
            $query_str["svcnotifperiod"] = "-";
        if( empty( $query_str["svcnotifopts"] ) )
            $query_str["svcnotifopts"] = "-";
        if( empty( $query_str["svcnotifcmds"] ) )
            $query_str["svcnotifcmds"] = "-";
        if( empty( $query_str["hstnotifperiod"] ) )
            $query_str["hstnotifperiod"] = "-";
        if( empty( $query_str["hstnotifcmds"] ) )
            $query_str["hstnotifcmds"] = "-";
        if( empty( $query_str["pager"] ) )
            $query_str["pager"] = "-";
        if( empty( $query_str["emailaddr"] ) )
            $query_str["emailaddr"] = "-";
        if( empty( $query_str["hstnotifopts"] ) )
            $query_str["hstnotifopts"] = "-";
        $json = json_encode( $query_str );

        # Do the REST edit contact request
        $request = new RestRequest(
          RESTURL.'/modify/contacts',
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
     * NEW CONTACT GROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_newcontactgroupdialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="newcontactgroupform" '.
              'name="newcontactgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=4&newcontactgroup=1';
        print '">';
        print '<fieldset>';
        # Hostname
        print '<p>';
        print '<label for="contactgroupname">Contact Group Name *</label>';
        print '<input class="field" type="text" id="contactgroupname" ';
        print ' name="name" required="required" ';
        print '/>';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" ';
        print ' required="required" />';
        print '</p>';
        # Members
        print '<p>';
        print '<label for="amembers">Members<br>(space delimited)</label>';
        print '<input class="field" type="text" id="amembers" name="members" ';
        print '/>';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var amembers = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "amembers" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_new_contactgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newcontactgroupdlg\" ".
              " title=\"New Contact Group\"></div>";
        print '<script>';
        # Addcontact button
        print 'var newcontactgroup = function() { ';
        print ' $.getJSON( $("#newcontactgroupform").attr("action"), '; # <- url
        print ' $("#newcontactgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#contactstable").html("").';
        print '      load("'.$url.'&contactstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#newcontactgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newcontactgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Contact Group": newcontactgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_contactgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["newcontactgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST new contactgroup request
        $request = new RestRequest(
          RESTURL.'/add/contactgroups',
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
     * DELETE CONTACT GROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delcontactgroupdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delcontactgroupform" '.
              'name="delcontactgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=4&delcontactgroup=1';
        print '">';
        print '<h2>About to <b>DELETE</b> contact group:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Contact Group' to confirm ".
              "or 'Close' to cancel.</h2>";
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_contactgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delcontactgroupdlg\" ".
              "title=\"Delete Contact Group\"></div>";
        print '<script>';
        # Addcontact button
        print 'var delcontactgroup = function() { ';
        print ' $.getJSON( $("#delcontactgroupform").attr("action"), '; #<- url
        print ' $("#delcontactgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#contactstable").html("").';
        print '      load("'.$url.'&contactstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() '.
              '{ $("#delcontactgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delcontactgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : '.
              '{ "Delete Contact Group": delcontactgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_contactgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["delcontactgroup"] );
        $query_str["folder"] = FOLDER;
        $json = json_encode( $query_str );

        # Do the REST add contact request
        $request = new RestRequest(
          RESTURL.'/delete/contactgroups',
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
     * EDIT CONTACT GROUP DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_editcontactgroupdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/contactgroups?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="editcontactgroupform" name="editcontactgroupform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=4&editcontactgroup=1';
        print '">';
        print '<fieldset>';
        # Hostname
        print '<p>';
        print '<label for="contactgroupname">Contact Group Name *</label>';
        print '<input class="field" type="text" id="contactgroupname" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" ';
        print ' value="'.$alias.'" required="required" />';
        print '</p>';
        # Members
        print '<p>';
        print '<label for="bmembers">Members<br>(space delimited)</label>';
        print '<input class="field" type="text" id="bmembers" name="members" ';
        print ' value="'.$members.'" />';
        print '</p>';
        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var bmembers = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "bmembers" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_edit_contactgroup_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"editcontactgroupdlg\" title=\"Edit Contact Group\"></div>";
        print '<script>';
        # Addcontact button
        print 'var editcontactgroup = function() { ';
        print ' $.getJSON( $("#editcontactgroupform").attr("action"), '; # <- url
        print ' $("#editcontactgroupform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#contactstable").html("").';
        print '      load("'.$url.'&contactstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editcontactgroupdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editcontactgroupdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Edit Contact Group": editcontactgroup, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function edit_contactgroup_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Hostgroup' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["tab"] );
        unset( $query_str["editcontactgroup"] );
        $query_str["folder"] = FOLDER;
        # Handle deleting fields
        if( empty( $query_str["members"] ) )
            $query_str["members"] = "-";
        $json = json_encode( $query_str );

        # Do the REST edit contactgroup request
        $request = new RestRequest(
          RESTURL.'/modify/contactgroups',
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

    /*

       ===================================================================

                              END OF CONTACTS TAB

       ===================================================================

     */

    /**********************************************************************
     *
     * SERVICESETS TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_servicesets_page( ) {
    # ------------------------------------------------------------------------

        global $g_tab;

        $url = create_url( );

        # Not so nice, disable Enter key.
        print "<script>".
              "$(document).ready(function() {".
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
              # Load the right pane
              #'$("#hoststable").html("").'.
              '$("#svcsetstable").'.
              'load("'.$url.'&svcsetstable=true");'.
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
        #show_hosts_tab_right_pane( );
        print '<div id="svcsetstable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_servicesets_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_servicesets_tab_left_pane( ) {
    # ------------------------------------------------------------------------

        show_apply_button( );

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_servicesets_tab_right_pane( ) {
    # ------------------------------------------------------------------------

        $s = get_and_sort_servicesets_unique( );
        print "<p>".count($s)." servicesets.</p>";
        print "<table><thead><tr>";

        # Sort by host name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" title=\"Add New Service Set\" onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newsvcsetdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=1&newsvcsetdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $s as $item ) {
            if( $num % 2 == 0 )
                print "<tr class=shaded>";
            else
                print "<tr>";

            // NAME
            print "<td><span id=\"$num\" class=link> + ".$item."</span></td>";
            // Actions
            print "<td style=\"float: right\">";
            print "<a class=\"icon icon-clone\" title=\"Clone Service Set\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#clonesvcsetdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=1&clonesvcsetdialog=true".
              "&amp;name=".$item."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Service Set\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delsvcsetdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=1&delsvcsetdialog=true".
              "&amp;name=".$item."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "</tr>";
            // SERVICES FOR THIS SERVICESET - HIDDEN
            print "<tr id='hid$num' class=hidden>";
            print "<td colspan=\"5\">".
                  #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
                  "Loading...".
                  "</td></tr>";
            # Save in names array for later 'bind'.
            $names[$num]=$item;
            ++$num;
        }
        print "</tbody>";
        print "</table>";

        print "<script>";
        for( $x=1 ; $x<$num ; $x++ ) {
            print "$('#$x').bind('click', function() {";
            print " $.get('/nagrestconf/".SCRIPTNAME."?tab=1&fragment1id=";
            print $names[$x];
            print "', function(data) {";
            print " $('#hid$x').html(data);";
            #print ' alert(data);';
            print " }); ";
            print " $('#hid$x').toggleClass(\"hidden\"); });";
            print "$('#$x').bind('mouseenter mouseleave', function(event){";
            print " $(this).toggleClass(\"linkover\");});";
        }
        print "</script>";
    }

    /***********************************************************************
     *
     * DELETE SERVICESET DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delsvcsetdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Service Set

        print '<form id="delsvcsetform" name="delsvcsetform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&delsvcset=1';
        print '">';
        print '<h2>About to <b>DELETE</b> svcset:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Service Set' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<p>';
        print '<input class="field" type="checkbox" id="delsvcsetservices"';
        print ' name="delsvcsetservices" value="1" />';
        print '<label for="delsvcsetservices">Click to confirm deletion of all';
        print ' services in this service set.</label>';
        print '</p>';
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_svcset_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Service Set

        # 'Add New Service Set' dialog box div
        print "<div id=\"delsvcsetdlg\" title=\"Delete Service Set\"></div>";
        print '<script>';
        # Addsvcset button
        print 'var delsvcset = function() { ';
        print ' $.getJSON( $("#delsvcsetform").attr("action"), '; # <- url
        print ' $("#delsvcsetform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#delsvcsetdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delsvcsetdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Delete Service Set": delsvcset, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_svcset_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["delsvcset"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["delsvcsetservices"] ) ) {
            unset( $query_str["delsvcsetservices"] );
            if( isset( $query_str["name"] ) ) {
                $a = array();
                $a["name"] = $query_str["name"];
                $a["svcdesc"] = '.*';
                $a["folder"] = FOLDER;
                $json = json_encode( $a );
                $request2 = new RestRequest(
                  RESTURL.'/delete/servicesets',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request2 );
                $request2->execute();
                $slist = json_decode( $request2->getResponseBody(), true );
                ### Check $slist->http_code ###
            } else {
                $retval["message"] = "Internal error: name empty";
                $retval["code"] = "400";
                print( json_encode( $retval ) );
                exit( 0 );
            }
        } else {
                $retval["message"] = "Error: Deletion was not confirmed.";
                $retval["code"] = "400";
                print( json_encode( $retval ) );
                exit( 0 );
        }

        #$json = json_encode( $query_str );

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $resp = $request2->getResponseInfo();
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit( 0 );
    }

    /***********************************************************************
     *
     * ADD NEW SERVICSET DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_new_svcset_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Service Set

        # 'Add New Service Set' dialog box div
        print "<div id=\"newsvcsetdlg\" title=\"Add New Service Set\"></div>";
        print '<script>';
        # Addsvcset button
        print 'var addsvcset = function() { ';
        print ' $.getJSON( $("#newsvcsetform").attr("action"), '; # <- url
        print ' $("#newsvcsetform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#newsvcsetdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newsvcsetdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Add Service Set": addsvcset, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_newsvcsetdialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Service Set

        print '<form id="newsvcsetform" name="newsvcsetform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&newsvcset=1';
        print '">';
        print '<fieldset>';
        print '<p>A new service set can be added by creating a new ';
        print 'service in the service sets table. Type the name of the ';
        print 'new service set below, and a PING check will be created ';
        print 'to initialise the service set.</p>';
        print '<p style="font-weight:bold;">';
        print "$name</p>";
        # Hostname
        print '<p>';
        print '<label for="csvcsetname">New service set name *</label>';
        print '<input class="field" type="text" id="csvcsetname" name="newname"'.
              ' required="required" />';
        print '</p>';
        print "<p>Click 'Add Service Set' to confirm or 'Close' to cancel.</p>";
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
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
    function add_new_svcset_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Service Set' dialog
    # JSON is returned to the dialog.

        $a = get_and_sort_servicetemplates();

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["newsvcset"] );
        $query_str["folder"] = FOLDER;
        $query_str["template"] = $a[0]["name"];
        $query_str["name"] = $query_str["newname"];
        $query_str["command"] = "check_ping!100.0,20%!500.0,60%";
        $query_str["svcdesc"] = "PING";
        unset( $query_str["newsvcset"] );
        $json = json_encode( $query_str );

        # Do the REST add svcset request
        $request = new RestRequest(
          RESTURL.'/add/servicesets',
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
     * CLONE SERVICSET DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_clone_svcset_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"clonesvcsetdlg\" title=\"Clone Service Set\"></div>";
        print '<script>';
        # Addhost button
        print 'var clonesvcset = function() { ';
        print ' $.getJSON( $("#clonesvcsetform").attr("action"), '; # <- url
        print ' $("#clonesvcsetform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#clonesvcsetdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#clonesvcsetdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Clone Service Set": clonesvcset, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_clonesvcsetdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="clonesvcsetform" name="clonesvcsetform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&clonesvcset=1';
        print '">';
        print '<fieldset>';
        print '<p>Clone service set:</p>';
        print '<p style="font-weight:bold;">';
        print "$name</p>";
        # Hostname
        print '<p>';
        print '<label for="csvcsetname">New service set name *</label>';
        print '<input class="field" type="text" id="csvcsetname" name="copyto"'.
              ' required="required" />';
        print '</p>';
        print "<p>Click 'Clone Service Set' to confirm or 'Close' to cancel.</p>";
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
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
    function clone_svcset_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Clone Service' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        # Sanity checks
        if( ! isset( $query_str["name"] ) ) {
            $retval["message"] = "Internal error: name or svcdesc empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $query_str["copyto"] )
                   || empty( $query_str["copyto"] ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( strchr( $query_str["copyto"],"*" ) ) {
            $retval["message"] = "Wildcard not allowed.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        $tohost = trim( $query_str["copyto"] );
        $fromhost = $query_str["name"];

        # Clone the host

        # Get service details from original host from REST
        unset( $request );
        $request = new RestRequest(
        RESTURL.'/show/servicesets?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($fromhost).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        foreach( $hlist as $svc ) {
            foreach( $svc as $item ) extract( $item );

            # Change " to \". otherwise we get 'folder not found'
            if( isset( $svcdesc ) ) {
                $svcdesc = strtr( $svcdesc, array( '%22' => '%5C%22',) );
            }
            if( isset( $command ) ) {
                $command = strtr( $command, array( '%22' => '%5C%22',) );
            }

            $newservice["folder"] = FOLDER;
            $newservice["name"] = $tohost;
            $newservice["template"] = $template;
            $newservice["command"] =  $command;
            $newservice["svcdesc"] = $svcdesc;
            $newservice["svcgroup"] = $svcgroup;
            $newservice["contacts"] = $contacts;
            $newservice["contactgroups"] = $contactgroups;
            $newservice["freshnessthresh"] = $freshnessthresh;
            $newservice["activechecks"] = $activechecks;
            $newservice["customvars"] = $customvars;
            $newservice["maxcheckattempts"] = $maxcheckattempts;
            $newservice["checkinterval"] = $checkinterval;
            $newservice["retryinterval"] = $retryinterval;
            $newservice["passivechecks"] = $passivechecks;
            $newservice["manfreshnessthresh"] = $manfreshnessthresh;
            $newservice["checkfreshness"] = $checkfreshness;
            $newservice["retainstatusinfo"] = $retainstatusinfo;
            $newservice["retainnonstatusinfo"] = $retainnonstatusinfo;
            $json = json_encode( $newservice );
            $request = new RestRequest(
              RESTURL.'/add/servicesets',
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
            if( $retval["code"] != 200 ) {
                print( json_encode( $retval ) );
                exit( 0 );
            }
        }

        $retval["message"] = "Success. Service set cloned.";
        $retval["code"] = 200;
        print( json_encode( $retval ) );

        exit( 0 );
    }

    /***********************************************************************
     *
     * CLONE SERVICESET SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_clone_svcset_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"clonesvcsetsvcdlg\" title=\"Clone Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var clonesvcsetsvc = function() { ';
        print ' $.getJSON( $("#clonesvcsetsvcform").attr("action"), '; # <- url
        print ' $("#clonesvcsetsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#clonesvcsetsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#clonesvcsetsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Clone Service": clonesvcsetsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_clonesvcsetsvcdialog_buttons( $name, $svcdesc ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="clonesvcsetsvcform" name="clonesvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&clonesvcsetsvc=1';
        print '">';
        print '<fieldset>';
        print '<p>Clone service:</p>';
        print '<p style="font-weight:bold;">';
        print "&quot;".urldecode($svcdesc)."&quot; in <br>$name</p>";
        # Hostname
        print '<p>';
        print '<label for="chostname">Copy to service set *</label>';
        print '<input class="field" type="text" id="chostname" name="copyto"'.
              ' required="required" />';
        print '</p>';
        print '<p>';
        print '<label for="csvcdesc">New service name *</label>';
        print '<input class="field" type="text" id="csvcdesc" name="newsvcdesc"'.
              ' required="required" value="'.urldecode($svcdesc).'" />';
        print '</p>';
        print "<p>Click 'Clone Service' to confirm or 'Close' to cancel.</p>";
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '<input type="hidden" name="svcdesc" value="';
        print urldecode($svcdesc);
        print '"/>';
        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for service sets
        $hgs = get_and_sort_servicesets_unique( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var chostname = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "chostname" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function clone_svcset_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Clone Service' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        # Sanity checks
        if( ! ( isset( $query_str["name"] )
              && isset( $query_str["svcdesc"] )
            ) ) {
            $retval["message"] = "Internal error: name or svcdesc empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $query_str["copyto"] )
                   || empty( $query_str["copyto"] ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $query_str["newsvcdesc"] )
                   || empty( $query_str["newsvcdesc"] ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } 

        $copyto = trim( $query_str["copyto"] );
        # Does the copyto host exist?
        $a = get_and_sort_servicesets( $copyto );
        $itemfound = 0;
        foreach( $a as $item ) {
            if( isset($item["name"]) && $item["name"]==$copyto )
                $itemfound = 1;
        }
        if( $itemfound == 0 ) {
            $retval["message"] = "Serviceset '".$copyto."' not found.";
            $retval["code"] = 400;
            print( json_encode( $retval ) );
            exit( 0 );
        }

        # Rely on fact that "name" is always the first item in the list
        # returned from any REST request.
        $tohost = $a[0]["name"];

        $fromhost = $query_str["name"];
        $fromsvc = $query_str["svcdesc"];

        # Get service details from REST
        unset( $request );
        $request = new RestRequest(
        RESTURL.'/show/servicesets?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($fromhost).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        # Can't search for specific service check using the REST interface.
        # Have to ask for all services for the host (above) and search it:
        foreach( $hlist as $svc ) {
            foreach( $svc as $item ) extract( $item );
            if( $svcdesc == urlencode($fromsvc) ) break;
        }

        # Change " to \". otherwise we get 'folder not found'
        if( isset( $svcdesc ) ) {
            $svcdesc = strtr( $svcdesc, array( '%22' => '%5C%22',) );
        }
        if( isset( $command ) ) {
            $command = strtr( $command, array( '%22' => '%5C%22',) );
        }
        #if( isset( $query_str("newsvcdesc") ) ) {
        #    $newsvcdesc = strtr( $newsvcdesc, array( '%22' => '%5C%22',) );
        #}

        $newservice["folder"] = FOLDER;
        $newservice["name"] = $tohost;
        $newservice["template"] = $template;
        $newservice["command"] =  $command;
        $newservice["svcdesc"] = $query_str["newsvcdesc"];
        $newservice["svcgroup"] = $svcgroup;
        $newservice["contacts"] = $contacts;
        $newservice["contactgroups"] = $contactgroups;
        $newservice["freshnessthresh"] = $freshnessthresh;
        $newservice["activechecks"] = $activechecks;
        $newservice["customvars"] = $customvars;
        $newservice["maxcheckattempts"] = $maxcheckattempts;
        $newservice["checkinterval"] = $checkinterval;
        $newservice["retryinterval"] = $retryinterval;
        $newservice["passivechecks"] = $passivechecks;
        $newservice["manfreshnessthresh"] = $manfreshnessthresh;
        $newservice["checkfreshness"] = $checkfreshness;
        $newservice["retainstatusinfo"] = $retainstatusinfo;
        $newservice["retainnonstatusinfo"] = $retainnonstatusinfo;
        $json = json_encode( $newservice );
        $request = new RestRequest(
          RESTURL.'/add/servicesets',
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
     * DELETE SERVICESET SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delete_svcset_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delsvcsetsvcdlg\" title=\"Delete Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var delsvcsetsvc = function() { ';
        print ' $.getJSON( $("#delsvcsetsvcform").attr("action"), '; # <- url
        print ' $("#delsvcsetsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#delsvcsetsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delsvcsetsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Delete Service": delsvcsetsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_delsvcsetsvcdialog_buttons( $name, $svcdesc ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delsvcsetsvcform" name="delsvcsetsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&delsvcsetsvc=1';
        print '">';
        print '<h2>About to delete service:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">';
        print " &quot;".urldecode($svcdesc)."&quot; in <br>$name</h2>";
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
    function delete_svcset_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["delsvcsetsvc"] );
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
          RESTURL.'/delete/servicesets',
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
     * EDIT SERVICESET SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_edit_svcset_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print "<div id=\"editsvcsetsvcdlg\" title=\"Edit Service\"></div>";
        print '<script>';
        print 'var editsvcsetsvc = function() { ';
        print ' $.getJSON( $("#editsvcsetsvcform").attr("action"), '; # <- url
        print ' $("#editsvcsetsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#editsvcsetsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#editsvcsetsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Apply Changes": editsvcsetsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_editsvcsetsvcdialog_buttons( $name, $svcdesc_in ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to edit a host

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/servicesets?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        # Can't search for specific service check using the REST interface.
        # Have to ask for all services for the host (above) and search it:
        foreach( $hlist as $svcset ) {
            foreach( $svcset as $item ) extract( $item );
            if( $svcdesc == $svcdesc_in ) break;
        }

        print '<form id="editsvcsetsvcform" name="editsvcsetsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&editsvcsetsvc=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="editsvcsettabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Disabled
        #print '<p>';
        #print '<label for="sdisabled">Disabled</label>';
        #$checked="";
        #if( $disable == "1" ) $checked="checked";
        #print '<input class="field" type="checkbox" id="sdisabled"';
        #print ' name="disable" '.$checked.' />';
        #print '</p>';

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
        $newcmd = strtr( $newcmd, array("\""=>"\\\"","\\"=>"\\\\") );
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
        print '<label for="gcontacts">Contacts</label>';
        print '<input class="field" type="text" id="gcontacts"';
        print ' value="'.$contacts.'" name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="gcontactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="gcontactgroup"';
        print ' value="'.$contactgroups.'" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="ecustomvars">Custom Variables</label>';
        print '<input class="field" type="text" id="ecustomvars"';
        print ' value="'.$customvars.'" name="customvars">';
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
        print '<label for="emfta">Freshness threshold</label>';
        print '<input class="field" type="text" id="emfta"';
        print ' value="'.$manfreshnessthresh.'" name="manfreshnessthresh">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold (distributed)</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' value="'.$freshnessthresh.'" name="freshnessthresh">';
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
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        $checked="checked";
        if( $activechecks == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" '.$checked.' />';
        print '</p>';
        # Notes
        print '<p>';
        print '<label for="snotes">Custom Variables</label>';
        print '<input class="field" type="text" id="snotes"';
        print ' value="'.$notes.'" name="notes">';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Notification Options
        print '<p>';
        print '<label for="ynotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="ynotifopts" '.
              'value="'.$notifopts.'" name="notifopts" />';
        print '</p>';
        # Notifications Enabled
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        $selected=""; if( ! strlen($notifications_enabled) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $notifications_enabled == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $notifications_enabled == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        # Retain Status Info
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
        print '$( "#editsvcsettabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontactgroup = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontactgroup" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var escommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "escommand" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_svcnotifopts( "ynotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function edit_svcset_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["editsvcsetsvc"] );
        unset( $query_str["tab"] );
        $query_str["folder"] = FOLDER;
        #if( isset( $query_str["disable"] ) ) {
        #    if( $query_str["disable"] == "2" ) $query_str["disable"] = "2";
        #    elseif( $query_str["disable"] == "1" ) $query_str["disable"] = "1";
        #    else $query_str["disable"] = "0";
        #}
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
        if( ! strlen( $query_str["notifications_enabled"] ) )
            $query_str["notifications_enabled"] = "-";
        if( empty( $query_str["notifopts"] ) )
            $query_str["notifopts"] = "-";
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
        if( empty( $query_str["notes"] ) )
            $query_str["notes"] = "-";
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/modify/servicesets',
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
     * ADD NEW SERVICESET SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_new_svcset_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newsvcsetsvcdlg\" title=\"Add New Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var addsvcsetsvc = function() { ';
        print ' $.getJSON( $("#newsvcsetsvcform").attr("action"), '; # <- url
        print ' $("#newsvcsetsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#svcsetstable").html("").';
        print '      load("'.$url.'&svcsetstable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#newsvcsetsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newsvcsetsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Service": addsvcsetsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_newsvcsetsvcdialog_buttons( $svcsetname ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="newsvcsetsvcform" name="newsvcsetsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&newsvcsetsvc=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="newsvcsettabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Disabled
        #print '<p>';
        #print '<label for="sdisabled">Disabled</label>';
        #$checked="";
        #if( $disable == "1" ) $checked="checked";
        #print '<input class="field" type="checkbox" id="sdisabled"';
        #print ' name="disable" '.$checked.' />';
        #print '</p>';

        # Disabled
        print '<p>';
        print '<label for="sdisabled">Status</label>';
        print '<input type="radio" name="disable"';
        print ' value="0" checked />Enabled &nbsp;';
        print '<input type="radio" name="disable"';
        print ' value="1" />Disabled &nbsp;';
        print '<input type="radio" name="disable"';
        print ' value="2" />Testing';
        print '</p>';

        # Hostname
        print '<p>';
        print '<label for="hostname">Service Set</label>';
        print '<input class="field" type="text" id="hostname" name="name"';
        print ' value="'.$svcsetname.'" readonly="readonly" />';
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
        print '<p>';
        print '<label for="escommand">Command *</label>';
        print '<input class="field" type="text" id="escommand" name="command"';
        print ' required="required" />';
        print '</p>';

        # Service Description
        print '<p>';
        print '<label for="svcdesc">Description</label>';
        print '<input class="field" type="text" id="svcdesc" name="svcdesc"';
        print ' value="" />';
        print '</p>';
        # Service Groups
        print '<p>';
        print '<label for="svcgroup">Service Groups</label>';
        print '<input class="field" type="text" id="svcgroup"';
        print ' value="" name="svcgroup">';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="gcontacts">Contacts</label>';
        print '<input class="field" type="text" id="gcontacts"';
        print ' value="" name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="gcontactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="gcontactgroup"';
        print ' value="" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="customvars">Custom Variables</label>';
        print '<input class="field" type="text" id="customvars"';
        print ' value="" name="customvars">';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Check interval
        print '<p>';
        print '<label for="echeckinterval">Check Interval</label>';
        print '<input class="field" type="text" id="echeckinterval"';
        print ' value="" name="checkinterval">';
        print '</p>';
        # Retry interval
        print '<p>';
        print '<label for="eretryinterval">Retry Interval</label>';
        print '<input class="field" type="text" id="eretryinterval"';
        print ' value="" name="retryinterval">';
        print '</p>';
        # Max check attempts
        print '<p>';
        print '<label for="emaxcheckattempts">Max Check Attempts</label>';
        print '<input class="field" type="text" id="emaxcheckattempts"';
        print ' value="" name="maxcheckattempts">';
        print '</p>';
        # Freshness threshold manual
        print '<p>';
        print '<label for="emfta">Freshness Threshold</label>';
        print '<input class="field" type="text" id="emfta"';
        print ' value="" name="manfreshnessthresh">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold (distributed)</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' value="" name="freshnessthresh">';
        print '</p>';
        # Passive Checks
        print '<p style="margin-top: 12px;">';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        # Check Freshness
        print '<p>';
        print '<label for="scheckfreshness">Check Freshness</label>';
        print '<select name="checkfreshness" id="scheckfreshness" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" checked />';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Notification Options
        print '<p>';
        print '<label for="xnotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="xnotifopts" '.
              'value="" name="notifopts" />';
        print '</p>';
        # Notifications Enabled
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';
        print '</div>';
        print '<script>';
        print '$( "#newsvcsettabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontactgroup = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontactgroup" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var escommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "escommand" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_svcnotifopts( "xnotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function add_new_svcset_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["newsvcsetsvc"] );
        unset( $query_str["tab"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["svcdesc"] ) ) {
            $query_str["svcdesc"] = strtr( $query_str["svcdesc"], 
                                           array( '"' => '\"',) );
            $query_str["svcdesc"] = urlencode($query_str["svcdesc"]);
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
        $json = json_encode( $query_str );

        # Do the REST add service request
        $request = new RestRequest(
          RESTURL.'/add/servicesets',
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

    /*

       ===================================================================

                             END OF SERVICESETS TAB

       ===================================================================

     */

    /**********************************************************************
     *
     * HOSTS TAB
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_hosts_page( ) {
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
              #'$("#hoststable").html("").'.
              '$("#hoststable").'.
              'load("'.$url.'&hoststable=true");'.
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
        #show_hosts_tab_right_pane( );
        print '<div id="hoststable">'.
              #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
              '<p>Loading</p>'.
              '</div>';
        print "</div>";
        print "</div>";
        print "<div class=\"col2\">";
        show_hosts_tab_left_pane( );
        print "</div>";
        print "</div>";
        print "</div>";

    }

    # ------------------------------------------------------------------------
    function show_hosts_tab_left_pane( ) {
    # ------------------------------------------------------------------------
        global $g_tab, $g_hgfilter, $g_hfilter, $g_folders;

        show_apply_button( );

        $hgfilter="";
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        if( isset( $query_str['hgfilter'] ) ) {
            $hgfilter=$query_str['hgfilter'];
        }
        $hfilter="";
        if( isset( $query_str['hfilter'] ) ) {
            $hfilter=$query_str['hfilter'];
        }

        #print "<span id=\"applyconf\">";
        $g_hfilter = 0; # <-- don't include hfilter
        $url = create_url( );

        # This will be a plugin
        /*
        print "<p style='margin-bottom:10px'>Environment:<br />".
              "<select id='folder' name='folder' type='text'>";
        foreach( $g_folders as $item ) {
            $selected = "";
            #if( $item["name"] == $template ) $selected = " selected";
            print '<option value="'.$item.'"'.$selected.'>';
            print $item.'</option>';
        }
        print '</select>';
        print '</p>';
        print "<hr />";
        */

        print '<style>
        #qfilter{
            border-style: solid;
            border-width: 1px;
            padding-top: 4px;
            margin-bottom: 8px;
            margin-right: 4px;
            border-color: #B0B0B0;
            padding-bottom: 10px;
            padding-left: 6px;
            padding-right: 2px;}
        #qfilterhead{
            -webkit-border-top-left-radius: 6px;
            -webkit-border-top-right-radius: 6px;
            -moz-border-radius-topleft: 6px;
            -moz-border-radius-topright: 6px;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
            text-align: center;
            font-weight: bold;
            background-color: #EAEAEA;
            border-style: solid;
            border-width: 1px;
            border-bottom-style: none;
            margin-top: 6px;
            margin-right: 4px;
            margin-bottom: 0px;
            border-color: #B0B0B0;
            padding-top: 0px;
            padding-bottom: 4px;
            padding-left: 2px;
            padding-right: 2px;}
        </style>';
        print '<div id="qfilterhead"><p>Quick Filter</p>';
        print '</div>';
        print '<div id="qfilter">';
        print "<p style='margin-bottom:10px'>Filter by Name:<br>".
              "<input class='filtermain' id='hregex' name='hregex' type='text'".
              " style='width:100px;'".
              " value='".$hfilter."'".
              " /><span class='btn ui-corner-all' ".
              " onClick='".
              "var a=encodeURIComponent($(\"#hregex\").val());".
              "window.location=\"$url\"+\"&amp;hfilter=\"+a;".
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
        $g_hgfilter = 0; # <-- don't include hgfilter
        $url = create_url( );
        print "<p>Filter by Hostgroup:<br>";
        print "<select id=\"hgsel\" name=\"Hostgroup\" onChange=\"".
              "var a=$('select#hgsel option:selected').val();".
              #"alert( 'hello '+a );".
              "window.location='$url'+'&amp;hgfilter='+a".
              ";\">";
        print "<option value=\"all\">All</option>";
        $a =  get_and_sort_hostgroups();
        foreach( $a as $item ) {
            $name = $item['name'];
            if( $name == $hgfilter ) {
                print "<option value=\"$name\" selected>$name</option>";
            } else {
                print "<option value=\"$name\">$name</option>";
            }
        }
        print "</select></p>";
        print "</div>";
        #print "<hr />";

        show_revert_button( );

        plugins_buttons( );
    }

    # ------------------------------------------------------------------------
    function show_hosts_tab_right_pane( ) {
    # ------------------------------------------------------------------------
        global $g_sort, $g_sort_new, $g_hgfilter;

        #$list = $hglist[0];
        if( empty( $g_hgfilter ) || $g_hgfilter == "all" ) {
            $a = get_and_sort_hosts( $sort=$g_sort );
        } else {
            $a = get_and_sort_hosts( $sort=$g_sort, $filter=$g_hgfilter, $column=5 );
        }
        print "<p>".count($a)." hosts.</p>";
        print "<table><thead><tr>";

        # Sort by host name
        $g_sort_new = "name";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Name </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by ip address
        $g_sort_new = "ipaddress";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Address </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by ip Hostgroup
        $g_sort_new = "hostgroup";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Hostgroup </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Sort by ip Hostgroup
        $g_sort_new = "template";
        $url = create_url( );
        print "<td><a href='".$url."'><span class=black>Host Template </span>";
        print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
              " alt=\"arrow\"></a></td>";

        # Controls
        print "<td style=\"text-align:right;\">";
        print "<a class=\"icon icon-add\" title=\"Add New Host\" onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#newhostdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?newhostdialog=true').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";

        #print "<td></td>";
        print "</tr></thead><tbody>";

        $num=1;
        foreach( $a as $item ) {
            $style="";
            if( $item['disable'] == "1" ) {
                #$style = ' style="color: red;"';
                $style = ' style="background-color: #F7DCC6;"';
            } elseif( $item['disable'] == "2" ) {
                #$style = ' style="color: red;"';
                $style = ' style="background-color: #FFFC9E;"';
            } 

            if( $num % 2 == 0 )
                print "<tr class=shaded$style>";
            else
                print "<tr$style>";

            // NAME
            print "<td><span id=\"$num\" class=link> + ".$item['name']."</span></td>";
            // IP ADDRESS
            print "<td>".$item['ipaddress']."</td>";
            // HOSTGROUP
            print "<td>".$item['hostgroup']."</td>";
            // TEMPLATE
            print "<td>".$item['template']."</td>";
            // Actions
            print "<td style=\"float: right\">";
            if( $item['disable'] == 2 ) {
                print "<a class=\"icon icon-testoff\" title=\"Switch Testing Mode Off\"";
                print " onClick=\"".
                  #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
                  "$('#enablehostdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?enablehostdialog=true".
                  "&amp;name=".$item['name']."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            } else {
                print "<a class=\"icon icon-test\" title=\"Switch Testing Mode On\"";
                print " onClick=\"".
                  #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
                  "$('#testinghostdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?testinghostdialog=true".
                  "&amp;name=".$item['name']."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            }
            print "<a class=\"icon icon-clone\" title=\"Clone Host\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#clonehostdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?clonehostdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-edit\" title=\"Edit Host\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#edithostdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?edithostdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Host\"";
            print " onClick=\"".
              #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
              "$('#delhostdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?delhostdialog=true".
              "&amp;name=".$item['name']."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\"></a>";
            if( $item['disable'] == 1 ) {
                print "<a class=\"icon icon-enable\" title=\"Enable Host\"";
                print " onClick=\"".
                  #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
                  "$('#enablehostdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?enablehostdialog=true".
                  "&amp;name=".$item['name']."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
                print "</td>";
            } else {
                print "<a class=\"icon icon-disable\" title=\"Disable Host\"";
                print " onClick=\"".
                  #"if( confirm('Are you sure ?') ) {alert( 'hello' );}; return false;".
                  "$('#disablehostdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?disablehostdialog=true".
                  "&amp;name=".$item['name']."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
                print "</td>";
            }
            print "</tr>";
            // SERVICES FOR THIS HOST - HIDDEN
            print "<tr id='hid$num' class=hidden>";
            print "<td colspan=\"5\">".
                  #'<img src="/nagrestconf/images/loadingAnimation.gif" />'.
                  "Loading...".
                  "</td></tr>";
            # Save in names array for later 'bind'.
            $names[$num]=$item['name'];
            ++$num;
        }
        print "</tbody>";
        print "</table>";

        print "<script>";
        for( $x=1 ; $x<$num ; $x++ ) {
            print "$('#$x').bind('click', function() {";
            print " $.get('/nagrestconf/".SCRIPTNAME."?tab=2&fragment1id=";
            print $names[$x];
            print "', function(data) {";
            print " $('#hid$x').html(data);";
            #print ' alert(data);';
            print " }); ";
            print " $('#hid$x').toggleClass(\"hidden\"); });";
            print "$('#$x').bind('mouseenter mouseleave', function(event){";
            print " $(this).toggleClass(\"linkover\");});";
        }
        print "</script>";
    }

    /***********************************************************************
     *
     * ENABLE HOST DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_enablehostdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to Enable a host

        print '<form id="enablehostform" name="enablehostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?enablehost=1';
        print '">';
        print '<h2>About to enable host:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Enable Host' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<h2>';
        print 'NOTE: All services for this host will also be enabled.';
        print '</h2>';
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_enable_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"enablehostdlg\" title=\"Enable Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var enablehost = function() { ';
        print ' $.getJSON( $("#enablehostform").attr("action"), '; # <- url
        print ' $("#enablehostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#enablehostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#enablehostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Enable Host": enablehost, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function enable_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["enablehost"] );
        $query_str["folder"] = FOLDER;

        $query_str["disable"] = "0";
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/modify/hosts',
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

        if( isset( $query_str["name"] ) ) {

            $list = get_and_sort_services( $query_str["name"] );

            $a = array();
            $a["name"] = $query_str["name"];
            $a["folder"] = FOLDER;
            $a["disable"] = "0";

            foreach( $list as $item ) {
                $a["svcdesc"] = $item['svcdesc'];
                $json = json_encode( $a );
                $request2 = new RestRequest(
                  RESTURL.'/modify/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request2 );
                $request2->execute();
                $slist = json_decode( $request2->getResponseBody(), true );
                ### Check $slist->http_code ###
            }
        } else {
            $retval["message"] = "Internal error: name empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        print( json_encode( $retval ) );

        exit( 0 );
    }

    /***********************************************************************
     *
     * TESTING MODE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_testinghostdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to Disable a host

        print '<form id="testinghostform" name="testinghostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?testinghost=1';
        print '">';
        print '<h2>Host is about to enter testing mode:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Disable Host' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<h2>';
        print 'NOTE: All services for this host will also enter testing mode.';
        print '</h2>';
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_testing_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"testinghostdlg\" title=\"Testing Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var testinghost = function() { ';
        print ' $.getJSON( $("#testinghostform").attr("action"), '; # <- url
        print ' $("#testinghostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#testinghostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#testinghostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Enable Testing Mode": testinghost, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function testing_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["testinghost"] );
        $query_str["folder"] = FOLDER;

        if( isset( $query_str["name"] ) ) {

            $list = get_and_sort_services( $query_str["name"] );

            $a = array();
            $a["name"] = $query_str["name"];
            $a["folder"] = FOLDER;
            $a["disable"] = "2";

            foreach( $list as $item ) {
                $a["svcdesc"] = $item['svcdesc'];
                $json = json_encode( $a );
                $request2 = new RestRequest(
                  RESTURL.'/modify/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request2 );
                $request2->execute();
                $slist = json_decode( $request2->getResponseBody(), true );
                ### Check $slist->http_code ###
            }
        } else {
            $retval["message"] = "Internal error: name empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        $query_str["disable"] = "2";
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/modify/hosts',
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
     * DISABLE HOST DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_disablehostdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to Disable a host

        print '<form id="disablehostform" name="disablehostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?disablehost=1';
        print '">';
        print '<h2>About to disable host:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Disable Host' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<h2>';
        print 'NOTE: All services for this host will also be disabled.';
        print '</h2>';
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_disable_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"disablehostdlg\" title=\"Disable Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var disablehost = function() { ';
        print ' $.getJSON( $("#disablehostform").attr("action"), '; # <- url
        print ' $("#disablehostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#disablehostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#disablehostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Disable Host": disablehost, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function disable_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["disablehost"] );
        $query_str["folder"] = FOLDER;

        if( isset( $query_str["name"] ) ) {

            $list = get_and_sort_services( $query_str["name"] );

            $a = array();
            $a["name"] = $query_str["name"];
            $a["folder"] = FOLDER;
            $a["disable"] = "1";

            foreach( $list as $item ) {
                $a["svcdesc"] = $item['svcdesc'];
                $json = json_encode( $a );
                $request2 = new RestRequest(
                  RESTURL.'/modify/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request2 );
                $request2->execute();
                $slist = json_decode( $request2->getResponseBody(), true );
                ### Check $slist->http_code ###
            }
        } else {
            $retval["message"] = "Internal error: name empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        $query_str["disable"] = "1";
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/modify/hosts',
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
     * DELETE HOST DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delhostdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="delhostform" name="delhostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?delhost=1';
        print '">';
        print '<h2>About to <b>DELETE</b> host:</h2>';
        print '<h2 style="margin-left:60px;font-weight:bold;">'.$name.'</h2>';
        print "<h2>Click 'Delete Host' to confirm or 'Close' to cancel.</h2>";
        #print '<span class="errorlabel">Oops - it seems there are some';
        #print ' errors! Please check and correct them.</span>';
        # Hostname
        print '<p>';
        print '<input class="field" type="checkbox" id="delservices"';
        print ' name="delservices" value="1" />';
        print '<label for="delservices">Also delete service checks associated';
        print ' with this host.</label>';
        print '</p>';
        print '<p>';
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '</p>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_delete_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"delhostdlg\" title=\"Delete Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var delhost = function() { ';
        print ' $.getJSON( $("#delhostform").attr("action"), '; # <- url
        print ' $("#delhostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#delhostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#delhostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Delete Host": delhost, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function delete_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["delhost"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["delservices"] ) ) {
            unset( $query_str["delservices"] );
            if( isset( $query_str["name"] ) ) {
                $a = array();
                $a["name"] = $query_str["name"];
                $a["svcdesc"] = '.*';
                $a["folder"] = FOLDER;
                $json = json_encode( $a );
                $request2 = new RestRequest(
                  RESTURL.'/delete/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request2 );
                $request2->execute();
                $slist = json_decode( $request2->getResponseBody(), true );
                ### Check $slist->http_code ###
            } else {
                $retval["message"] = "Internal error: name empty";
                $retval["code"] = "400";
                print( json_encode( $retval ) );
                exit( 0 );
            }
        }
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/delete/hosts',
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
     * EDIT HOST DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_edit_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"edithostdlg\" title=\"Edit Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var edithost = function() { ';
        print ' $.getJSON( $("#edithostform").attr("action"), '; # <- url
        print ' $("#edithostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("p#deleteme").remove(); $("#edithostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#edithostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Apply Changes": edithost, "Close": cancel }';
        print ', close : function( event, ui ) { $("p#deleteme").remove(); }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_edithostdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to edit a host

        # Get form details from REST
        $request = new RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($name).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        #print_r( $hlist[0] );
        foreach( $hlist[0] as $item ) extract( $item );

        print '<form id="edithostform" name="edithostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?edithost=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="edithosttabs">';
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

        # Disabled
        #print '<p>';
        #print '<label for="edisabled">Disabled</label>';
        #$checked="";
        #if( $disable == "1" ) $checked="checked";
        #print '<input class="field" type="checkbox" id="edisabled"';
        #print ' name="disable" '.$checked.' />';
        #print '</p>';

        # Hostname
        print '<p>';
        print '<label for="ehostname">Host name *</label>';
        print '<input class="field" type="text" id="ehostname" name="name"';
        print ' value="'.$name.'" readonly="readonly" required="required" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="ealias">Alias *</label>';
        print '<input class="field" type="text" id="ealias" name="alias"';
        print ' value="'.$alias.'" required="required" />';
        print '</p>';
        # IP Address
        print '<p>';
        print '<label for="eipaddress">IP Address *</label>';
        print '<input class="field" type="text" id="eipaddress" name="ipaddress"';
        print ' value="'.$ipaddress.'" required="required" />';
        print '</p>';
        # Service Set (auto-complete)
        print '<p>';
        print '<label for="aserviceset">Service Sets</label>';
        print '<input class="field" id="aserviceset"';
        print ' value="'.$servicesets.'" name="servicesets">';
        print '</p>';
        # Refresh
        #print '<p>';
        #print '<label for="reapply">Re-apply Service Sets</label>';
        print '<input type="checkbox" id="reapply"';
        print ' name="reapply" />';
        #print '</p>';
        # Host Template
        $hts = get_and_sort_hosttemplates( );
        print '<p>';
        print '<label for="ehosttemplate">Host Template</label>';
        print '<select class="field" id="ehosttemplate" name="template" required="required">';
        foreach( $hts as $item ) {
            $selected = "";
            if( $item["name"] == $template ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>';
            print $item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Host Group
        $hgs = get_and_sort_hostgroups( );
        print '<p>';
        print '<label for="ehostgroup">Hostgroup</label>';
        print '<select class="field" id="ehostgroup" name="hostgroup" required="required">';
        foreach( $hgs as $item ) {
            $selected = "";
            if( $item["name"] == $hostgroup ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>';
            print $item["alias"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="fcontact">Contacts</label>';
        print '<input class="field" type="text" id="fcontact"';
        print ' value="'.$contact.'" name="contact">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="fcontactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="fcontactgroup"';
        print ' value="'.$contactgroups.'" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="fcustomvars">Custom Variables</label>';
        print '<input class="field" type="text" id="fcustomvars"';
        print ' value="'.$customvars.'" name="customvars">';
        print '</p>';

        ###:TAB2
        print '</div>';
        print '<div id="fragment-2">';
        # Max check attempts
        print '<p>';
        $newcmd = urldecode( $command );
        $newcmd = strtr( $newcmd, array("\""=>"\\\"","\\"=>"\\\\") );
        print '<label for="fcommand">Check Command</label>';
        print '<input class="field" type="text" id="fcommand"';
        print ' value="'.$newcmd.'" name="command">';
        print '<script>$("#fcommand").val("'.$newcmd.'");</script>';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="eactivechecks">Active Check</label>';
        $checked="checked";
        if( $activechecks == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="eactivechecks"';
        print ' name="activechecks" '.$checked.' />';
        print '</p>';
        print '<p>';
        print '<label for="emaxcheckattempts">Max check attempts</label>';
        print '<input class="field" type="text" id="emaxcheckattempts"';
        print ' value="'.$maxcheckattempts.'" name="maxcheckattempts">';
        print '</p>';
        # Notes
        print '<p>';
        print '<label for="notes">Notes</label>';
        print '<input class="field" type="text" id="notes"';
        print ' value="'.$notes.'" name="notes">';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Parents
        print '<p>';
        print '<label for="fparents">Parents</label>';
        print '<input class="field" type="text" id="fparents"';
        print ' value="'.$parents.'" name="parents">';
        print '</p>';
        #
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
        print '</p>';
        print '</div>';
        print '</div>';
        print '<script>';
        #print '$( "#edithosttabs" ).tabs({heightStyle: "fill"});';
        print '$( "#edithosttabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus();'.
              'if( $("input#reapplyfake").length == 0 ) { $(\'<p style="float: right;padding-right:16px;" id="deleteme"><label style="display:inline-block; position: relative; top: -2px; padding-right: 4px" >Re-apply Service Sets</label><input type="checkbox" id="reapplyfake" style="display:inline-block;" /></p>\''.
              ').insertAfter("html body div.ui-dialog div.ui-dialog-buttonpane div.ui-dialog-buttonset");};'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print '$( "input#reapplyfake" ).prop("checked",false);';
        print '$( "input#reapplyfake" ).change( function() { $("#reapply").prop("checked",$(this).prop("checked"));});';
        print 'var fcontact = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "fcontact" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var fcontactgroup = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "fcontactgroup" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var fcommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "fcommand" );
        print '</script>';

        # Auto-complete for service-sets
        $hgs = get_and_sort_servicesets_unique( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var aserviceset = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"$item\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "aserviceset" );
        print '</script>';

        # Auto-complete for parents
        $hgs = get_and_sort_hosts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var fparents = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "fparents" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function edit_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["newhost"] );
        if( isset( $query_str["reapply"] ) ) {
            # Delete host
            $query_str["folder"] = FOLDER;
            unset( $query_str["delservices"] );
            if( isset( $query_str["name"] ) && $query_str["disable"] == 0 ) {
                $a = array();
                $a["name"] = $query_str["name"];
                $a["svcdesc"] = '.*';
                $a["folder"] = FOLDER;
                $json = json_encode( $a );
                $request2 = new RestRequest(
                  RESTURL.'/delete/services',
                  'POST',
                  'json='.$json
                );
                set_request_options( $request2 );
                $request2->execute();
                $slist = json_decode( $request2->getResponseBody(), true );
                ### Check $slist->http_code ###
            } elseif (isset( $query_str["name"] ) && 
                $query_str["disable"] != 0 )
            {
                $retval["message"] = 
                    "Host must be enabled to re-apply the service sets.";
                $retval["code"] = "400";
                print( json_encode( $retval ) );
                exit( 0 );
            } else {
                $retval["message"] = "Internal error: name empty";
                $retval["code"] = "400";
                print( json_encode( $retval ) );
                exit( 0 );
            }
            $json = json_encode( $query_str );

            # Do the REST add host request
            $request = new RestRequest(
              RESTURL.'/delete/hosts',
              'POST',
              'json='.$json
            );
            set_request_options( $request );
            $request->execute();
            $slist = json_decode( $request->getResponseBody(), true );

            # Add host

            if( isset( $query_str["activechecks"] ) )
                $query_str["activechecks"] = "1";
            else
                $query_str["activechecks"] = "0";

            if( isset( $query_str["command"] ) ) {
                $query_str["command"] = strtr( $query_str["command"], 
                                               array( '"' => '\"',) );
                $query_str["command"] = urlencode($query_str["command"]);
            }

            $json = json_encode( $query_str );

            # Do the REST add host request
            $request = new RestRequest(
              RESTURL.'/add/hosts',
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

        unset( $query_str["reapply"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["disable"] ) ) {
            if( $query_str["disable"] == "2" ) $query_str["disable"] = "2";
            elseif( $query_str["disable"] == "1" ) $query_str["disable"] = "1";
            else $query_str["disable"] = "0";
        }
        # Handle deleting fields
        if( ! strlen( $query_str["retainstatusinfo"] ) )
            $query_str["retainstatusinfo"] = "-";
        if( ! strlen( $query_str["retainnonstatusinfo"] ) )
            $query_str["retainnonstatusinfo"] = "-";
        if( empty( $query_str["contact"] ) )
            $query_str["contact"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
        if( empty( $query_str["command"] ) )
            $query_str["command"] = "-";
        if( empty( $query_str["maxcheckattempts"] ) )
            $query_str["maxcheckattempts"] = "-";
        if( empty( $query_str["servicesets"] ) )
            $query_str["servicesets"] = "-";
        if( empty( $query_str["customvars"] ) )
            $query_str["customvars"] = "-";
        if( empty( $query_str["notes"] ) )
            $query_str["notes"] = "-";
        if( empty( $query_str["parents"] ) )
            $query_str["parents"] = "-";

        if( isset( $query_str["command"] ) ) {
            $query_str["command"] = strtr( $query_str["command"], 
                                           array( '"' => '\"',) );
            $query_str["command"] = urlencode($query_str["command"]);
        }

        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/modify/hosts',
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
     * ADD NEW HOST DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_new_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newhostdlg\" title=\"Add New Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var addhost = function() { ';
        print ' $.getJSON( $("#newhostform").attr("action"), '; # <- url
        print ' $("#newhostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#newhostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newhostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Host": addhost, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["newhost"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["command"] ) ) {
            $query_str["command"] = strtr( $query_str["command"], 
                                           array( '"' => '\"',) );
            $query_str["command"] = urlencode($query_str["command"]);
        }
        if( isset( $query_str["activechecks"] ) )
            $query_str["activechecks"] = "1";
        else
            $query_str["activechecks"] = "0";
        $json = json_encode( $query_str );

        # Do the REST add host request
        $request = new RestRequest(
          RESTURL.'/add/hosts',
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

    # ------------------------------------------------------------------------
    function show_newhostdialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="newhostform" name="newhostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?newhost=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="newhosttabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Disabled TODO Allow services to be started disabled
        #print '<p>';
        #print '<label for="sdisabled">Status</label>';
        #print '<input type="radio" name="disable"';
        #print ' value="0" checked />Enabled &nbsp;';
        #print '<input type="radio" name="disable"';
        #print ' value="1" />Disabled &nbsp;';
        #print '<input type="radio" name="disable"';
        #print ' value="2" />Testing';
        #print '</p>';

        # Disabled
        #print '<p>';
        #print '<label for="edisabled">Disabled</label>';
        #$checked="";
        #if( $disable == "1" ) $checked="checked";
        #print '<input class="field" type="checkbox" id="edisabled"';
        #print ' name="disable" '.$checked.' />';
        #print '</p>';

        # Hostname
        print '<p>';
        print '<label for="ehostname">Host name *</label>';
        print '<input class="field" type="text" id="ehostname" name="name"';
        print ' value="" required="required" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="ealias">Alias *</label>';
        print '<input class="field" type="text" id="ealias" name="alias"';
        print ' value="" required="required" />';
        print '</p>';
        # IP Address
        print '<p>';
        print '<label for="eipaddress">IP Address *</label>';
        print '<input class="field" type="text" id="eipaddress" name="ipaddress"';
        print ' value="" required="required" />';
        print '</p>';
        # Service Set (auto-complete)
        print '<p>';
        print '<label for="serviceset">Service Sets</label>';
        print '<input class="field" id="serviceset"';
        print ' value="'.$servicesets.'" name="servicesets">';
        print '</p>';
        # Host Template
        $hts = get_and_sort_hosttemplates( );
        print '<p>';
        print '<label for="ehosttemplate">Host Template</label>';
        print '<select class="field" id="ehosttemplate" name="template" required="required">';
        foreach( $hts as $item ) {
            $selected = "";
            if( $item["name"] == $template ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>';
            print $item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Host Group
        $hgs = get_and_sort_hostgroups( );
        print '<p>';
        print '<label for="ehostgroup">Hostgroup</label>';
        print '<select class="field" id="ehostgroup" name="hostgroup" required="required">';
        foreach( $hgs as $item ) {
            $selected = "";
            if( $item["name"] == $hostgroup ) $selected = " selected";
            print '<option value="'.$item["name"].'"'.$selected.'>';
            print $item["alias"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="econtact">Contacts</label>';
        print '<input class="field" type="text" id="econtact"';
        print ' value="" name="contact">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="econtactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="econtactgroup"';
        print ' value="" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="fcustomvars">Custom Variables</label>';
        print '<input class="field" type="text" id="fcustomvars"';
        print ' value="" name="customvars">';
        print '</p>';

        ###:TAB2
        print '</div>';
        print '<div id="fragment-2">';
        # Max check attempts
        print '<p>';
        print '<label for="ecommand">Check Command</label>';
        print '<input class="field" type="text" id="ecommand"';
        print ' value="" name="command">';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="eactivechecks">Active Check</label>';
        print '<input class="field" type="checkbox" id="eactivechecks"';
        print ' name="activechecks" checked />';
        print '</p>';
        print '<p>';
        print '<label for="emaxcheckattempts">Max check attempts</label>';
        print '<input class="field" type="text" id="emaxcheckattempts"';
        print ' value="" name="maxcheckattempts">';
        print '</p>';
        # Notes
        print '<p>';
        print '<label for="enotes">Notes</label>';
        print '<input class="field" type="text" id="enotes"';
        print ' value="" name="notes">';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Parents
        print '<p>';
        print '<label for="fparents">Parents</label>';
        print '<input class="field" type="text" id="fparents"';
        print ' value="" name="parents">';
        print '</p>';
        #
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '</p>';
        print '</div>';
        print '</div>';
        print '<script>';
        #print '$( "#edithosttabs" ).tabs({heightStyle: "fill"});';
        print '$( "#newhosttabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var econtact = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "econtact" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var econtactgroup = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "econtactgroup" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var ecommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "ecommand" );
        print '</script>';

        # Auto-complete for service-sets
        $hgs = get_and_sort_servicesets_unique( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var serviceset = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"$item\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "serviceset" );
        print '</script>';

        exit( 0 );
    }

    /***********************************************************************
     *
     * CLONE HOST DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_clone_host_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"clonehostdlg\" title=\"Clone Host\"></div>";
        print '<script>';
        # Addhost button
        print 'var clonehost = function() { ';
        print ' $.getJSON( $("#clonehostform").attr("action"), '; # <- url
        print ' $("#clonehostform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#clonehostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#clonehostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Clone Host": clonehost, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_clonehostdialog_buttons( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="clonehostform" name="clonehostform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?clonehost=1';
        print '">';
        print '<fieldset>';
        print '<p>Clone host:</p>';
        print '<p style="font-weight:bold;">';
        print "$name</p>";
        # Hostname
        print '<p>';
        print '<label for="chostname">Name of new host *</label>';
        print '<input class="field" type="text" id="chostname" name="copyto"'.
              ' required="required" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="calias">New alias *</label>';
        print '<input class="field" type="text" id="calias" name="alias"'.
              ' required="required" />';
        print '</p>';
        # IP Address
        print '<p>';
        print '<label for="ipaddress">New IP Address *</label>';
        print '<input class="field" type="text" id="ipaddress" name="ipaddress"'.
              ' required="required" />';
        print '</p>';
        print "<p>Click 'Clone Host' to confirm or 'Close' to cancel.</p>";
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
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
    function clone_host_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Clone Service' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        # Sanity checks
        if( ! isset( $query_str["name"] ) ) {
            $retval["message"] = "Internal error: name or svcdesc empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $query_str["alias"] )
                   || empty( $query_str["alias"] ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $query_str["copyto"] )
                   || empty( $query_str["copyto"] ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( strchr( $query_str["copyto"],"*" ) ) {
            $retval["message"] = "Wildcard not allowed.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        }

        $tohost = trim( $query_str["copyto"] );
        $fromhost = $query_str["name"];
        $newipaddress = $query_str["ipaddress"];
        $newalias = $query_str["alias"];

        # Clone the host

        $request = new RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($fromhost).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        $new_qs = array();
        $new_qs["folder"] = FOLDER;
        foreach( $hlist[0] as $item2 ) {
            foreach( $item2 as $key => $val ) {
                $new_qs[$key] = $val; 
            }
        }
        $new_qs["name"] = $tohost;
        $new_qs["ipaddress"] =  $newipaddress;
        $new_qs["alias"] =  $newalias;
        # Don't create with servicesets field set otherwise
        # the host will be created from service sets. Save
        # and add servicesets field later.
        $oldservicesets = $new_qs["servicesets"];
        $new_qs["servicesets"] = "";
        $json = json_encode( $new_qs );

        $request = new RestRequest(
          RESTURL.'/add/hosts',
          'POST',
          'json='.$json
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        # Return json if there was an error
        $retval = array();
        #$retval["message"] = "(ADD HOST) ".$slist[0]." ".$json;
        $retval["message"] = "(ADD HOST) ".$slist[0];
        $resp = $request->getResponseInfo();
        $retval["code"] = $resp["http_code"];
        if( $retval["code"] != 200 ) {
            print( json_encode( $retval ) );
            exit( 0 );
        }

        # Modify the host to add servicesets. Services won't
        # be created automatically with 'modify', unlike 'add'.

        $modifyhost["folder"] = FOLDER;
        $modifyhost["name"] = $tohost;
        $modifyhost["servicesets"] = $oldservicesets;
        $json = json_encode( $modifyhost );
        $request = new RestRequest(
          RESTURL.'/modify/hosts',
          'POST',
          'json='.$json
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );
        $retval = array();
        #$retval["message"] = "(MODIFY HOST) ".$slist[0]." ".$json;
        $retval["message"] = "(MODIFY HOST) ".$slist[0];
        $resp = $request->getResponseInfo();
        $retval["code"] = $resp["http_code"];
        if( $retval["code"] != 200 ) {
            print( json_encode( $retval ) );
            exit( 0 );
        }

        # Clone the services

        # Get service details from original host from REST
        unset( $request );
        $request = new RestRequest(
        RESTURL.'/show/services?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($fromhost).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        foreach( $hlist as $svc ) {
            foreach( $svc as $item ) extract( $item );

            # Change " to \". otherwise we get 'folder not found'
            if( isset( $svcdesc ) ) {
                $svcdesc = strtr( $svcdesc, array( '%22' => '%5C%22',) );
            }
            if( isset( $command ) ) {
                $command = strtr( $command, array( '%22' => '%5C%22',) );
            }

            $new_qs = array();
            $new_qs["folder"] = FOLDER;
            foreach( $svc as $item2 ) {
                foreach( $item2 as $key => $val ) {
                    $new_qs[$key] = $val; 
                }
            }
            $new_qs["name"] = $tohost;
            $new_qs["svcdesc"] = $svcdesc;
            $new_qs["command"] = $command;
            $json = json_encode( $new_qs );

            $request = new RestRequest(
              RESTURL.'/add/services',
              'POST',
              'json='.$json
            );
            set_request_options( $request );
            $request->execute();
            $slist = json_decode( $request->getResponseBody(), true );

            # Return json
            $retval = array();
            $retval["message"] = "Host was added but: ".$slist[0]." ".$json;
            $resp = $request->getResponseInfo();
            $retval["code"] = $resp["http_code"];
            if( $retval["code"] != 200 ) {
                print( json_encode( $retval ) );
                exit( 0 );
            }
        }

        $retval["message"] = "Success. Host cloned.";
        $retval["code"] = 200;
        print( json_encode( $retval ) );

        exit( 0 );
    }

    /***********************************************************************
     *
     * CLONE SERVICE (TO OTHER HOST) DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_clone_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"clonesvcdlg\" title=\"Clone Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var clonesvc = function() { ';
        print ' $.getJSON( $("#clonesvcform").attr("action"), '; # <- url
        print ' $("#clonesvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#clonesvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#clonesvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Clone Service": clonesvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_clonesvcdialog_buttons( $name, $svcdesc ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="clonesvcform" name="clonesvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?clonesvc=1';
        print '">';
        print '<fieldset>';
        print '<p>Clone service:</p>';
        print '<p style="font-weight:bold;">';
        print "&quot;".urldecode($svcdesc)."&quot; on <br>$name</p>";
        # Hostname
        print '<p>';
        print '<label for="chostname">Copy to host *</label>';
        print '<input class="field" type="text" id="chostname" name="copyto"'.
              ' required="required" />';
        print '</p>';
        print '<p>';
        print '<label for="csvcdesc">New service name *</label>';
        print '<input class="field" type="text" id="csvcdesc" name="newsvcdesc"'.
              ' required="required" value="'.urldecode($svcdesc).'" />';
        print '</p>';
        print "<p>Click 'Clone Service' to confirm or 'Close' to cancel.</p>";
        print '<input type="hidden" name="name" value="';
        print $name;
        print '"/>';
        print '<input type="hidden" name="svcdesc" value="';
        print urldecode($svcdesc);
        print '"/>';
        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for hosts groups
        $hgs = get_and_sort_hosts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var chostname = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "chostname" );
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function clone_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Clone Service' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        $copyto = trim( $query_str["copyto"] );

        # Sanity checks
        if( ! ( isset( $query_str["name"] )
              && isset( $query_str["svcdesc"] )
            ) ) {
            $retval["message"] = "Internal error: name or svcdesc empty";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $copyto ) || empty( $copyto ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( ! isset( $query_str["newsvcdesc"] )
                   || empty( $query_str["newsvcdesc"] ) ) {
            $retval["message"] = "A required field is empty.";
            $retval["code"] = "400";
            print( json_encode( $retval ) );
            exit( 0 );
        } 

        # Does the copyto host exist?
        $request = new RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.$copyto.'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );
        if( ! isset( $slist[0] ) ) {
            $retval["message"] = "Host not found.";
            $retval["code"] = 400;
            print( json_encode( $retval ) );
            exit( 0 );
        } else if( isset( $slist[1] ) ) {
            $retval["message"] = "Host name matches more than one host.";
            $retval["code"] = 400;
            print( json_encode( $retval ) );
            exit( 0 );
        }

        # Rely on fact that "name" is always the first item in the list
        # returned from any REST request.
        $tohost = $slist[0][0]["name"];

        $fromhost = trim( $query_str["name"] );
        $fromsvc = trim( $query_str["svcdesc"] );

        # Get service details from REST
        unset( $request );
        $request = new RestRequest(
        RESTURL.'/show/services?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($fromhost).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        # Can't search for specific service check using the REST interface.
        # Have to ask for all services for the host (above) and search it:
        foreach( $hlist as $svc ) {
            foreach( $svc as $item ) extract( $item );
            if( $svcdesc == urlencode($fromsvc) ) break;
        }

        # Change " to \". otherwise we get 'folder not found'
        if( isset( $svcdesc ) ) {
            $svcdesc = strtr( $svcdesc, array( '%22' => '%5C%22',) );
        }
        if( isset( $command ) ) {
            $command = strtr( $command, array( '%22' => '%5C%22',) );
        }

        $newservice["folder"] = FOLDER;
        $newservice["name"] = $tohost;
        $newservice["template"] = $template;
        $newservice["command"] =  $command;
        $newservice["svcdesc"] = $query_str["newsvcdesc"];
        $newservice["svcgroup"] = $svcgroup;
        $newservice["contacts"] = $contacts;
        $newservice["contactgroups"] = $contactgroups;
        $newservice["freshnessthresh"] = $freshnessthresh;
        $newservice["activechecks"] = $activechecks;
        $newservice["customvars"] = $customvars;
        $newservice["maxcheckattempts"] = $maxcheckattempts;
        $newservice["checkinterval"] = $checkinterval;
        $newservice["retryinterval"] = $retryinterval;
        $newservice["passivechecks"] = $passivechecks;
        $newservice["manfreshnessthresh"] = $manfreshnessthresh;
        $newservice["checkfreshness"] = $checkfreshness;
        $newservice["retainstatusinfo"] = $retainstatusinfo;
        $newservice["retainnonstatusinfo"] = $retainnonstatusinfo;
        $newservice["notes"] = $notes;
        $json = json_encode( $newservice );
        $request = new RestRequest(
          RESTURL.'/add/services',
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
     * DELETE SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_delsvcdialog_buttons( $name, $svcdesc ) {
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
    function show_delete_svc_dlg_div( ) {
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
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
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
    function delete_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
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
    function show_edit_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

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
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
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
    function show_editsvcdialog_buttons( $name, $svcdesc ) {
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
        #print '<p>';
        #print '<label for="sdisabled">Disabled</label>';
        #$checked="";
        #if( $disable == "1" ) $checked="checked";
        #print '<input class="field" type="checkbox" id="sdisabled"';
        #print ' name="disable" '.$checked.' />';
        #print '</p>';

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
        $newcmd = strtr( $newcmd, array("\""=>"\\\"","\\"=>"\\\\") );
        print '<p>';
        print '<label for="iescommand">Command *</label>';
        print '<input class="field" type="text" id="iescommand" name="command"';
              # Using <.. value="\"" ..> does not work so...
        print ' required="required" />';
              # ...have to use javascript to set the value:
        print '<script>$("#iescommand").val("'.$newcmd.'");</script>';
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
        print '<label for="gcontacts">Contacts</label>';
        print '<input class="field" type="text" id="gcontacts"';
        print ' value="'.$contacts.'" name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="gcontactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="gcontactgroup"';
        print ' value="'.$contactgroups.'" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="customvars">Custom Variables</label>';
        print '<input class="field" type="text" id="customvars"';
        print ' value="'.$customvars.'" name="customvars">';
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
        print '<label for="emfta">Freshness Threshold</label>';
        print '<input class="field" type="text" id="emfta"';
        print ' value="'.$manfreshnessthresh.'" name="manfreshnessthresh">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold (distributed)</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' value="'.$freshnessthresh.'" name="freshnessthresh">';
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
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        $checked="checked";
        if( $activechecks == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" '.$checked.' />';
        print '</p>';
        # Notes
        print '<p>';
        print '<label for="snotes">Notes</label>';
        print '<input class="field" type="text" id="snotes"';
        print ' value="" name="notes">';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Notification Options
        print '<p>';
        print '<label for="ynotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="ynotifopts" '.
              'value="'.$notifopts.'" name="notifopts" />';
        print '</p>';
        # Notifications Enabled
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        $selected=""; if( ! strlen($notifications_enabled) ) $selected="selected";
        print '<option value="" '.$selected.'>Nagios default</option>';
        $selected=""; if( $notifications_enabled == "1" ) $selected="selected";
        print '<option value="1" '.$selected.'>Enabled</option>';
        $selected=""; if( $notifications_enabled == "0" ) $selected="selected";
        print '<option value="0" '.$selected.'>Disabled</option>';
        print '</select>';
        print '</p>';
        # Retain Status Info
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

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontactgroup = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontactgroup" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var iescommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "iescommand" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_svcnotifopts( "ynotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function edit_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
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
        if( ! strlen( $query_str["notifications_enabled"] ) )
            $query_str["notifications_enabled"] = "-";
        if( empty( $query_str["notifopts"] ) )
            $query_str["notifopts"] = "-";
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
        if( empty( $query_str["notes"] ) )
            $query_str["notes"] = "-";
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

    /***********************************************************************
     *
     * ADD NEW SERVICE DIALOG
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_new_svc_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"newsvcdlg\" title=\"Add New Service\"></div>";
        print '<script>';
        # Addhost button
        print 'var addsvc = function() { ';
        print ' $.getJSON( $("#newsvcform").attr("action"), '; # <- url
        print ' $("#newsvcform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html(""+message).show();';
        $url = create_url( );
        print '    $("#hoststable").html("").';
        print '      load("'.$url.'&hoststable=true");';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html(""+message).show();';
        print ' }});';
        print '};';
        # Cancel button
        print 'var cancel = function() { $("#newsvcdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#newsvcdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Create Service": addsvc, "Close": cancel }';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function add_new_svc_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Create the query
        parse_str( $_SERVER['QUERY_STRING'], $query_str );
        unset( $query_str["newsvc"] );
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["svcdesc"] ) ) {
            $query_str["svcdesc"] = strtr( $query_str["svcdesc"], 
                                           array( '"' => '\"',) );
            $query_str["svcdesc"] = urlencode($query_str["svcdesc"]);
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
        $json = json_encode( $query_str );

        # Do the REST add service request
        $request = new RestRequest(
          RESTURL.'/add/services',
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

    # ------------------------------------------------------------------------
    function show_newsvcdialog_buttons( $hostname ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        print '<form id="newsvcform" name="newsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?newsvc=1';
        print '">';
        print '<fieldset>';

        ###:TAB1
        print '<div id="newservicetabs">';
        print '<ul>';
        print '<li><a href="#fragment-1"><span>Standard</span></a></li>';
        print '<li><a href="#fragment-2"><span>Additional</span></a></li>';
        print '<li><a href="#fragment-3"><span>Advanced</span></a></li>';
        print '</ul>';
        print '<div id="fragment-1">';

        # Disabled
        print '<p>';
        print '<label for="sdisabled">Status</label>';
        print '<input type="radio" name="disable"';
        print ' value="0" checked />Enabled &nbsp;';
        print '<input type="radio" name="disable"';
        print ' value="1">Disabled &nbsp;';
        print '<input type="radio" name="disable"';
        print ' value="2">Testing';
        print '</p>';

        # Hostname
        print '<p>';
        print '<label for="hostname">Host name</label>';
        print '<input class="field" type="text" id="hostname" name="name"';
        print ' value="'.$hostname.'" readonly="readonly" />';
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
        print '<p>';
        print '<label for="escommand">Command *</label>';
        print '<input class="field" type="text" id="escommand" name="command"';
              # Using <.. value="\"" ..> does not work so...
        print ' required="required" />';
              # ...have to use javascript to set the value:
        print '<script>$("#escommand").val("");</script>';
        print '</p>';

        # Service Description
        print '<p>';
        print '<label for="svcdesc">Description</label>';
        print '<input class="field" type="text" id="svcdesc" name="svcdesc"';
        print ' value="" />';
        print '</p>';
        # Service Groups
        print '<p>';
        print '<label for="svcgroup">Service Groups</label>';
        print '<input class="field" type="text" id="svcgroup"';
        print ' value="" name="svcgroup">';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="gcontacts">Contacts</label>';
        print '<input class="field" type="text" id="gcontacts"';
        print ' value="" name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="gcontactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="gcontactgroup"';
        print ' value="" name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="customvars">Custom Variables</label>';
        print '<input class="field" type="text" id="customvars"';
        print ' value="" name="customvars">';
        print '</p>';
        print '</div>';

        ###:TAB2
        print '<div id="fragment-2">';
        # Check interval
        print '<p>';
        print '<label for="echeckinterval">Check Interval</label>';
        print '<input class="field" type="text" id="echeckinterval"';
        print ' value="" name="checkinterval">';
        print '</p>';
        # Retry interval
        print '<p>';
        print '<label for="eretryinterval">Retry Interval</label>';
        print '<input class="field" type="text" id="eretryinterval"';
        print ' value="" name="retryinterval">';
        print '</p>';
        # Max check attempts
        print '<p>';
        print '<label for="emaxcheckattempts">Max Check Attempts</label>';
        print '<input class="field" type="text" id="emaxcheckattempts"';
        print ' value="" name="maxcheckattempts">';
        print '</p>';
        # Freshness threshold manual
        print '<p>';
        print '<label for="emfta">Freshness threshold</label>';
        print '<input class="field" type="text" id="emfta"';
        print ' value="" name="manfreshnessthresh">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold (distributed)</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' value="" name="freshnessthresh">';
        print '</p>';
        # Passive Checks
        print '<p style="margin-top: 12px;">';
        print '<label for="spassivechecks">Passive Checks Enabled</label>';
        print '<select name="passivechecks" id="spassivechecks" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        # Check Freshness
        print '<p>';
        print '<label for="scheckfreshness">Check Freshness</label>';
        print '<select name="checkfreshness" id="scheckfreshness" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" checked />';
        print '</p>';
        # Notes
        print '<p>';
        print '<label for="notes">Notes</label>';
        print '<input class="field" type="text" id="notes"';
        print ' value="" name="notes">';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Notification Options
        print '<p>';
        print '<label for="xnotifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="xnotifopts" '.
              'value="" name="notifopts" />';
        print '</p>';
        # Notifications Enabled
        print '<p>';
        print '<label for="snotifen">Notifications Enabled</label>';
        print '<select name="notifications_enabled" id="snotifen" class="field">';
        print '<option value="" selected >Nagios default</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        # Retain Status Info
        print '<p>';
        print '<label for="srsi">Retain Status Info</label>';
        print '<select name="retainstatusinfo" id="srsi" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '<p>';
        print '<label for="srnsi">Retain Nonstatus Info</label>';
        print '<select name="retainnonstatusinfo" id="srnsi" class="field">';
        print '<option value="" selected >From template</option>';
        print '<option value="1">Enabled</option>';
        print '<option value="0">Disabled</option>';
        print '</select>';
        print '</p>';
        print '</div>';
        print '</div>';
        print '<script>';
        print '$( "#newservicetabs" ).tabs();';
        print '</script>';
        ###:TABEND

        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        # Auto-complete for contacts
        $hgs = get_and_sort_contacts( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontacts = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item['name']."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontacts" );
        print '</script>';

        # Auto-complete for contact groups
        $hgs = get_and_sort_contactgroups( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var gcontactgroup = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".$item["name"]."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript( "gcontactgroup" );
        print '</script>';

        # Auto-complete for commands
        $hgs = get_and_sort_commands( );
        print '<script>';
        print '$( document ).ready( function() {';
        print 'var escommand = [';
        $comma="";
        foreach( $hgs as $item ) {
            print "$comma\"".urldecode($item['name'])."\"";
            $comma=",";
        }
        print'];';
        autocomplete_jscript_single( "escommand" );
        print '</script>';

        # Auto-complete for notif opts
        print '<script>';
        print '$( document ).ready( function() {';
        autocomplete_svcnotifopts( "xnotifopts" );
        print '});';
        print '</script>';

        exit( 0 );
    }

    /*

       ===================================================================

                               END OF HOSTS TAB

       ===================================================================

     */

    /***********************************************************************
     *
     * PAGE BUTTONS (REVERT, APPLY, RESTART)
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function show_applyconfiguration_dlg_div( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Host

        # 'Add New Host' dialog box div
        print "<div id=\"applyconfigdlg\" ".
              " title=\"Apply and Restart\"></div>";
        print '<script>';
        # Addtimeperiod button
        print 'var applyconfigvar = function() { ';
        # Disable button
        print '$( ".ui-button:contains(Apply Configuration)" )';
        print '.button( "option", "disabled", true );';
        # Show spinner
        print '$("#applyconfigtextarea").css("background-image",';
        print "\"url('images/working.gif')\");";
        # Do REST stuff
        print ' $.getJSON( $("#applyconfigform").attr("action"), '; # <- url
        print ' $("#applyconfigform").serialize(),';             # <- data
        print ' function(response) {';                       # <- success
        print '  var code = response.code;';
        print '  var message = response.message;';
        print '  if( code == 200 ) {';
        print '    $(".flash.error").hide();';
        print '    $(".flash.notice").html("Success").show();';
        print '    $("#applyconfigtextarea").val(message);';
        print '    $("#applyconfigtextarea").show();';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html("Fail").show();';
        print '    $("#applyconfigtextarea").val(message);';
        print '    $("#applyconfigtextarea").show();';
        print '  }';
        # Enable button
        print '$( ".ui-button:contains(Apply Configuration)" )';
        print '.button( "option", "disabled", false );';
        # Disable spinner
        print ' $("#applyconfigtextarea").css("background-image","none");';
        # Scroll to bottom
        print ' var a = $("#applyconfigtextarea");';
        print ' a.scrollTop( a[0].scrollHeight - a.height() );';
        print ' });';
        print '};';
        # Cancel button
        print 'var cancel = function() { '.
              '$("#applyconfigdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#applyconfigdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Apply Configuration": applyconfigvar, "Close": cancel }';
        print ', modal : true';
        # TODO print ', resizable : true';
        print ', resizable : false';
        print ' } );';
        print '</script>';
    }

    # ------------------------------------------------------------------------
    function show_applyconfiguration_dialog_buttons( ) {
    # ------------------------------------------------------------------------
    # Outputs a html form fragment to add a New Hostgroup

        print '<form id="applyconfigform" '.
              'name="applyconfigform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=6&apply=1';
        print '">';
        print '<fieldset>';
        print '</fieldset>';
        print '</form>';
        print '<p>Log output:</p>';
        print '<textarea id="applyconfigtextarea" wrap="logical"';
        print 'readonly="true" >';
        print '</textarea>';
        print "<p>Click 'Apply Configuration' to apply and restart nagios"; 
        print ", or click 'Close' to cancel.</p>";
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function apply_configuration_using_REST( ) {
    # ------------------------------------------------------------------------
    # This is called by the 'Add New Host' dialog
    # JSON is returned to the dialog.

        # Apply the configuration
        $request = new RestRequest(
          RESTURL.'/apply/nagiosconfig',
          'POST',
          'json={"folder":"'.FOLDER.'","verbose":"true"}'
        );
        set_request_options( $request );
        $request->execute();
        $slist_raw = json_decode( $request->getResponseBody(), true );

        # Check the configuration
        $request = new RestRequest(
          RESTURL.'/check/nagiosconfig?json='.
          '{"folder":"'.FOLDER.'","verbose":"true"}',
          'GET'
        );
        set_request_options( $request );
        $request->execute();
        $slist_raw2 = json_decode( $request->getResponseBody(), true );

        # Restart Nagios
        $request = new RestRequest(
          RESTURL.'/restart/nagios',
          'POST',
          'json={"folder":"'.FOLDER.'","verbose":"true"}'
        );
        set_request_options( $request );
        $request->execute();
        $slist_raw3 = json_decode( $request->getResponseBody(), true );

        # Add newlines
        $slist="";
        $slist.="--------------------------------------------------";
        $slist.="--------------------------------------------------\n";
        $slist.="- APPLYING NAGIOS CONFIGURATION (csv2nag -y all)\n";
        $slist.="--------------------------------------------------";
        $slist.="--------------------------------------------------\n\n";
        foreach( $slist_raw as $slist_item )
            $slist.="$slist_item\n";
        $slist.="\n--------------------------------------------------";
        $slist.="--------------------------------------------------\n";
        $slist.="- NAGIOS CONFIGURATION CHECK (nagios -v /etc/nagios/nagios.cfg)\n";
        $slist.="--------------------------------------------------";
        $slist.="--------------------------------------------------\n\n";
        foreach( $slist_raw2 as $slist_item )
            $slist.="$slist_item\n";
        $slist.="\n--------------------------------------------------";
        $slist.="--------------------------------------------------\n";
        $slist.="- SCHEDULING NAGIOS RESTART\n";
        $slist.="--------------------------------------------------";
        $slist.="--------------------------------------------------\n\n";
        foreach( $slist_raw3 as $slist_item )
            $slist.="$slist_item\n";

        # Return json
        $retval = array();
        $retval["message"] = $slist;
        $resp = $request->getResponseInfo();
        $retval["code"] = $resp["http_code"];
        print( json_encode( $retval ) );

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function apply_configuration( ) {
    # ------------------------------------------------------------------------
    # TODO: This function is not used any more
    # Revert to last-known-good configuration using REST.

        print '<h2>Applying the Nagios configuration</h2>';

        #print '<input type="button" value="Abort" />';
        #print '<input type="button" value="Continue" />';

        $request = new RestRequest(
          RESTURL.'/apply/nagiosconfig',
          'POST',
          'json={"folder":"'.FOLDER.'","verbose":"false"}'
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        print '<pre style="margin-left: 50px;">';
        print $slist[0];
        #print '<h2>'.$slist[0].'</h2>';
        #print_r( $slist );
        print '</pre>';

        print '<h2>Returning to main configuration screen in a few seconds...</h2>';

        $url = create_url( );
        print '<script>';
        print ' setTimeout( function() {';
        print '  window.location="'.$url.'";';
        print ' }, 6000 );';
        print '</script>';

        print "\n</BODY>";
        print "\n</HTML>";
    }

    # ------------------------------------------------------------------------
    function restart_nagios( ) {
    # ------------------------------------------------------------------------
    # TODO: This function is not used any more
    # Revert to last-known-good configuration using REST.

        print '<h2>Restarting Nagios</h2>';
        print '<h2>New Configuration will show in Nagios within 3 minutes</h2>';

        #print '<input type="button" value="Abort" />';
        #print '<input type="button" value="Continue" />';

        $request = new RestRequest(
          RESTURL.'/restart/nagios',
          'POST',
          'json={"folder":"'.FOLDER.'","verbose":"false"}'
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        print '<pre style="margin-left: 50px;">';
        print $slist[0];
        #print '<h2>'.$slist[0].'</h2>';
        #print_r( $slist );
        print '</pre>';

        print '<h2>Returning to main configuration screen in a few seconds...</h2>';

        $url = create_url( );
        print '<script>';
        print ' setTimeout( function() {';
        print '  window.location="'.$url.'";';
        print ' }, 6000 );';
        print '</script>';

        print "\n</BODY>";
        print "\n</HTML>";

    }

    # ------------------------------------------------------------------------
    function revert_to_last_known_good( ) {
    # ------------------------------------------------------------------------
    # Revert to last-known-good configuration using REST.

        print '<h2>Reverting to last-known-good configuration</h2>';

        #print '<input type="button" value="Abort" />';
        #print '<input type="button" value="Continue" />';

        $request = new RestRequest(
          RESTURL.'/apply/nagioslastgoodconfig',
          'POST',
          'json={"folder":"'.FOLDER.'"}'
        );
        set_request_options( $request );
        $request->execute();
        $slist = json_decode( $request->getResponseBody(), true );

        print '<h2>'.$slist[0].'</h2>';
        #print '<pre>';
        #print_r( $slist[0] );
        #print '</pre>';

        print '<h2>Returning to main configuration screen in a few seconds...</h2>';

        $url = create_url( );
        print '<script>';
        print ' setTimeout( function() {';
        print '  window.location="'.$url.'";';
        print ' }, 6000 );';
        print '</script>';

        print "\n</BODY>";
        print "\n</HTML>";

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_svcset_service_fragment( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html fragment to display service info

        print "<td colspan=\"5\">";
        print "<table style=\"float:right;width:95%;margin-right:30px;\">";
        print "<thead><tr style='font-weight: normal;'>";
        print "<td>Service Description</td>";
        print "<td>Service Template</td>";
        print "<td>Command</td>";
        print "<td style=\"text-align: right\">";
        print "<a class=\"icon icon-add\" title=\"Add New Service\" onClick=\"".
              "$('#newsvcsetsvcdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?tab=1&newsvcsetsvcdialog=true".
              "&amp;hostname=".$name."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";
        print "</tr></thead><tbody>";

        $a = get_and_sort_servicesets( $name );
        $num=1;
        foreach( $a as $item ) {
            $style="";
            if( $item['disable'] == "1" ) {
                $style = ' style="background-color: #F7DCC6;"';
            } elseif( $item['disable'] == "2" ) {
                $style = ' style="background-color: #FFFC9E;"';
            } 

            if( $num % 2 == 0 )
                print "<tr class=innershaded$style>";
            else
                print "<tr$style>";

            print "<td>".urldecode($item['svcdesc'])."</td>";
            print "<td>".$item['template']."</td>";
            print "<td>".urldecode($item['command'])."</td>";
            // Actions
            print "<td style=\"float:right;\">";
            print "<a class=\"icon icon-clone\" title=\"Clone service to other".
                  " serviceset\" onClick=\"$('#clonesvcsetsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?tab=1&clonesvcsetsvcdialog=true".
                  "&amp;hostname=".$name."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "<a class=\"icon icon-edit\" title=\"Edit Service\"".
                  " onClick=\"$('#editsvcsetsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?tab=1&editsvcsetsvcdialog=true".
                  "&amp;hostname=".$name."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Service\"".
                  " onClick=\"$('#delsvcsetsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?tab=1&delsvcsetsvcdialog=true".
                  "&amp;hostname=".$name."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "</td>";
            print "</tr>";
            ++$num;
        }
        print "</table>";
        print "</td>";

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function show_service_fragment( $name ) {
    # ------------------------------------------------------------------------
    # Outputs a html fragment to display service info

        print "<td colspan=\"5\">";
        print "<table style=\"float:right;width:95%;margin-right:30px;\">";
        print "<thead><tr style='font-weight: normal;'>";
        print "<td>Service Description</td>";
        print "<td>Service Template</td>";
        print "<td>Command</td>";
        print "<td style=\"text-align: right\">";
        print "<a class=\"icon icon-add\" title=\"Add New Service\" onClick=\"".
              "$('#newsvcdlg').html('').". // Gets cached
              "load('/nagrestconf/".SCRIPTNAME."?newsvcdialog=true".
              "&amp;hostname=".$name."').".
              "dialog('open'); ".
              "return false;".
              "\" href=\"\">";
        print "</a></td>";
        print "</tr></thead><tbody>";

        $a=get_and_sort_services( $name );
        $num=1;
        foreach( $a as $item ) {
            $style="";
            if( $item['disable'] == "1" ) {
                $style = ' style="background-color: #F7DCC6;"';
            } elseif( $item['disable'] == "2" ) {
                $style = ' style="background-color: #FFFC9E;"';
            } 

            if( $num % 2 == 0 )
                print "<tr class=innershaded$style>";
            else
                print "<tr$style>";

            print "<td>".urldecode($item['svcdesc'])."</td>";
            print "<td>".$item['template']."</td>";
            print "<td>".urldecode($item['command'])."</td>";
            // Actions
            print "<td style=\"float:right;\">";
            print "<a class=\"icon icon-clone\" title=\"Clone service to other".
                  " host\" onClick=\"$('#clonesvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?clonesvcdialog=true".
                  "&amp;hostname=".$name."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "<a class=\"icon icon-edit\" title=\"Edit Service\"".
                  " onClick=\"$('#editsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?editsvcdialog=true".
                  "&amp;hostname=".$name."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "<a class=\"icon icon-delete\" title=\"Delete Service\"".
                  " onClick=\"$('#delsvcdlg').html('').". // Gets cached
                  "load('/nagrestconf/".SCRIPTNAME."?delsvcdialog=true".
                  "&amp;hostname=".$name."&amp;svcdesc=".urlencode($item['svcdesc'])."').".
                  "dialog('open'); ".
                  "return false;".
                  "\" href=\"\"></a>";
            print "</td>";
            print "</tr>";
            ++$num;
        }
        print "</table>";
        print "</td>";

        exit( 0 );
    }

    /***********************************************************************
     *
     * MAIN HELPERS
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function commands_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        if( isset( $query_str['commandstable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }

            # Shows the hosts table html fragment
            show_commands_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['delcommandsdialog'] )) {

            show_delcommanddialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newcommandsdialog'] )) {

            show_newcommanddialog_buttons( );

        } else if( isset( $query_str['editcommandsdialog'] )) {

            show_editcommanddialog_buttons( $query_str['name'] );

        # Configure the server using REST

        } else if( isset( $query_str['delcommand'] )) {

            delete_command_using_REST( );

        } else if( isset( $query_str['newcommand'] )) {

            add_new_command_using_REST( );

        } else if( isset( $query_str['editcommand'] )) {

            edit_command_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function timeperiods_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        if( isset( $query_str['timeperiodstable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }

            # Shows the hosts table html fragment
            show_timeperiods_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['deltimeperiodsdialog'] )) {

            show_deltimeperioddialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newtimeperiodsdialog'] )) {

            show_newtimeperioddialog_buttons( );

        } else if( isset( $query_str['edittimeperiodsdialog'] )) {

            show_edittimeperioddialog_buttons( $query_str['name'] );

        # Configure the server using REST

        } else if( isset( $query_str['deltimeperiod'] )) {

            delete_timeperiod_using_REST( );

        } else if( isset( $query_str['newtimeperiod'] )) {

            add_new_timeperiod_using_REST( );

        } else if( isset( $query_str['edittimeperiod'] )) {

            edit_timeperiod_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function templates_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        if( isset( $query_str['templatestable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }

            # Shows the hosts table html fragment
            show_templates_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );
        }

        # HTML Fragments

        if( isset( $query_str['delhosttemplatedialog'] )) {

            show_delhosttemplatedialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newhosttemplatedialog'] )) {

            show_newhosttemplatedialog_buttons( );

        } else if( isset( $query_str['edithosttemplatedialog'] )) {

            show_edithosttemplatedialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['delsvctemplatedialog'] )) {

            show_delsvctemplatedialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newsvctemplatedialog'] )) {

            show_newsvctemplatedialog_buttons( );

        } else if( isset( $query_str['editsvctemplatedialog'] )) {

            show_editsvctemplatedialog_buttons( $query_str['name'] );

        # Configure the server using REST

        } else if( isset( $query_str['delhosttemplate'] )) {

            delete_hosttemplate_using_REST( );

        } else if( isset( $query_str['newhosttemplate'] )) {

            add_new_hosttemplate_using_REST( );

        } else if( isset( $query_str['edithosttemplate'] )) {

            edit_hosttemplate_using_REST( );

        } else if( isset( $query_str['delsvctemplate'] )) {

            delete_svctemplate_using_REST( );

        } else if( isset( $query_str['newsvctemplate'] )) {

            add_new_svctemplate_using_REST( );

        } else if( isset( $query_str['editsvctemplate'] )) {

            edit_svctemplate_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function contacts_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        if( isset( $query_str['contactstable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }

            # Shows the hosts table html fragment
            show_contacts_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['delcontactgroupdialog'] )) {

            show_delcontactgroupdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newcontactgroupdialog'] )) {

            show_newcontactgroupdialog_buttons( );

        } else if( isset( $query_str['editcontactgroupdialog'] )) {

            show_editcontactgroupdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['delcontactdialog'] )) {

            show_delcontactdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newcontactdialog'] )) {

            show_newcontactdialog_buttons( );

        } else if( isset( $query_str['editcontactdialog'] )) {

            show_editcontactdialog_buttons( $query_str['name'] );

        # Configure the server using REST

        } else if( isset( $query_str['delcontactgroup'] )) {

            delete_contactgroup_using_REST( );

        } else if( isset( $query_str['newcontactgroup'] )) {

            add_new_contactgroup_using_REST( );

        } else if( isset( $query_str['editcontactgroup'] )) {

            edit_contactgroup_using_REST( );

        } else if( isset( $query_str['delcontact'] )) {

            delete_contact_using_REST( );

        } else if( isset( $query_str['newcontact'] )) {

            add_new_contact_using_REST( );

        } else if( isset( $query_str['editcontact'] )) {

            edit_contact_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function hostgroups_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        if( isset( $query_str['hostgroupstable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }

            # Shows the hosts table html fragment
            show_groups_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['delhostgroupdialog'] )) {

            show_delhostgroupdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newhostgroupdialog'] )) {

            show_newhostgroupdialog_buttons( );

        } else if( isset( $query_str['edithostgroupdialog'] )) {

            show_edithostgroupdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['delsvcgroupdialog'] )) {

            show_delsvcgroupdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newsvcgroupdialog'] )) {

            show_newsvcgroupdialog_buttons( );

        } else if( isset( $query_str['editsvcgroupdialog'] )) {

            show_editsvcgroupdialog_buttons( $query_str['name'] );

        # Configure the server using REST

        } else if( isset( $query_str['delhostgroup'] )) {

            delete_hostgroup_using_REST( );

        } else if( isset( $query_str['newhostgroup'] )) {

            add_new_hostgroup_using_REST( );

        } else if( isset( $query_str['edithostgroup'] )) {

            edit_hostgroup_using_REST( );

        } else if( isset( $query_str['delsvcgroup'] )) {

            delete_svcgroup_using_REST( );

        } else if( isset( $query_str['newsvcgroup'] )) {

            add_new_svcgroup_using_REST( );

        } else if( isset( $query_str['editsvcgroup'] )) {

            edit_svcgroup_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function servicesets_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        # Options that display a new page

        if( isset( $query_str['svcsetstable'] )) {

            $g_sort = "hostgroup";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }
            if( isset( $query_str['hgfilter'] )) {
                $g_hgfilter = $query_str['hgfilter'];
            } else {
                $g_hgfilter = "";
            }

            # Shows the servicesets table html fragment
            show_servicesets_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['fragment1id'] )) {

            show_svcset_service_fragment( $query_str['fragment1id'] );

        } else if( isset( $query_str['newsvcsetdialog'] )) {

            show_newsvcsetdialog_buttons( );

        } else if( isset( $query_str['delsvcsetdialog'] )) {

            show_delsvcsetdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['clonesvcsetdialog'] )) {

            show_clonesvcsetdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newsvcsetsvcdialog'] )) {

            show_newsvcsetsvcdialog_buttons( $query_str['hostname'] );

        } else if( isset( $query_str['editsvcsetsvcdialog'] )) {

            show_editsvcsetsvcdialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        } else if( isset( $query_str['delsvcsetsvcdialog'] )) {

            show_delsvcsetsvcdialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        } else if( isset( $query_str['clonesvcsetsvcdialog'] )) {

            show_clonesvcsetsvcdialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        # Configure the server using REST

        } else if( isset( $query_str['newsvcset'] )) {

            add_new_svcset_using_REST( );

        } else if( isset( $query_str['delsvcset'] )) {

            delete_svcset_using_REST( );

        } else if( isset( $query_str['clonesvcset'] )) {

            clone_svcset_using_REST( );

        } else if( isset( $query_str['newsvcsetsvc'] )) {

            add_new_svcset_svc_using_REST( );

        } else if( isset( $query_str['delsvcsetsvc'] )) {

            delete_svcset_svc_using_REST( );

        } else if( isset( $query_str['editsvcsetsvc'] )) {

            edit_svcset_svc_using_REST( );

        } else if( isset( $query_str['clonesvcsetsvc'] )) {

            clone_svcset_svc_using_REST( );

        }
    }

    # ------------------------------------------------------------------------
    function hosts_page_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Check for options that return html fragments or JSON

        global $g_sort, $g_hgfilter;

        # Options that display a new page

        if( isset( $query_str['hoststable'] )) {

            $g_sort = "name";
            if( isset( $query_str['sort'] )) {
                $g_sort = $query_str['sort'];
            }
            if( isset( $query_str['hgfilter'] )) {
                $g_hgfilter = $query_str['hgfilter'];
            } else {
                $g_hgfilter = "";
            }

            # Shows the hosts table html fragment
            show_hosts_tab_right_pane( );
            exit( 0 );

        } else if( isset( $query_str['revert'] )) {

            show_html_header();
            revert_to_last_known_good( );
            exit( 0 );

        } else if( isset( $query_str['apply'] )) {

            apply_configuration_using_REST( );
            #show_html_header();
            #restart_nagios( );
            exit( 0 );

        }

        # HTML Fragments

        if( isset( $query_str['fragment1id'] )) {

            show_service_fragment( $query_str['fragment1id'] );

        } else if( isset( $query_str['applyconfig'] )) {

            show_applyconfiguration_dialog_buttons( );

        } else if( isset( $query_str['newhostdialog'] )) {

            show_newhostdialog_buttons( );

        } else if( isset( $query_str['edithostdialog'] )) {

            show_edithostdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['delhostdialog'] )) {

            show_delhostdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['disablehostdialog'] )) {

            show_disablehostdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['testinghostdialog'] )) {

            show_testinghostdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['enablehostdialog'] )) {

            show_enablehostdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['clonehostdialog'] )) {

            show_clonehostdialog_buttons( $query_str['name'] );

        } else if( isset( $query_str['newsvcdialog'] )) {

            show_newsvcdialog_buttons( $query_str['hostname'] );

        } else if( isset( $query_str['editsvcdialog'] )) {

            show_editsvcdialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        } else if( isset( $query_str['delsvcdialog'] )) {

            show_delsvcdialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        } else if( isset( $query_str['clonesvcdialog'] )) {

            show_clonesvcdialog_buttons( $query_str['hostname'],
                                        $query_str['svcdesc'] );

        # Configure the server using REST

        } else if( isset( $query_str['newhost'] )) {

            add_new_host_using_REST( );

        } else if( isset( $query_str['delhost'] )) {

            delete_host_using_REST( );

        } else if( isset( $query_str['disablehost'] )) {

            disable_host_using_REST( );

        } else if( isset( $query_str['testinghost'] )) {

            testing_host_using_REST( );

        } else if( isset( $query_str['enablehost'] )) {

            enable_host_using_REST( );

        } else if( isset( $query_str['edithost'] )) {

            edit_host_using_REST( );

        } else if( isset( $query_str['clonehost'] )) {

            clone_host_using_REST( );

        } else if( isset( $query_str['newsvc'] )) {

            add_new_svc_using_REST( );

        } else if( isset( $query_str['delsvc'] )) {

            delete_svc_using_REST( );

        } else if( isset( $query_str['editsvc'] )) {

            edit_svc_using_REST( );

        } else if( isset( $query_str['clonesvc'] )) {

            clone_svc_using_REST( );

        }
    }

    /***********************************************************************
     *
     * PLUGINS CODE CALLED BY PLUGINS (AKA PLUGINS API, ETC)
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function spi_add_action( $action, $cb_func ) {
    # ------------------------------------------------------------------------
        global $g_plugins_init_list,
               $g_plugins_tabs_list,
               $g_plugins_dlg_divs_list,
               $g_plugins_buttons_list,
               $g_plugins_actions_list;

        if( $action == 'init' ) {
            $g_plugins_init_list[] = $cb_func;
        }
        else if( $action == 'tab' ) {
            $g_plugins_tabs_list[] = $cb_func;
        }
        else if( $action == 'button' ) {
            $g_plugins_buttons_list[] = $cb_func;
        }
        else if( $action == 'dlgdiv' ) {
            $g_plugins_dlg_divs_list[] = $cb_func;
        }
        else if( $action == 'action' ) {
            $g_plugins_actions_list[] = $cb_func;
        }
    }

    # ------------------------------------------------------------------------
    function spi_get_tab_idx( ) {
    # ------------------------------------------------------------------------
    # Returns the tab index. Tabs are ordered by tab idx from right to left in
    # the GUI. This is the the key field in $g_tab_names

        global $g_tab;

        return $g_tab;
    }

    # ------------------------------------------------------------------------
    function spi_get_tab_id( $tab_id ) {
    # ------------------------------------------------------------------------
    # Returns the tab id passed around as 'tab=' in the query string.
    # This is the $value[2] in $g_tab_names.

        return $g_tab;
    }

    # ------------------------------------------------------------------------
    function spi_get_tab_prettyname( $tab_id ) {
    # ------------------------------------------------------------------------
    # Returns the name of the tab as displayed on-screen - it may have spaces
    # in it. This is the $value[1] in $g_tab_names.

        return $g_tab;
    }

    # ------------------------------------------------------------------------
    function spi_get_tab_name( $tab_id ) {
    # ------------------------------------------------------------------------
    # Returns the name of the tab. Appears as id=#name in HTML code.
    # This is the $value[0] in $g_tab_names.

        global $g_tab_names;

        foreach( $g_tab_names as $value ) {
            if( $value[2] == $tab_id )
                return $value[0];
        }
    }

    # ------------------------------------------------------------------------
    function &spi_get_tab_names_array( ) {
    # ------------------------------------------------------------------------
    # Returns the $g_tab_names array. Use the reference assignment to modify.

        global $g_tab_names;

        # Key: Page tab order from right to left
        # array( 0 - #idname, 1 - tab text, 2 - query string tab no. )
        $g_tab_names = array(
            7 => array("hosts","Hosts",2),
            6 => array("servicesets","Service Sets",1),
            5 => array("templates","Templates",5),
            4 => array("contacts","Contacts",4),
            3 => array("groups","Groups",3),
            2 => array("commands","Commands",7),
            1 => array("timeperiods","Timeperiods",6),
        );

        return $g_tab_names;
    }

    /***********************************************************************
     *
     * PLUGINS CODE CALLED BY NAGRESTCONF
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function plugins_actions( $query_str ) {
    # ------------------------------------------------------------------------
    # Added to the end of the page.

        global $g_plugins_actions_list;

        if( ! is_array( $g_plugins_actions_list ) ) return;

        foreach( $g_plugins_actions_list as $cb_func ) {
            $cb_func( $query_str );
        }
    }

    # ------------------------------------------------------------------------
    function plugins_dlg_divs( ) {
    # ------------------------------------------------------------------------
    # Added to the end of the page.

        global $g_plugins_dlg_divs_list;

        if( ! is_array( $g_plugins_dlg_divs_list ) ) return;

        foreach( $g_plugins_dlg_divs_list as $cb_func ) {
            $cb_func();
        }
    }

    # ------------------------------------------------------------------------
    function plugins_buttons( ) {
    # ------------------------------------------------------------------------
        global $g_plugins_buttons_list;

        if( ! is_array( $g_plugins_buttons_list ) ) return;

        foreach( $g_plugins_buttons_list as $cb_func ) {
            $cb_func();
        }
    }

    # ------------------------------------------------------------------------
    function plugins_tabs( ) {
    # ------------------------------------------------------------------------
        global $g_plugins_tabs_list;

        if( ! is_array( $g_plugins_tabs_list ) ) return;

        foreach( $g_plugins_tabs_list as $cb_func ) {
            $cb_func();
        }
    }

    # ------------------------------------------------------------------------
    function plugins_init( ) {
    # ------------------------------------------------------------------------
        global $g_plugins_init_list;

        if( ! is_array( $g_plugins_init_list ) ) return;

        foreach( $g_plugins_init_list as $cb_func ) {
            $cb_func();
        }
    }

    # ------------------------------------------------------------------------
    function plugins_load( $pattern ) {
    # ------------------------------------------------------------------------

        $pwd_was = getcwd( );
        chdir( LIBDIR );

        foreach( glob($pattern) as $file ) {
            include $file;
        }

        chdir( $pwd_was ); 
    }

    /***********************************************************************
     *
     * HELPER FUNCS
     *
     ***********************************************************************
     */

    # ------------------------------------------------------------------------
    function set_request_options( $request ) {
    # ------------------------------------------------------------------------
        $request->setUsername(RESTUSER);
        $request->setPassword(RESTPASS);
        if( defined('SSLKEY') ) $request->setSSLKey(SSLKEY);
        if( defined('SSLCERT') ) $request->setSSLCert(SSLCERT);
    }

    # ------------------------------------------------------------------------
    function read_config_file( ) {
    # ------------------------------------------------------------------------
        global $g_folders;

        $ini_array = parse_ini_file( 
            "/etc/nagrestconf/nagrestconf.ini" );
        define( "FOLDER", $ini_array["folder"][0] );
        $g_folders = $ini_array["folder"];
        define( "RESTUSER", $ini_array["restuser"] );
        define( "RESTPASS", $ini_array["restpass"] );
        define( "RESTURL", $ini_array["resturl"] );
        if( !empty($ini_array["sslkey"]) )
            define( "SSLKEY", $ini_array["sslkey"] );
        if( !empty($ini_array["sslcert"]) )
            define( "SSLCERT", $ini_array["sslcert"] );
    }

    # ------------------------------------------------------------------------
    function check_REST_connection( ) {
    # ------------------------------------------------------------------------
        # This query should return a JSON empty list '[]'.
        $request = new RestRequest(
          RESTURL.'/show/hosts?json='.
          '{"folder":"'.FOLDER.'","filter":"ThIsWoNtMaTcH"}',
          'GET');
        set_request_options( $request );
        $request->execute();
        $resp = $request->getResponseInfo();
        $test=$request->getResponseBody();
        if( trim($test) == "null" || strlen($test) < 2 ) {
            echo "<h1><br />&nbsp;&nbsp;Could not execute query using ";
            echo "REST.<br />";
            echo "&nbsp;&nbsp;Please check system settings.</h1>";
            exit( 1 );
        }
        if( $resp["http_code"] != 200 ) {
            echo "<h1><br />&nbsp;&nbsp;Could not execute query using ";
            echo "REST.<br />";
            echo "&nbsp;&nbsp;Please check system settings.<br /><br />";
            echo "&nbsp;&nbsp;REST return code: ".$resp["http_code"];
            if( $resp["http_code"] == 401 )
                echo " (Unauthorized)";
            if( $resp["http_code"] == 403 )
                echo " (Forbidden)";
            echo "<br /><br />";
            if( strlen(json_decode($test)) > 2 ) {
                echo "&nbsp;&nbsp;Error was:<br /><br />";
                echo "&nbsp;&nbsp;".json_decode($test);
            }
            echo "</h1>";
            exit( 1 );
        }
    }

    # ------------------------------------------------------------------------
    function init_tab_names( ) {
    # ------------------------------------------------------------------------
        global $g_tab_names;

        # Key: Page tab order from right to left
        # array( 0 - #idname, 1 - tab text, 2 - query string tab no. )
        $g_tab_names = array(
            1 => array("hosts","Hosts",2),
            2 => array("servicesets","Service Sets",1),
            3 => array("templates","Templates",5),
            4 => array("contacts","Contacts",4),
            5 => array("groups","Groups",3),
            6 => array("commands","Commands",7),
            7 => array("timeperiods","Timeperiods",6),
        );
    }

    # ------------------------------------------------------------------------
    function main( ) {
    # ------------------------------------------------------------------------
        global $g_tab;

        session_start( );

        date_default_timezone_set('UTC');

        read_config_file( );

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        $g_tab = 2; #<-- Default to 2, the Hosts tab. Don't change this.
                    #    This defaults to the 'Hosts' tab page.

        if( isset( $query_str['tab'] )) {
            $g_tab = (int) $query_str['tab'];
        }

        init_tab_names( );

        plugins_load( 'plugins-enabled/*.php' );

        # Call all the plugins 'init' functions
        plugins_init( );

        if( ! empty($query_str['name']) ) 
            $query_str['name'] = urlencode( $query_str['name'] );

        header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
        header('Pragma: no-cache'); // HTTP 1.0.
        header('Expires: 0'); // Proxies.

        switch( $g_tab ) {
            # ---------------------------------------------------------------
            case 1: # Service Sets Tab
            # ---------------------------------------------------------------
                #session_start( );
                servicesets_page_actions( $query_str );

                plugins_actions( $query_str );

                show_html_header();

                check_REST_connection();

                show_servicesets_page( );

                show_delete_svcset_dlg_div( );
                show_new_svcset_dlg_div( );
                show_clone_svcset_dlg_div( );
                show_clone_svcset_svc_dlg_div( );
                show_delete_svcset_svc_dlg_div( );
                show_edit_svcset_svc_dlg_div( );
                show_new_svcset_svc_dlg_div( );
                break;
            # ---------------------------------------------------------------
            case 2: # Hosts Tab
            # ---------------------------------------------------------------
                # Show html fragments or run REST actions (all will exit(0))
                hosts_page_actions( $query_str );

                plugins_actions( $query_str );

                #session_start( );
                show_html_header();

                check_REST_connection();

                # Write the whole page if we get this far
                show_hosts_page( );

                # Output divs to contain dialog boxes
                show_new_host_dlg_div( );
                show_delete_host_dlg_div( );
                show_edit_host_dlg_div( );
                show_new_svc_dlg_div( );
                show_edit_svc_dlg_div( );
                show_delete_svc_dlg_div( );
                show_clone_svc_dlg_div( );
                show_clone_host_dlg_div( );
                show_disable_host_dlg_div( );
                show_testing_host_dlg_div( );
                show_enable_host_dlg_div( );
                break;
            # ---------------------------------------------------------------
            case 3: # Hostgroups (Groups) Tab
            # ---------------------------------------------------------------
                # Show html fragments or run REST actions
                hostgroups_page_actions( $query_str );

                plugins_actions( $query_str );

                #session_start( );
                show_html_header();

                check_REST_connection();

                show_hostgroups_page( );

                # Output divs to contain dialog boxes
                show_new_hostgroup_dlg_div( );
                show_delete_hostgroup_dlg_div( );
                show_edit_hostgroup_dlg_div( );
                show_new_svcgroup_dlg_div( );
                show_delete_svcgroup_dlg_div( );
                show_edit_svcgroup_dlg_div( );
                break;
            # ---------------------------------------------------------------
            case 4: # Contacts Tab
            # ---------------------------------------------------------------
                # Show html fragments or run REST actions
                contacts_page_actions( $query_str );

                plugins_actions( $query_str );

                #session_start( );
                show_html_header();

                check_REST_connection();

                show_contacts_page( );

                # Output divs to contain dialog boxes
                show_new_contactgroup_dlg_div( );
                show_delete_contactgroup_dlg_div( );
                show_edit_contactgroup_dlg_div( );
                show_new_contact_dlg_div( );
                show_delete_contact_dlg_div( );
                show_edit_contact_dlg_div( );
                break;
            # ---------------------------------------------------------------
            case 5: # Templates Tab
            # ---------------------------------------------------------------
                # Show html fragments or run REST actions
                templates_page_actions( $query_str );

                plugins_actions( $query_str );

                #session_start( );
                show_html_header();

                check_REST_connection();

                show_templates_page( );

                # Output divs to contain dialog boxes
                show_new_hosttemplate_dlg_div( );
                show_delete_hosttemplate_dlg_div( );
                show_edit_hosttemplate_dlg_div( );
                show_new_svctemplate_dlg_div( );
                show_delete_svctemplate_dlg_div( );
                show_edit_svctemplate_dlg_div( );
                break;
            # ---------------------------------------------------------------
            case 6: # Timeperiods Tab
            # ---------------------------------------------------------------
                # Show html fragments or run REST actions
                timeperiods_page_actions( $query_str );

                plugins_actions( $query_str );

                #session_start( );
                show_html_header();

                check_REST_connection();

                show_timeperiods_page( );

                # Output divs to contain dialog boxes
                show_new_timeperiod_dlg_div( );
                show_delete_timeperiod_dlg_div( );
                show_edit_timeperiod_dlg_div( );
                break;
            # ---------------------------------------------------------------
            case 7: # Commands Tab
            # ---------------------------------------------------------------
                # Show html fragments or run REST actions
                commands_page_actions( $query_str );

                plugins_actions( $query_str );

                #session_start( );
                show_html_header();

                check_REST_connection();

                show_commands_page( );

                # Output divs to contain dialog boxes
                show_new_command_dlg_div( );
                show_delete_command_dlg_div( );
                show_edit_command_dlg_div( );
                break;
            # ---------------------------------------------------------------
            default:
            # ---------------------------------------------------------------
                plugins_actions( $query_str );
                plugins_tabs( );
        }

        show_applyconfiguration_dlg_div( );
        plugins_dlg_divs( );

        print "\n</BODY>";
        print "\n</HTML>";
    }

    main( );

# vim: ts=4:sw=4:et:smartindent:tw=78
?>
