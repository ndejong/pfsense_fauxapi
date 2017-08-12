#!/bin/bash
# 
# Copyright 2016 Nicholas de Jong  <contact[at]nicholasdejong.com>
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

# include source library
source $(dirname ${0})/bash/fauxapi_lib.sh

# check args exist
if [ -z ${1} ] || [ -z ${2} ] || [ -z ${3} ]; then
    echo 'usage: '$0' <host> <apikey> <apisecret>'
    exit 1
fi

# config
fauxapi_host=${1}
fauxapi_apikey=${2}
fauxapi_apisecret=${3}

# establish the debug and auth then export
export fauxapi_debug=false
export fauxapi_auth=`fauxapi_auth ${fauxapi_apikey} ${fauxapi_apisecret}`


# config_get
# NB: must have the 'jq' binary to process the JSON response easily!
#fauxapi_config_get ${fauxapi_host} | jq .data.config > /tmp/pfsense-fauxapi.json

# config_set
#fauxapi_config_set ${fauxapi_host} /tmp/pfsense-fauxapi.json

# config_reload
#fauxapi_config_reload ${fauxapi_host}

# config_backup
#fauxapi_config_backup ${fauxapi_host}

# config_backup_list
#fauxapi_config_backup_list ${fauxapi_host}

# config_restore
# fauxapi_config_restore ${fauxapi_host} /cf/conf/backup/config-1481190790.xml

# fauxapi_system_stats
#fauxapi_system_stats ${fauxapi_host}

# gateway_status
fauxapi_gateway_status ${fauxapi_host}

# send_event
#fauxapi_send_event ${fauxapi_host} 'filter reload'
#fauxapi_send_event ${fauxapi_host} 'interface all reload'

# rule_get
#fauxapi_rule_get ${fauxapi_host}
#fauxapi_rule_get ${fauxapi_host} 5

# alias_update_urltables
#fauxapi_alias_update_urltables ${fauxapi_host}

# system_reboot
# fauxapi_system_reboot ${fauxapi_host}
