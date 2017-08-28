#!/usr/bin/python3
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

import sys, json
from python.fauxapi_lib import FauxapiLib

# check args exist
if(len(sys.argv) < 4):
    print()
    print('usage: ' + sys.argv[0] + ' <host> <apikey> <apisecret>')
    print()
    print('pipe JSON output through jq for easy pretty print output:-')
    print(' $ ' + sys.argv[0] + ' <host> <apikey> <apisecret> | jq .')
    print()
    sys.exit(1)

# config
fauxapi_host=sys.argv[1]
fauxapi_apikey=sys.argv[2]
fauxapi_apisecret=sys.argv[3]

FauxapiLib = FauxapiLib(fauxapi_host, fauxapi_apikey, fauxapi_apisecret, debug=False)


# config get
config = FauxapiLib.config_get()
print(json.dumps(config))

# config get - filter section only
#config_filter = FauxapiLib.config_get('filter')  # get 'filter' section only

# config set
#print(json.dumps(FauxapiLib.config_set(config)))

# config get and set within the 'aliases' section only
#config_aliases = FauxapiLib.config_get('aliases')
#print(json.dumps(FauxapiLib.config_set(config_aliases, 'aliases')))

# config reload
#print(json.dumps(FauxapiLib.config_reload()))

# config backuo
#print(json.dumps(FauxapiLib.config_backup()))

# config_backup_list
#print(json.dumps(FauxapiLib.config_backup_list()))

# config_restore
#print(json.dumps(FauxapiLib.config_restore('/cf/conf/backup/config-1503921775.xml')))

# system_stats
#print(json.dumps(FauxapiLib.system_stats()))

# gateway_status
#print(json.dumps(FauxapiLib.gateway_status()))

# send_event - filter reload
#print(json.dumps(FauxapiLib.send_event('filter reload')))

# send_event - interface all reload
#print(json.dumps(FauxapiLib.send_event('interface all reload')))

# rule_get - get all rules
#print(json.dumps(FauxapiLib.rule_get()))

# rule_get - get rule number 5
#print(json.dumps(FauxapiLib.rule_get(5)))

# alias_update_urltables
#print(json.dumps(FauxapiLib.alias_update_urltables()))

# # system reboot
#print(json.dumps(FauxapiLib.system_reboot()))

# function_call - examples
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'return_gateways_status',
#         'args': [False]
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'discover_last_backup'
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'return_gateways_status',
#         'includes': ['gwlb.inc']
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'return_gateways_status_text',
#         'args': [True, False]
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'get_carp_status',
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'get_dns_servers',
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'get_system_pkg_version',
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'pkg_list_repos',
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'get_services',
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'get_service_status',
#         'args': ['ntpd']
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'is_service_enabled',
#         'args': ['ntpd']
#     }
# )))
#
# print(json.dumps(
#     FauxapiLib.function_call({
#         'function': 'is_service_running',
#         'args': ['ntpd']
#     }
# )))
