
[[ $ROOTOSNAME == "jessie" ]] && {

 :

# To build package
#
#cat >>$ROOTNAGIOSDIR/etc/apt/sources.list <<EnD
#deb-src http://ftp.debian.org/debian testing main
#EnD
#
#apt-get update
#apt-get install devscripts build-essential dpkg-dev
#apt-get install autoconf autotools-dev uuid-dev doxygen libtool libsctp-dev
#apt-get source libapr1
#cd apr-1.5.1
#debuild -us -uc

}
