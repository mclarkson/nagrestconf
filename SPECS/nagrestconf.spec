%define name nagrestconf
%define version 1
%define php php
%if "%{?dist}" == ".el5"
%define php php53
%endif
# The following line may be required
#%define debug_package %{nil}

Summary: Nagios REST configuration tools.
# Don't replace the next line with %{name}, hudson build script needs it
Name: nagrestconf
Version: %{version}
Release: 1
License: GPL
Group: Applications/System
Source: nagrestconf-%{version}.tar.gz
Requires: bash, grep, nagios >= 3, procmail, sed, gawk, grep, %php >= 5.3, httpd, mod_ssl, subversion
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

# Post Install
%post

# Pre Uninstall
%preun

# Post Uninstall
%postun

%install

echo "php requires = %{php}"

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
install -D -m 755 scripts/dcc_configure ${RPM_BUILD_ROOT}%_bindir/dcc_configure
install -D -m 755 scripts/slc_configure ${RPM_BUILD_ROOT}%_bindir/slc_configure
install -D -m 755 scripts/upgrade_setup_files.sh ${RPM_BUILD_ROOT}%_bindir/upgrade_setup_files.sh
install -D -m 755 scripts/update_nagios ${RPM_BUILD_ROOT}%_bindir/update_nagios
install -D -m 755 scripts/auto_reschedule_nagios_check ${RPM_BUILD_ROOT}%_bindir/auto_reschedule_nagios_check
install -D -m 755 scripts/nagrestconf_install ${RPM_BUILD_ROOT}%_bindir/nagrestconf_install

# PHP Directories
install -d -m 755 ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/
install -d -m 755 ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/plugins/
install -d -m 755 ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/plugins-enabled/
cp -r nagrestconf ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/
cp -r rest ${RPM_BUILD_ROOT}/usr/share/nagrestconf/htdocs/

%files
%defattr(755,root,root,755)
%_bindir
%defattr(644,root,root,755)
/usr/share/nagrestconf/htdocs/
%doc doc/initial-config doc/initial-config.dcc doc/bulk-loading README doc/README.html
%config(noreplace) /etc/httpd/conf.d/rest.conf
%config(noreplace) /etc/httpd/conf.d/nagrestconf.conf
%config(noreplace) /etc/nagrestconf/nagrestconf.ini
%config(noreplace) /etc/nagrestconf/restart_nagios.conf
%config(noreplace) /etc/nagrestconf/csv2nag.conf
%config(noreplace) /etc/nagrestconf/nagctl.conf

%clean
%{__rm} -rf %{buildroot}

%changelog
* Fri Jan 4 2013 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Moved set code out to nagrestconf_install script.

* Wed Nov 7 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Added schedulehostdowntime and delhostdowntime.

* Wed Nov 7 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Added nagios objects: service dependencies, host dependencies,
  service escalations, host escalations, extra service info and
  extra host info.

* Mon Nov 5 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Added more nagios directives to the REST api. Host/Service groups and timeperiods now complete.

* Fri Nov 2 2012 Mark Clarkson <mark.clarkson@smorg.co.uk>
- Added more nagios directives to the REST api. Host templates, services and servicesets now complete.

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
