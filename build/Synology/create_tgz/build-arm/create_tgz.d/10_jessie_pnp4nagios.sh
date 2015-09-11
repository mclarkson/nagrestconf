
[[ $ROOTOSNAME == "jessie" ]] && {

cat >>$ROOTNAGIOSDIR/etc/apt/sources.list <<EnD
deb http://ftp.uk.debian.org/debian testing main
EnD

cat >$ROOTNAGIOSDIR/etc/apt/preferences.d/jessie.pref <<EnD
Package: *
Pin: release a=stable
Pin-Priority: 995
EnD

cat >$ROOTNAGIOSDIR/etc/apt/preferences.d/testing.pref <<EnD
Package: *
Pin: release a=testing
Pin-Priority: 750
EnD

run "apt-get update"
run "apt-get install -qy pnp4nagios"

}

