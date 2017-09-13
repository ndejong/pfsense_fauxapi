# FauxAPI - v1.2
A REST API interface for pfSense 2.3+ to facilitate devops:-
 - https://github.com/ndejong/pfsense_fauxapi

Additionally available are a set of [client libraries](#user-content-client_libraries) 
that hence make programmatic access and management of pfSense hosts for devops 
tasks feasible.


## API Action Summary
 - [alias_update_urltables](#user-content-alias_update_urltables) - Causes the pfSense host to immediately update any urltable alias entries from their (remote) source URLs.
 - [config_backup](#user-content-config_backup) - Causes the system to take a configuration backup and add it to the regular set of system change backups.
 - [config_backup_list](#user-content-config_backup_list) - Returns a list of the currently available system configuration backups.
 - [config_get](#user-content-config_get) - Returns the system configuration as a JSON formatted string.
 - [config_reload](#user-content-config_reload) - Causes the pfSense system to perform a reload of the `config.xml` file.
 - [config_restore](#user-content-config_restore) - Restores the pfSense system to the named backup configuration.
 - [config_set](#user-content-config_set) - Sets a full system configuration and (by default) reloads once successfully written and tested.
 - [function_call](#user-content-function_call) - Call directly a pfSense PHP function with API user supplied parameters.
 - [gateway_status](#user-content-gateway_status) - Returns gateway status data.
 - [rule_get](#user-content-rule_get) - Returns the numbered list of loaded pf rules from a `pfctl -sr -vv` command on the pfSense host.
 - [send_event](#user-content-send_event) - Performs a pfSense "send_event" command to cause various pfSense system actions.
 - [system_reboot](#user-content-system_reboot) - Reboots the pfSense system.
 - [system_stats](#user-content-system_stats) - Returns various useful system stats.


## Approach
At its core FauxAPI simply reads the core pfSense `config.xml` file, converts it 
to JSON and returns to the API caller.  Similarly it can take a JSON formatted 
configuration and write it to the pfSense `config.xml` and handles the required 
reload operations.  The ability to programmatically interface with a running 
pfSense host(s) is enormously useful however it should also be obvious that this 
provides the API user the ability to create configurations that can break your 
pfSense system.

FauxAPI provides easy backup and restore API interfaces that by default store
configuration backups on all configuration write operations thus it is very easy 
to roll-back even if the API user manages to deploy a "very broken" configuration.

Multiple sanity checks take place to make sure a user provided JSON config will 
correctly convert into the (slightly quirky) pfSense XML `config.xml` format and 
then reload as expected in the same way.  However, because it is not a real 
per-action application-layer interface it is still possible for the API caller 
to create configuration changes that make no sense and can potentially disrupt 
your pfSense system - as the package name states, it is a "Faux" API to pfSense
filling a gap in functionality with the current pfSense product.

A common source of confusion is the requirement to pass the _FULL_ configuration 
into the **config_set** action and not just the portion of the configuration you 
wish to adjust.  A **config_patch** action is in development and is expected in 
a future release.

Because FauxAPI is a utility that interfaces with the pfSense `config.xml` there
are some cases where reloading the configuration file is not enough and you 
may need to "tickle" pfSense a little more to do what you want.  This is not 
common however a good example is getting newly defined network interfaces or 
VLANs to be recognized.  These situations are easily handled by calling the
**send_event** action with the payload **interface reload all** - see the example 
included below and refer to a the resolution to [user issue #10](https://github.com/ndejong/pfsense_fauxapi/issues/10)

__NB:__ *As at FauxAPI v1.2 the **function_call** action has been introduced that 
now provides the ability to issue function calls directly into pfSense.*


## Installation
Until the FauxAPI is added to the pfSense FreeBSD-ports tree you will need to 
install manually from **root** as shown:-
```bash
set fauxapi_baseurl='https://raw.githubusercontent.com/ndejong/pfsense_fauxapi/master/package'
set fauxapi_latest=`curl --silent $fauxapi_baseurl/LATEST`
fetch $fauxapi_baseurl/$fauxapi_latest
pkg install $fauxapi_latest
```

Installation and de-installation is quite straight forward, further examples can 
be found [here](https://github.com/ndejong/pfsense_fauxapi/tree/master/package).

Refer to the published package [`SHA256SUMS`](https://github.com/ndejong/pfsense_fauxapi/blob/master/package/SHA256SUMS)


## Debugging
FauxAPI comes with awesome debug logging capability, simply insert `__debug=true` 
as a URL request parameter and the response data will contain rich debugging log 
data about the flow of the request.

If you are looking for more debugging at various points feel free to submit a 
pull request or lodge an issue describing your requirement and I'll see what
can be done to accommodate.


<a name="client_libraries"></a>
## Client libraries

#### Python
A [Python interface](https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs) 
to pfSense was perhaps the most desired end-goal at the onset of the FauxAPI 
package project.  Anyone that has tried to parse the pfSense `config.xml` files 
using a Python based library will understand that things don't quite work out as 
expected or desired.

```python
import pprint, sys
from fauxapi_lib import FauxapiLib
FauxapiLib = FauxapiLib('<host-address>', '<fauxapi-key>', '<fauxapi-secret>')

aliases = FauxapiLib.config_get('aliases')
## perform some kind of manipulation to `aliases` here ##
pprint.pprint(FauxapiLib.config_set(aliases, 'aliases'))
```

It is recommended to review [`python-lib-test-example.py`](https://github.com/ndejong/pfsense_fauxapi/blob/master/client-libs/python-lib-test-example.py)
to observe worked examples with the library.  Of small note is that the Python 
library supports the ability to get and set single sections of the pfSense 
system, not just the entire system configuration as with the Bash library.

#### Bash
The [Bash client library](https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs) 
makes it possible to add a line with `source fauxapi_lib.sh` to your bash script 
and then access a pfSense host configuration directly as a JSON string
```bash
source fauxapi_lib.sh
export fauxapi_auth=`fauxapi_auth <fauxapi-key> <fauxapi-secret>`

fauxapi_config_get <host-address> | jq .data.config > /tmp/config.json
## perform some kind of manipulation to `/tmp/config.json` here ##
fauxapi_config_set <host-address> /tmp/config.json
```

It is recommended to review [`bash-lib-test-example.sh`](https://github.com/ndejong/pfsense_fauxapi/blob/master/client-libs/bash-lib-test-example.sh)
to get a better idea how to use it.

#### PHP
A PHP interface does not yet exist, it should be fairly easy to develop by 
observing the Bash and Python examples - if you do please submit it as a github 
pull request, there are no doubt others that will appreciate a PHP interface.


## API Authentication
A deliberate design decision to decouple FauxAPI authentication from both the 
pfSense user authentication and the pfSense `config.xml` system.  This was done 
to limit the possibility of an accidental API change that removes access to the 
host.  It also seems more prudent to only establish API user(s) manually via the 
FauxAPI `/etc/fauxapi/credentials.ini` file - happy to receive feedback about 
this approach.

The two sample FauxAPI keys (PFFAexample01 and PFFAexample02) and their 
associated secrets in the sample `credentials.ini` file are hard-coded to be
inoperative, you must create entirely new values before your client scripts
will be able to issue commands to FauxAPI.

API authentication itself is performed on a per-call basis with the auth value 
inserted as an additional **fauxapi-auth** HTTP request header, it can be 
calculated as such:-
```
fauxapi-auth: <apikey>:<timestamp>:<nonce>:<hash>

For example:-
fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55
```

Where the &lt;hash&gt; value is calculated like so:-
```
<hash> = sha256(<apisecret><timestamp><nonce>)
```

This is all handled in the [client libraries](#user-content-client_libraries) 
provided, but as can be seen it is relatively easy to implement even in a Bash 
shell script.

Getting the API credentials right seems to be a common source of confusion in
getting started with FauxAPI because the rules about valid API keys and secret 
values are pedantic to help make ensure poor choices are not made.

The API key + API secret values that you will need to create in `/etc/fauxapi/credentials.ini`
have the following rules:-
 - <apikey_value> and <apisecret_value> may have alphanumeric chars ONLY!
 - <apikey_value> MUST start with the prefix PFFA (pfSense Faux API)
 - <apikey_value> MUST be >= 12 chars AND <= 40 chars in total length
 - <apisecret_value> MUST be >= 40 chars AND <= 128 chars in length
 - you must not use the sample key/secret in the `credentials.ini` since they
   are hard coded to fail.

To make things easier consider using the following shell commands to generate 
valid values:-

#### apikey_value
```bash
echo PFFA`head /dev/urandom | base64 -w0 | tr -d /+= | head -c 20`
```

#### apisecret_value
```bash
echo `head /dev/urandom | base64 -w0 | tr -d /+= | head -c 60`
```

NB: Make sure the client side clock is within 60 seconds of the pfSense host 
clock else the auth token values calculated by the client will not be valid - 60 
seconds seems tight, however, provided you are using NTP to look after your 
system time it's quite unlikely to cause issues - happy to receive feedback 
about this.


## API Authorization
The file `/etc/fauxapi/credentials.ini` additionally provides a method to restrict
the API actions available to the API key using the **permit** configuration 
parameter.  Permits are comma delimited and may contain * wildcards to match more
than one rule as shown in the example below.

```
[PFFAexample01]
secret = abcdefghijklmnopqrstuvwxyz0123456789abcd
permit = alias_*, config_*, gateway_*, rule_*, send_*, system_*, function_*
owner = example key PFFAexample01 - hardcoded to be inoperative
```


## API REST Actions
The following REST based API actions are provided, example cURL call request 
examples are provided for each.  The API user is perhaps more likely to interface 
with the [client libraries](#user-content-client_libraries) as documented above 
rather than directly with these REST end-points.

The framework around the FauxAPI has been put together with the idea of being
able to easily add more actions at a later time, if you have ideas for actions 
that might be useful be sure to get in contact.

NB: the cURL requests below use the '--insecure' switch because many pfSense
deployments do not deploy certificate chain signed SSL certificates.  A reasonable 
improvement in this regard might be to implement certificate pinning at the 
client side to hence remove scope for man-in-middle concerns.

<br>
---
<a name="alias_update_urltables"></a>
### alias_update_urltables
 - Causes the pfSense host to immediately update any urltable alias entries
   from their (remote) source URLs.  Optionally update just one table by 
   specifying the table name, else all tables are updated.
 - HTTP: **GET**
 - Params:
    - **table** (optional, default = null)

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=alias_update_urltables"
```

*Example Response*
```javascript
{
  "callid": "598ec756b4d09",
  "action": "alias_update_urltables",
  "message": "ok",
  "data": {
    "updates": {
      "bruteforceblocker": {
        "url": "https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/bruteforceblocker.ipset",
        "status": [
          "no changes."
        ]
      }
    }
  }
}
```

<br>
---
<a name="config_backup"></a>
### config_backup
 - Causes the system to take a configuration backup and add it to the regular 
   set of pfSense system backups at `/cf/conf/backup/`
 - HTTP: **GET**
 - Params: none

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=config_backup"
```

*Example Response*
```javascript
{
  "callid": "583012fea254f",
  "action": "config_backup",
  "message": "ok",
  "data": {
    "backup_config_file": "/cf/conf/backup/config-1479545598.xml"
  }
}
```

<br>
---
<a name="config_backup_list"></a>
### config_backup_list
 - Returns a list of the currently available pfSense system configuration backups.
 - HTTP: **GET**
 - Params: none

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=config_backup_list"
```

*Example Response*
```javascript
{
  "callid": "583065cb670db",
  "action": "config_backup_list",
  "message": "ok",
  "data": {
    "backup_files": [
      {
        "filename": "/cf/conf/backup/config-1479545598.xml",
        "timestamp": "20161119Z144635",
        "description": "fauxapi-PFFA4797d073@192.168.10.10: update via fauxapi for callid: 583012fea254f",
        "version": "15.5",
        "filesize": 18535
      },
      ....
```

<br>
---
<a name="config_get"></a>
### config_get
 - Returns the system configuration as a JSON formatted string.  Additionally, 
   using the optional **config_file** parameter it is possible to retrieve backup
   configurations by providing the full path to it under the `/cf/conf/backup` 
   path.
 - HTTP: **GET**
 - Params:
    - **config_file** (optional, default=`/cf/config/config.xml`)

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=config_get"
```

*Example Response*
```javascript
{
    "callid": "583012fe39f79",
    "action": "config_get",
    "message": "ok",
    "data": {
      "config_file": "/cf/conf/config.xml",
      "config": {
        "version": "15.5",
        "staticroutes": "",
        "snmpd": {
          "syscontact": "",
          "rocommunity": "public",
          "syslocation": ""
        },
        "shaper": "",
        "installedpackages": {
          "pfblockerngsouthamerica": {
            "config": [
             ....
```

Hint: use `jq` to parse the response JSON and obtain the config only, as such:-
```bash
cat /tmp/faux-config-get-output-from-curl.json | jq .data.config > /tmp/config.json
```

<br>
---
<a name="config_reload"></a>
### config_reload
 - Causes the pfSense system to perform a reload of the `config.xml` file, by 
   default this happens when the **config_set** action occurs hence there is 
   normally no need to explicitly call this after a **config_set** action.
 - HTTP: **GET**
 - Params: none

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=config_reload"
```

*Example Response*
```javascript
{
  "callid": "5831226e18326",
  "action": "config_reload",
  "message": "ok"
}
```

<br>
---
<a name="config_restore"></a>
### config_restore
 - Restores the pfSense system to the named backup configuration.
 - HTTP: **GET**
 - Params:
    - **config_file** (required, full path to the backup file to restore)

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=config_restore&config_file=/cf/conf/backup/config-1479545598.xml"
```

*Example Response*
```javascript
{
  "callid": "583126192a789",
  "action": "config_restore",
  "message": "ok",
  "data": {
    "config_file": "/cf/conf/backup/config-1479545598.xml"
  }
}
```

<br>
---
<a name="config_set"></a>
### config_set
 - Sets a full system configuration and (by default) takes a system config
   backup and (by default) causes the system config to be reloaded once 
   successfully written and tested.
 - NB: be sure to pass the *FULL* system configuration in here, not just the 
   piece you wish to adjust!
 - HTTP: **POST**
 - Params:
    - **do_backup** (optional, default = true)
    - **do_reload** (optional, default = true)

*Example Request*
```bash
curl \
    -X POST \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    --header "Content-Type: application/json" \
    --data @/tmp/config.json \
    "https://<host-address>/fauxapi/v1/?action=config_set"
```

*Example Response*
```javascript
{
  "callid": "583065cae8993",
  "action": "config_set",
  "message": "ok",
  "data": {
    "do_backup": true,
    "do_reload": true
  }
}
```

<br>
---
<a name="function_call"></a>
### function_call
 - Call directly a pfSense PHP function with API user supplied parameters.  Note
   that is action is a *VERY* raw interface into the inner workings of pfSense 
   and it is not recommended for API users that do not have a solid understanding 
   of PHP and pfSense.  Additionally, not all pfSense functions are appropriate 
   to be called through the FauxAPI and only very limited testing has been 
   performed against the possible outcomes and responses.  It is possible to 
   harm your pfSense system if you do not 100% understand what is going on.
 - Functions to be called via this interface *MUST* be defined in the file 
   `/etc/inc/fauxapi/pfsense_function_calls.txt` only a handful very basic and 
   read-only pfSense functions are enabled by default.
 - HTTP: **POST**
 - Params: none

*Example Request*
```bash
curl \
    -X POST \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    --header "Content-Type: application/json" \
    --data "{\"function\": \"get_services\"}" \
    "https://<host-address>/fauxapi/v1/?action=function_call"
```

*Example Response*
```javascript
{
  "callid": "59a29e5017905",
  "action": "function_call",
  "message": "ok",
  "data": {
    "return": [
      {
        "name": "unbound",
        "description": "DNS Resolver"
      },
      {
        "name": "ntpd",
        "description": "NTP clock sync"
      },
      ....

```

<br>
---
<a name="gateway_status"></a>
### gateway_status
 - Returns gateway status data.
 - HTTP: **GET**
 - Params: none

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=gateway_status"
```

*Example Response*
```javascript
{
  "callid": "598ecf3e7011e",
  "action": "gateway_status",
  "message": "ok",
  "data": {
    "gateway_status": {
      "10.22.33.1": {
        "monitorip": "8.8.8.8",
        "srcip": "10.22.33.100",
        "name": "GW_WAN",
        "delay": "4.415ms",
        "stddev": "3.239ms",
        "loss": "0.0%",
        "status": "none"
      }
    }
  }
}
```

<br>
---
<a name="rule_get"></a>
### rule_get
 - Returns the numbered list of loaded pf rules from a `pfctl -sr -vv` command 
   on the pfSense host.  An empty rule_number parameter causes all rules to be
   returned.
 - HTTP: **GET**
 - Params:
    - **rule_number** (optional, default = null)

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=rule_get&rule_number=5"
```

*Example Response*
```javascript
{
  "callid": "583c279b56958",
  "action": "rule_get",
  "message": "ok",
  "data": {
    "rules": [
      {
        "rule": "anchor \"openvpn/*\" all",
        "evaluations": "14134",
        "packets": "0",
        "bytes": "0",
        "states": "0",
        "inserted": "21188",
        "statecreations": "0"
      }
    ]
  }
}
```

<br>
---
<a name="send_event"></a>
### send_event
 - Performs a pfSense "send_event" command to cause various pfSense system 
   actions as is also available through the pfSense console interface.  The 
   following standard pfSense send_event combinations are permitted:-
    - filter: reload, sync
    - interface: all, newip, reconfigure
    - service: reload, restart, sync
 - HTTP: **POST**
 - Params: none

*Example Request*
```bash
curl \
    -X POST \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    --header "Content-Type: application/json" \
    --data "[\"interface reload all\"]" \
    "https://<host-address>/fauxapi/v1/?action=send_event"
```

*Example Response*
```javascript
{
  "callid": "58312bb3398bc",
  "action": "send_event",
  "message": "ok"
}
```

<br>
---
<a name="system_reboot"></a>
### system_reboot
 - Just as it says, reboots the system.
 - HTTP: **GET**
 - Params: none

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=system_reboot"
```

*Example Response*
```javascript
{
  "callid": "58312bb3487ac",
  "action": "system_reboot",
  "message": "ok"
}
```

<br>
---
<a name="system_stats"></a>
### system_stats
 - Returns various useful system stats.
 - HTTP: **GET**
 - Params: none

*Example Request*
```bash
curl \
    -X GET \
    --silent \
    --insecure \
    --header "fauxapi-auth: <auth-value>" \
    "https://<host-address>/fauxapi/v1/?action=system_stats"
```

*Example Response*
```javascript
{
  "callid": "583d5ce3301f4",
  "action": "system_stats",
  "message": "ok",
  "data": {
    "stats": {
      "cpu": "2",
      "mem": "16",
      "uptime": "9 Days 20 Hours 02 Minutes 08 Seconds",
      "states": "364/48000",
      "temp": "",
      "datetime": "20161129Z104804",
      "interfacestatistics": "",
      "interfacestatus": "",
      "cpufreq": "",
      "load_average": [
        "0.29",
        "0.29",
        "0.28"
      ],
      "mbuf": "1016/30414",
      "mbufpercent": "3",
      "statepercent": "1"
    }
  }
}
```
---

## Versions and Testing
The FauxAPI has been developed against pfSense 2.3.2, 2.3.3 and 2.3.4 it has 
not (yet) been tested against 2.3.0 or 2.3.1 or the (currently) in development 
2.4 releases.  Further, it is apparent that the pfSense packaging technique 
changed significantly prior to 2.3.x so it is unlikely that it will be 
backported to anything prior to 2.3.0.

Testing is reasonable but does not achieve 100% code coverage within the FauxAPI 
codebase.  Two client side test scripts (1x Bash, 1x Python) that both 
demonstrate and test all possible server side actions are provided.  Under the 
hood FauxAPI, performs real-time sanity checks and tests to make sure the user 
supplied configurations will save, load and reload as expected.

__Shout Out:__ *Anyone that happens to know of _any_ test harness or test code 
for pfSense please get in touch - I'd very much prefer to integrate with existing 
pfSense test infrastructure if it already exists.*


## Releases
#### v1.0 - 2016-11-20
 - initial release

#### v1.1 - 2017-08-12
 - 2x new API actions **alias_update_urltables** and **gateway_status**
 - update documentation to address common points of confusion, especially the 
   requirement to provide the _full_ config file not just the portion to be 
   updated.
 - testing against pfSense 2.3.2 and 2.3.3

#### v1.2 - 2017-08-27
 - new API action **function_call** allowing the user to reach deep into the inner 
   code infrastructure of pfSense, this feature is intended for people with a 
   solid understanding of PHP and pfSense.
 - the `credentials.ini` file now provides a way to control the permitted API 
   actions.
 - various update documentation updates.
 - testing against pfSense 2.3.4


## FauxAPI License
```
Copyright 2017 Nicholas de Jong  

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
