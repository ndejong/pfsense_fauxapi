#!/bin/bash

remote_host=${1}
remote_user=root

if [ -z ${remote_host} ]; then
    echo 'usage: '$0' <host-address>'
    exit 1
fi

PORTNAME=pfSense-pkg-FauxAPI
PREFIX=usr/local
DATADIR=${PREFIX}/share/${PORTNAME}

ssh $remote_user@$remote_host "/usr/local/bin/php -f /etc/rc.packages ${PORTNAME} DEINSTALL"

ssh $remote_user@$remote_host "rm -Rf /${DATADIR}"
ssh $remote_user@$remote_host "rm -Rf /${PREFIX}/pkg/fauxapi.xml"
ssh $remote_user@$remote_host "rm -Rf /etc/inc/priv/fauxapi.priv.inc"
ssh $remote_user@$remote_host "rm -Rf /etc/fauxapi"
ssh $remote_user@$remote_host "rm -Rf /etc/inc/fauxapi"
ssh $remote_user@$remote_host "rm -Rf /cf/conf/fauxapi"
ssh $remote_user@$remote_host "rm -Rf /${PREFIX}/www/fauxapi"

ssh $remote_user@$remote_host "/usr/local/bin/php -f /etc/rc.packages ${PORTNAME} POST-DEINSTALL"
