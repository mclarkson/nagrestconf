for i in t[0-9]*.sh
do
    DESC=`sed -n 's/ *# *DESC: *\(.*\)/\1/p' $i`
    ./$i >$i.output
    diff -q $i.output $i.answer
    if [[ $? -eq 0 ]];then echo "PASSED $i [$DESC]"
        else echo "$i FAILED"; fi
done
