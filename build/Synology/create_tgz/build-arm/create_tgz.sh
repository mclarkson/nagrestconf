#!/bin/bash

# ---------------------------------------------------------------------------
# CHANGE AS NECESSARY
# ---------------------------------------------------------------------------

SHELL="/bin/bash"

# ---------------------------------------------------------------------------
# DON'T CHANGE ANYTHING BELOW
# ---------------------------------------------------------------------------

[[ -z $1 || ! -e $1 ]] && {
    echo "Usage $0 conffile"
    exit 0
}

source $1

# ---------------------------------------------------------------------------
main()
# ---------------------------------------------------------------------------
{
    trap exit_handler EXIT

    init
    sanity_checks

    [[ ! -e $ROOTBASEDIR ]] && create_base_chroot

    copy_base_chroot
    setup_chroot
    create_tarball
}

# ---------------------------------------------------------------------------
init()
# ---------------------------------------------------------------------------
{
    # E.g. /root/synology/ds412+
    ROOTDIR="$ROOT/$ROOTALL/$ROOTNAME"

    # E.g. /root/synology/ds412+/bin
    ROOTBINDIR="$ROOTDIR/$ROOTBINNAME"

    # E.g. /root/synology/ds412+/plugins
    ROOTPLUGINSDIR="$ROOTDIR/$ROOTPLUGINSNAME"

    # E.g. /root/synology/ds412+/wheezy
    ROOTOSNAMEDIR="$ROOTDIR/$ROOTOSNAME"

    # E.g. /root/synology/ds412+/wheezy/tarball
    ROOTTARBALLDIR="$ROOTDIR/$ROOTOSNAME/tarball"

    # E.g. /root/synology/ds412+/wheezy/debs
    ROOTDEBDIR="$ROOTOSNAMEDIR/$ROOTDEBNAME"

    # E.g. /root/synology/ds412+/wheezy/base
    ROOTBASEDIR="$ROOTOSNAMEDIR/$ROOTBASENAME"

    # E.g. /root/synology/ds412+/wheezy/nagios-chroot
    ROOTNAGIOSDIR="$ROOTOSNAMEDIR/$ROOTNAGIOSNAME"
}

# ---------------------------------------------------------------------------
exit_handler()
# ---------------------------------------------------------------------------
{
    [[ ! -e $ROOTNAGIOSDIR ]] && exit 0
    cd $ROOTNAGIOSDIR
    [[ ! -e dev ]] && exit 0
    umount dev proc sys 2>/dev/null
}

# ---------------------------------------------------------------------------
create_base_chroot()
# ---------------------------------------------------------------------------
{
    [[ -e $ROOTBASEDIR ]] && {
        echo "File exists, $ROOTBASEDIR/. Must be moved/deleted first."
        exit 1
    }
    mkdir -p $ROOTOSNAMEDIR
    cd $ROOTOSNAMEDIR
    debootstrap $CHROOTOPTS $ROOTOSNAME $ROOTBASENAME \
        "http://ftp.uk.debian.org/debian"
}

# ---------------------------------------------------------------------------
copy_base_chroot()
# ---------------------------------------------------------------------------
{
    [[ -e $ROOTNAGIOSDIR ]] && {
        echo "File exists, $ROOTNAGIOSDIR/. Will not overwrite."
        exit 1
    }
    mkdir $ROOTNAGIOSDIR
    echo "Copying $ROOTBASEDIR/ to $ROOTNAGIOSDIR/"
    rsync -aHS $ROOTBASEDIR/ $ROOTNAGIOSDIR/
}

# ---------------------------------------------------------------------------
run()
# ---------------------------------------------------------------------------
{
    set +H
    echo "$1" | chroot $ROOTNAGIOSDIR
    [[ $? -ne 0 ]] && {
        echo -e "\n ---- ERROR ----\n"
        exit 1
    }
    set -H
}

# ---------------------------------------------------------------------------
setup_chroot()
# ---------------------------------------------------------------------------
{
    export LC_ALL=C

    cd $ROOTNAGIOSDIR
    mount --bind /dev dev
    mount --bind /proc proc
    mount --bind /sys sys
    cp $ROOTDEBDIR/*.deb .

    # System install

    # Seed created with
    #  debconf-get-selections | grep -e locales -e nagios3-cgi 
    cat >$ROOTNAGIOSDIR/seed <<EnD
nagios3-cgi nagios3/adminpassword-repeat    password
nagios3-cgi nagios3/adminpassword   password
nagios3-cgi nagios3/nagios1-in-apacheconf   boolean false
nagios3-cgi nagios3/adminpassword-mismatch  note
nagios3-cgi nagios3/httpd   multiselect apache2
locales locales/locales_to_be_generated multiselect en_GB ISO-8859-1, en_GB.ISO-8859-15 ISO-8859-15, en_GB.UTF-8 UTF-8
locales locales/default_environment_locale  select  en_GB.UTF-8
EnD
    run "apt-get update && apt-get -qy upgrade"
    run "apt-get -qy install debconf-utils"
    run "debconf-set-selections /seed"
    rm -f $ROOTNAGIOSDIR/seed

    run "apt-get -qy install gdebi-core bc sudo locales"

    run "mv /usr/sbin/invoke-rc.d /root"
    run "echo -e '#!/bin/bash\nexit 0' >/usr/sbin/invoke-rc.d"
    run "chmod +x /usr/sbin/invoke-rc.d"

    run "mv /sbin/start-stop-daemon /root"
    run "echo -e '#!/bin/bash\nexit 0' >/sbin/start-stop-daemon"
    run "chmod +x /sbin/start-stop-daemon"

    run "gdebi --apt-line nagrestconf_* | tail -2 | head -1 | xargs apt-get -qy install"

    # Install other packages for create_tgz.d/
    cd $ROOT
    for i in create_tgz.d/*.sh; do
      source $i
    done
    cd $ROOTNAGIOSDIR

    run "dpkg -i *.deb"
    run "nagrestconf_install -a"
    run "slc_configure --folder=local"
    # Fixed in nagrestconf_install:
    #run "mv /var/spool/cron/root /var/spool/cron/crontabs/"
    #run "chmod 0600 /var/spool/cron/crontabs/root"

    # Change 80 to 8888 and 443 to 4443
    run "sed -i 's/80/8888/g;s/443/4443/g' /etc/apache2/ports.conf"
    # Change access permissions
    #run "sed -i 's/\(Allow from \).*/\1all/' /etc/apache2/conf.d/nagrestconf.conf"

    # Wheezy
    #cat >$ROOTNAGIOSDIR/etc/apache2/conf.d/nagrestconf.conf <<EnD
    # Jessie
    cat >$ROOTNAGIOSDIR/etc/apache2/conf-available/nagrestconf.conf <<EnD
Alias /nagrestconf "/usr/share/nagrestconf/htdocs/nagrestconf"

<Directory /usr/share/nagrestconf/htdocs/nagrestconf/>

  #SSLRequireSSL

  # Only allow from the local host
  Order deny,allow
  Deny from all
  Allow from all

  AllowOverride All

  # Require authentication
  AuthName "Nagrestconf Access"
  AuthType Basic
  AuthUserFile /etc/nagios3/nagrestconf.users
  Require valid-user

</Directory>
EnD
    # Change URL (add :8888)
    run "sed -i 's/\(^.*127.0.0.1\)/\1:8888/' /etc/nagrestconf/nagrestconf.ini"

    # Nagios.cfg 
    run "sed -i 's/check_external_commands=0/check_external_commands=1/g' /etc/nagios3/nagios.cfg"
    run "sed -i 's/enable_embedded_perl=1/enable_embedded_perl=0/g' /etc/nagios3/nagios.cfg"
    run "sed -i 's/use_syslog=1/use_syslog=0/g' /etc/nagios3/nagios.cfg"
    run "sed -i 's/\(broker_module=.*npcdmod\.o.*\)/#\1/' /etc/nagios3/nagios.cfg"
    run "sed -i 's/\(^process_performance_data=\)0.*/\11/' /etc/nagios3/nagios.cfg"
    run "sed -i 's/\(^RUN=\).*/\1\"yes\"/' /etc/default/npcd"
    run "echo 'broker_module=/usr/lib/pnp4nagios/npcdmod.o config_file=/etc/pnp4nagios/npcd.cfg' >>/etc/nagios3/nagios.cfg"
    run "cp /usr/share/pnp4nagios/html/templates.dist/default.php /usr/share/pnp4nagios/html/templates.dist/check_swap_activity.php"
    # Enable html description
    # Change escape_html_tags=0
    run "sed -i 's/escape_html_tags=1/escape_html_tags=0/g' /etc/nagios3/cgi.cfg"
    # Relax perms
    run "chmod 770 /var/lib/nagios3/rw/"

    # Add custom plugins directory
    run "mkdir -p /usr/local/nagios/plugins"
    run "echo -e '\n# Custom plugins\n\$USER5\$=/usr/local/nagios/plugins' >>/etc/nagios3/resource.cfg"

    # nagios3 password
    run "htpasswd -bc /etc/nagios3/htpasswd.users nagiosadmin nagiosadmin"

    # Add check_any plugin in $USER5$ .. and utils.pm|sh
    #echo -e '#!/bin/bash\n/usr/lib/nagios/plugins/$@' \
    #  >>usr/local/nagios/plugins/check_any
    #chmod +x usr/local/nagios/plugins/check_any
    #cp /usr/lib/nagios/plugins/utils.* /usr/local/nagios/plugins/

    # Copy plugins and mailsender
    cp -a $ROOTPLUGINSDIR/* $ROOTNAGIOSDIR/usr/local/nagios/plugins/
    cp $ROOTBINDIR/mailsender $ROOTNAGIOSDIR/usr/local/bin
    chmod 755 $ROOTNAGIOSDIR/usr/local/bin/mailsender

    # Add sudoers entry for sudo1 sudo5
    echo -e '%nagios ALL = NOPASSWD: /usr/local/nagios/plugins/*, /usr/lib/nagios/plugins/*\n' >> $ROOTNAGIOSDIR/etc/sudoers

    # For disk space check
    run "mkdir volume1"

    # Cleanup
    run "apt-get clean"
    run "rm -rf /usr/share/doc/*"
    run "rm -rf /usr/share/man/*"
    run "rm -rf /var/lib/apt/lists/*"
    run "mv /root/start-stop-daemon /sbin/"
    run "mv /root/invoke-rc.d /usr/sbin/"
    run ">etc/resolv.conf"
    run ">root/.bash_history"
    run "rm -f nagrestconf*"

    # Ash redirection
    run "ln -s /bin/bash /bin/ash"
}

# ---------------------------------------------------------------------------
create_tarball()
# ---------------------------------------------------------------------------
{
    cd $ROOTNAGIOSDIR
    umount dev proc sys 2>/dev/null
    mkdir -p $ROOTTARBALLDIR
    cd $ROOTOSNAMEDIR
    cat >application.cfg <<EnD
text = Synagios Monitoring
description = Synagios - monitoring with your NAS.
icon_16 = images/meerkat_16.png
icon_24 = images/meerkat_24.png
icon_32 = images/meerkat_32.png
icon_48 = images/meerkat_48.png
icon_64 = images/meerkat_64.png
type = url
protocol = http
port = 8888
path = /
EnD
    cat >config <<EnD
{
    ".url": {
        "com.Synagios.Synagios": {
            "type": "legacy",
            "allUsers": false,
            "title": "Synagios",
            "desc": "Synagios - monitoring with your NAS.",
            "icon": "images/meerkat_{0}.png",
            "url": "3rdparty/Synagios/redirect.cgi"
        }
    }
}
EnD
    cat >redirect.cgi <<EnD
#!/bin/sh

SYNAGIOS_PORT=8888

echo "Status: 307"
echo -e "Location: http://\${SERVER_ADDR}:\${SYNAGIOS_PORT}/nagrestconf\r\n\r\n"

EnD

    cp -a "$ROOT/$ROOTALL/images" .
    chmod 644 "$ROOT/$ROOTALL/images"/*
    chmod 755 redirect.cgi
    tar czf $ROOTTARBALLDIR/nagios-chroot.tgz $ROOTNAGIOSNAME \
        application.cfg config redirect.cgi images/

    rm -rf application.cfg config redirect.cgi images

    echo "Tarball created in $ROOTTARBALLDIR/nagios-chroot.tgz"
}

# ---------------------------------------------------------------------------
sanity_checks()
# ---------------------------------------------------------------------------
{
    [[ ! -e $ROOTDIR ]] && mkdir -p $ROOTDIR
    [[ ! -e $ROOTDEBDIR ]] && {
        mkdir -p $ROOTDEBDIR
        echo "Copy nagrestconf debian packages into $ROOTDEBDIR."
        exit 1
    }

    found=`find $ROOTDEBDIR -maxdepth 1 -name "*.deb" 2>/dev/null | wc -l`
    NFILES=$found
    [[ $found -ne $NUM_DEBS ]] && {
        echo "Expected $NUM_DEBS debian packages, found $NFILES."
        exit 1
    }
 
    [[ ! -e $ROOTPLUGINSDIR ]] && {
        mkdir -p $ROOTPLUGINSDIR
        echo "Copy nagios plugins into $ROOTPLUGINSDIR"
        exit 1
    }

    [[ ! -e $ROOTBINDIR/mailsender ]] && {
        mkdir -p $ROOTBINDIR
        echo "Copy mailsender binary into $ROOTBINDIR"
        exit 1
    }
}

main

