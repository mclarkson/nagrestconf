#!/bin/bash
#
#    Copyright (C) 2010 Mark Clarkson
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# File:     check_puppet_log.sh
# Author:   Mark Clarkson
# Date:     10 Mar 2012
# Version:  0.10
# Modified:
#           20XX-MO-DY Name
#           * Reason
#
# Purpose:  Check the puppet log for errors.
#
# Notes:
#

set -f
shopt -u extglob

# ---------------------------------------------------------------------------
# SETINGS - MODIFY AS NEEDED
# ---------------------------------------------------------------------------

# For testing copy /var/log/messages to /tmp/messages and change to:
# LOGFILE=/tmp/messages
LOGFILE=/var/log/messages

#
#
# -------------------- DO NOT MODIFY ANYTHING BELOW -------------------------
#
#

# ---------------------------------------------------------------------------
# GLOBALS
# ---------------------------------------------------------------------------

# Program args
ME="$0"
CMDLINE="$@"

NAGOK=0
NAGWARN=1
NAGCRIT=2
NAGUNKN=3
SUDO="/bin/cat $LOGFILE"

# ----------------------------------------------------------------------------
usage()
# ----------------------------------------------------------------------------
{
    echo "check_puppet_log.sh [-s]"
    echo
    echo "Checks log for puppet errors"
    echo "Use -s option to use sudo for reading /var/log/messages"
}

# ----------------------------------------------------------------------------
parse_options()
# ----------------------------------------------------------------------------
# Purpose:      Parse program options and set globals.
# Arguments:    None
# Returns:      Nothing
{
    local new

    set -- $CMDLINE
    while true
    do
        case $1 in
            -h) usage ; exit 0
            ;;
            -s) SUDO="sudo $SUDO"
            ;;
            ?*) echo "Syntax error." ; exit 1
            ;;
        esac
        shift 1 || break
    done
}

# ----------------------------------------------------------------------------
main()
# ----------------------------------------------------------------------------
# Purpose:      Script starts here
# Arguments:    None
# Returns:      Nothing
{
    parse_options

    retval=$NAGOK

    last=`$SUDO | egrep -e "(puppet-agent|puppetd).*Could not retrieve catalog;" \
         -e "(puppet-agent|puppetd).*Finished catalog run in .* seconds" \
         | tail -3`

    last1=`echo "$last" | head -1`
    last2=`echo "$last" | tail -2 | head -1`
    last3=`echo "$last" | tail -1`

    errors=0
    [[ "$last1" =~ "Could" ]] && let errors+=1
    [[ "$last2" =~ "Could" ]] && let errors+=1
    [[ "$last3" =~ "Could" ]] && let errors+=1

    if [[ $errors -eq 3 ]]; then
        echo "WARNING: Puppet run did not complete. Error was 'Could not retrieve catalog'."
        retval=$NAGWARN
    else
        betweenlinenos=`$SUDO | egrep -n -e "(puppet-agent|puppetd).*Finished catalog run" \
            -e "(puppet-agent|puppetd).* Starting Puppet client" \
            | tail -2 | sed 's/:.*//'`
        read a b < <( echo $betweenlinenos )
        if [[ -z $a && -z $b ]]; then
            lines=""
        else
            lines=`$SUDO | sed -n "$a,$b {p}"`
        fi
        if echo "$lines" | egrep -qs "(puppet-agent|puppetd).*fail"; then
            echo "WARNING: Puppet run completed but there were failures."
            retval=$NAGWARN
        elif echo "$lines" | egrep -qs "(puppet-agent|puppetd).*Could not apply complete catalog"; then
            echo "WARNING: Puppet run completed but did not apply the complete catalog."
            retval=$NAGWARN
        elif echo "$lines" | egrep -qs "(puppet-agent|puppetd).*Could not"; then
            echo "WARNING: Puppet run completed but there were problems."
            retval=$NAGWARN
        else
            if [[ $errors -gt 0 ]]; then
                [[ $errors -ne 1 ]] && s="s"
                echo "PUPPET LOG OK (but with $errors failure$s)"
            else
                echo "PUPPET LOG OK"
            fi
            retval=$NAGOK
        fi
    fi

    exit $retval
}

main
