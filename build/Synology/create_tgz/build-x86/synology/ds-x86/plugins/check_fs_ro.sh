#!/bin/bash
# Copyright (C) 2010 Nokia
#
# File:     check_fs_ro.sh
# Author:   Mark Clarkson
# Date:     6 Oct 2010
# Version:  0.10
# Modified:
#           2010-09-30 XXXX
#           * XXXX
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

NAGOK=0
NAGWARN=1
NAGCRIT=2
NAGUNKN=3

# ----------------------------------------------------------------------------
usage()
# ----------------------------------------------------------------------------
{
    echo "Check that ext* filesystems are not ro."
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
            #-v) shift ; gVersion=$1
            #;;
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
    fs=`grep -E '^.* .* ext.* .*ro(,|$| )' /proc/mounts`

    if [[ -n $fs ]]; then
        rofs=`echo $fs | awk '{ print $1; }'`
        echo "CRITICAL: $rofs is a read-only filesystem."
        retval=$NAGCRIT
    else
        echo "OK."
    fi

    exit $retval
}

main
