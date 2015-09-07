#!/bin/bash

OUTPUT=$(${0%/*}/check_jmx $@)
EXIT_STATUS=$?
VALUE="$(echo $OUTPUT | sed 's/.*{\(.*\)}.*/\1;/' | sed 's/;/; /g')"
echo "$OUTPUT | $VALUE"

exit $EXIT_STATUS
