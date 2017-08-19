#!/bin/bash
# 
# Copyright 2017 Nicholas de Jong  <contact[at]nicholasdejong.com>
# 
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
# 
#     http://www.apache.org/licenses/LICENSE-2.0
# 
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
# 

fauxapi_auth() {

    fauxapi_apikey=${1}
    fauxapi_apisecret=${2}

    fauxapi_timestamp=`date --utc +%Y%m%dZ%H%M%S`
    fauxapi_nonce=`head -c 40 /dev/urandom | md5sum | head -c 8`

    # NB:-
    #  auth = apikey:timestamp:nonce:HASH(apisecret:timestamp:nonce)

    fauxapi_hash=`echo -n ${fauxapi_apisecret}${fauxapi_timestamp}${fauxapi_nonce} | sha256sum | cut -d' ' -f1`
    fauxapi_auth=${fauxapi_apikey}:${fauxapi_timestamp}:${fauxapi_nonce}:${fauxapi_hash}

    echo ${fauxapi_auth}
}

fauxapi_config_get() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=config_get&__debug=${fauxapi_debug}"`
}

fauxapi_config_backup() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=config_backup&__debug=${fauxapi_debug}"`
}

fauxapi_config_reload() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=config_reload&__debug=${fauxapi_debug}"`
}

fauxapi_config_restore() {
    fauxapi_host=${1}
    fauxapi_configfile=${2}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=config_restore&config_file=${fauxapi_configfile}&__debug=${fauxapi_debug}"`
}

fauxapi_config_backup_list() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=config_backup_list&__debug=${fauxapi_debug}"`
}

fauxapi_config_set() {
    fauxapi_host=${1}
    fauxapi_configfile=${2}
    echo `curl \
        -X POST \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        --header "Content-Type: application/json" \
        --data @${fauxapi_configfile} \
        "https://${fauxapi_host}/fauxapi/v1/?action=config_set&__debug=${fauxapi_debug}"`
}

fauxapi_send_event() {
    fauxapi_host=${1}
    fauxapi_command=${2}
    echo `curl \
        -X POST \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        --header "Content-Type: application/json" \
        --data "[\"${fauxapi_command}\"]" \
        "https://${fauxapi_host}/fauxapi/v1/?action=send_event&__debug=${fauxapi_debug}"`
}

fauxapi_system_reboot() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=system_reboot&__debug=${fauxapi_debug}"`
}

fauxapi_system_stats() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=system_stats&__debug=${fauxapi_debug}"`
}

fauxapi_rule_get() {
    fauxapi_host=${1}
    fauxapi_rule_number=${2}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=rule_get&rule_number=${fauxapi_rule_number}&__debug=${fauxapi_debug}"`
}

fauxapi_alias_update_urltables() {
    fauxapi_host=${1}
    fauxapi_table=${2}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=alias_update_urltables&table=${fauxapi_table}&__debug=${fauxapi_debug}"`
}

fauxapi_gateway_status() {
    fauxapi_host=${1}
    echo `curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=gateway_status&__debug=${fauxapi_debug}"`
}

fauxapi_function_call() {
    fauxapi_host=${1}
    fauxapi_include=${2}
    fauxapi_function=${3}
    fauxapi_args=${4}
    echo `curl \
        -X POST \
        --silent \
        --insecure \
        --header "fauxapi-auth: ${fauxapi_auth}" \
        --header "Content-Type: application/json" \
        --data "${fauxapi_args}" \
        "https://${fauxapi_host}/fauxapi/v1/?action=function_call&include=${fauxapi_include}&function=${fauxapi_function}&__debug=${fauxapi_debug}"`
}
