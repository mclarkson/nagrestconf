#!/bin/bash
#
# DESC: nagctl - items_existsN

sed 's/^ *source /#source /' ../nagctl >/tmp/nagctl.$$
sed -i 's/^ *main /#main /' /tmp/nagctl.$$
sed -i 's/^ *#TeSt: //' /tmp/nagctl.$$

. /tmp/nagctl.$$

rm /tmp/nagctl.$$

cat > /tmp/test_data.setup <<EnD
blob1,blob2,blob3,blob4,blob5,blob6,blob7,blob8,blob9
search1,search2,search3,search4,search5,search6,search7,search8,search9
search 1,search 2,search 3,search 4,search 5,search 6,search 7,search 8,search 9
search1,blobbob,search3,search4,search5,search6,search7,search8,search9
search1,search2,blobbob9,search4,search5,search6,search7,search8,search9
1,2,3,4,5,6,7,8,9
1,,3,4,,6,7,8,9
didi1,didi2,didi3,didi4,didi5,didi6,didi7,didi8,didi9
EnD

gfile_ncols[0]="9"
gservice="/tmp/test"
gfilename[0]="data"

if item_existsN "1" 0 "search1"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "3" 0 "blobbob9"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "9" 0 "didi9"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1 3" 0 "search1,search3"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1 3" 0 "search1,search3"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1 2 3 4 5" 0 "1,2,3,4,5"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1 3 5 7 9" 0 "1,3,5,7,9"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1 3 5 7 9" 0 "1,4,5,7,9"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1" 0 "search 1"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1 3" 0 "search 1,search 3"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "3 4 8" 0 "search 3,search 4,search 8"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "3 4 8" 0 "search 3,search4,search 8"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "9" 0 "didi8"; then echo "FOUND"; else echo "NOT FOUND"; fi

if item_existsN "1" 0 "search"; then echo "FOUND"; else echo "NOT FOUND"; fi

rm /tmp/test_data.setup

# Should produce
cat >`basename $0`.answer <<EnD
query is '^search1,.*,.*,.*,.*,.*,.*,.*,.*'
FOUND
query is '.*,.*,blobbob9,.*,.*,.*,.*,.*,.*'
FOUND
query is '.*,.*,.*,.*,.*,.*,.*,.*,didi9'
FOUND
query is '^search1,.*,search3,.*,.*,.*,.*,.*,.*'
FOUND
query is '^search1,.*,search3,.*,.*,.*,.*,.*,.*'
FOUND
query is '^1,2,3,4,5,.*,.*,.*,.*'
FOUND
query is '^1,.*,3,.*,5,.*,7,.*,9'
FOUND
query is '^1,.*,4,.*,5,.*,7,.*,9'
NOT FOUND
query is '^search 1,.*,.*,.*,.*,.*,.*,.*,.*'
FOUND
query is '^search 1,.*,search 3,.*,.*,.*,.*,.*,.*'
FOUND
query is '.*,.*,search 3,search 4,.*,.*,.*,search 8,.*'
FOUND
query is '.*,.*,search 3,search4,.*,.*,.*,search 8,.*'
NOT FOUND
query is '.*,.*,.*,.*,.*,.*,.*,.*,didi8'
NOT FOUND
query is '^search,.*,.*,.*,.*,.*,.*,.*,.*'
NOT FOUND
EnD
