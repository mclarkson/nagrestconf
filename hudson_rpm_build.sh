#!/bin/bash

# Script to build rpms from hudson

#RELEASE="2"
PWD=`pwd`
BASE=$PWD

if [ -n "${BUILD_NUMBER}" ]; then
    POINTRELEASE="${BUILD_NUMBER}"
else
    POINTRELEASE="0"
fi

cd $BASE

for DIR in SOURCES SPECS; do 
        if [ ! -d "${DIR}" ]; then 
                echo "'${DIR}' directory not found in $PWD!" 1>&2 
                exit 1 
        fi 
done

echo ----
ls -lh
echo ----
cat POINTRELEASE
echo ----

. POINTRELEASE

rm -rf TMP
mkdir -p TMP BUILD RPMS SRPMS|| exit 1 
rm -rf TMP/* BUILD/* RPMS/* SRPMS/*|| exit 1

for BINARY in rpm rpmbuild; do 
        if [ ! -x "`which ${BINARY} 2> /dev/null`" ]; then 
                echo "'${BINARY}' binary not found!" 1>&2 
                exit 1 
        fi 
done

GPG_KEYNAME="n/a"

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

GRV=0

for PKG in `( cd SPECS; ls *.spec )`; do 
	echo
        echo package=$PKG

	if grep -qs "Name: *%" SPECS/${PKG}; then
		NAME=`grep -m 1 "^Name:" SPECS/${PKG} | sed "s/Name: *//g;s/{//g;s/}//g;s/%//g"`
		NAME=`grep -E -m1 "(%define|%global) *$NAME" SPECS/${PKG} | awk '{ print $NF; }'`
	else
		NAME=`grep -m 1 "^Name:" SPECS/${PKG} | sed "s/Name: *//g"`
	fi
	if grep -qs "Version: *%" SPECS/${PKG}; then
		VERSION=`grep -m 1 "^Version:" SPECS/${PKG} | sed "s/Version: *//g;s/{//g;s/}//g;s/%//g"`
		VERSION=`grep -E -m1 "(%define|%global) *$VERSION" SPECS/${PKG} | awk '{ print $NF; }'`
	else
		VERSION=`grep -m 1 "^Version:" SPECS/${PKG} | sed "s/Version: *//g"`
	fi
	if grep -qs "Release: *%" SPECS/${PKG}; then
		RELEASE=`grep -m 1 "^Release:" SPECS/${PKG} | sed "s/Release: *//g;s/{//g;s/}//g;s/%//g"`
		RELEASE=`grep -E -m1 "(%define|%global) *$RELEASE" SPECS/${PKG} | awk '{ print $NF; }'`
	else
		RELEASE=`grep -m 1 "^Release:" SPECS/${PKG} | sed "s/Release: *//g"`
	fi

	[[ -z $NAME ]] && {
		echo "Could not work out the package name from the spec file. Cannot continue"
		exit 2
	}

	echo "Package Name:    $NAME"

	[[ -z $VERSION ]] && {
		echo "Could not work out the version from the spec file. Cannot continue"
		exit 2
	}

	echo "Package Version: $VERSION"

	[[ -z $RELEASE ]] && {
		echo "Could not work out the release from the spec file. Cannot continue"
		exit 2
	}

    #SVN_REV=`svn info SOURCES | sed -n '/Revision:/ { s/Revision: //p }'`
    #
	#echo "Subversion Revision: $SVN_REV"
    #
    #POINTRELEASE=$SVN_REV

	echo "Package Release: $RELEASE"
	echo "New Version No.: $VERSION-${POINTRELEASE}"

    sed "s/^Release: .*/Release: ${POINTRELEASE}/g" \
    ${BASE}/SPECS/${PKG} > ${BASE}/TMP/${PKG}

    echo "Preparing sources for '${NAME}-${VERSION}'..."

	if [[ -d SOURCES/${NAME}-${VERSION} ]]; then
		echo "Tarring existing source directory."
        N="${NAME}-${VERSION}-${POINTRELEASE}"
        cp -a SOURCES/${NAME}-${VERSION} SOURCES/$N
		tar cvzf SOURCES/${N}.tar.gz -C SOURCES ${N} --exclude=.svn
	else
		echo "Unpacking source tarball."
		tar -C TMP/ -xvzf SOURCES/${NAME}-${VERSION}.tar.gz

		[[ -e TMP/${NAME}-${VERSION} ]] || {
			echo "Tar file did not unpack into '${NAME}-${VERSION}'"
			echo "The tar file is invalid. Cannot continue."
			exit 1
		}
	fi
	echo

#        cp -pR SOURCES/${NAME}-${VERSION}.tar.gz TMP/${NAME}-${VERSION}
#
#        find TMP/* -depth -type f -name '.DS_Store' -exec rm -rf \{} \; 
#        find TMP/* -depth -type d -name '.svn' -exec rm -rf \{} \; 
#        find TMP/* -depth -type f -name '#*#' -exec rm -rf \{} \; 
#        find TMP/* -depth -type f -name '*~' -exec rm -rf \{} \; 
#        find TMP/* -depth -type l -name '.#*' -exec rm -rf \{} \;
#
#        rm -f SOURCES/${NAME}-${VERSION}.tar.gz 
#        tar -czf TMP/${NAME}-${VERSION}.tar.gz -C TMP ${NAME}-${VERSION} || exit 1 
#        mv TMP/${NAME}-${VERSION}.tar.gz SOURCES/ || exit 1
#
#        rm -rf TMP/${NAME}-${VERSION}

    if [ -x "`which gpg 2> /dev/null`" -a ! -z "`gpg --list-keys \"${GPG_KEYNAME}\"`" ]; then 
        echo "Building signed package for '${NAME}-${VERSION}'..." 
        echo 
        echo "Using key '${GPG_KEYNAME}' to sign packages..." 
        rpmbuild -ba --sign --rcfile ${BASE}/TMP/rpmrc ${BASE}/TMP/${PKG} 
    else 
        echo "Building un-signed package for '${NAME}-${VERSION}'..." 
        echo 
        # RH5 -> rpmbuild -ba --rcfile ${BASE}/TMP/rpmrc ${BASE}/TMP/${PKG} 
        # RH6 ->
        if grep -qs 4\. /etc/redhat-release; then dist=.el4; fi
        if grep -qs 5\. /etc/redhat-release; then dist=.el5; fi
        if grep -qs 6\. /etc/redhat-release; then dist=.el6; fi
        rpmbuild --define "_topdir ${BASE}" --define "dist $dist" -ba --rcfile ${BASE}/TMP/rpmrc ${BASE}/TMP/${PKG} 
    fi

    RV=$?

    if [ ${RV} -ne 0 ]; then 
        GRV=$(expr ${GRV} + 1) 
        echo 1>&2 
        echo "Package '${NAME}-${VERSION}' failed to build!" 1>&2 
        echo 1>&2 
    else 
        echo 
        echo "${VERSION}.${POINTRELEASE}-${RELEASE}" \
            > ${BASE}/TMP/version-release.txt
        echo "Package '${NAME}-${VERSION}.${POINTRELEASE}' was built successfully!" 
    fi

    #rm -rf BUILD/${NAME}-${VERSION} SOURCES/${NAME}-${VERSION}.tar.gz 

    echo "Cleaning SOURCES directory..."
    find SOURCES/$NAME-$VERSION* ! -name "*.tar.gz" -exec rm -rf {} \;

done

if [ ${GRV} -ne 0 ]; then 
        echo "Ouch, ${GRV} package build(s) failed - please examine!" 1>&2 
        exit ${GRV} 
fi

#scp -i /var/lib/hudson/TEMPSSHCRED_BYMC $BASE/RPMS/noarch/*.rpm mclarkson@frs.sourceforge.net:/home/frs/project/nagrestconf/Centos_5/

exit 0
