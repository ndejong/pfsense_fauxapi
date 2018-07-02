#!/usr/bin/python3
#
# Copyright 2018 Nicholas de Jong  <contact[at]nicholasdejong.com>
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

import os, sys, json, urllib.request

sys.path.append(os.path.abspath(os.path.join(os.path.curdir, '../client-libs/python')))     # hack to make this work in-place
from  fauxapi_lib import FauxapiLib


# parameters used to create the instance of FauxapiLib
fauxapi_host = '192.168.1.200'      # set this to your pfsense host with the fauxapi package installed
fauxapi_apikey = 'PFFAdevtrash'     # set this to your own API key
fauxapi_apisecret = 'devtrashdevtrashdevtrashdevtrashdevtrash' # set this to your own API secret

# parameters used in the call to UpdateAwsAliasesFauxapi.update() function below
update_regions_match='ap-*'     # all regions in Asia Pacific
update_services_match='ec2'     # just limit to EC2
update_ipv4=True                # include ipv4 addresses
update_ipv6=True                # include ipv6 addresses


class UpdateAwsAliasesFauxapiException(Exception):
    pass


class UpdateAwsAliasesFauxapi():

    fauxapi_host = None
    fauxapi_apikey = None
    fauxapi_apisecret = None

    system_config= None
    aws_ipranges_uri = 'https://ip-ranges.amazonaws.com/ip-ranges.json'

    FauxapiLib = None

    def __init__(self, fauxapi_host, fauxapi_apikey, fauxapi_apisecret, debug=False):
        self.FauxapiLib = FauxapiLib(fauxapi_host, fauxapi_apikey, fauxapi_apisecret, debug)

    def update(self, regions_match='*', services_match='*', ipv4=True, ipv6=True):

        # Use FauxapiLib to load the remote system config into memory
        self.system_config = self.FauxapiLib.config_get()

        # download ip-ranges.json parse and iterate
        for name, data in sorted(self.get_aws_ipranges().items()):
            if regions_match == '*' or regions_match.replace('*','').replace('-','').lower() in name:
                if services_match == '*' or services_match.replace('*', '').replace('_', '').lower() in name:
                    addresses = []
                    if ipv4 is True and len(data['ipv4']) > 0:
                        addresses += data['ipv4']
                    if ipv6 is True and len(data['ipv6']) > 0:
                        addresses += data['ipv6']
                    self.update_alias_in_config(
                        name=name,
                        description=data['description'],
                        addresses=addresses,
                        aws_create_date=data['aws_create_date']
                    )

        # Use FauxapiLib to save to the remote system the new edited config
        result = self.FauxapiLib.config_set(self.system_config)
        print(json.dumps(result))

    def update_alias_in_config(self, name, description, addresses, aws_create_date):

        addresses.sort()

        # candidate alias to apply
        alias_data = {
            'name': name,
            'type': 'network',
            'address': ' '.join(addresses),
            'descr': description,
            'detail': '||'.join(['ip-ranges.json createDate: {}'.format(aws_create_date)] * len(addresses))
        }

        if 'aliases' not in self.system_config or type(self.system_config['aliases']) is not dict:
            self.system_config['aliases'] = {}

        if 'alias' not in self.system_config['aliases'] or type(self.system_config['aliases']['alias']) is not list:
            self.system_config['aliases']['alias'] = []

        alias_found = False
        for index, alias in enumerate(self.system_config['aliases']['alias']):
            if alias['name'] == name:
                alias_found = True
                if alias['address'] != alias_data['address']:
                    self.system_config['aliases']['alias'][index] = alias_data

        if alias_found is False:
            self.system_config['aliases']['alias'].append(alias_data)

    def get_aws_ipranges(self):

        with urllib.request.urlopen(self.aws_ipranges_uri) as response:
            aws_ipranges_data = json.loads(response.read())

        ipranges = {}
        for prefix in aws_ipranges_data['prefixes'] + aws_ipranges_data['ipv6_prefixes']:

            name = 'aws_{}_{}'.format(
                prefix['region'].replace('-','').lower(),
                prefix['service'].replace('_','').lower()
            )[:32]

            if name not in ipranges:
                ipranges[name] = {
                    'ipv4': [],
                    'ipv6': [],
                    'description': 'AWS {region} {service}'.format(region=prefix['region'], service=prefix['service']),
                    'aws_create_date': aws_ipranges_data['createDate']
                }

            if 'ip_prefix' in prefix and len(prefix['ip_prefix']) > 0:
                ipranges[name]['ipv4'].append(prefix['ip_prefix'])

            if 'ipv6_prefix' in prefix and len(prefix['ipv6_prefix']) > 0:
                ipranges[name]['ipv6'].append(prefix['ipv6_prefix'])

        return ipranges


if __name__ == '__main__':
    UpdateAwsAliasesFauxapi(fauxapi_host, fauxapi_apikey, fauxapi_apisecret).update(
        regions_match=update_regions_match,
        services_match=update_services_match,
        ipv4=update_ipv4,
        ipv6=update_ipv6
    )
