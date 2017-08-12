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

import os
import json
import base64
import urllib
import requests
import datetime
import hashlib


class FauxapiLibException(Exception):
    pass


class FauxapiLib:

    host = None
    proto = None
    debug = None
    apikey = None
    apisecret = None
    use_snakeoil_https = None

    def __init__(self, host, apikey, apisecret, use_verified_https=False, debug=False):
        self.proto = 'https'
        self.base_url = 'fauxapi/v1'
        self.host = host
        self.apikey = apikey
        self.apisecret = apisecret
        self.use_verified_https = use_verified_https
        self.debug = debug
        if not self.use_verified_https:
            requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

    def config_get(self, section=None):
        res = self._api_request('GET', 'config_get')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete config_get() request', json.loads(res.text))
        else:
            config = json.loads(res.text)
        if section is None:
            return config['data']['config']
        elif section in config['data']['config']:
            return config['data']['config'][section]
        raise FauxapiLibException('unable to complete config_get request, section is unknown', section)

    def config_set(self, config_user, section=None):
        if section is None:
            config = config_user
        else:
            config = self.config_get(section=None)
            config[section] = config_user
        res = self._api_request('POST', 'config_set', data=json.dumps(config))
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete config_set() request', json.loads(res.text))
        return json.loads(res.text)

    def config_reload(self):
        res = self._api_request('GET', 'config_reload')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete config_reload() request', json.loads(res.text))
        return json.loads(res.text)

    def config_backup(self):
        res = self._api_request('GET', 'config_backup')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete system_reboot() request', json.loads(res.text))
        return json.loads(res.text)

    def config_backup_list(self):
        res = self._api_request('GET', 'config_backup_list')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete config_backup_list() request', json.loads(res.text))
        return json.loads(res.text)

    def config_restore(self, config_file):
        res = self._api_request('GET', 'config_restore', params={'config_file': config_file})
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete config_restore() request', json.loads(res.text))
        return json.loads(res.text)

    def send_event(self, command):
        res = self._api_request('POST', 'send_event', data=json.dumps([command]))
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete send_event() request', json.loads(res.text))
        return json.loads(res.text)

    def system_reboot(self):
        res = self._api_request('GET', 'system_reboot')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete system_reboot() request', json.loads(res.text))
        return json.loads(res.text)

    def system_stats(self):
        res = self._api_request('GET', 'system_stats')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete system_stats() request', json.loads(res.text))
        return json.loads(res.text)

    def gateway_status(self):
        res = self._api_request('GET', 'gateway_status')
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete gateway_status() request', json.loads(res.text))
        return json.loads(res.text)

    def rule_get(self, rule_number=None):
        res = self._api_request('GET', 'rule_get', params={'rule_number': rule_number})
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete rule_get() request', json.loads(res.text))
        return json.loads(res.text)

    def alias_update_urltables(self, table=None):
        if table is None:
            res = self._api_request('GET', 'alias_update_urltables')
        else:
            res = self._api_request('GET', 'alias_update_urltables', params={'table': table})
        if res.status_code != 200:
            raise FauxapiLibException('unable to complete alias_update_urltables() request', json.loads(res.text))
        return json.loads(res.text)

    def _api_request(self, method, action, params={}, data=None):
        if self.debug:
            params['__debug'] = 'true'
        url = '{proto}://{host}/{base_url}/?action={action}&{params}'.format(
            proto=self.proto, host=self.host, base_url=self.base_url, action=action, params=urllib.parse.urlencode(params))
        if method.upper() == 'GET':
            return requests.get(url,
                headers={'fauxapi-auth': self._generate_auth()},
                verify=self.use_verified_https
            )
        elif method.upper() == 'POST':
            return requests.post(url,
                headers={'fauxapi-auth': self._generate_auth()},
                verify=self.use_verified_https,
                data=data
            )
        raise FauxapiLibException('request method not supported!', method)

    def _generate_auth(self):
        # auth = apikey:timestamp:nonce:HASH(apisecret:timestamp:nonce)
        nonce = base64.b64encode(os.urandom(40)).decode('utf-8').replace('=', '').replace('/', '').replace('+', '')[0:8]
        timestamp = datetime.datetime.utcnow().strftime('%Y%m%dZ%H%M%S')
        hash = hashlib.sha256('{}{}{}'.format(self.apisecret, timestamp, nonce).encode('utf-8')).hexdigest()
        return '{}:{}:{}:{}'.format(self.apikey, timestamp, nonce, hash)

