
[[ $ROOTOSNAME == "jessie" ]] && {

# Add the testing repo, where pnp4nagios is, but
# prefer packages from stable (jessie)

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

sed -i 's#AuthUserFile.*#AuthUserFile /etc/nagios3/htpasswd.users#' \
    $ROOTNAGIOSDIR/etc/apache2/conf-available/pnp4nagios.conf
}

