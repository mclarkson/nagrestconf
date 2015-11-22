#!/bin/bash
set -e
pushd SOURCES/
tar cvzf nagrestconf-1.tar.gz nagrestconf-1
popd
rpmbuild --define "_topdir `pwd`" -bb SPECS/nagrestconf.spec
