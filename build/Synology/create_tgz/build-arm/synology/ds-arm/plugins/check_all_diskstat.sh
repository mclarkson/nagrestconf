#!/bin/bash

CHK=/usr/lib64/nagios/plugins/check_diskstat.sh
WARN=50
CRIT=100
ERROR=0
RETVAL=0

for DEVICE in `ls /sys/block|grep -v -e hd -e fd -e sr`; do
	if [ -L /sys/block/$DEVICE/device ]; then
		DEVNAME=$(echo /dev/$DEVICE | sed 's#!#/#g')
        output=`$CHK -b -d $DEVICE -W $WARN -C $CRIT | sed "s#\([^ ]*\)=#\"$DEVNAME \1\"=#g"`
        [[ $? -ne 0 ]] && $RETVAL=$?
        OUT="$OUT${OUT:+ }$DEVNAME: ${output%|*}"
        PERF="$PERF${output##*|}"
	fi
done

echo "$OUT|$PERF"

exit $RETVAL
