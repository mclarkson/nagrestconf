#!/bin/bash

OK=0
ERROR=1

n=1
[[ -n $2 ]] && n=2

if [[ -n $1 ]]; then
    filename="$1"
else
    echo '["Internal error: csv2json filename [headerline]"]'
    exit $ERROR
fi

[[ -d "$filename" || ! -r "$filename" ]] && {
    echo '["Could not find file, \"'"${filename##*/}"'\"."]'
    exit $ERROR
}

sed -i 's/\r//' "$filename"
echo "["
sed -n $n',$ {
s/"//g;
s/,[\t ]*/,/g;
s/[\t ]*,/,/g;
s/\([^,]*\)/"\1"/g;
s/^/[/;s/$/],/;
$ {s/,$//}; 
p;}' "$filename"
echo "]"

exit $OK
