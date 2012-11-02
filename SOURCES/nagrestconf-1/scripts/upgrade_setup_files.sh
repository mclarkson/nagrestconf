#!/bin/bash

# For WWWUSER and NAG_OBJ_DIR
. /etc/nagrestconf/nagctl.conf

# Add columns for CONTACTS

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_contacts.setup`; do 

    OLDFILE="$FILE.old"
    mv $FILE $OLDFILE
    :>$FILE
    while read line; do
        numcommas=`echo "$line" | sed "s/[^,]//g" | wc -c`
        if [[ $numcommas != 23 ]]; then
            a=$((24-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE

done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_hosts.setup`; do 

    OLDFILE="$FILE.old"
    mv $FILE $OLDFILE
    :>$FILE
    while read line; do
        numcommas=`echo "$line" | sed "s/[^,]//g" | wc -c`
        if [[ $numcommas != 44 ]]; then
            a=$((45-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE

done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_hosttemplates.setup`; do 

    OLDFILE="$FILE.old"
    mv $FILE $OLDFILE
    :>$FILE
    while read line; do
        numcommas=`echo "$line" | sed "s/[^,]//g" | wc -c`
        if [[ $numcommas != 37 ]]; then
            a=$((38-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE

done

