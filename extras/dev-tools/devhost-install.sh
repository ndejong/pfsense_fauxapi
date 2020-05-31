#!/bin/bash

remote_host=${1}
remote_user=root
local_base_path=$(realpath $(dirname $(realpath $0))/../../)

if [ -z ${remote_host} ]; then
    echo 'usage: '${0}' <host-address>'
    exit 1
fi

PORTNAME=pfSense-pkg-FauxAPI
FILESDIR=${local_base_path}/${PORTNAME}/files/
PREFIX=usr/local
DATADIR=${PREFIX}/share/${PORTNAME}
STAGEDIR=$remote_user@$remote_host:/

ssh $remote_user@$remote_host " \
    mkdir -p /${DATADIR}; \
    mkdir -p /${PREFIX}/pkg; \
    mkdir -p /etc/inc/priv; \
    mkdir -p /etc/fauxapi; \
    mkdir -p /etc/inc/fauxapi; \
    mkdir -p /${PREFIX}/www/fauxapi/v1; \
    mkdir -p /${PREFIX}/www/fauxapi/admin; \
"

scp ${FILESDIR}${PREFIX}/pkg/fauxapi.xml \
		${STAGEDIR}${PREFIX}/pkg

scp ${FILESDIR}/etc/inc/priv/fauxapi.priv.inc \
                ${STAGEDIR}/etc/inc/priv

 scp ${FILESDIR}/etc/fauxapi/credentials.sample.ini \
                 ${STAGEDIR}/etc/fauxapi
scp ${local_base_path}/extras/dev-tools/credentials.ini \
                ${STAGEDIR}/etc/fauxapi

scp ${FILESDIR}/etc/fauxapi/pfsense_function_calls.sample.txt \
                ${STAGEDIR}/etc/fauxapi
scp ${local_base_path}/extras/dev-tools/pfsense_function_calls.txt \
                ${STAGEDIR}/etc/fauxapi

scp ${FILESDIR}${PREFIX}/www/fauxapi/v1/index.php \
                ${STAGEDIR}${PREFIX}/www/fauxapi/v1

scp ${FILESDIR}${PREFIX}/www/fauxapi/admin/about.php \
                ${STAGEDIR}${PREFIX}/www/fauxapi/admin

scp ${FILESDIR}${PREFIX}/www/fauxapi/admin/credentials.php \
                ${STAGEDIR}${PREFIX}/www/fauxapi/admin

scp ${FILESDIR}${PREFIX}/www/fauxapi/admin/logs.php \
                ${STAGEDIR}${PREFIX}/www/fauxapi/admin

scp ${FILESDIR}/etc/inc/fauxapi/fauxapi.inc \
                ${STAGEDIR}/etc/inc/fauxapi

scp ${FILESDIR}/etc/inc/fauxapi/fauxapi_actions.inc \
                ${STAGEDIR}/etc/inc/fauxapi

scp ${FILESDIR}/etc/inc/fauxapi/fauxapi_auth.inc \
                ${STAGEDIR}/etc/inc/fauxapi

scp ${FILESDIR}/etc/inc/fauxapi/fauxapi_logger.inc \
                ${STAGEDIR}/etc/inc/fauxapi

scp ${FILESDIR}/etc/inc/fauxapi/fauxapi_pfsense_interface.inc \
                ${STAGEDIR}/etc/inc/fauxapi

scp ${FILESDIR}/etc/inc/fauxapi/fauxapi_utils.inc \
                ${STAGEDIR}/etc/inc/fauxapi

scp ${FILESDIR}${DATADIR}/info.xml \
		${STAGEDIR}${DATADIR}

ssh $remote_user@$remote_host "/usr/local/bin/php -f /etc/rc.packages ${PORTNAME} POST-INSTALL"
