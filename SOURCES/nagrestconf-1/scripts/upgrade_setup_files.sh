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
        if [[ $numcommas != 24 ]]; then
            a=$((25-$numcommas))
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
        if [[ $numcommas != 46 ]]; then
            a=$((47-$numcommas))
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
        if [[ $numcommas != 39 ]]; then
            a=$((40-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE

done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_services*.setup`; do 

    OLDFILE="$FILE.old"
    mv $FILE $OLDFILE
    :>$FILE
    while read line; do
        numcommas=`echo "$line" | sed "s/[^,]//g" | wc -c`
        if [[ $numcommas != 46 ]]; then
            a=$((47-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE

done

for FILE in `ls $NAG_OBJ_DIR/*/setup/*_hostgroups.setup`; do 

    OLDFILE="$FILE.old"
    mv $FILE $OLDFILE
    :>$FILE
    while read line; do
        numcommas=`echo "$line" | sed "s/[^,]//g" | wc -c`
        if [[ $numcommas != 7 ]]; then
            a=$((8-$numcommas))
            echo "$line`printf %${a}s | tr " " ,`" >>$FILE
        else
            echo "$line" >>$FILE
        fi
    done <$OLDFILE
    chown $WWWUSER: $FILE

done

