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
<TITLE>Nagios REST Configurator</TITLE>
<meta name="author" content="Mark Clarkson">
<meta name="keywords" content="">
<meta name="description" content="">
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="main.css">
<script src = js/jquery-1.9.1.js></script>
<script src = js/jquery-ui-1.10.3.custom.min.js></script>
<link rel=stylesheet type=text/css
    href=css/redmond/jquery-ui.css />
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

        global $g_tab;

        $tab1="taboff1";
        $tab2="taboff2";
        $tab3="taboff3";
        $tab4="taboff4";
        $tab5="taboff5";
        $tab6="taboff6";
        $tab7="taboff7";
        switch( $g_tab ) {
            case 1: $tab1="tabon1"; break;
            case 2: $tab2="tabon2"; break;
            case 3: $tab3="tabon3"; break;
            case 4: $tab4="tabon4"; break;
            case 5: $tab5="tabon5"; break;
            case 6: $tab6="tabon6"; break;
            case 7: $tab7="tabon7"; break;
        }

        #print "<img src=\"./images/logo.png\" id=logo alt=\"logo\">";
        #print "<h1>Smorg</h1>";
        print "<h1 class=\"titletext\">Nagios REST Configurator</h1>";
        #print "<h2 class=\"pghdr\">Nagios REST Configurator</h2>";

        print "<div id=$tab1>";
        if( $g_tab != 1 ) print "<a href=\"index.php?tab=1\">";
        print "Service Sets";
        if( $g_tab != 1 ) print "</a>";
        print "</div>";

        print "<div id=$tab2>";
        if( $g_tab != 2 ) print "<a href=\"index.php?tab=2\">";
        print "Hosts";
        if( $g_tab != 2 ) print "</a>";
        print "</div>";

        print "<div id=$tab3>";
        if( $g_tab != 3 ) print "<a href=\"index.php?tab=3\">";
        print "Groups";
        if( $g_tab != 3 ) print "</a>";
        print "</div>";

        print "<div id=$tab4>";
        if( $g_tab != 4 ) print "<a href=\"index.php?tab=4\">";
        print "Contacts";
        if( $g_tab != 4 ) print "</a>";
        print "</div>";

        print "<div id=$tab5>";
        if( $g_tab != 5 ) print "<a href=\"index.php?tab=5\">";
        print "Templates";
        if( $g_tab != 5 ) print "</a>";
        print "</div>";

        print "<div id=$tab6>";
        if( $g_tab != 6 ) print "<a href=\"index.php?tab=6\">";
        print "Timeperiods";
        if( $g_tab != 6 ) print "</a>";
        print "</div>";

        print "<div id=$tab7>";
        if( $g_tab != 7 ) print "<a href=\"index.php?tab=7\">";
        print "Commands";
        if( $g_tab != 7 ) print "</a>";
        print "</div>";
    }

    # ------------------------------------------------------------------------
    function show_revert_and_apply_buttons( ) {
    # ------------------------------------------------------------------------
        global $g_tab;

        $url = create_url( );

        print '<input id="revert" type="button" value="Revert Changes" />';
        print '<script>';
        print ' $("#revert").bind("click", function() {';
        print ' var ans = confirm( "Reverting to Last Known Good';
        print ' configuration\nAll changes will be lost - Really Revert?" );';
        print ' if( ans ) window.location="'.$url.'&revert=true";';
        print '} );';
        print '</script>';
        print "<hr />";
        print '<input id="apply" type="button" value="Apply Changes" />';
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
        print "<hr />";
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

        show_revert_and_apply_buttons( );
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
        print "<div id=\"edithostgroupdlg\" title=\"Add New Hostgroup\"></div>";
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

        show_revert_and_apply_buttons();
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
            print "<td>".substr($item['command'],0,100);
            if( strlen($item['command'])>100 ) print "...";
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
            $query_str["name"] = urlencode($query_str["name"]);
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
        $newcmd = strtr( $command, array("\""=>"\\\"","\\"=>"\\\\") );
        print '<p>';
        print '<label for="command">Command</label>';
        print '<input class="field" type="text" id="command" name="command"';
              # Using <.. value="\"" ..> does not work so...
        print ' required="required" value="'.$newcmd.'" />';
              # ...have to use javascript to set the value:
        print '<script>$("#command").val("'.$newcmd.'");</script>';
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
            $query_str["name"] = urlencode($query_str["name"]);
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

        show_revert_and_apply_buttons();
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

        show_revert_and_apply_buttons();
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
        #$g_sort_new = "use";
        #$url = create_url( );
        #print "<td><a href='".$url."'><span class=black>Use </span>";
        #print "<img width=8 src=/nagrestconf/images/ArrowDown.svg.png".
        #      " alt=\"arrow\"></a></td>";

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
        # name
        print '<p>';
        print '<label for="hosttemplatename">Host Template Name *</label>';
        print '<input class="field" type="text" id="hosttemplatename" ';
        print ' name="name" required="required" ';
        print '/>';
        print '</p>';
        # Use
        #print '<p>';
        #print '<label for="use">Use Template *</label>';
        #print '<input class="field" type="text" id="use" name="use" ';
        #print ' required="required" />';
        #print '</p>';
        # Contacts
        print '<p>';
        print '<label for="contacts">Contacts</label>';
        print '<input class="field" type="text" id="contacts" name="contacts" />';
        print '</p>';
        # Contact Groups
        print '<p>';
        print '<label for="contactgroups">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroups" name="contactgroups" />';
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
        print '<label for="checkperiod">Check Period *</label>';
        print '<input class="field" type="text" id="checkperiod" '.
              'value="24x7" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="notifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="notifperiod" '.
              'value="24x7" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="60" name="notifinterval" required="required" />';
        print '</p>';
        # Check Command
        print '<p>';
        print '<label for="checkcommand">Check Command</label>';
        print '<input class="field" type="text" id="checkcommand" '.
              'value="check-host-alive" name="checkcommand" />';
        print '</p>';
        # Notification Options
        print '<p>';
        print '<label for="notifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="notifopts" '.
              'value="d u r" name="notifopts" />';
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
        # Hostname
        print '<p>';
        print '<label for="hosttemplatename">Contact Name *</label>';
        print '<input class="field" type="text" id="hosttemplatename" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Use
        #print '<p>';
        #print '<label for="use">Use Template *</label>';
        #print '<input class="field" type="text" id="use" name="use" ';
        #print ' required="required"';
        #print ' value="'.$use.'" />';
        #print '</p>';
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
        print '<label for="checkperiod">Check Period *</label>';
        print '<input class="field" type="text" id="checkperiod" '.
              'value="'.$checkperiod.'" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="notifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="notifperiod" '.
              'value="'.$notifperiod.'" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="'.$notifinterval.'" name="notifinterval" required="required" />';
        print '</p>';
        # Check Command
        print '<p>';
        print '<label for="checkcommand">Check Command</label>';
        print '<input class="field" type="text" id="checkcommand" '.
              'value="'.$checkcommand.'" name="checkcommand" />';
        print '</p>';
        # Notification Options
        print '<p>';
        print '<label for="notifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="notifopts" '.
              'value="'.$notifopts.'" name="notifopts" />';
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
        # Handle deleting fields
        if( empty( $query_str["contacts"] ) )
            $query_str["contacts"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
        if( empty( $query_str["notifopts"] ) )
            $query_str["notifopts"] = "-";
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
        # name
        print '<p>';
        print '<label for="svctemplatename">Service Template Name *</label>';
        print '<input class="field" type="text" id="svctemplatename" ';
        print ' name="name" required="required" ';
        print '/>';
        print '</p>';
        # Use
        #print '<p>';
        #print '<label for="use">Use Template *</label>';
        #print '<input class="field" type="text" id="use" name="use" ';
        #print ' required="required" />';
        #print '</p>';
        # Contacts
        print '<p>';
        print '<label for="contacts">Contacts</label>';
        print '<input class="field" type="text" id="contacts" name="contacts" />';
        print '</p>';
        # Contact Groups
        print '<p>';
        print '<label for="contactgroups">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroups" name="contactgroups" />';
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
        print '<label for="checkperiod">Check Period *</label>';
        print '<input class="field" type="text" id="checkperiod" '.
              'value="24x7" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="60" name="notifinterval" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="notifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="notifperiod" '.
              'value="24x7" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Options
        print '<p>';
        print '<label for="notifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="notifopts" '.
              'name="notifopts" />';
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
        # Hostname
        print '<p>';
        print '<label for="svctemplatename">Contact Name *</label>';
        print '<input class="field" type="text" id="svctemplatename" ';
        print ' readonly="readonly" name="name" required="required" ';
        print ' value="'.$name.'" />';
        print '</p>';
        # Use
        #print '<p>';
        #print '<label for="use">Use Template *</label>';
        #print '<input class="field" type="text" id="use" name="use" ';
        #print ' required="required"';
        #print ' value="'.$use.'" />';
        #print '</p>';
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
              'value="'.$maxcheckattempts.'" name="maxcheckattempts" required="required" />';
        print '</p>';
        # Check Period
        print '<p>';
        print '<label for="checkperiod">Check Period *</label>';
        print '<input class="field" type="text" id="checkperiod" '.
              'value="'.$checkperiod.'" name="checkperiod" required="required" />';
        print '</p>';
        # Notification Interval
        print '<p>';
        print '<label for="notifinterval">Notif Interval *</label>';
        print '<input class="field" type="text" id="notifinterval" '.
              'value="'.$notifinterval.'" name="notifinterval" required="required" />';
        print '</p>';
        # Notification Period
        print '<p>';
        print '<label for="notifperiod">Notif Period *</label>';
        print '<input class="field" type="text" id="notifperiod" '.
              'value="'.$notifperiod.'" name="notifperiod" required="required" />';
        print '</p>';
        # Notification Options
        print '<p>';
        print '<label for="notifopts">Notif Opts</label>';
        print '<input class="field" type="text" id="notifopts" '.
              'value="'.$notifopts.'" name="notifopts" />';
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
        # Handle deleting fields
        if( empty( $query_str["contacts"] ) )
            $query_str["contacts"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
        if( empty( $query_str["notifopts"] ) )
            $query_str["notifopts"] = "-";
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

        show_revert_and_apply_buttons();
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
        print '<label for="svcnotifperiod">Service Notif Period *</label>';
        print '<input class="field" type="text" id="svcnotifperiod" '.
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
        print '<label for="svcnotifcmds">Service Notif Cmds *</label>';
        print '<input class="field" type="text" id="svcnotifcmds" '.
              'value="notify-service-by-email" name="svcnotifcmds" />';
        print '</p>';
        # Host Notification Period
        print '<p>';
        print '<label for="hstnotifperiod">Host Notif Period *</label>';
        print '<input class="field" type="text" id="hstnotifperiod" '.
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
        print '<label for="hstnotifcmds">Host Notif Cmds *</label>';
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
        print '<label for="svcnotifperiod">Service Notif Period</label>';
        print '<input class="field" type="text" id="svcnotifperiod" '.
              'name="svcnotifperiod"';
        print ' value="'.$svcnotifperiod.'" />';
        print '</p>';
        # Service Notification Options
        print '<p>';
        print '<label for="svcnotifopts">Service Notif Opts</label>';
        print '<input class="field" type="text" id="svcnotifopts" '.
              'name="svcnotifopts"';
        print ' value="'.$svcnotifopts.'" />';
        print '</p>';
        # Service Notification Commands
        print '<p>';
        print '<label for="svcnotifcmds">Service Notif Cmds</label>';
        print '<input class="field" type="text" id="svcnotifcmds" '.
              'name="svcnotifcmds"';
        print ' value="'.$svcnotifcmds.'" />';
        print '</p>';
        # Host Notification Period
        print '<p>';
        print '<label for="hstnotifperiod">Host Notif Period</label>';
        print '<input class="field" type="text" id="hstnotifperiod" '.
              'name="hstnotifperiod"';
        print ' value="'.$hstnotifperiod.'" />';
        print '</p>';
        # Host Notification Options
        print '<p>';
        print '<label for="hstnotifopts">Host Notif Opts</label>';
        print '<input class="field" type="text" id="hstnotifopts" '.
              'name="hstnotifopts"';
        print ' value="'.$hstnotifopts.'" />';
        print '</p>';
        # Host Notification Commands
        print '<p>';
        print '<label for="hstnotifcmds">Host Notif Cmds</label>';
        print '<input class="field" type="text" id="hstnotifcmds" '.
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
        # Handle deleting fields
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
        print '<label for="members">Members<br>(space delimited)</label>';
        print '<input class="field" type="text" id="members" name="members" ';
        print '/>';
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
        print '<label for="members">Members<br>(space delimited)</label>';
        print '<input class="field" type="text" id="members" name="members" ';
        print ' value="'.$members.'" />';
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

        show_revert_and_apply_buttons( );
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
        
        $copyto = $query_str["copyto"];
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

        $a = get_and_sort_servicesets( $name );

        # Can't search for specific service check using the REST interface.
        # Have to ask for all services for the host (above) and search it:
        foreach( $a as $svcset ) {
            extract( $svcset );
            if( $svcdesc == $svcdesc_in ) break;
        }

        print '<form id="editsvcsetsvcform" name="editsvcsetsvcform" method="get"';
        print ' action="/nagrestconf/'.SCRIPTNAME.'?tab=1&editsvcsetsvc=1';
        print '">';
        print '<fieldset>';

        # Hostname
        print '<p>';
        print '<label for="svcsetname">Service Set Name</label>';
        print '<input class="field" type="text" id="svcsetname" name="name"';
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
     * ADD NEW SERVICSET SERVICE DIALOG
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
        # Hostname
        print '<p>';
        print '<label for="svcsetname">Service Set Name</label>';
        print '<input class="field" type="text" id="svcsetname" name="name"';
        print ' value="'.$svcsetname.'" readonly="readonly" />';
        print '</p>';
        # Service Template
        $st = get_and_sort_servicetemplates( );
        print '<p>';
        print '<label for="svctemplate">Service Template *</label>';
        print '<select class="field" id="svctemplate" name="template"';
        print ' required="required">';
        foreach( $st as $item ) {
            print '<option value="'.$item["name"].'">'.$item["name"]
              .'</option>';
        }
        print '</select>';
        print '</p>';
        # Command
        print '<p>';
        print '<label for="command">Command *</label>';
        print '<input class="field" type="text" id="command" name="command"';
        print ' required="required" />';
        print '</p>';
        # Service Description
        print '<p>';
        print '<label for="svcdesc">Description *</label>';
        print '<input class="field" type="text" id="svcdesc" name="svcdesc"';
        print ' required="required" />';
        print '</p>';
        # Service Groups
        print '<p>';
        print '<label for="svcgroup">Service Groups</label>';
        print '<input class="field" type="text" id="svcgroup"';
        print ' name="svcgroup">';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="contacts">Contacts</label>';
        print '<input class="field" type="text" id="contacts"';
        print ' name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="contactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="customvars">Custom Variables</label>';
        print '<input class="field" type="text" id="customvars"';
        print ' name="customvars">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' name="freshnessthresh">';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" checked />';
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
              "  $(document).keydown(function(event){".
              "      if(event.keyCode == 13) {".
              "        event.preventDefault();".
              "      return false;".
              "      }".
              "    });".
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
        global $g_tab, $g_hgfilter, $g_hfilter;

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
        print "<p style='margin-bottom:10px'>Filter by host regex:<br>".
              "<input id='hregex' name='hregex' type='text'".
              " style='width:100px;'".
              " value='".$hfilter."'".
              " /><span class='btn ui-corner-all' ".
              " onClick='".
              "var a=encodeURIComponent($(\"#hregex\").val());".
              "window.location=\"$url\"+\"&amp;hfilter=\"+a;".
              "'>go</span>".
              "</p>";
        $g_hgfilter = 0; # <-- don't include hgfilter
        $url = create_url( );
        print "<p>Filter by hostgroup:<br>";
        print "<select id=\"hgsel\" name=\"Hostgroup\" onChange=\"".
              "var a=$('select.#hgsel option:selected').val();".
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
        print "<hr />";

        show_revert_and_apply_buttons( );
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
        print 'var cancel = function() { $("#edithostdlg").dialog("close"); };';
        # Setup the dialog
        print '$( "div#edithostdlg" ).dialog( { ';
        print 'autoOpen : false';
        print ', width : 500';
        print ', resizable : false';
        print ', position : { my: "center top", at: "center top+60" }';
        print ', buttons : { "Apply Changes": edithost, "Close": cancel }';
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
        print ' value="'.$contact.'" name="contact">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="econtactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="econtactgroup"';
        print ' value="'.$contactgroups.'" name="contactgroups">';
        print '</p>';
        # Service Set - DON'T SHOW SERVICESETS WHEN EDITING
        #$hgs = get_and_sort_servicesets_unique( );
        #print '<p>';
        #print '<label for="serviceset">Service Set</label>';
        #print '<select class="field" id="serviceset" name="servicesets">';
        #foreach( $hgs as $item ) {
        #    print '<option value="'.$item.'">'.$item.'</option>';
        #}
        #print '</select>';
        #print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="eactivechecks">Active Check</label>';
        $checked="checked";
        if( $activechecks == "0" ) $checked="";
        print '<input class="field" type="checkbox" id="eactivechecks"';
        print ' name="activechecks" '.$checked.' />';
        print '</p>';

        ###:TAB2
        print '</div>';
        print '<div id="fragment-2">';
        # Max check attempts
        print '<p>';
        print '<label for="emaxcheckattempts">Max check attempts</label>';
        print '<input class="field" type="text" id="emaxcheckattempts"';
        print ' value="'.$maxcheckattempts.'" name="maxcheckattempts">';
        print '</p>';
        print '</div>';

        ###:TAB3
        print '<div id="fragment-3">';
        # Max check attempts
        print '<p>';
        print '<label for="ersi">Retain status info</label>';
        $checked="";
        if( $retainstatusinfo == "1" ) $checked="checked";
        print '<input class="field" type="checkbox" id="ersi"';
        print ' name="retainstatusinfo" '.$checked.' />';
        print '</p>';
        print '<p>';
        print '<label for="ernsi">Retain nonstatus info</label>';
        $checked="";
        if( $retainnonstatusinfo == "1" ) $checked="checked";
        print '<input class="field" type="checkbox" id="ernsi"';
        print ' name="retainnonstatusinfo" '.$checked.' />';
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
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

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
        $query_str["folder"] = FOLDER;
        if( isset( $query_str["disable"] ) ) {
            if( $query_str["disable"] == "2" ) $query_str["disable"] = "2";
            elseif( $query_str["disable"] == "1" ) $query_str["disable"] = "1";
            else $query_str["disable"] = "0";
        }
        if( isset( $query_str["retainstatusinfo"] ) )
            $query_str["retainstatusinfo"] = "1";
        else
            $query_str["retainstatusinfo"] = "0";
        if( isset( $query_str["retainnonstatusinfo"] ) )
            $query_str["retainnonstatusinfo"] = "1";
        else
            $query_str["retainnonstatusinfo"] = "0";
        # Handle deleting fields
        if( empty( $query_str["contact"] ) )
            $query_str["contact"] = "-";
        if( empty( $query_str["contactgroups"] ) )
            $query_str["contactgroups"] = "-";
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
        # Hostname
        print '<p>';
        print '<label for="hostname">Host name *</label>';
        print '<input class="field" type="text" id="hostname" name="name" required="required" />';
        print '</p>';
        # Alias
        print '<p>';
        print '<label for="alias">Alias *</label>';
        print '<input class="field" type="text" id="alias" name="alias" required="required" />';
        print '</p>';
        # IP Address
        print '<p>';
        print '<label for="ipaddress">IP Address *</label>';
        print '<input class="field" type="text" id="ipaddress" name="ipaddress" required="required" />';
        print '</p>';
        # Host Template
        $hts = get_and_sort_hosttemplates( );
        print '<p>';
        print '<label for="hosttemplate">Host Template</label>';
        print '<select class="field" id="hosttemplate" name="template" required="required">';
        foreach( $hts as $item ) {
            print '<option value="'.$item["name"].'">'.$item["name"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Host Group
        $hgs = get_and_sort_hostgroups( );
        print '<p>';
        print '<label for="hostgroup">Hostgroup</label>';
        print '<select class="field" id="hostgroup" name="hostgroup" required="required">';
        foreach( $hgs as $item ) {
            print '<option value="'.$item["name"].'">'.$item["alias"].'</option>';
        }
        print '</select>';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="contact">Contacts</label>';
        print '<input class="field" type="text" id="contact" name="contact">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="contactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroup" name="contactgroups">';
        print '</p>';
        # Service Set
        $hgs = get_and_sort_servicesets_unique( );
        print '<p>';
        print '<label for="serviceset">Service Set</label>';
        print '<select class="field" id="serviceset" name="servicesets">';
        foreach( $hgs as $item ) {
            print '<option value="'.$item.'">'.$item.'</option>';
        }
        print '</select>';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="activechecks">Active Check</label>';
        print '<input class="field" type="checkbox" id="activechecks"';
        print ' name="activechecks" checked />';
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

        $request = new RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.urlencode($fromhost).'"}', 'GET');
        set_request_options( $request );
        $request->execute();
        $hlist = json_decode( $request->getResponseBody(), true );

        foreach( $hlist[0] as $item ) extract( $item );

        $newhost["folder"] = FOLDER;
        $newhost["name"] = $tohost;
        $newhost["alias"] = $alias;
        $newhost["ipaddress"] =  "change the ip address";
        $newhost["template"] = $template;
        $newhost["hostgroup"] = $hostgroup;
        $newhost["contact"] = $contact;
        $newhost["contactgroups"] = $contactgroups;
        $newhost["activechecks"] = $activechecks;
        $json = json_encode( $newhost );
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
        $retval["message"] = $slist;
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
            $retval["message"] = "Host was added but: ".$slist;
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

        exit( 0 );
    }

    # ------------------------------------------------------------------------
    function clone_svc_using_REST( ) {
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
        
        # Does the copyto host exist?
        $request = new RestRequest(
        RESTURL.'/show/hosts?json={"folder":"'.FOLDER.'",'.
        '"column":"1","filter":"'.$query_str["copyto"].'"}', 'GET');
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

        $fromhost = $query_str["name"];
        $fromsvc = $query_str["svcdesc"];

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
        print '</fieldset>';
        print '</form>';
        print '<div class="flash notice" style="display:none"></div>';
        print '<div class="flash error" style="display:none"></div>';
        print '<script>'.
              '$(".ui-button:contains(Close)").focus()'.
              '</script>';

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
            print '<option value="'.$item["name"].'">'.$item["name"]
              .'</option>';
        }
        print '</select>';
        print '</p>';
        # Command
        print '<p>';
        print '<label for="command">Command *</label>';
        print '<input class="field" type="text" id="command" name="command"';
        print ' required="required" />';
        print '</p>';
        # Service Description
        print '<p>';
        print '<label for="svcdesc">Description *</label>';
        print '<input class="field" type="text" id="svcdesc" name="svcdesc"';
        print ' required="required" />';
        print '</p>';
        # Service Groups
        print '<p>';
        print '<label for="svcgroup">Service Groups</label>';
        print '<input class="field" type="text" id="svcgroup"';
        print ' name="svcgroup">';
        print '</p>';
        # Contact
        print '<p>';
        print '<label for="contacts">Contacts</label>';
        print '<input class="field" type="text" id="contacts"';
        print ' name="contacts">';
        print '</p>';
        # Contact Group
        print '<p>';
        print '<label for="contactgroup">Contact Groups</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' name="contactgroups">';
        print '</p>';
        # Custom Variables
        print '<p>';
        print '<label for="customvars">Custom Variables</label>';
        print '<input class="field" type="text" id="customvars"';
        print ' name="customvars">';
        print '</p>';
        # Freshness Threshold
        print '<p>';
        print '<label for="freshnessthresh">Freshness Threshold</label>';
        print '<input class="field" type="text" id="contactgroup"';
        print ' name="freshnessthresh">';
        print '</p>';
        # Active Checks
        print '<p>';
        print '<label for="sactivechecks">Active Check</label>';
        print '<input class="field" type="checkbox" id="sactivechecks"';
        print ' name="activechecks" checked />';
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
        print '    $("#applyconfigtextarea").html(""+message).show();';
        print '  } else {';
        print '    $(".flash.notice").hide();';
        print '    $(".flash.error").html("Fail").show();';
        print '    $("#applyconfigtextarea").html(""+message).show();';
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
        $ini_array = parse_ini_file( 
            "/etc/nagrestconf/nagrestconf.ini" );
        define( "FOLDER", $ini_array["folder"][0] );
        define( "RESTUSER", $ini_array["restuser"] );
        define( "RESTPASS", $ini_array["restpass"] );
        define( "RESTURL", $ini_array["resturl"] );
        if( !empty($ini_array["sslkey"]) )
            define( "SSLKEY", $ini_array["sslkey"] );
        if( !empty($ini_array["sslcert"]) )
            define( "SSLCERT", $ini_array["sslcert"] );
    }

    # ------------------------------------------------------------------------
    function main( ) {
    # ------------------------------------------------------------------------
        global $g_tab;

        date_default_timezone_set('UTC');
        
        read_config_file( );

        parse_str( $_SERVER['QUERY_STRING'], $query_str );

        $g_tab = 2; #<-- Default to 2, the Hosts tab. Don't change this.

        if( isset( $query_str['tab'] )) {
            $g_tab = (int) $query_str['tab'];
        }

        if( ! empty($query_str['name']) ) 
		$query_str['name'] = urlencode( $query_str['name'] );

        switch( $g_tab ) {
            # ---------------------------------------------------------------
            case 1: # Service Sets Tab
            # ---------------------------------------------------------------
                #session_start( );
                servicesets_page_actions( $query_str );

                show_html_header();

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

                #session_start( );
                show_html_header();

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

                #session_start( );
                show_html_header();

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

                #session_start( );
                show_html_header();

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

                #session_start( );
                show_html_header();

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

                #session_start( );
                show_html_header();

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

                #session_start( );
                show_html_header();

                show_commands_page( );

                # Output divs to contain dialog boxes
                show_new_command_dlg_div( );
                show_delete_command_dlg_div( );
                show_edit_command_dlg_div( );
                break;
            # ---------------------------------------------------------------
            default:
            # ---------------------------------------------------------------
        }

        show_applyconfiguration_dlg_div( );

        print "\n</BODY>";
        print "\n</HTML>";
    }

    main( );

# vim: ts=4:sw=4:et:smartindent:tw=78
?>
