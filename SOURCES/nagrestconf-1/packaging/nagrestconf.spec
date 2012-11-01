%define name nagrestconf
%define version 1
# The following line may be required
#%define debug_package %{nil}

Summary: Nagios REST configuration tools.
Name: nagrestconf
Version: 1
Release: 1
License: GPL
Group: Applications/System
Source: nagrestconf-1.tar.gz
Requires: bash, grep, nagios >= 3, procmail, sed, gawk, grep, php53, httpd, mod_ssl, subversion
# PreReq: sh-utils
BuildArch: noarch
BuildRoot: %{_builddir}/%{name}-%{version}/tmp
Packager: Mark Clarkson
Vendor: Smorg

%description
Configuration tools for Nagios. Includes csv2nag, nagctl, the REST interface and the web configurator GUI.

%prep
%setup -q

# Pre Install
%pre
if [ "$1" = "1" ]; then
    # Perform tasks to prepare for the initial installation
    usermod -a -G nagios apache
    usermod -a -G nagiocmd apache

    # Add crontab
    %{__sed} -i '/nagios_restart_request/d' /var/spool/cron/root
    %{__cat} >>/var/spool/cron/root <<EnD
* * * * * /usr/bin/test -e /tmp/nagios_restart_request && ( /bin/rm /tmp/nagios_restart_request; /usr/bin/restart_nagios; )
EnD
    touch /var/spool/cron/

    # Add sudoers entry
    %{__sed} -i '/\/usr\/bin\/csv2nag -y all/d' /etc/sudoers
    %{__sed} -i '/nagios.*requiretty/d' /etc/sudoers
    %{__cat} >>/etc/sudoers <<EnD
Defaults:%nagios !requiretty
%nagios ALL = NOPASSWD: /usr/sbin/nagios -v *, /usr/bin/csv2nag -y all

EnD

elif [ "$1" = "2" ]; then
  # Perform whatever maintenance must occur before the upgrade begins
  :
fi

:

# Post Install
%post

    # Add the line slc_configure wants

    if ! grep -qs "<SERVICE_LINE_CFG_ENTRY>" /etc/nagios/nagios.cfg; then
        %{__sed} -i '/SERVICE_LINE_CFG_ENTRY/d' /etc/nagios/nagios.cfg
        echo "## Next line added by nagrestconf"  >>/etc/nagios/nagios.cfg
        echo "<SERVICE_LINE_CFG_ENTRY>" >>/etc/nagios/nagios.cfg

        # Comment out cfg_ lines in nagios.cfg

        cp /etc/nagios/nagios.cfg /etc/nagios/nagios.cfg.rpmsave

        %{__sed} -i \
            's/^[[:space:]]*cfg_/# - commented out by nagrestconf - cfg_/' \
            /etc/nagios/nagios.cfg

        slc_configure --folder=local
    fi

    # Restart the webserver so the new configs are picked up

    /sbin/service httpd restart

    echo "Nagrestconf has been configured for http://127.0.0.1/nagrestconf/."

# Pre Uninstall
%preun

if [ "$1" = 0 ] ; then
    %{__sed} -i '/nagios_restart_request/d' /var/spool/cron/root
    touch /var/spool/cron
    %{__sed} -i '/\/usr\/bin\/csv2nag -y all/d' /etc/sudoers
fi

:

# Post Uninstall
%postun

%install

[ "$RPM_BUILD_ROOT" != "/" ] && %{__rm} -rf %{buildroot}

# Config
install -d -m 755 ${RPM_BUILD_ROOT}/%_sysconfdir/
#cp -r etc/httpd ${RPM_BUILD_ROOT}/%_sysconfdir/
install -D -m 640 etc/httpd/conf.d/nagrestconf.conf ${RPM_BUILD_ROOT}/%_sysconfdir/httpd/conf.d/nagrestconf.conf
install -D -m 640 etc/httpd/conf.d/rest.conf ${RPM_BUILD_ROOT}/%_sysconfdir/httpd/conf.d/rest.conf
#cp -r etc/nagrestconf ${RPM_BUILD_ROOT}%_sysconfdir/
install -D -m 640 etc/nagrestconf/csv2nag.conf ${RPM_BUILD_ROOT}/%_sysconfdir/nagrestconf/csv2nag.conf
install -D -m 640 etc/nagrestconf/nagctl.conf ${RPM_BUILD_ROOT}/%_sysconfdir/nagrestconf/nagctl.conf
install -D -m 640 etc/nagrestconf/nagrestconf.ini ${RPM_BUILD_ROOT}/%_sysconfdir/nagrestconf/nagrestconf.ini
install -D -m 640 etc/nagrestconf/restart_nagios.conf ${RPM_BUILD_ROOT}/%_sysconfdir/nagrestconf/restart_nagios.conf

# Scripts
install -D -m 755 scripts/csv2nag ${RPM_BUILD_ROOT}%_bindir/csv2nag
install -D -m 755 scripts/nagctl ${RPM_BUILD_ROOT}%_bindir/nagctl
install -D -m 755 scripts/restart_nagios ${RPM_BUILD_ROOT}%_bindir/restart_nagios
install -D -m 755 scripts/slc_configure ${RPM_BUILD_ROOT}%_bindir/slc_configure
install -D -m 755 scripts/upgrade_setup_files.sh ${RPM_BUILD_ROOT}%_bindir/upgrade_setup_files.sh

# PHP Directories
install -d -m 755 ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/
cp -r nagrestconf ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/
cp -r rest ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/

%files
%defattr(755,root,root,755)
%_bindir
%defattr(644,root,root,755)
/usr/share/nagrestconf/htdocs/
%doc doc/initial-config doc/bulk-loading README doc/README.html
%config(noreplace) /etc/httpd/conf.d/rest.conf
%config(noreplace) /etc/httpd/conf.d/nagrestconf.conf
%config(noreplace) /etc/nagrestconf/nagrestconf.ini
%config(noreplace) /etc/nagrestconf/restart_nagios.conf
%config(noreplace) /etc/nagrestconf/csv2nag.conf
%config(noreplace) /etc/nagrestconf/nagctl.conf

%clean
%{__rm} -rf %{buildroot}

%changelog
* Thu Nov 1 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Added more nagios directives to the REST api. Host and Contacts now complete.

* Fri Jul 6 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Lots of additional configuration tabs, fixes and extra checks.

* Wed May 16 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Speech marks fix. Both types can now be used in service command input boxes.

* Wed May 16 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Bug fixes and UI enhancements. Added disable host and services feature.

* Sun May 13 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Added nagrestconf alpha release.

* Tue Sep 6 2011 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Misc fixes and operates as dcc or slc via DCC variable.

* Tue Oct 5 2010 Mark Clarkson <mark.clarkson@smorg.co.uk>
- First packaged version
