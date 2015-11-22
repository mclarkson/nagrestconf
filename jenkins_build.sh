#!/bin/bash
set -e

# OS Version
if grep -qs 4\. /etc/redhat-release; then dist=.el4; fi
if grep -qs 5\. /etc/redhat-release; then dist=.el5; fi
if grep -qs 6\. /etc/redhat-release; then dist=.el6; fi
if grep -qs 7\. /etc/redhat-release; then dist=.el7; fi

BASE=$PWD

# Release Version - edits the spec file
POINTRELEASE=`head -1 ./SOURCES/nagrestconf-1/debian.jessie/changelog | sed 's/^.*(1\.//;s/).*//'`
echo "New Version No.: 1.${POINTRELEASE}"
sed "s/^%define *version.*/%define version 1.${POINTRELEASE}/g" SPECS/nagrestconf.spec > TMP/nagrestconf.spec

SYSMACROS="`rpm --showrc | grep macrofiles | \
            sed 's/^macrofiles[ ]*:[ ]*//ig'`"
cat > TMP/rpmrc <<EOM 
include: /usr/lib/rpm/rpmrc 
macrofiles: ${SYSMACROS}:${BASE}/TMP/rpmmacros 
EOM

cat > TMP/rpmmacros <<EOM 
%_topdir ${BASE} 
%_tmppath ${BASE}/TMP 
%_signature gpg 
%_gpg_name ${GPG_KEYNAME}
%__os_install_post %{nil} 
EOM

# Build
pushd SOURCES/
tar cvzf nagrestconf-1.tar.gz nagrestconf-1
popd
rpmbuild --define "_topdir `pwd`" --define "dist $dist" --rcfile $BASE/TMP/rpmrc -ba $BASE/TMP/nagrestconf.spec
