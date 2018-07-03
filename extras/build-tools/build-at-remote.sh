#!/bin/bash

remote_host=${1}
remote_user=root
local_base_path=$(realpath $(dirname $(realpath $0))/../../)

if [ -z ${remote_host} ]; then
    echo 'usage: '$0' <host-address>'
    exit 1
fi

PORTNAME=pfSense-pkg-FauxAPI
STAGEDIR=$remote_user@$remote_host:/

# push the code to the remote FreeBSD system
rsync -rv --delete ${local_base_path}/${PORTNAME}/ ${STAGEDIR}/usr/ports/sysutils/${PORTNAME}

# do the build
ssh $remote_user@$remote_host "cd /usr/ports/sysutils/${PORTNAME}; make clean; make package"

# pull the .txz packages back
rsync --ignore-existing $remote_user@$remote_host:/usr/ports/sysutils/${PORTNAME}/work/pkg/*.txz ${local_base_path}/package

# re-roll the SHA256SUMS
cd ${local_base_path}/package
sha256sum pfSense-pkg-FauxAPI-*.txz > SHA256SUMS
