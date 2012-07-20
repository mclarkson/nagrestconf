#!/bin/bash
#
# File: REST_setup.sh
# Author: Mark Clarkson
# Date: 20 Jan 2010
# Version: 0.1
# Modified:
#
# Purpose: Set up monitoring for a service-line using service-sets only
#
# Notes:
#   Add a line to your .netrc to do auth. E.g.
#   machine 127.0.0.1 login nagiosadmin password NagiosPassword

#curl() { :; }

# ----------------------------------------------------------------------------
# CONFIGURATION SECTION
# ----------------------------------------------------------------------------

# IP: The ip address of the remote service line container
IP=127.0.0.1

# FOLDER: the folder name to work in on the remote service line container
FOLDER="local"

# DELETE: 1 - delete existing config, 0 - don't
DELETE=1

# A FEW RULES:
#
# 1 When multiple items are required separate them with commas with no extra
#   spaces. (See the contactgroups example below)
#
# 2 When spaces are required, in descriptive fields for example, use
#   underscores instead. (See contacts example below)
#
# 3 To leave a field unset use '-'. (See contacts example below)
#   Most fields cannot be left unset.
#

# servicesets:
# All three variables must be set, serviceset_name, serviceset_svctemplate, serviceset
# Increment the array index value, [N].
N=-1
#
serviceset_name[++N]="basic-linux-checks"
serviceset_svctemplate[N]="prod_stmpl"
serviceset[N]='\"check_any!check_load -r -w 4,4,4 -c 8,8,8\",\"svcdesc\":\"Load\"
\"check_any!check_puppet_wrapper\",\"svcdesc\":\"Puppet Log\"
\"check_any!check_fs_ro.sh\",\"svcdesc\":\"Read-write filesystems\"
\"check_any!check_puppet_version.sh -v puppet-2.6.16-1\",\"svcdesc\":\"Puppet Version\"
\"check_ping!100.0,20%!500.0,60%\",\"svcdesc\":\"PING\"
\"check_any!check_disk -e -w 10% -c 5%\",\"svcdesc\":\"Disks\"
\"check_any!check_swap -w 10% -c 5%\",\"svcdesc\":\"Swap\"
\"check_any!check_dns -w 0.5 -c 1 -t 30 -H www.google.com\",\"svcdesc\":\"DNS\"
\"check_any!check_ntp -H 0.uk.pool.ntp.org -w 0.5 -c 1.0\",\"svcdesc\":\"NTP Time\",\"template\":\"prod_10trytmpl\"
\"check_any!check_users -w 15 -c 20\",\"svcdesc\":\"Num Users\"
\"check_any!check_procs -w 40 -c 80 -s Z\",\"svcdesc\":\"Processes: Zombies\"
\"check_any!check_procs -w 1:1 -C puppetd\",\"svcdesc\":\"Processes: Puppet Client\"
\"check_any!check_cpu.sh\",\"svcdesc\":\"CPU\"
\"check_any!check_mem.pl -w 80 -c 95\",\"svcdesc\":\"Memory\"'
#
serviceset_name[++N]="basic-windows-checks"
serviceset_svctemplate[N]="prod_stmpl"
serviceset[N]='\"check_ping!300.0,20%!800.0,60%\",\"svcdesc\":\"PING\"
\"check_nt!CPULOAD! -l 5,80,90\",\"svcdesc\":\"CPU Load\"
\"check_nt!MEMUSE! -w 80 -c 90\",\"svcdesc\":\"Memory Usage\"
\"check_nt!UPTIME!\",\"svcdesc\":\"Uptime\"
\"check_nt!CLIENTVERSION!\",\"svcdesc\":\"NSClient++ Version\"
\"check_nt!USEDDISKSPACE! -l c -w 85 -c 95\",\"svcdesc\":\"C: System Drive\"
\"check_nt!USEDDISKSPACE! -l d -w 85 -c 95\",\"svcdesc\":\"D: Drive\"
\"check_nt!USEDDISKSPACE! -l e -w 85 -c 95\",\"svcdesc\":\"E: Drive\"
'

# servicegroups:
# EXAMPLE: servicegroups="servicegroup1 A_Service_Group"
servicegroups="
"

# hostgroups:
# EXAMPLE: hostgroups="hostgroup1 A_Host_Group"
hostgroups="
hostgroup1 Host_Group_1
hostgroup2 Host_Group_2
"

# hosts:
# EXAMPLE: hosts="host1.europe.company.com 10.218.1.1 hostgroup1 hosttmpl
#          host2.europe.company.com 10.218.1.2 hostgroup1,hostgroup2"
hosts="
# CSL Production Hosts
hg1-win80.company.local 192.168.1.2 hostgroup1 prod_htmpl basic-windows-checks
hg2-lin01.company.local 192.168.1.3 hostgroup2 prod_htmpl basic-linux-checks
#
# LOTS MORE HOSTS HERE
#
"

# hosttemplates: 
# EXAMPLE: hosttemplates="tc2prod basic-linux-host readonly tier3-admins"
hosttemplates="
prod_htmpl prodgui,prodgui_ro prod-admins
"

# servicetemplates:
# EXAMPLE: servicetemplates="production-service base-service readonly tier3-admins"
servicetemplates="prod_stmpl 3 prodgui,prodgui_ro prod-admins
prod_10trytmpl 10 prodgui,prodgui_ro prod-admins
"

# contacts: 
# EXAMPLE: contacts="readonly Read_Only_User - 0"
contacts="prodgui_ro Production_Read_Only_GUI_User - 0 24x7 w_u_c_r notify-service-by-email 24x7 d_u_r notify-host-by-email
prodgui Production_RW_GUI_User - 1 24x7 w_u_c_r notify-service-by-email 24x7 d_u_r notify-host-by-email
local_admin Local_Admin local_admin@company.local 1 24x7 w_u_c_r notify-service-by-email 24x7 d_u_r notify-host-by-email
"

# contactgroups: 
# EXAMPLE: contactgroups="tier3-admins Tier_3_Admins mark,john"
contactgroups="prod-admins Production_Admins local_admin
"

# Fine grained alerting control
# Format: <contact|-> <contactgroup|-> <host regex> <service regex>
# E.g. To alert admins contact group for any disk related problems on any host:
alerting="
# Alert Local admin for all hosts for all disk problems:
- prod-admins .* .*Disk.*
- prod-admins .* .*Drive.*
"

# ----------------------------
# ADD COMMANDS AND TIMEPERIODS
# ----------------------------

# Use '\\\\' to end up with '\' in the nagios .cfg file.

add_commands_and_timeperiods()
{
# TIMEPERIODS

echo
echo "-----------------------------------------------------------"
echo "- Add Timeperiods"
echo "-----------------------------------------------------------"
echo

curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"none","alias":"No time is a good time"}' http://$IP/rest/add/timeperiods
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"24x7","alias":"24 Hours A Day, 7 Days A Week","definition":"sunday|00:00-24:00,monday|00:00-24:00,tuesday|00:00-24:00,wednesday|00:00-24:00,thursday|00:00-24:00,friday|00:00-24:00,saturday|00:00-24:00"}' http://$IP/rest/add/timeperiods
# COMMANDS

echo
echo "-----------------------------------------------------------"
echo "- Add Commands"
echo "-----------------------------------------------------------"
echo

curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_nrpe","command":"$USER1$/check_nrpe -H $HOSTADDRESS$ -t 60 -c $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_nrpe++","command":"$USER1$/check_nrpe -H $HOSTADDRESS$ -t 60 -c $ARG1$ -a \"$ARG2$\""}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_any","command":"$USER1$/check_nrpe -H $HOSTADDRESS$ -t 60 -c check_any -a \"$ARG1$\""}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_any2","command":"$USER1$/check_nrpe -H $HOSTADDRESS$ -t 60 -c check_any2 -a \"$ARG1$\" \"$ARG2$\" "}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_any3","command":"$USER1$/check_nrpe -H $HOSTADDRESS$ -t 60 -c check_any3 -a \"$ARG1$\" \"$ARG2$\" \"$ARG3$\" "}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_any4","command":"$USER1$/check_nrpe -H $HOSTADDRESS$ -t 60 -c check_any4 -a \"$ARG1$\" \"$ARG2$\" \"$ARG3$\" \"$ARG4$\" "}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"notify-host-by-email","command":"/usr/bin/printf \"%b\" \"***** Nagios *****\\\\\\\\n\\\\\\\\nNotification Type: $NOTIFICATIONTYPE$\\\\\\\\nHost: $HOSTNAME$\\\\\\\\nState: $HOSTSTATE$\\\\\\\\nAddress: $HOSTADDRESS$\\\\\\\\nInfo: $HOSTOUTPUT$\\\\\\\\n\\\\\\\\nDate/Time: $LONGDATETIME$\\\\\\\\n\" | /bin/mail -s \"** $NOTIFICATIONTYPE$ Host Alert: $HOSTNAME$ is $HOSTSTATE$ **\" $CONTACTEMAIL$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"notify-service-by-email","command":"/usr/bin/printf \"%b\" \"***** Nagios *****\\\\\\\\n\\\\\\\\nNotification Type: $NOTIFICATIONTYPE$\\\\\\\\n\\\\\\\\nService: $SERVICEDESC$\\\\\\\\nHost: $HOSTALIAS$\\\\\\\\nAddress: $HOSTADDRESS$\\\\\\\\nState: $SERVICESTATE$\\\\\\\\n\\\\\\\\nDate/Time: $LONGDATETIME$\\\\\\\\n\\\\\\\\nAdditional Info:\\\\\\\\n\\\\\\\\n$SERVICEOUTPUT$\" | /bin/mail -s \"** $NOTIFICATIONTYPE$ Service Alert: $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$ **\" $CONTACTEMAIL$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check-host-alive","command":"$USER1$/check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 2"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_webinject","command":"$USER1$/check_webinject.pl -c witest/$ARG1$ witest/$ARG2$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_snmp_int","command":"$USER1$/check_snmp_int.pl -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_netapp","command":"$USER1$/check_netapp3a.pl -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_ftp","command":"$USER1$/check_ftp -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_hpjd","command":"$USER1$/check_hpjd -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_snmp","command":"$USER1$/check_snmp -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_http","command":"$USER1$/check_http -I $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_ping","command":"$USER1$/check_ping -H $HOSTADDRESS$ -w $ARG1$ -c $ARG2$ -p 5"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_pop","command":"$USER1$/check_pop -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_imap","command":"$USER1$/check_imap -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_smtp","command":"$USER1$/check_smtp -H $HOSTADDRESS$ $ARG1$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"process-service-perfdata-file","command":"/bin/mv /usr/local/pnp4nagios/var/service-perfdata /usr/local/pnp4nagios/var/spool/service-perfdata.$TIMET$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"process-host-perfdata-file","command":"/bin/mv /usr/local/pnp4nagios/var/host-perfdata /usr/local/pnp4nagios/var/spool/host-perfdata.$TIMET$"}' http://$IP/rest/add/commands
curl -knX POST -d 'json={"folder":"'$FOLDER'","name":"check_nt","command":"$USER1$/check_nt -H $HOSTADDRESS$ -s password -p 12489 -v $ARG1$ $ARG2$"}' http://$IP/rest/add/commands
}

# ----------------------------------------------------------------------------
# DON'T MODIFY ANYTHING BELOW
# ----------------------------------------------------------------------------

# -----------------------
# DELETE CONFIG
# -----------------------
if [[ $DELETE -eq 1 ]]; then
echo "-----------------------------------------------------------"
echo "- Delete EVERYTHING"
echo "-----------------------------------------------------------"
echo
    echo curl -sknX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\",
                  \"svcdesc\":\".*\"}'" \
        http://${IP}/rest/delete/services
    curl -sknX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\",
                  \"svcdesc\":\".*\"}" \
        http://${IP}/rest/delete/services

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/hosts
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/hosts

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/hosttemplates
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/hosttemplates

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/servicetemplates
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/servicetemplates

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/contactgroups
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/contactgroups

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/contacts
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/contacts

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/hostgroups
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/hostgroups

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\",
                  \"svcdesc\":\".*\"}'" \
        http://${IP}/rest/delete/servicegroups
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\",
                  \"svcdesc\":\".*\"}" \
        http://${IP}/rest/delete/servicegroups

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\",
                  \"svcdesc\":\".*\"}'" \
        http://${IP}/rest/delete/servicesets
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\",
                  \"svcdesc\":\".*\"}" \
        http://${IP}/rest/delete/servicesets

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/timeperiods
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/timeperiods

    echo

    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\", \"name\":\".*\"}'" \
        http://${IP}/rest/delete/commands
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\", \"name\":\".*\"}" \
        http://${IP}/rest/delete/commands
fi

add_commands_and_timeperiods

# -----------------------
# DEFINE CONTACTS
# -----------------------

echo
echo "-----------------------------------------------------------"
echo "- Add Contacts"
echo "-----------------------------------------------------------"
echo

while read i; do
    [[ -z $i ]] && continue
    read name alias emailaddr cansubmitcmds \
         svcnotifperiod svcnotifopts svcnotifcmds \
         hstnotifperiod hstnotifopts hstnotifcmds < <(echo $i)
    [[ -z $cansubmitcmds ]] && cansubmitcmds=1
    [[ $emailaddr = '-' ]] && unset emailaddr
    alias=`echo "$alias" | tr _ " "`
    svcnotifopts=`echo "$svcnotifopts" | tr _ " "`
    hstnotifopts=`echo "$hstnotifopts" | tr _ " "`
    contactgroups=`echo "$contactgroups" | tr , " "`
    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\",
            \"name\":\"$name\", 
            \"alias\":\"$alias\",
            \"emailaddr\":\"$emailaddr\",
            \"svcnotifperiod\":\"$svcnotifperiod\",
            \"svcnotifopts\":\"$svcnotifopts\",
            \"svcnotifcmds\":\"$svcnotifcmds\",
            \"hstnotifperiod\":\"$hstnotifperiod\",
            \"hstnotifopts\":\"$hstnotifopts\",
            \"hstnotifcmds\":\"$hstnotifcmds\",
            \"cansubmitcmds\":\"$cansubmitcmds\"}'" \
        http://${IP}/rest/add/contacts
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\",
            \"name\":\"$name\", 
            \"alias\":\"$alias\",
            \"emailaddr\":\"$emailaddr\",
            \"svcnotifperiod\":\"$svcnotifperiod\",
            \"svcnotifopts\":\"$svcnotifopts\",
            \"svcnotifcmds\":\"$svcnotifcmds\",
            \"hstnotifperiod\":\"$hstnotifperiod\",
            \"hstnotifopts\":\"$hstnotifopts\",
            \"hstnotifcmds\":\"$hstnotifcmds\",
            \"cansubmitcmds\":\"$cansubmitcmds\"}" \
        http://${IP}/rest/add/contacts
    echo
done < <(echo "$contacts")

echo
echo "-----------------------------------------------------------"
echo "- Add Contact Groups"
echo "-----------------------------------------------------------"
echo

while read i; do
    [[ -z $i ]] && continue
    read name alias members < <(echo $i)
    alias=`echo "$alias" | tr _ " "`
    members=`echo "$members" | tr , " "`
    echo curl -knX POST \
        -d "'json={\"folder\":\"$FOLDER\",
            \"name\":\"$name\", 
            \"alias\":\"$alias\",
            \"members\":\"$members\"}'" \
        http://${IP}/rest/add/contacts
    curl -knX POST \
        -d "json={\"folder\":\"$FOLDER\",
            \"name\":\"$name\", 
            \"alias\":\"$alias\",
            \"members\":\"$members\"}" \
        http://${IP}/rest/add/contactgroups
    echo
done < <(echo "$contactgroups")

echo
echo "-----------------------------------------------------------"
echo "- Add Service Groups"
echo "-----------------------------------------------------------"
echo

while read i; do
    [[ -z $i ]] && continue
    read name alias < <(echo $i)
    alias=`echo "$alias" | tr _ " "`
    echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"alias\":\"$alias\"}'" http://${IP}/rest/add/servicegroups 
    curl -knX POST -d "json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"alias\":\"$alias\"}" http://${IP}/rest/add/servicegroups 
    echo
done < <(echo "$servicegroups")

echo
echo "-----------------------------------------------------------"
echo "- Add Host Templates"
echo "-----------------------------------------------------------"
echo

while read i; do
    [[ -z $i ]] && continue
    read name contacts contactgroups < <(echo $i)
    if [[ $contacts = '-' ]]; then
        unset contacts
    else
        contacts=`echo "$contacts" | tr , " "`
    fi
    contactgroups=`echo "$contactgroups" | tr , " "`
    echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"checkinterval\":\"5\",
        \"checkperiod\":\"24x7\",
        \"retryinterval\":\"1\",
        \"maxcheckattempts\":\"3\",
        \"notifperiod\":\"24x7\",
        \"notifinterval\":\"60\",
        \"contacts\":\"$contacts\",
        \"contactgroups\":\"$contactgroups\"}'" \
        http://${IP}/rest/add/hosttemplates 
    curl -knX POST -d "json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"checkinterval\":\"5\",
        \"checkperiod\":\"24x7\",
        \"retryinterval\":\"1\",
        \"maxcheckattempts\":\"3\",
        \"notifperiod\":\"24x7\",
        \"notifinterval\":\"60\",
        \"contacts\":\"$contacts\",
        \"contactgroups\":\"$contactgroups\"}" \
        http://${IP}/rest/add/hosttemplates 
    echo
done < <(echo "$hosttemplates")

echo
echo "-----------------------------------------------------------"
echo "- Add Service Templates"
echo "-----------------------------------------------------------"
echo

while read i; do
    [[ -z $i ]] && continue
    read name maxchecks contacts contactgroups checkinterval< <(echo $i)
    if [[ $contacts = '-' ]]; then
        unset contacts
    else
        contacts=`echo "$contacts" | tr , " "`
    fi
    [[ -z $checkinterval ]] && checkinterval=5
    contactgroups=`echo "$contactgroups" | tr , " "`
    echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"checkinterval\":\"$checkinterval\",
        \"checkperiod\":\"24x7\",
        \"retryinterval\":\"1\",
        \"maxcheckattempts\":\"$maxchecks\",
        \"notifinterval\":\"60\",
        \"notifperiod\":\"24x7\",
        \"contacts\":\"$contacts\",
        \"contactgroups\":\"$contactgroups\"}'" \
        http://${IP}/rest/add/servicetemplates
    curl -knX POST -d "json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"checkinterval\":\"$checkinterval\",
        \"checkperiod\":\"24x7\",
        \"retryinterval\":\"1\",
        \"maxcheckattempts\":\"$maxchecks\",
        \"notifinterval\":\"60\",
        \"notifperiod\":\"24x7\",
        \"contacts\":\"$contacts\",
        \"contactgroups\":\"$contactgroups\"}" \
        http://${IP}/rest/add/servicetemplates
    echo
done < <(echo "$servicetemplates")

echo
echo "-----------------------------------------------------------"
echo "- Add Service Sets"
echo "-----------------------------------------------------------"
echo

for i in `seq 0 $((${#serviceset[*]}-1))`; do
    while read j; do
        [[ -z $j || $j == "#"* ]] && continue
        # Allow use of a custom service template
        if ( echo "$i" | grep -qs "template\":" ); then
        echo curl -knX POST \
            -d "'json={\"folder\":\"$FOLDER\",
            \"name\":\"${serviceset_name[$i]}\",
            \"command\":$j}'" http://${IP}/rest/add/servicesets
        curl -knX POST \
            -d "json={\"folder\":\"$FOLDER\",
            \"name\":\"${serviceset_name[$i]}\",
            \"command\":$j}" http://${IP}/rest/add/servicesets
        else
        echo curl -knX POST \
            -d "'json={\"folder\":\"$FOLDER\",
            \"name\":\"${serviceset_name[$i]}\",
            \"template\":\"${serviceset_svctemplate[$i]}\",
            \"command\":$j}'" http://${IP}/rest/add/servicesets
        curl -knX POST \
            -d "json={\"folder\":\"$FOLDER\",
            \"name\":\"${serviceset_name[$i]}\",
            \"template\":\"${serviceset_svctemplate[$i]}\",
            \"command\":$j}" http://${IP}/rest/add/servicesets
        fi
        echo
    done < <( echo "${serviceset[$i]}" | sed "s/qUoTe/'/g" )
done


echo
echo "-----------------------------------------------------------"
echo "- Add Host Groups"
echo "-----------------------------------------------------------"
echo

while read i; do
    [[ -z $i ]] && continue
    read name alias < <(echo $i)
    alias=`echo "$alias" | tr _ " "`
    echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"alias\":\"$alias\"}'" http://${IP}/rest/add/hostgroups 
    curl -knX POST -d "json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"alias\":\"$alias\"}" http://${IP}/rest/add/hostgroups 
    echo
done < <(echo "$hostgroups")

echo
echo "-----------------------------------------------------------"
echo "- Add Hosts"
echo "-----------------------------------------------------------"
echo

# Use , separator for col 3 if multiple hostgroups are needed.
while read name ipaddr hostgroup template svcset; do
    [[ -z $name || $name == "#"* ]] && continue
    if [[ $hostgroup = '-' ]]; then
        unset hostgroup
    else
        hostgroup=`echo "$hostgroup" | tr , " "`
    fi
    echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"alias\":\"$name\",
        \"ipaddress\":\"$ipaddr\",
        \"template\":\"$template\",
        \"hostgroup\":\"$hostgroup\",
        \"servicesets\":\"$svcset\"}'" http://${IP}/rest/add/hosts 
    curl -knX POST -d "json={\"folder\":\"$FOLDER\",
        \"name\":\"$name\",
        \"alias\":\"$name\",
        \"ipaddress\":\"$ipaddr\",
        \"template\":\"$template\",
        \"hostgroup\":\"$hostgroup\",
        \"servicesets\":\"$svcset\"}" http://${IP}/rest/add/hosts 
    echo
done < <(echo "$hosts")

[[ -n $alerting ]] && {
    echo
    echo "-----------------------------------------------------------"
    echo "- Apply"
    echo "-----------------------------------------------------------"
    echo

    echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\"}'" http://${IP}/rest/apply/nagiosconfig
    curl -knX POST -d "json={\"folder\":\"$FOLDER\"}" http://${IP}/rest/apply/nagiosconfig
    echo

    # Everything is setup, now do modifications

    echo
    echo "-----------------------------------------------------------"
    echo "- Fine grained alerting control"
    echo "-----------------------------------------------------------"
    echo

    while read i; do
	[[ -z $i || $i == "#"* ]] && continue
	read contacts contactgroups regex1 regex2 < <(echo "$i")
	[[ $contacts = '-' ]] && unset contacts
	[[ $contactgroups = '-' ]] && unset contactgroups
	contacts=`echo "$contacts" | tr , " "`
	contactgroups=`echo "$contactgroups" | tr , " "`
	/usr/bin/curl -sknX GET "http://${IP}/rest/show/services?json=\{\"folder\":\"$FOLDER\"\}" \
          | sed 's/,/\n/g;s/\[{/\n/g;s/{/    /g;s/\]\]/\n/g;s/[}\[\]]*//g;s/"//g' \
          | sed -n "/name:$regex1/,/^$/ { /name:$regex1/ {H;}; /svcdesc:$regex2/ {H;x;s/name://;s/svcdesc://;s/\n//g;p}; /^$/ {x;d;}; }" \
	  | while read a b
	do
        b=`echo "$b" | sed 's#/#\\\\\\\/#g'`
	echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\",
	    \"name\":\"$a\",
	    \"svcdesc\":\"$b\",
	    \"contacts\":\"$contacts\",
	    \"contactgroups\":\"$contactgroups\"}'" \
	    http://${IP}/rest/modify/services
	curl -knX POST -d "json={\"folder\":\"$FOLDER\",
	    \"name\":\"$a\",
	    \"svcdesc\":\"$b\",
	    \"contacts\":\"$contacts\",
	    \"contactgroups\":\"$contactgroups\"}" \
	    http://${IP}/rest/modify/services
	 echo
	done
    done < <(echo "$alerting")
}

echo
echo "-----------------------------------------------------------"
echo "- Apply, Check and Restart"
echo "-----------------------------------------------------------"
echo

echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\"}'" http://${IP}/rest/apply/nagiosconfig
curl -knX POST -d "json={\"folder\":\"$FOLDER\"}" http://${IP}/rest/apply/nagiosconfig
echo

#curl -knX GET "http://${IP}/rest/check/nagiosconfig?json=\{\"folder\":\"$FOLDER\",\"verbose\":\"true\"\}" | sed 's/","/\n/g;s/\\t/\t/g;s%\\/%/%g;s/\["//;s/"\]//'

echo curl -knX GET "'http://${IP}/rest/check/nagiosconfig?json=\{\"folder\":\"$FOLDER\",\"verbose\":\"false\"\}'"
curl -knX GET "http://${IP}/rest/check/nagiosconfig?json=\{\"folder\":\"$FOLDER\",\"verbose\":\"false\"\}"
echo

echo curl -knX POST -d "'json={\"folder\":\"$FOLDER\"}'" http://${IP}/rest/restart/nagios
curl -knX POST -d "json={\"folder\":\"$FOLDER\"}" http://${IP}/rest/restart/nagios
echo

