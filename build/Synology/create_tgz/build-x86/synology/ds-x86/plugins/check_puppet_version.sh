#!/bin/bash
# Copyright (C) 2010 Nokia
#
# File:     check_jboss
# Author:   Mark Clarkson
# Date:     6 Oct 2010
# Version:  0.10
# Modified:
#           2010-09-30 XXXX
#           * XXXX
#
# Purpose:  This a wrapper around check_jboss.pl that remaps the following chars
#           -NNN to <NNN
#           +NNN to >NNN
#           ~NNN to !NNN
#           ~SSS to !"SSS"
#
# Notes:
#

set -f
shopt -u extglob

# ---------------------------------------------------------------------------
# SETINGS - MODIFY AS NEEDED
# ---------------------------------------------------------------------------

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

gVersion=

NAGOK=0
NAGWARN=1
NAGCRIT=2
NAGUNKN=3

# ----------------------------------------------------------------------------
usage()
# ----------------------------------------------------------------------------
{
    echo "Check the puppet version."
    echo "Supply with exactly one -v argument - the puppet version as"
    echo "reported by rpm -qv puppet. for example:"
    echo "./check_puppet_version.sh -v puppet-2.6.6-1"
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
            -v) shift ; gVersion=$1
            ;;
            -h) usage ; exit 0
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
    version=`rpm -qv puppet`

    if ! echo $version | grep -qs $gVersion; then
        echo "WARNING: Version is $version - should be $gVersion"
        retval=$NAGWARN
    else
        echo "OK. Version is $gVersion"
    fi

    exit $retval
}

main
