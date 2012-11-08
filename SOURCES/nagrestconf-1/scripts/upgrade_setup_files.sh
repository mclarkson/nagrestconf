#!/bin/bash

# For WWWUSER and NAG_OBJ_DIR
. /etc/nagrestconf/nagctl.conf

append_commas()
{
    # uses globals

    OLDFILE="$FILE.old"
    mv $FILE $OLDFILE
    :>$FILE
    numcommaswanted=$((NUMCOLS-1))
    while read line; do
        numcommas=`echo -n "$line" | sed "s/[^,]//g" | wc -c`
        if [[ $numcommas != $numcommaswanted ]]; then
            a=$(($numcommaswanted-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE
}

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_contacts.setup`; do
    NUMCOLS=24
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_hosts.setup`; do
    NUMCOLS=46
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_servicetemplates.setup`; do
    NUMCOLS=43
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_hosttemplates.setup`; do
    NUMCOLS=39
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_services*.setup`; do
    NUMCOLS=46
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_hostgroups.setup`; do
    NUMCOLS=8
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_servicegroups.setup`; do
    NUMCOLS=8
    append_commas
done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_timeperiods.setup`; do
    NUMCOLS=6
    append_commas
done

