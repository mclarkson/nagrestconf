#!/bin/sh

PROGNAME=`basename $0`
PROGPATH=`echo $0 | sed -e 's,[\\/][^\\/][^\\/]*$,,'`
#. $PROGPATH/utils.sh
javacmd=`which java`
CLASSPATH=/usr/lib64/nagios/plugins/:/usr/lib64/nagios/plugins/sqljdbc.jar
export CLASSPATH
. /usr/lib64/nagios/plugins/utils.sh

argc=$#

print_usage() {
        echo "Usage: $PROGNAME -H hostaddress -u {username} -p {password}"
        }
print_help() {
        print_usage
        echo ""
        echo "-h for help"
        echo ""
        echo "-H (hostaddress)"
          echo "-u (username)"
          echo "-p (password)"
        echo "Example command"
        echo "check_sqljob -H 10.70.5.45 -u sa -p s22css"
        echo ""
        exit 0
}

case "$1" in
        -h)
                print_help
                exit 0
                ;;
        -v)
                print_revision $PROGNAME $REVISION
                exit 0
                ;;
        -H)
                if [ $argc -eq 6 ]
                then
                   ret=`$javacmd SqlJobMon $2 $4 $6`
                fi
                ret1=`echo  $ret | grep CRITICAL| awk '{print $4}'`
                if [ "$ret1" =  "CRITICAL:" ]
                then
                  echo $ret
                  exit 2
                fi
                ret2=`echo  $ret | grep OK| awk '{print $4}'`
                if [ "$ret2" = "OK:"  ]
                then
                  echo $ret
                  exit 0
                else
                  echo "UNKNOWN:"
                  exit 3
                fi
                ;;
         *)
                print_help
                exit $STATE_UNKNOWN
                ;;
esac
