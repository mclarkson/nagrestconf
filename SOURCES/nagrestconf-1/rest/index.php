<?php
# Copyright(C) 2010 Mark Clarkson <mark.clarkson@smorg.co.uk>
#
#    This software is provided under the terms of the GNU
#    General Public License (GPL), as published at: 
#    http://www.gnu.org/licenses/gpl.html .
#
#
# File:     index.php
# Author:   Mark Clarkson
# Date:     11 Sep 2010
# Version:  0.10
# Modified:
#           201X-0X-XX Mark Clarkson
#           * XXX
#
# Purpose:  This a utility to help with creation of Nagios configuration
#           files.
#
# Notes:
#        TODO !!! Secure where name=value items are sent !!!
#
#        ERROR codes created with:
#
# awk 'BEGIN { a=1000; } /ERROR/ { gsub( "ERROR [0-9]+", "ERROR "a );
#     print $0; a=a+1; } !/ERROR/ { print $0; }' \
#     /var/www/html/rest/index.php > new
#

/*
 * Client Access
 * -------------
 * POST with curl (-i shows headers):
 *   curl --noproxy 127.0.0.1 -i -X POST \
 *     -d 'json={"filter":"bob"}' \
 *     http://127.0.0.1:9091/rest/adaf
 *
 * GET with curl:
 *   curl -i -X GET 'http://192.168.2.99/rest/a/b?json=\{"filter":"bob"\}'
 * 
 * Security
 * --------
 * On the server add /etc/httpd/conf.d/rest.conf:

<Directory /var/www/html/rest/>
  AllowOverride All
  AuthName "REST Access"
  AuthType Basic
  AuthUserFile /etc/nagios/htpasswd.users
  Require valid-user
</Directory>

 * Then insert '-n' to curl command and use a ~/.netrc with mode 0600:
 * Example ~/.netrc:

machine 192.168.2.99 login nagiosadmin password Password

 * URL Rewriting
 * -------------
 * Add a /var/www/html/rest/.htaccess file:

Options +FollowSymLinks
RewriteEngine on
RewriteRule ^.*$ index.php

 */

define( 'NAGCTL_CMD', "/usr/bin/nagctl" );

error_reporting(E_ALL ^ E_NOTICE);

# ---------------------------------------------------------------------------
class RestServer
# ---------------------------------------------------------------------------
{
    private $request_vars;
    private $data;
    private $jadata;
    private $http_accept;
    private $method;
    private $cmd;
    private $subcmd;

    # ------------------------------------------------------------------------
    public function __construct()
    # ------------------------------------------------------------------------
    {
        $this->request_vars     = array();
        $this->data             = '';
        $this->http_accept      =
            (strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
        $this->method           = 'get';

        $request=stristr($_SERVER["REQUEST_URI"],
            '?'.$_SERVER["QUERY_STRING"],true);
        if( ! $request ) { $request=$_SERVER["REQUEST_URI"]; }
        $parts = explode('/', $request);
        $numparts = count($parts);
        /*
         * Accepts URL path of the form:
         *   /rest/show/hostgroups?filter=all
         *          |      |         |
         *         cmd   subcmd    cmdargs
         */
        if( $numparts == 4 ) {
            $this->cmd = $parts[$numparts-2];
            $this->subcmd = $parts[$numparts-1];
        } else {
            $this->sendResponse( 404 );
        }

        $this->processRequest();
    }

    # ------------------------------------------------------------------------
    public function setSubcmd( $subcmd )
    # ------------------------------------------------------------------------
    {
        $this->subcmd = $subcmd;
    }

    # ------------------------------------------------------------------------
    public function setCmd( $cmd )
    # ------------------------------------------------------------------------
    {
        $this->cmd = $cmd;
    }

    # ------------------------------------------------------------------------
    public function setData($data)
    # ------------------------------------------------------------------------
    {
        $this->data = $data;
    }

    # ------------------------------------------------------------------------
    public function setMethod($method)
    # ------------------------------------------------------------------------
    {
        $this->method = $method;
    }

    # ------------------------------------------------------------------------
    public function setRequestVars($request_vars)
    # ------------------------------------------------------------------------
    {
        $this->request_vars = $request_vars;
    }

    # ------------------------------------------------------------------------
    public function getCmd( )
    # ------------------------------------------------------------------------
    {
        return $this->cmd;
    }

    # ------------------------------------------------------------------------
    public function getSubcmd( )
    # ------------------------------------------------------------------------
    {
        return $this->subcmd;
    }

    # ------------------------------------------------------------------------
    public function getJAData()
    # ------------------------------------------------------------------------
    {
        return $this->jadata;
    }

    # ------------------------------------------------------------------------
    public function getData()
    # ------------------------------------------------------------------------
    {
        return $this->data;
    }

    # ------------------------------------------------------------------------
    public function getMethod()
    # ------------------------------------------------------------------------
    {
        return $this->method;
    }

    # ------------------------------------------------------------------------
    public function getHttpAccept()
    # ------------------------------------------------------------------------
    {
        return $this->http_accept;
    }

    # ------------------------------------------------------------------------
    public function getRequestVars()
    # ------------------------------------------------------------------------
    {
        return $this->request_vars;
    }

    # ------------------------------------------------------------------------
    public function processRequest()
    # ------------------------------------------------------------------------
    {
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $data           = array();

        switch ($request_method)
        {
            case 'head':
            case 'get':
                $data = $_GET;
                break;
            case 'post':
                $data = $_POST;
                break;
            case 'put':
                parse_str(file_get_contents('php://input'), $put_vars);
                $data = $put_vars;
                break;
        }

        $this->setMethod($request_method);

        $this->setRequestVars($data);

        if(isset($data['json']))
        {
            $this->setData(json_decode($data['json']));
            $this->jadata = json_decode($data['json'],True);

            # TODO: Eventually this should go...
            foreach( $this->jadata as &$item ) {
                $item = strtr( $item, array(
                    "," => "`",
                    "\\" => "\\\\",
                    "%2c" => "%60",
                    "%2C" => "%60",
                    # Undo json_decode escape char decoding
                    "\n" => "\\n",
                    "\r" => "\\r",
                    "\b" => "\\b",
                    "\f" => "\\f",
                    "\t" => "\\t",
                 ) );
               $item = trim( $item );
            }
        }
    }

    # ------------------------------------------------------------------------
    public function sendResponse( $status = 200, $body = '', 
                                  $content_type = 'text/html')
    # ------------------------------------------------------------------------
    {
        $status_header = 'HTTP/1.1 ' . $status . ' ' .
                         $this->getStatusCodeMessage($status);
        header($status_header);
        header('Content-type: ' . $content_type);

        if($body != '')
        {
            echo $body . "\n";
            exit;
        }
        else
        {
            $message = '';

            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 404:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] .
                               ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing ' .
                               'your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            echo $message;

            exit;
        }
    }


    # ------------------------------------------------------------------------
    public function getStatusCodeMessage($status)
    # ------------------------------------------------------------------------
    {
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}

# ---------------------------------------------------------------------------
class WriteCmd
# ---------------------------------------------------------------------------
# Create a command that modifies the csv files.
{
    private $newcmdline;          /* the command, or an error message */
    private $retcode;             /* the http return code to send */
    private $cmd;                 /* The user command */
    private $subcmd;              /* The user sub-command */
    private $jsondata;            /* The user json data */
    private $jsonadata;           /* The user json data */
    private $subcmdtype;
    // Add/Modify/Delete
    const HOSTTEMPLATES = 1;
    const SERVICETEMPLATES = 2;
    const HOSTS = 3;
    const SERVICES = 4;
    const CONTACTS = 5;
    const CONTACTGROUPS = 6;
    const HOSTGROUPS = 7;
    const SERVICESETS = 8;
    const SERVICEGROUPS = 9;
    const TIMEPERIODS = 10;
    const COMMANDS = 11;
    const SERVICEDEPS = 12;
    const HOSTDEPS = 13;
    const SERVICEESC = 14;
    const HOSTESC = 15;
    const SERVICEEXTINFO = 16;
    const HOSTEXTINFO = 17;
    // Restart
    const RESTART_NAGIOS = 1;
    // Apply
    const APPLY_NAGIOSCONFIG = 1;
    const APPLY_NAGIOSLASTGOODCONFIG = 2;
    // Pipecmd
    const PIPECMD_ENABLEHOSTSVCCHECKS = 1;
    const PIPECMD_DISABLEHOSTSVCCHECKS = 2;
    const PIPECMD_DISABLESVCCHECK = 3;
    const PIPECMD_ENABLESVCCHECK = 4;
    const PIPECMD_SCHEDULEHOSTDOWNTIME = 5;
    const PIPECMD_DELHOSTDOWNTIME = 6;
    const PIPECMD_SCHEDULEHOSTSVCDOWNTIME = 7;
    const PIPECMD_DELHOSTSVCDOWNTIME = 8;
    const PIPECMD_SCHEDULESVCDOWNTIME = 9;
    const PIPECMD_DELSVCDOWNTIME = 10;

    private $cmdtype;
    const CMD_ADD = 1;
    const CMD_DELETE = 2;
    const CMD_MODIFY = 3;
    const CMD_RESTART = 4;
    const CMD_APPLY = 5;
    const CMD_PIPECMD = 6;

    # ------------------------------------------------------------------------
    public function __construct( $cmd, $subcmd, $jsondata, $jsonarrdata )
    # ------------------------------------------------------------------------
    {
        $this->retcode = 200;

        $this->cmd = $cmd;
        if( ! $this->setCmdType() ) {
            $this->newcmdline =
                "ERROR 1003: Invalid command '" . $cmd . "'.";
            $this->retcode = 405;
            return;
        }

        $this->jsondata = $jsondata;
        $this->jsonadata = $jsonarrdata;

        $this->subcmd = $subcmd;
        switch( $this->cmdtype )
        {
            case self::CMD_DELETE:
                if( ! $this->setSubDeleteCmdType() ) {
                    $this->newcmdline =
                        "ERROR 1004: Invalid type '" . $subcmd . "'.";
                    $this->retcode = 405;
                    return;
                }
                if( ! $this->createDeleteCommand() ) {
                    $this->retcode = 405;
                    return;
                }
                break;
            case self::CMD_MODIFY:
                if( ! $this->setSubModifyCmdType() ) {
                    $this->newcmdline =
                        "ERROR 1005: Invalid type '" . $subcmd . "'.";
                    $this->retcode = 405;
                    return;
                }
                if( ! $this->createModifyCommand() ) {
                    $this->retcode = 405;
                    return;
                }
                break;
            case self::CMD_ADD:
                if( ! $this->setSubAddCmdType() ) {
                    $this->newcmdline =
                        "ERROR 1006: Invalid type '" . $subcmd . "'.";
                    $this->retcode = 405;
                    return;
                }
                if( ! $this->createAddCommand() ) {
                    $this->retcode = 405;
                    return;
                }
                break;
            case self::CMD_RESTART:
                if( ! $this->setSubRestartCmdType() ) {
                    $this->newcmdline =
                        "ERROR 1007: Invalid type '" . $subcmd . "'.";
                    $this->retcode = 405;
                    return;
                }
                if( ! $this->createRestartCommand() ) {
                    $this->retcode = 405;
                    return;
                }
                break;
            case self::CMD_APPLY:
                if( ! $this->setSubApplyCmdType() ) {
                    $this->newcmdline =
                        "ERROR 1008: Invalid type '" . $subcmd . "'.";
                    $this->retcode = 405;
                    return;
                }
                if( ! $this->createApplyCommand() ) {
                    $this->retcode = 405;
                    return;
                }
                break;
            case self::CMD_PIPECMD:
                if( ! $this->setSubPipecmdCmdType() ) {
                    $this->newcmdline =
                        "ERROR 1009: Invalid type '" . $subcmd . "'.";
                    $this->retcode = 405;
                    return;
                }
                if( ! $this->createPipecmdCommand() ) {
                    $this->retcode = 405;
                    return;
                }
                break;
        }
    }

    # ------------------------------------------------------------------------
    public function getCommand()
    # ------------------------------------------------------------------------
    {
        return $this->newcmdline ;
    }

    # ------------------------------------------------------------------------
    public function getReturnCode()
    # ------------------------------------------------------------------------
    {
        return $this->retcode ;
    }

    # ------------------------------------------------------------------------
    private function createApplyCommand()
    # ------------------------------------------------------------------------
    {
        // apply nagios config
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1010: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->subcmdtype == self::APPLY_NAGIOSCONFIG ) {
            $this->newcmdline = NAGCTL_CMD .  " " .
                $this->jsondata->{'folder'} .
                " apply nagiosconfig";
            if( isset($this->jsondata->{'verbose'}) &&
                $this->jsondata->{'verbose'} == "true" ) {
                    $this->newcmdline .= ' " verbose=1;"';
            }
        }
        if( $this->subcmdtype == self::APPLY_NAGIOSLASTGOODCONFIG ) {
            $this->newcmdline = NAGCTL_CMD .  " " .
                $this->jsondata->{'folder'} .
                " apply nagioslastgoodconfig";
        }

        return True;
    }

    # ------------------------------------------------------------------------
    private function createRestartCommand()
    # ------------------------------------------------------------------------
    {
        // restart nagios
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1011: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
        $this->newcmdline = NAGCTL_CMD .  " " . $this->jsondata->{'folder'} .
            " restart nagios";

        return True;
    }

    # ------------------------------------------------------------------------
    private function createDeleteCommand()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        $this->newcmdline = NAGCTL_CMD;

        switch( $this->subcmdtype ) {
            case self::HOSTTEMPLATES:
                $retval = $this->createHosttemplatesDeleteCmd();
                break;
            case self::HOSTS:
                $retval = $this->createHostsDeleteCmd();
                break;
            case self::SERVICETEMPLATES:
                $retval = $this->createServicetemplatesDeleteCmd();
                break;
            case self::SERVICES:
                $retval = $this->createServicesDeleteCmd();
                break;
            case self::HOSTGROUPS:
                $retval = $this->createHostgroupsDeleteCmd();
                break;
            case self::SERVICEGROUPS:
                $retval = $this->createServicegroupsDeleteCmd();
                break;
            case self::CONTACTS:
                $retval = $this->createContactsDeleteCmd();
                break;
            case self::CONTACTGROUPS:
                $retval = $this->createContactgroupsDeleteCmd();
                break;
            case self::SERVICESETS:
                $retval = $this->createServicesetsDeleteCmd();
                break;
            case self::TIMEPERIODS:
                $retval = $this->createTimeperiodsDeleteCmd();
                break;
            case self::COMMANDS:
                $retval = $this->createCommandsDeleteCmd();
                break;
            case self::SERVICEDEPS:
                $retval = $this->createServicedepsDeleteCmd();
                break;
            case self::HOSTDEPS:
                $retval = $this->createHostdepsDeleteCmd();
                break;
            case self::SERVICEESC:
                $retval = $this->createServiceescDeleteCmd();
                break;
            case self::HOSTESC:
                $retval = $this->createHostescDeleteCmd();
                break;
            case self::SERVICEEXTINFO:
                $retval = $this->createServiceextinfoDeleteCmd();
                break;
            case self::HOSTEXTINFO:
                $retval = $this->createHostextinfoDeleteCmd();
                break;
            default:
                $this->newcmdline =
                    "ERROR 1012: Unknown error.";
                $retval = False;
        }

        return $retval ;
    }

    # ------------------------------------------------------------------------
    private function createModifyCommand()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        $this->newcmdline = NAGCTL_CMD;

        switch( $this->subcmdtype ) {
            case self::HOSTTEMPLATES:
                $retval = $this->createHosttemplatesModifyCmd();
                break;
            case self::HOSTS:
                $retval = $this->createHostsModifyCmd();
                break;
            case self::SERVICETEMPLATES:
                $retval = $this->createServicetemplatesModifyCmd();
                break;
            case self::SERVICES:
                $retval = $this->createServicesModifyCmd();
                break;
            case self::HOSTGROUPS:
                $retval = $this->createHostgroupsModifyCmd();
                break;
            case self::SERVICEGROUPS:
                $retval = $this->createServicegroupsModifyCmd();
                break;
            case self::CONTACTS:
                $retval = $this->createContactsModifyCmd();
                break;
            case self::CONTACTGROUPS:
                $retval = $this->createContactgroupsModifyCmd();
                break;
            case self::SERVICESETS:
                $retval = $this->createServicesetsModifyCmd();
                break;
            case self::TIMEPERIODS:
                $retval = $this->createTimeperiodsModifyCmd();
                break;
            case self::COMMANDS:
                $retval = $this->createCommandsModifyCmd();
                break;
            case self::SERVICEDEPS:
                $retval = $this->createServicedepsModifyCmd();
                break;
            case self::HOSTDEPS:
                $retval = $this->createHostdepsModifyCmd();
                break;
            case self::SERVICEESC:
                $retval = $this->createServiceescModifyCmd();
                break;
            case self::HOSTESC:
                $retval = $this->createHostescModifyCmd();
                break;
            case self::SERVICEEXTINFO:
                $retval = $this->createServiceextinfoModifyCmd();
                break;
            case self::HOSTEXTINFO:
                $retval = $this->createHostextinfoModifyCmd();
                break;
            default:
                $this->newcmdline =
                    "ERROR 1013: Unknown error.";
                $retval = False;
        }

        return $retval ;
    }

    # ------------------------------------------------------------------------
    private function createPipecmdCommand()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        $this->newcmdline = NAGCTL_CMD;

        switch( $this->subcmdtype ) {
            case self::PIPECMD_ENABLEHOSTSVCCHECKS:
                $retval = $this->createEnablehostsvcchecksPipecmdCmd();
                break;
            case self::PIPECMD_DISABLEHOSTSVCCHECKS:
                $retval = $this->createDisablehostsvcchecksPipecmdCmd();
                break;
            case self::PIPECMD_ENABLESVCCHECK:
                $retval = $this->createEnablesvccheckPipecmdCmd();
                break;
            case self::PIPECMD_SCHEDULEHOSTDOWNTIME:
                $retval = $this->createScheduleHostDowntimePipecmdCmd();
                break;
            case self::PIPECMD_DELHOSTDOWNTIME:
                $retval = $this->createDelHostDowntimePipecmdCmd();
                break;
            case self::PIPECMD_SCHEDULEHOSTSVCDOWNTIME:
                $retval = $this->createScheduleHostSvcDowntimePipecmdCmd();
                break;
            case self::PIPECMD_DELHOSTSVCDOWNTIME:
                $retval = $this->createDelHostSvcDowntimePipecmdCmd();
                break;
            case self::PIPECMD_SCHEDULESVCDOWNTIME:
                $retval = $this->createScheduleSvcDowntimePipecmdCmd();
                break;
            case self::PIPECMD_DELSVCDOWNTIME:
                $retval = $this->createDelSvcDowntimePipecmdCmd();
                break;
            default:
                $this->newcmdline =
                    "ERROR 1014: Unknown error.";
                $retval = False;
        }

        return $retval ;
    }

    # ------------------------------------------------------------------------
    private function createAddCommand()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        $this->newcmdline = NAGCTL_CMD;

        switch( $this->subcmdtype ) {
            case self::HOSTTEMPLATES:
                $retval = $this->createHosttemplatesAddCmd();
                break;
            case self::HOSTS:
                $retval = $this->createHostsAddCmd();
                break;
            case self::SERVICETEMPLATES:
                $retval = $this->createServicetemplatesAddCmd();
                break;
            case self::SERVICES:
                $retval = $this->createServicesAddCmd();
                break;
            case self::HOSTGROUPS:
                $retval = $this->createHostgroupsAddCmd();
                break;
            case self::SERVICEGROUPS:
                $retval = $this->createServicegroupsAddCmd();
                break;
            case self::CONTACTS:
                $retval = $this->createContactsAddCmd();
                break;
            case self::CONTACTGROUPS:
                $retval = $this->createContactgroupsAddCmd();
                break;
            case self::SERVICESETS:
                $retval = $this->createServicesetsAddCmd();
                break;
            case self::TIMEPERIODS:
                $retval = $this->createTimeperiodsAddCmd();
                break;
            case self::COMMANDS:
                $retval = $this->createCommandsAddCmd();
                break;
            case self::SERVICEDEPS:
                $retval = $this->createServicedepsAddCmd();
                break;
            case self::HOSTDEPS:
                $retval = $this->createHostdepsAddCmd();
                break;
            case self::SERVICEESC:
                $retval = $this->createServiceescAddCmd();
                break;
            case self::HOSTESC:
                $retval = $this->createHostescAddCmd();
                break;
            case self::SERVICEEXTINFO:
                $retval = $this->createServiceextinfoAddCmd();
                break;
            case self::HOSTEXTINFO:
                $retval = $this->createHostextinfoAddCmd();
                break;
            default:
                $this->newcmdline =
                    "ERROR 1015: Unknown error.";
                $retval = False;
        }

        return $retval ;
    }

    # ------------------------------------------------------------------------
    private function setCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->cmd ) {
            case 'add':
                $this->cmdtype = self::CMD_ADD;
                break;
            case 'modify':
                $this->cmdtype = self::CMD_MODIFY;
                break;
            case 'delete':
                $this->cmdtype = self::CMD_DELETE;
                break;
            case 'restart':
                $this->cmdtype = self::CMD_RESTART;
                break;
            case 'apply':
                $this->cmdtype = self::CMD_APPLY;
                break;
            case 'pipecmd':
                $this->cmdtype = self::CMD_PIPECMD;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function setSubDeleteCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'hosttemplates':
                $this->subcmdtype = self::HOSTTEMPLATES;
                break;
            case 'servicetemplates':
                $this->subcmdtype = self::SERVICETEMPLATES;
                break;
            case 'hosts':
                $this->subcmdtype = self::HOSTS;
                break;
            case 'services':
                $this->subcmdtype = self::SERVICES;
                break;
            case 'hostgroups':
                $this->subcmdtype = self::HOSTGROUPS;
                break;
            case 'servicegroups':
                $this->subcmdtype = self::SERVICEGROUPS;
                break;
            case 'contacts':
                $this->subcmdtype = self::CONTACTS;
                break;
            case 'contactgroups':
                $this->subcmdtype = self::CONTACTGROUPS;
                break;
            case 'servicesets':
                $this->subcmdtype = self::SERVICESETS;
                break;
            case 'timeperiods':
                $this->subcmdtype = self::TIMEPERIODS;
                break;
            case 'commands':
                $this->subcmdtype = self::COMMANDS;
                break;
            case 'servicedeps':
                $this->subcmdtype = self::SERVICEDEPS;
                break;
            case 'hostdeps':
                $this->subcmdtype = self::HOSTDEPS;
                break;
            case 'serviceesc':
                $this->subcmdtype = self::SERVICEESC;
                break;
            case 'hostesc':
                $this->subcmdtype = self::HOSTESC;
                break;
            case 'serviceextinfo':
                $this->subcmdtype = self::SERVICEEXTINFO;
                break;
            case 'hostextinfo':
                $this->subcmdtype = self::HOSTEXTINFO;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function setSubModifyCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'hosttemplates':
                $this->subcmdtype = self::HOSTTEMPLATES;
                break;
            case 'servicetemplates':
                $this->subcmdtype = self::SERVICETEMPLATES;
                break;
            case 'hosts':
                $this->subcmdtype = self::HOSTS;
                break;
            case 'services':
                $this->subcmdtype = self::SERVICES;
                break;
            case 'hostgroups':
                $this->subcmdtype = self::HOSTGROUPS;
                break;
            case 'servicegroups':
                $this->subcmdtype = self::SERVICEGROUPS;
                break;
            case 'contacts':
                $this->subcmdtype = self::CONTACTS;
                break;
            case 'contactgroups':
                $this->subcmdtype = self::CONTACTGROUPS;
                break;
            case 'servicesets':
                $this->subcmdtype = self::SERVICESETS;
                break;
            case 'timeperiods':
                $this->subcmdtype = self::TIMEPERIODS;
                break;
            case 'commands':
                $this->subcmdtype = self::COMMANDS;
                break;
            case 'servicedeps':
                $this->subcmdtype = self::SERVICEDEPS;
                break;
            case 'hostdeps':
                $this->subcmdtype = self::HOSTDEPS;
                break;
            case 'serviceesc':
                $this->subcmdtype = self::SERVICEESC;
                break;
            case 'hostesc':
                $this->subcmdtype = self::HOSTESC;
                break;
            case 'serviceextinfo':
                $this->subcmdtype = self::SERVICEEXTINFO;
                break;
            case 'hostextinfo':
                $this->subcmdtype = self::HOSTEXTINFO;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function setSubAddCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'hosttemplates':
                $this->subcmdtype = self::HOSTTEMPLATES;
                break;
            case 'servicetemplates':
                $this->subcmdtype = self::SERVICETEMPLATES;
                break;
            case 'hosts':
                $this->subcmdtype = self::HOSTS;
                break;
            case 'services':
                $this->subcmdtype = self::SERVICES;
                break;
            case 'hostgroups':
                $this->subcmdtype = self::HOSTGROUPS;
                break;
            case 'servicegroups':
                $this->subcmdtype = self::SERVICEGROUPS;
                break;
            case 'contacts':
                $this->subcmdtype = self::CONTACTS;
                break;
            case 'contactgroups':
                $this->subcmdtype = self::CONTACTGROUPS;
                break;
            case 'servicesets':
                $this->subcmdtype = self::SERVICESETS;
                break;
            case 'timeperiods':
                $this->subcmdtype = self::TIMEPERIODS;
                break;
            case 'commands':
                $this->subcmdtype = self::COMMANDS;
                break;
            case 'servicedeps':
                $this->subcmdtype = self::SERVICEDEPS;
                break;
            case 'hostdeps':
                $this->subcmdtype = self::HOSTDEPS;
                break;
            case 'serviceesc':
                $this->subcmdtype = self::SERVICEESC;
                break;
            case 'hostesc':
                $this->subcmdtype = self::HOSTESC;
                break;
            case 'serviceextinfo':
                $this->subcmdtype = self::SERVICEEXTINFO;
                break;
            case 'hostextinfo':
                $this->subcmdtype = self::HOSTEXTINFO;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function setSubPipecmdCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'enablehostsvcchecks':
                $this->subcmdtype = self::PIPECMD_ENABLEHOSTSVCCHECKS;
                break;
            case 'disablehostsvcchecks':
                $this->subcmdtype = self::PIPECMD_DISABLEHOSTSVCCHECKS;
                break;
            case 'enablesvccheck':
                $this->subcmdtype = self::PIPECMD_ENABLESVCCHECK;
                break;
            case 'disablesvccheck':
                $this->subcmdtype = self::PIPECMD_DISABLESVCCHECK;
                break;
            case 'schedhstdowntime':
                $this->subcmdtype = self::PIPECMD_SCHEDULEHOSTDOWNTIME;
                break;
            case 'delhstdowntime':
                $this->subcmdtype = self::PIPECMD_DELHOSTDOWNTIME;
                break;
            case 'schedhstsvcdowntime':
                $this->subcmdtype = self::PIPECMD_SCHEDULEHOSTSVCDOWNTIME;
                break;
            case 'delhstsvcdowntime':
                $this->subcmdtype = self::PIPECMD_DELHOSTSVCDOWNTIME;
                break;
            case 'schedsvcdowntime':
                $this->subcmdtype = self::PIPECMD_SCHEDULESVCDOWNTIME;
                break;
            case 'delsvcdowntime':
                $this->subcmdtype = self::PIPECMD_DELSVCDOWNTIME;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function setSubApplyCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'nagiosconfig':
                $this->subcmdtype = self::APPLY_NAGIOSCONFIG;
                break;
            case 'nagioslastgoodconfig':
                $this->subcmdtype = self::APPLY_NAGIOSLASTGOODCONFIG;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function setSubRestartCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'nagios':
                $this->subcmdtype = self::RESTART_NAGIOS;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function genericPipecmdPrefix()
    # ------------------------------------------------------------------------
    {
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1016: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
    
        $this->newcmdline .= " " . $this->jsondata->{'folder'} . " pipecmd";

        return True;
    }
    # ------------------------------------------------------------------------
    private function createEnablehostsvcchecksPipecmdCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericPipecmdPrefix() == False ) return False;

        $this->newcmdline .= " enablehostsvcchecks";

        if( $this->jsondata->{'name'} ) {
                $this->newcmdline .= ' " name=' . $this->jsondata->{'name'}
                    . ';';
        } else {
            $this->newcmdline = "ERROR 1017: 'name' is undefined";
            $this->retcode = 405;
            return False;
        }

        $this->newcmdline .= '"';

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createDisablehostsvcchecksPipecmdCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericPipecmdPrefix() == False ) return False;

        $this->newcmdline .= " disablehostsvcchecks";

        if( $this->jsondata->{'name'} ) {
                $this->newcmdline .= ' " name=' . $this->jsondata->{'name'}
                    . ';';
        } else {
            $this->newcmdline = "ERROR 1018: 'name' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'comment'} ) {
                $this->newcmdline .= 'comment=\"' .
                    $this->jsondata->{'comment'}
                    . '\";';
        }

        $this->newcmdline .= '"';

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function createEnablesvccheckPipecmdCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericPipecmdPrefix() == False ) return False;

        $this->newcmdline .= " enablesvccheck";

        if( $this->jsondata->{'name'} ) {
                $this->newcmdline .= ' " name=' . $this->jsondata->{'name'}
                    . ';';
        } else {
            $this->newcmdline = "ERROR 1019: 'name' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'svcdesc'} ) {
                $this->newcmdline .= 'svcdesc=\"'
                    . $this->jsondata->{'svcdesc'} . '\";';
        } else {
            $this->newcmdline = "ERROR 1020: 'svcdesc' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'comment'} ) {
                $this->newcmdline .= 'comment=\"' .
                    $this->jsondata->{'comment'}
                    . '\";';
        }

        $this->newcmdline .= '"';

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function createDisablesvccheckPipecmdCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericPipecmdPrefix() == False ) return False;

        $this->newcmdline .= " disablesvccheck";

        if( $this->jsondata->{'name'} ) {
                $this->newcmdline .= ' " name=' . $this->jsondata->{'name'}
                    . ';';
        } else {
            $this->newcmdline = "ERROR 1021: 'name' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'svcdesc'} ) {
                $this->newcmdline .= 'svcdesc='
                    . $this->jsondata->{'svcdesc'} . ';';
        } else {
            $this->newcmdline = "ERROR 1022: 'svcdesc' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'comment'} ) {
                $this->newcmdline .= 'comment=\"' .
                    $this->jsondata->{'comment'}
                    . '\";';
        }

        $this->newcmdline .= '"';

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function createScheduleHostDowntimePipecmdCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericPipecmdPrefix() == False ) return False;

        $this->newcmdline .= " schedhstdowntime";

        if( $this->jsondata->{'name'} ) {
                $this->newcmdline .= ' " name=' . $this->jsondata->{'name'}
                    . ';';
        } else {
            $this->newcmdline = "ERROR 1023: 'name' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'starttime'} ) {
                $this->newcmdline .= 'starttime='
                    . $this->jsondata->{'starttime'} . ';';
        } else {
            $this->newcmdline = "ERROR 1024: 'starttime' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'endtime'} ) {
                $this->newcmdline .= 'endtime='
                    . $this->jsondata->{'endtime'} . ';';
        } else {
            $this->newcmdline = "ERROR 1025: 'endtime' is undefined";
            $this->retcode = 405;
            return False;
        }
        if( $this->jsondata->{'flexible'} ) {
                $this->newcmdline .= 'flexible='
                    . $this->jsondata->{'flexible'} . ';';
        }
        if( $this->jsondata->{'duration'} ) {
                $this->newcmdline .= 'duration='
                    . $this->jsondata->{'duration'} . ';';
        }
        if( $this->jsondata->{'author'} ) {
                $this->newcmdline .= 'author='
                    . $this->jsondata->{'author'} . ';';
        }
        if( $this->jsondata->{'comment'} ) {
                $this->newcmdline .= 'comment=\"' .
                    $this->jsondata->{'comment'}
                    . '\";';
        }

        $this->newcmdline .= '"';

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function createDelHostDowntimePipecmdCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericPipecmdPrefix() == False ) return False;

        $this->newcmdline .= " delhstdowntime";

        if( $this->jsondata->{'name'} ) {
                $this->newcmdline .= ' " name=' . $this->jsondata->{'name'}
                    . ';';
        } else {
            $this->newcmdline = "ERROR 1050: 'name' is undefined";
            $this->retcode = 405;
            return False;
        }

        $this->newcmdline .= '"';

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function genericDeletePrefix()
    # ------------------------------------------------------------------------
    {
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1026: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
    
        $this->newcmdline .= " " . $this->jsondata->{'folder'} . " delete";
        $this->newcmdline .= " " . $this->subcmd . " '";

        return True;
    }
    # ------------------------------------------------------------------------
    private function createServicetemplatesDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $notifopts . ",";
        $this->newcmdline .= $checkinterval . ",";
        $this->newcmdline .= $normchecki . ",";
        $this->newcmdline .= $retryinterval . ",";
        $this->newcmdline .= $notifinterval . ",";
        $this->newcmdline .= $notifperiod;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$activechecks;
        $this->newcmdline .= ",".$customvars;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHosttemplatesDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $normchecki . ",";
        $this->newcmdline .= $checkinterval . ",";
        $this->newcmdline .= $retryinterval . ",";
        $this->newcmdline .= $notifperiod . ",";
        $this->newcmdline .= $notifopts;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkcommand;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$obsessoverhost;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $ipaddress . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= $hostgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $servicesets;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$parents;
        $this->newcmdline .= ",".$command;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverhost;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicesDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= urlencode($command) . ",";
        # Allow mass deletions
        $this->newcmdline .= 
                strtr( urlencode($svcdesc), array( '%2A' => '*',) )
                . ",";
        $this->newcmdline .= $svcgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $freshnessthresh . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $customvars;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostgroupsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$members;
        $this->newcmdline .= ",".$hostgroupmembers;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicegroupsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$members;
        $this->newcmdline .= ",".$servicegroupmembers;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createContactsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $emailaddr . ",";
        $this->newcmdline .= $svcnotifperiod . ",";
        $this->newcmdline .= $svcnotifopts . ",";
        $this->newcmdline .= $svcnotifcmds . ",";
        $this->newcmdline .= $hstnotifperiod . ",";
        $this->newcmdline .= $hstnotifopts . ",";
        $this->newcmdline .= $hstnotifcmds . ",";
        $this->newcmdline .= $cansubmitcmds;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$svcnotifenabled;
        $this->newcmdline .= ",".$hstnotifenabled;
        $this->newcmdline .= ",".$pager;
        $this->newcmdline .= ",".$address1;
        $this->newcmdline .= ",".$address2;
        $this->newcmdline .= ",".$address3;
        $this->newcmdline .= ",".$address4;
        $this->newcmdline .= ",".$address5;
        $this->newcmdline .= ",".$address6;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$contactgroups;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createContactgroupsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $members;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicesetsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= urlencode($command) . ",";
        # Allow mass deletions
        $this->newcmdline .= 
                strtr( urlencode($svcdesc), array( '%2A' => '*',) )
                . ",";
        $this->newcmdline .= $svcgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $freshnessthresh . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $customvars;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createTimeperiodsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $definition . ",";
        $this->newcmdline .= $exclude;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$exception;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createCommandsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= 
                strtr( urlencode($name), array( '%2A' => '*',) );
        $this->newcmdline .= ",".$command;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicedepsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($dephostname);
        $this->newcmdline .= ",".$dephostgroupname;
        $this->newcmdline .= ",".$depsvcdesc;
        $this->newcmdline .= ",".$hostname;
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$inheritsparent;
        $this->newcmdline .= ",".$execfailcriteria;
        $this->newcmdline .= ",".$notiffailcriteria;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostdepsDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($dephostname);
        $this->newcmdline .= ",".$dephostgroupname;
        $this->newcmdline .= ",".$hostname;
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$inheritsparent;
        $this->newcmdline .= ",".$execfailcriteria;
        $this->newcmdline .= ",".$notiffailcriteria;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServiceescDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostescDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServiceextinfoDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostextinfoDeleteCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericDeletePrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function genericModifyPrefix()
    # ------------------------------------------------------------------------
    {
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1027: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
    
        $this->newcmdline .= " " . $this->jsondata->{'folder'} . " modify";
        $this->newcmdline .= " " . $this->subcmd . " '";
        
        return True;
    }
    # ------------------------------------------------------------------------
    private function createServicetemplatesModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $notifopts . ",";
        $this->newcmdline .= $checkinterval . ",";
        $this->newcmdline .= $normchecki . ",";
        $this->newcmdline .= $retryinterval . ",";
        $this->newcmdline .= $notifinterval . ",";
        $this->newcmdline .= $notifperiod;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$activechecks;
        $this->newcmdline .= ",".$customvars;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".urlencode($action_url);
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHosttemplatesModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $normchecki . ",";
        $this->newcmdline .= $checkinterval . ",";
        $this->newcmdline .= $retryinterval . ",";
        $this->newcmdline .= $notifperiod . ",";
        $this->newcmdline .= $notifopts;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkcommand;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$obsessoverhost;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".urlencode($action_url);

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $ipaddress . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= $hostgroup . ",";
        $this->newcmdline .= $contact . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $servicesets;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$parents;
        $this->newcmdline .= ",".$command;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverhost;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicesModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= urlencode($command) . ",";
        $this->newcmdline .= urlencode($svcdesc) . ",";
        $this->newcmdline .= $svcgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $freshnessthresh . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $customvars;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostgroupsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$members;
        $this->newcmdline .= ",".$hostgroupmembers;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicegroupsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$members;
        $this->newcmdline .= ",".$servicegroupmembers;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createContactsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $emailaddr . ",";
        $this->newcmdline .= $svcnotifperiod . ",";
        $this->newcmdline .= $svcnotifopts . ",";
        $this->newcmdline .= $svcnotifcmds . ",";
        $this->newcmdline .= $hstnotifperiod . ",";
        $this->newcmdline .= $hstnotifopts . ",";
        $this->newcmdline .= $hstnotifcmds . ",";
        $this->newcmdline .= $cansubmitcmds;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$svcnotifenabled;
        $this->newcmdline .= ",".$hstnotifenabled;
        $this->newcmdline .= ",".$pager;
        $this->newcmdline .= ",".$address1;
        $this->newcmdline .= ",".$address2;
        $this->newcmdline .= ",".$address3;
        $this->newcmdline .= ",".$address4;
        $this->newcmdline .= ",".$address5;
        $this->newcmdline .= ",".$address6;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$contactgroups;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createContactgroupsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $members;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicesetsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= urlencode($command) . ",";
        $this->newcmdline .= urlencode($svcdesc) . ",";
        $this->newcmdline .= $svcgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $freshnessthresh . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $customvars;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createTimeperiodsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $definition . ",";
        $this->newcmdline .= $exclude;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$exception;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createCommandsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($name);
        $this->newcmdline .= ",".urlencode($command);
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicedepsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($dephostname);
        $this->newcmdline .= ",".$dephostgroupname;
        $this->newcmdline .= ",".$depsvcdesc;
        $this->newcmdline .= ",".$hostname;
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$inheritsparent;
        $this->newcmdline .= ",".$execfailcriteria;
        $this->newcmdline .= ",".$notiffailcriteria;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$disable;


        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostdepsModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($dephostname);
        $this->newcmdline .= ",".$dephostgroupname;
        $this->newcmdline .= ",".$hostname;
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$inheritsparent;
        $this->newcmdline .= ",".$execfailcriteria;
        $this->newcmdline .= ",".$notiffailcriteria;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServiceescModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostescModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServiceextinfoModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostextinfoModifyCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericModifyPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }

    # ------------------------------------------------------------------------
    private function genericAddPrefix()
    # ------------------------------------------------------------------------
    {
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1028: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
    
        $this->newcmdline .= " " . $this->jsondata->{'folder'} . " add";
        $this->newcmdline .= " " . $this->subcmd . " '";

        return True;
    }
    # ------------------------------------------------------------------------
    private function createHosttemplatesAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $normchecki . ",";
        $this->newcmdline .= $checkinterval . ",";
        $this->newcmdline .= $retryinterval . ",";
        $this->newcmdline .= $notifperiod . ",";
        $this->newcmdline .= $notifopts;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkcommand;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$obsessoverhost;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".urlencode($action_url);

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicetemplatesAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $notifopts . ",";
        $this->newcmdline .= $checkinterval . ",";
        $this->newcmdline .= $normchecki . ",";
        $this->newcmdline .= $retryinterval . ",";
        $this->newcmdline .= $notifinterval . ",";
        $this->newcmdline .= $notifperiod;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$activechecks;
        $this->newcmdline .= ",".$customvars;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".urlencode($action_url);
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $ipaddress . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= $hostgroup . ",";
        $this->newcmdline .= $contact . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $servicesets;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$parents;
        $this->newcmdline .= ",".$command;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverhost;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$freshnessthresh;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicesAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= urlencode($command) . ",";
        $this->newcmdline .= urlencode($svcdesc) . ",";
        $this->newcmdline .= $svcgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $freshnessthresh . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $customvars;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostgroupsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$members;
        $this->newcmdline .= ",".$hostgroupmembers;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicegroupsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$members;
        $this->newcmdline .= ",".$servicegroupmembers;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createContactsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $use . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $emailaddr . ",";
        $this->newcmdline .= $svcnotifperiod . ",";
        $this->newcmdline .= $svcnotifopts . ",";
        $this->newcmdline .= $svcnotifcmds . ",";
        $this->newcmdline .= $hstnotifperiod . ",";
        $this->newcmdline .= $hstnotifopts . ",";
        $this->newcmdline .= $hstnotifcmds . ",";
        $this->newcmdline .= $cansubmitcmds;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$svcnotifenabled;
        $this->newcmdline .= ",".$hstnotifenabled;
        $this->newcmdline .= ",".$pager;
        $this->newcmdline .= ",".$address1;
        $this->newcmdline .= ",".$address2;
        $this->newcmdline .= ",".$address3;
        $this->newcmdline .= ",".$address4;
        $this->newcmdline .= ",".$address5;
        $this->newcmdline .= ",".$address6;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$contactgroups;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createContactgroupsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $members;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicesetsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $template . ",";
        $this->newcmdline .= urlencode($command) . ",";
        $this->newcmdline .= urlencode($svcdesc) . ",";
        $this->newcmdline .= $svcgroup . ",";
        $this->newcmdline .= $contacts . ",";
        $this->newcmdline .= $contactgroups . ",";
        $this->newcmdline .= $freshnessthresh . ",";
        $this->newcmdline .= $activechecks . ",";
        $this->newcmdline .= $customvars;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$displayname;
        $this->newcmdline .= ",".$isvolatile;
        $this->newcmdline .= ",".$initialstate;
        $this->newcmdline .= ",".$maxcheckattempts;
        $this->newcmdline .= ",".$checkinterval;
        $this->newcmdline .= ",".$retryinterval;
        $this->newcmdline .= ",".$passivechecks;
        $this->newcmdline .= ",".$checkperiod;
        $this->newcmdline .= ",".$obsessoverservice;
        $this->newcmdline .= ",".$manfreshnessthresh;
        $this->newcmdline .= ",".$checkfreshness;
        $this->newcmdline .= ",".$eventhandler;
        $this->newcmdline .= ",".$eventhandlerenabled;
        $this->newcmdline .= ",".$lowflapthresh;
        $this->newcmdline .= ",".$highflapthresh;
        $this->newcmdline .= ",".$flapdetectionenabled;
        $this->newcmdline .= ",".$flapdetectionoptions;
        $this->newcmdline .= ",".$processperfdata;
        $this->newcmdline .= ",".$retainstatusinfo;
        $this->newcmdline .= ",".$retainnonstatusinfo;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$firstnotifdelay;
        $this->newcmdline .= ",".$notifperiod;
        $this->newcmdline .= ",".$notifopts;
        $this->newcmdline .= ",".$notifications_enabled;
        $this->newcmdline .= ",".$stalkingoptions;
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createTimeperiodsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        $this->newcmdline .= $name . ",";
        $this->newcmdline .= $alias . ",";
        $this->newcmdline .= $definition . ",";
        $this->newcmdline .= $exclude;
        $this->newcmdline .= ",".$disable;
        $this->newcmdline .= ",".$exception;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createCommandsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($name);
        $this->newcmdline .= ",".urlencode($command);
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServicedepsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($dephostname);
        $this->newcmdline .= ",".$dephostgroupname;
        $this->newcmdline .= ",".$depsvcdesc;
        $this->newcmdline .= ",".$hostname;
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$inheritsparent;
        $this->newcmdline .= ",".$execfailcriteria;
        $this->newcmdline .= ",".$notiffailcriteria;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostdepsAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($dephostname);
        $this->newcmdline .= ",".$dephostgroupname;
        $this->newcmdline .= ",".$hostname;
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$inheritsparent;
        $this->newcmdline .= ",".$execfailcriteria;
        $this->newcmdline .= ",".$notiffailcriteria;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServiceescAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$svcdesc;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostescAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createServiceextinfoAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$hostgroupname;
        $this->newcmdline .= ",".$contacts;
        $this->newcmdline .= ",".$contactgroups;
        $this->newcmdline .= ",".$firstnotif;
        $this->newcmdline .= ",".$lastnotif;
        $this->newcmdline .= ",".$notifinterval;
        $this->newcmdline .= ",".$period;
        $this->newcmdline .= ",".$escopts;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
    # ------------------------------------------------------------------------
    private function createHostextinfoAddCmd()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        if( $this->genericAddPrefix() == False ) return False;

        extract( $this->jsonadata, EXTR_SKIP );

        # TODO urlencodes:
        # urlencode should be done for all and urldecode expanded in nagctl.
        $this->newcmdline .= urlencode($hostname);
        $this->newcmdline .= ",".$notes;
        $this->newcmdline .= ",".$notes_url;
        $this->newcmdline .= ",".$action_url;
        $this->newcmdline .= ",".$icon_image;
        $this->newcmdline .= ",".$icon_image_alt;
        $this->newcmdline .= ",".$vrml_image;
        $this->newcmdline .= ",".$statusmap_image;
        $this->newcmdline .= ",".$coords2d;
        $this->newcmdline .= ",".$coords3d;
        $this->newcmdline .= ",".$disable;

        $this->newcmdline .= "'";

        return $retval;
    }
}

# ---------------------------------------------------------------------------
class ReadCmd
# ---------------------------------------------------------------------------
# Create a command that reads from the csv files.
{
    private $newcmdline;          /* the command, or an error message */
    private $retcode;             /* the http return code to send */
    private $subcmd;
    private $subcmdtype;
    const HOSTTEMPLATES = 1;
    const SERVICETEMPLATES = 2;
    const HOSTS = 3;
    const SERVICES = 4;
    const CONTACTS = 5;
    const CONTACTGROUPS = 6;
    const HOSTGROUPS = 7;
    const NAGIOSCONFIG = 8;
    const SERVICESETS = 9;
    const SERVICEGROUPS = 10;
    const TIMEPERIODS = 11;
    const COMMANDS = 12;
    const SERVICEDEPS = 13;
    const HOSTDEPS = 14;
    const SERVICEESC = 15;
    const HOSTESC = 16;
    const SERVICEEXTINFO = 17;
    const HOSTEXTINFO = 18;
    private $jsondata;

    # ------------------------------------------------------------------------
    public function __construct( $cmd, $subcmd, $jsondata )
    # ------------------------------------------------------------------------
    {
        $this->retcode = 200;

        if( $cmd != "show" && $cmd != "check" ) {
            $this->newcmdline =
                "ERROR 1029: Invalid command for this request type.";
            $this->retcode = 405;
            return;
        } else {
            $this->newcmdline = "Valid";
        }

        $this->subcmd = $subcmd;
        if( ! $this->setSubCmdType() ) {
            $this->newcmdline =
                "ERROR 1030: Invalid type '" . $subcmd . "' to show.";
            $this->retcode = 405;
            return;
        }

        $this->jsondata = $jsondata;

        if( $cmd == "show" ) {
            if( ! $this->createShowCommand() ) {
                $this->retcode = 405;
                return;
            }
        } else {
            if( ! $this->createCheckCommand() ) {
                $this->retcode = 405;
                return;
            }
        }
    }

    # ------------------------------------------------------------------------
    public function getCommand()
    # ------------------------------------------------------------------------
    {
        return $this->newcmdline ;
    }

    # ------------------------------------------------------------------------
    public function getReturnCode()
    # ------------------------------------------------------------------------
    {
        return $this->retcode ;
    }

    # ------------------------------------------------------------------------
    public function getSubCmdType()
    # ------------------------------------------------------------------------
    {
        return $this->subcmdtype ;
    }

    # ------------------------------------------------------------------------
    private function createCheckCommand()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        $this->newcmdline = NAGCTL_CMD;

        switch( $this->subcmdtype ) {
            case self::NAGIOSCONFIG:
                $retval = $this->createCheckNagiosconfigCmd();
                break;
        }

        return $retval ;
    }

    # ------------------------------------------------------------------------
    private function createShowCommand()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        $this->newcmdline = NAGCTL_CMD;

        switch( $this->subcmdtype ) {
            case self::HOSTTEMPLATES:
                $retval = $this->createHosttemplatesCmd();
                break;
            case self::SERVICETEMPLATES:
                $retval = $this->createServicetemplatesCmd();
                break;
            case self::HOSTS:
                $retval = $this->createHostsCmd();
                break;
            case self::SERVICES:
                $retval = $this->createServicesCmd();
                break;
            case self::CONTACTS:
                $retval = $this->createContactsCmd();
                break;
            case self::CONTACTGROUPS:
                $retval = $this->createContactgroupsCmd();
                break;
            case self::HOSTGROUPS:
                $retval = $this->createHostgroupsCmd();
                break;
            case self::SERVICEGROUPS:
                $retval = $this->createServicegroupsCmd();
                break;
            case self::SERVICESETS:
                $retval = $this->createServicesetsCmd();
                break;
            case self::TIMEPERIODS:
                $retval = $this->createTimeperiodsCmd();
                break;
            case self::COMMANDS:
                $retval = $this->createCommandsCmd();
                break;
            case self::SERVICEDEPS:
                $retval = $this->createServicedepsCmd();
                break;
            case self::HOSTDEPS:
                $retval = $this->createHostdepsCmd();
                break;
            case self::SERVICEESC:
                $retval = $this->createServiceescCmd();
                break;
            case self::HOSTESC:
                $retval = $this->createHostescCmd();
                break;
            case self::SERVICEEXTINFO:
                $retval = $this->createServiceextinfoCmd();
                break;
            case self::HOSTEXTINFO:
                $retval = $this->createHostextinfoCmd();
                break;
            default:
                $this->newcmdline = "ERROR 1031: Unknown error.";
                $retval = False;
        }

        return $retval ;
    }

    # ------------------------------------------------------------------------
    private function setSubCmdType()
    # ------------------------------------------------------------------------
    {
        $retval = True;

        switch( $this->subcmd ) {
            case 'hosttemplates':
                $this->subcmdtype = self::HOSTTEMPLATES;
                break;
            case 'servicetemplates':
                $this->subcmdtype = self::SERVICETEMPLATES;
                break;
            case 'hosts':
                $this->subcmdtype = self::HOSTS;
                break;
            case 'services':
                $this->subcmdtype = self::SERVICES;
                break;
            case 'contacts':
                $this->subcmdtype = self::CONTACTS;
                break;
            case 'contactgroups':
                $this->subcmdtype = self::CONTACTGROUPS;
                break;
            case 'hostgroups':
                $this->subcmdtype = self::HOSTGROUPS;
                break;
            case 'servicegroups':
                $this->subcmdtype = self::SERVICEGROUPS;
                break;
            case 'nagiosconfig':
                $this->subcmdtype = self::NAGIOSCONFIG;
                break;
            case 'servicesets':
                $this->subcmdtype = self::SERVICESETS;
                break;
            case 'timeperiods':
                $this->subcmdtype = self::TIMEPERIODS;
                break;
            case 'commands':
                $this->subcmdtype = self::COMMANDS;
                break;
            case 'servicedeps':
                $this->subcmdtype = self::SERVICEDEPS;
                break;
            case 'hostdeps':
                $this->subcmdtype = self::HOSTDEPS;
                break;
            case 'serviceesc':
                $this->subcmdtype = self::SERVICEESC;
                break;
            case 'hostesc':
                $this->subcmdtype = self::HOSTESC;
                break;
            case 'serviceextinfo':
                $this->subcmdtype = self::SERVICEEXTINFO;
                break;
            case 'hostextinfo':
                $this->subcmdtype = self::HOSTEXTINFO;
                break;
            default:
                $retval = False;
        }
        return $retval;
    }

    # ------------------------------------------------------------------------
    private function createSearchQuery()
    # ------------------------------------------------------------------------
    {
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1032: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
    
        $this->newcmdline .= " " . $this->jsondata->{'folder'} . " show";
        $this->newcmdline .= " " . $this->subcmd . " \"";
        if( isset($this->jsondata->{'column'}) && $this->jsondata->{'column'} )
            $this->newcmdline .= "column='" . $this->jsondata->{'column'}
                                 . "';";
        else
            $this->newcmdline .= "column='1';";

        if( isset($this->jsondata->{'filter'}) && $this->jsondata->{'filter'} )
            $this->newcmdline .= "filter='" . $this->jsondata->{'filter'}
                                 . "';";
        else
            $this->newcmdline .= "filter='.*';";

        $this->newcmdline .= "\"";

        return True;
    }
    # ------------------------------------------------------------------------
    private function createHosttemplatesCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServicetemplatesCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createHostsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServicesCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createContactsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createContactgroupsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createHostgroupsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServicegroupsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServicesetsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createTimeperiodsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createCommandsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServicedepsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createHostdepsCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServiceescCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createHostescCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createServiceextinfoCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createHostextinfoCmd()
    # ------------------------------------------------------------------------
    {
        return $this->createSearchQuery();
    }
    # ------------------------------------------------------------------------
    private function createCheckNagiosconfigCmd()
    # ------------------------------------------------------------------------
    {
        if( ! $this->jsondata->{'folder'} ) {
            $this->newcmdline = "ERROR 1033: 'folder' is undefined";
            $this->retcode = 405;
            return False;
        }
    
        $this->newcmdline .= " " . $this->jsondata->{'folder'};
        $this->newcmdline .= " check nagiosconfig";
        if( $this->jsondata->{'verbose'} == "true" ) {
                $this->newcmdline .= ' " verbose=1;"';
        }

        return True;
    }
}


# ---------------------------------------------------------------------------
function csv2array( $output, $subcmd )
# ---------------------------------------------------------------------------
{
    $cols = array(
        'hosts' => array(
            1 => "name",
            2 => "alias",
            3 => "ipaddress",
            4 => "template",
            5 => "hostgroup",
            6 => "contact",
            7 => "contactgroups",
            8 => "activechecks",
            9 => "servicesets",
            10 => "disable",
            11 => "displayname",
            12 => "parents",
            13 => "command",
            14 => "initialstate",
            15 => "maxcheckattempts",
            16 => "checkinterval",
            17 => "retryinterval",
            18 => "passivechecks",
            19 => "checkperiod",
            20 => "obsessoverhost",
            21 => "checkfreshness",
            22 => "freshnessthresh",
            23 => "eventhandler",
            24 => "eventhandlerenabled",
            25 => "lowflapthresh",
            26 => "highflapthresh",
            27 => "flapdetectionenabled",
            28 => "flapdetectionoptions",
            29 => "processperfdata",
            30 => "retainstatusinfo",
            31 => "retainnonstatusinfo",
            32 => "notifinterval",
            33 => "firstnotifdelay",
            34 => "notifperiod",
            35 => "notifopts",
            36 => "notifications_enabled",
            37 => "stalkingoptions",
            38 => "notes",
            39 => "notes_url",
            40 => "icon_image",
            41 => "icon_image_alt",
            42 => "vrml_image",
            43 => "statusmap_image",
            44 => "coords2d",
            45 => "coords3d",
            46 => "action_url",
        ),
        'services' => array(
            1 => "name",
            2 => "template",
            3 => "command",
            4 => "svcdesc",
            5 => "svcgroup",
            6 => "contacts",
            7 => "contactgroups",
            8 => "freshnessthresh",
            9 => "activechecks",
            10 => "customvars",
            11 => "disable",
            12 => "displayname",
            13 => "isvolatile",
            14 => "initialstate",
            15 => "maxcheckattempts",
            16 => "checkinterval",
            17 => "retryinterval",
            18 => "passivechecks",
            19 => "checkperiod",
            20 => "obsessoverservice",
            21 => "manfreshnessthresh",
            22 => "checkfreshness",
            23 => "eventhandler",
            24 => "eventhandlerenabled",
            25 => "lowflapthresh",
            26 => "highflapthresh",
            27 => "flapdetectionenabled",
            28 => "flapdetectionoptions",
            29 => "processperfdata",
            30 => "retainstatusinfo",
            31 => "retainnonstatusinfo",
            32 => "notifinterval",
            33 => "firstnotifdelay",
            34 => "notifperiod",
            35 => "notifopts",
            36 => "notifications_enabled",
            37 => "stalkingoptions",
            38 => "notes",
            39 => "notes_url",
            40 => "action_url",
            41 => "icon_image",
            42 => "icon_image_alt",
            43 => "vrml_image",
            44 => "statusmap_image",
            45 => "coords2d",
            46 => "coords3d",
        ),
        'servicesets' => array(
            1 => "name",
            2 => "template",
            3 => "command",
            4 => "svcdesc",
            5 => "svcgroup",
            6 => "contacts",
            7 => "contactgroups",
            8 => "freshnessthresh",
            9 => "activechecks",
            10 => "customvars",
            11 => "disable",
            12 => "displayname",
            13 => "isvolatile",
            14 => "initialstate",
            15 => "maxcheckattempts",
            16 => "checkinterval",
            17 => "retryinterval",
            18 => "passivechecks",
            19 => "checkperiod",
            20 => "obsessoverservice",
            21 => "manfreshnessthresh",
            22 => "checkfreshness",
            23 => "eventhandler",
            24 => "eventhandlerenabled",
            25 => "lowflapthresh",
            26 => "highflapthresh",
            27 => "flapdetectionenabled",
            28 => "flapdetectionoptions",
            29 => "processperfdata",
            30 => "retainstatusinfo",
            31 => "retainnonstatusinfo",
            32 => "notifinterval",
            33 => "firstnotifdelay",
            34 => "notifperiod",
            35 => "notifopts",
            36 => "notifications_enabled",
            37 => "stalkingoptions",
            38 => "notes",
            39 => "notes_url",
            40 => "action_url",
            41 => "icon_image",
            42 => "icon_image_alt",
            43 => "vrml_image",
            44 => "statusmap_image",
            45 => "coords2d",
            46 => "coords3d",
        ),
        'hosttemplates' => array(
            1 => "name",
            2 => "use",
            3 => "contacts",
            4 => "contactgroups",
            5 => "normchecki",
            6 => "checkinterval",
            7 => "retryinterval",
            8 => "notifperiod",
            9 => "notifopts",
            10 => "disable",
            11 => "checkperiod",
            12 => "maxcheckattempts",
            13 => "checkcommand",
            14 => "notifinterval",
            15 => "passivechecks",
            16 => "obsessoverhost",
            17 => "checkfreshness",
            18 => "freshnessthresh",
            19 => "eventhandler",
            20 => "eventhandlerenabled",
            21 => "lowflapthresh",
            22 => "highflapthresh",
            23 => "flapdetectionenabled",
            24 => "flapdetectionoptions",
            25 => "processperfdata",
            26 => "retainstatusinfo",
            27 => "retainnonstatusinfo",
            28 => "firstnotifdelay",
            29 => "notifications_enabled",
            30 => "stalkingoptions",
            31 => "notes",
            32 => "notes_url",
            33 => "icon_image",
            34 => "icon_image_alt",
            35 => "vrml_image",
            36 => "statusmap_image",
            37 => "coords2d",
            38 => "coords3d",
            39 => "action_url",
        ),
        'servicetemplates' => array(
            1 => "name",
            2 => "use",
            3 => "contacts",
            4 => "contactgroups",
            5 => "notifopts",
            6 => "checkinterval",
            7 => "normchecki",
            8 => "retryinterval",
            9 => "notifinterval",
            10 => "notifperiod",
            11 => "disable",
            12 => "checkperiod",
            13 => "maxcheckattempts",
            14 => "freshnessthresh",
            15 => "activechecks",
            16 => "customvars",
            17 => "isvolatile",
            18 => "initialstate",
            19 => "passivechecks",
            20 => "obsessoverservice",
            21 => "manfreshnessthresh",
            22 => "checkfreshness",
            23 => "eventhandler",
            24 => "eventhandlerenabled",
            25 => "lowflapthresh",
            26 => "highflapthresh",
            27 => "flapdetectionenabled",
            28 => "flapdetectionoptions",
            29 => "processperfdata",
            30 => "retainstatusinfo",
            31 => "retainnonstatusinfo",
            32 => "firstnotifdelay",
            33 => "notifications_enabled",
            34 => "stalkingoptions",
            35 => "notes",
            36 => "notes_url",
            37 => "action_url",
            38 => "icon_image",
            39 => "icon_image_alt",
            40 => "vrml_image",
            41 => "statusmap_image",
            42 => "coords2d",
            43 => "coords3d",
        ),
        'hostgroups' => array(
            1 => "name",
            2 => "alias",
            3 => "disable",
            4 => "members",
            5 => "hostgroupmembers",
            6 => "notes",
            7 => "notes_url",
            8 => "action_url",
        ),
        'servicegroups' => array(
            1 => "name",
            2 => "alias",
            3 => "disable",
            4 => "members",
            5 => "servicegroupmembers",
            6 => "notes",
            7 => "notes_url",
            8 => "action_url",
        ),
        'contacts' => array(
            1 => "name",
            2 => "use",
            3 => "alias",
            4 => "emailaddr",
            5 => "svcnotifperiod",
            6 => "svcnotifopts",
            7 => "svcnotifcmds",
            8 => "hstnotifperiod",
            9 => "hstnotifopts",
            10 => "hstnotifcmds",
            11 => "cansubmitcmds",
            12 => "disable",
            13 => "svcnotifenabled",
            14 => "hstnotifenabled",
            15 => "pager",
            16 => "address1",
            17 => "address2",
            18 => "address3",
            19 => "address4",
            20 => "address5",
            21 => "address6",
            22 => "retainstatusinfo",
            23 => "retainnonstatusinfo",
            24 => "contactgroups",
        ),
        'contactgroups' => array(
            1 => "name",
            2 => "alias",
            3 => "members",
            4 => "disable",
        ),
        'timeperiods' => array(
            1 => "name",
            2 => "alias",
            3 => "definition",
            4 => "exclude",
            5 => "disable",
            6 => "exception",
        ),
        'commands' => array(
            1 => "name",
            2 => "command",
            3 => "disable",
        ),
        'servicedeps' => array(
            1 => "dephostname",
            2 => "dephostgroupname",
            3 => "depsvcdesc",
            4 => "hostname",
            5 => "hostgroupname",
            6 => "svcdesc",
            7 => "inheritsparent",
            8 => "execfailcriteria",
            9 => "notiffailcriteria",
            10 => "period",
            11 => "disable",
        ),
        'hostdeps' => array(
            1 => "dephostname",
            2 => "dephostgroupname",
            3 => "hostname",
            4 => "hostgroupname",
            5 => "inheritsparent",
            6 => "execfailcriteria",
            7 => "notiffailcriteria",
            8 => "period",
            9 => "disable",
        ),
        'serviceesc' => array(
            1 => "hostname",
            2 => "hostgroupname",
            3 => "svcdesc",
            4 => "contacts",
            5 => "contactgroups",
            6 => "firstnotif",
            7 => "lastnotif",
            8 => "notifinterval",
            9 => "period",
            10 => "escopts",
            11 => "disable",
        ),
        'hostesc' => array(
            1 => "hostname",
            2 => "hostgroupname",
            3 => "contacts",
            4 => "contactgroups",
            5 => "firstnotif",
            6 => "lastnotif",
            7 => "notifinterval",
            8 => "period",
            9 => "escopts",
            10 => "disable",
        ),
        'serviceextinfo' => array(
            1 => "hostname",
            2 => "svcdesc",
            3 => "notes",
            4 => "notes_url",
            5 => "action_url",
            6 => "icon_image",
            7 => "icon_image_alt",
            8 => "disable",
        ),
        'hostextinfo' => array(
            1 => "hostname",
            2 => "notes",
            3 => "notes_url",
            4 => "action_url",
            5 => "icon_image",
            6 => "icon_image_alt",
            7 => "vrml_image",
            8 => "statusmap_image",
            9 => "coords2d",
            10 => "coords3d",
            11 => "disable",
        ),
    );

    # Put commas back
    $jsoutput = array();
    for( $i=0; $i < sizeof($output) ; ++$i ) {
        $inner = array();
        $items = explode( ",", $output[$i] );
        for( $j=0; $j < sizeof($items); ++$j ) {
            $inner[] = array($cols[$subcmd][$j+1] =>
                           strtr( $items[$j], array(
                                  '`'=>',',
                                  '%60'=>'%2C' ) ));
        }
        $jsoutput[] = $inner;
    }

    return $jsoutput;
}

# ---------------------------------------------------------------------------
function main()
# ---------------------------------------------------------------------------
{
    $rs = new RestServer();

    switch( $rs->getMethod() )
    {

        case 'get':
            /* It's a read request */
            /* TODO check_user_read_access */
            $rc = new ReadCmd( $rs->getCmd(),     /* E.g. show     */
                               $rs->getSubcmd(),  /* E.g. hosts    */
                               $rs->getData()     /* The json data */
                             );
            $cmd = $rc->getCommand();
            if( $rc->getReturnCode() != 200 ) {
                $rs->sendResponse( $rc->getReturnCode(),
                                   //json_encode($ouput),
                                   $cmd,
                                   'application/json' );
            }

            $output = array();
            $exit_status = 1;
            exec( $cmd . ' >/dev/stdout 2>&1', $output, $exit_status );
            if( $exit_status > 0 ) {
                $rs->sendResponse( 400,
                                   json_encode($output[0]),
                                   'application/json' );
            }

            if( $rs->getCmd() == "show" ) {
                $jsoutput = csv2array( $output, $rs->getSubcmd() );
            } else {
                $jsoutput = $output;
            }
            
            $rs->sendResponse( $rc->getReturnCode(),
                               json_encode($jsoutput),
                               'application/json' );
            break;

        case 'post':
            /* It's a write request */
            /* TODO check_user_read_access */
            $wc = new WriteCmd( $rs->getCmd(),     /* E.g. show     */
                                $rs->getSubcmd(),  /* E.g. hosts    */
                                $rs->getData(),    /* The json data */
                                $rs->getJAData()   /* The json array data */
                              );
            $cmd = $wc->getCommand();
            if( $wc->getReturnCode() != 200 ) {
                $rs->sendResponse( $wc->getReturnCode(),
                                   json_encode($cmd),
                                   'application/json' );
            }
            
            $output = array();
            $exit_status = 1;
            exec( $cmd . ' >/dev/stdout 2>&1', $output, $exit_status );

            if( $exit_status > 0 ) {
                $rs->sendResponse( 400,
                                   //json_encode($output[0] . " " . $cmd),
                                   json_encode($output),
                                   'application/json' );
            }

            $rs->sendResponse( $wc->getReturnCode(),
                               json_encode($output),
                               'application/json' );
            break;
    }
}

main();

# vim:ts=4:et:sw=4:tw=76
?>

