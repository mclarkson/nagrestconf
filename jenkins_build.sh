#!/bin/bash
set -e

# OS Version
if grep -qs 4\. /etc/redhat-release; then dist=.el4; fi
if grep -qs 5\. /etc/redhat-release; then dist=.el5; fi
if grep -qs 6\. /etc/redhat-release; then dist=.el6; fi
if grep -qs 7\. /etc/redhat-release; then dist=.el7; fi

# Release Version - edits the spec file
POINTRELEASE=`head -1 ./SOURCES/nagrestconf-1/debian.jessie/changelog | sed 's/^.*(1\.//;s/).*//'`
echo "Package Release: $RELEASE"
echo "New Version No.: $VERSION.${POINTRELEASE}"
sed "s/^%define *version.*/%define version ${VERSION}.${POINTRELEASE}/g" \
${BASE}/SPECS/${PKG} > ${BASE}/TMP/${PKG}

# Build
pushd SOURCES/
tar cvzf nagrestconf-1.tar.gz nagrestconf-1
popd
rpmbuild --define "_topdir `pwd`" --define "dist $dist" -ba SPECS/nagrestconf.spec
