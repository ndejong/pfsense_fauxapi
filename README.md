# FauxAPI - v1.1
A REST API interface for pfSense to facilitate dev-ops:-
 - https://github.com/ndejong/pfsense_fauxapi

Additionally available are a set of [client libraries](#user-content-clientlibraries) 
thus making programmatic access and update of pfSense hosts for dev-ops tasks 
more feasible.

---

### Intent
The intent of FauxAPI is to provide a basic programmatic interface into pfSense 
2.3+ to facilitate dev-ops tasks with pfSense until version 3.x comes around 
offering a ground up API as has been indicated here - 
https://blog.pfsense.org/?p=1588

---

### Approach
The FauxAPI provides basic interfaces to perform actions directly on the main 
pfSense configuration file `/cf/config/config.xml`.  It should be obvious
therefore that this provides the ability to write configurations that can break 
your pfSense system.  The ability however to programmatically interface with a
running pfSense host(s) is enormously useful.

At it's core FauxAPI simply reads the pfSense `config.xml` file, converts it to 
JSON and returns to the caller.  Similarly it can take a JSON formatted config 
and write it to the pfSense `config.xml` and (by default) perform a config 
backup and handle the required config reload.

FauxAPI loads core pfSense libraries to issue system functions as would 
ordinarily occur through the regular GUI interface.  For those inclined to 
review the inner workings of the FauxAPI <> pfSense interface you can find them
located in the file `/etc/inc/fauxapi/fauxapi_pfsense_interface.inc`

There are several sanity checks in place to make sure a user provided JSON 
config will convert into the (slightly quirky) pfSense XML `config.xml` format 
and then reload as expected in the same way.  However, because it is not a real 
per action API interface it is still possible for the API caller to create 
configuration changes that make no sense and hence break your pfSense host - as 
the package name states, it is a "Faux" API, it's still very useful indeed.

Users of FauxAPI should also keep in mind that it is possible for pfSense (and 
other packages) to change the arrangement of the configuration items they refer 
to, while no such cases have been observed (yet) there is nothing stopping this 
from occurring and thus package or system upgrades could cause breaking 
configuration format changes.

A common source of confusion is the <span style="color:blue">requirement to pass 
the *FULL* configuration into the `config_set` call not just the portion of the 
configuration you wish to adjust </span> - the reason for this is that FauxAPI
is a utility that interfaces with the pfSense `config.xml` file without 
attempting to reach into the pfSense control layers (function calls) too much.
This is a deliberate position and helps make FauxAPI a low maintenance project 
that should keep working with pfSense as new versions evolve.

Because FauxAPI is a utility that interfaces with the pfSense `config.xml` there
are some cases where reloading the configuration file is not enough and you 
might need to "tickle" pfSense a little more to do what you want.  Good example
cases of this are getting newly defined network interfaces or VLANs to be 
recognized.  These situations are easily handled by doing a `send_event` with
the payload `interface reload all` - see the example included below or refer
to a the resolution to a user issue here - https://github.com/ndejong/pfsense_fauxapi/issues/10

---

### Versions and Testing
The FauxAPI has been developed against pfSense 2.3.2 and 2.3.3 - it has not (yet) 
been tested against 2.3.0 or 2.3.1 or the currently in development 2.4 releases.  
Further, it it understood that package packaging changed between pfSense 2.2 and 
2.3 so it seems unlikely that it will work with 2.2 - very happy to accept 
github pull requests to resolve if anyone cares to provide.

Testing is not (yet) thorough, there are however two client side test scripts 
(1x Bash, 1x Python) that test all possible server side actions.  The tests only 
test for success and not all possible failure modes.  This said, many failure 
scenarios have been considered and tested in development to cause FauxAPI to 
roll back if anything does not pass real-time sanity checks.

The FauxAPI REST call path has been name-spaced as v1 to accommodate future 
situations that may introduce breaking REST interface changes, in the event this
occurs a new v2 release would be possible without breaking existing v1 
implementations.

### Installation
Until the FauxAPI is added to the pfSense FreeBSD-ports tree you will need to 
install manually as shown:-
```bash
curl -s -O https://raw.githubusercontent.com/ndejong/pfsense_fauxapi/master/package/pfSense-pkg-FauxAPI-1.1.txz
pkg install pfSense-pkg-FauxAPI-1.1.txz
```
NB: take the time to ensure the SHA256 checksum is correct, they are provided in 
the file `SHA256SUMS`

Installation and de-installation examples can be found here:-
https://github.com/ndejong/pfsense_fauxapi/tree/master/package

### Releases
#### v1.0 - 2016-11-20
 - initial release

#### v1.1 - 2017-08-12
 - 2x new API calls `alias_update_urltables` and `get_gateway_status`
 - update documentation to address common points of confusion, expecially the 
   requirement to provide the _full_ config file not just the portion to be updated.
 - testing against pfSense 2.3.2 and 2.3.3

---

<a name="clientlibraries"></a>
### Client libraries

#### Python
 - https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs

A Python interface to pfSense was perhaps the most desired end-goal at the onset
of the FauxAPI package project.  Anyone that has tried to parse the pfSense 
`config.xml` files using a Python based library will understand that things 
don't quite work out as expected or desired.

```python
    import pprint, sys
    from fauxapi_lib import FauxapiLib
    FauxapiLib = FauxapiLib('<host-address>', '<fauxapi-key>', '<fauxapi-secret>')

    aliases = FauxapiLib.config_get('aliases')
    pprint.pprint(FauxapiLib.config_set(aliases, 'aliases'))
```

Again, it is recommended to review `python-lib-test-example.py` to observe 
worked examples with the library.  Of small note is that the Python library
supports the ability to get and set single sections of the pfSense system, not
just the entire system configuration as with the Bash library.

#### Bash
 - https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs

The Bash client library makes it possible to add a line with 
`source fauxapi_lib.sh` to your bash script and then access a pfSense host 
configuration directly as a JSON string
```bash
    source fauxapi_lib.sh
    export fauxapi_auth=`fauxapi_auth <fauxapi-key> <fauxapi-secret>`

    fauxapi_config_get <host-address> | jq .data.config > /tmp/config.json
    fauxapi_config_set <host-address> /tmp/config.json
```

It is recommended to review `bash-lib-test-example.sh` to get a better idea of
how to use it.

#### PHP
A PHP client does not yet exist, it should be fairly easy to develop by 
observing the Bash and Python examples - if you do please submit it as a github 
pull request, there are no doubt others that will appreciate a PHP interface.

---

### API Authentication
A deliberate design decision to decouple FauxAPI authentication from both the 
pfSense user authentication and the pfSense `config.xml` system.  This was done 
to limit the possibility of an accidental API change that removes access to the 
host.  It also seems more prudent to cause an API user to manually edit the 
FauxAPI `credentials.ini` file located at `/etc/fauxapi/credentials.ini` - happy 
to receive feedback about this.

The two sample FauxAPI keys (PFFAexample01 and PFFAexample02) and their 
associated secrets in the sample `credentials.ini` file are hard-coded to be
inoperative, you must create entirely new values before your client scripts
will be able to issue commands to FauxAPI.

API authentication itself is performed on a per-call basis with the auth value 
inserted as an additional `fauxapi-auth` HTTP request header, it can be 
calculated as such:-
```
    fauxapi-auth: <apikey>:<timestamp>:<nonce>:<hash>

    For example:-
    fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55
```

Where the `<hash>` value is calculated like so:-
```
    <hash> = sha256(<apisecret><timestamp><nonce>)
```

This is all handled in the [client libraries](#user-content-clientlibraries) 
provided, but as can be seen it is relatively easy to implement even in a Bash 
shell script - indeed a Bash include library `fauxapi_lib.sh` is provided that 
does this for you.

Getting the API credentials right seems to be a common source of confusion in
getting started with FauxAPI because the rules about valid API keys and secret 
values are pedantic to help make ensure poor choices are not made.

The API key+secret values you create for yourself in `/etc/fauxapi/credentials.ini`
have the following rules:-
 - <apikey_value> and <apisecret_value> may have alphanumeric chars ONLY!
 - <apikey_value> MUST start with the prefix PFFA (pfSense Faux API)
 - <apikey_value> MUST be >= 12 chars AND <= 40 chars in total length
 - <apisecret_value> MUST be >= 40 chars AND <= 128 chars in length
 - you must not use the sample key/secret in the `credentials.ini` since they
   are hard coded to fail.

Consider using the following shell commands to generate valid values:-

#### apikey_value
```bash
    echo PPFA`head /dev/urandom | base64 -w0 | tr -d /+= | head -c 20`
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

---

### API REST Actions
The following REST based API actions are provided, cURL call request examples
are provided for each.  The API user is perhaps more likely interface with the 
[client libraries](#user-content-clientlibraries) as documented further below 
rather than these REST end-points.

The framework around the FauxAPI has been put together with the idea of being
able to easily add more actions at a later time, if you have ideas for actions 
that might be useful be sure to get in contact.

NB: the cURL requests below use the '--insecure' switch because many pfSense
deployments do not deploy certificate chain signed SSL certificates, a 
reasonable improvement in this regard might be to implement certificate pinning
at the client side to hence remove scope for man-in-middle concerns.

NB2: the API user may append a `__debug=true` URL request parameter to 
retrieve debug logs within the response data when required.


### `/fauxapi/v1/?action=config_get`
 - Returns the current system configuration as a JSON formatted string.
 - HTTP: **`GET`**
 - Params:
    - `config_file` (optional, default=/cf/config/config.xml)

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=config_get"
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

Hint: use `jq` to obtain the config only, as such:-
```bash
    cat /tmp/faux-config-get-output-from-curl.json | jq .data.config > /tmp/config.json
```


### `/fauxapi/v1/?action=config_set`
 - Sets a full system configuration and (by default) takes a system config
   backup and causes the system config to be reloaded once successfully written.
   NB: be sure to pass the *FULL* system configuration in here, not just the 
   piece you wish to adjust!
 - HTTP: **`POST`**
 - Params:
    - `do_backup` (optional, default = true)
    - `do_reload` (optional, default = true)

*Example Request*
```bash
    curl \
        -X POST \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        --header "Content-Type: application/json" \
        --data @/tmp/config.json \
        "https://192.168.10.10/fauxapi/v1/?action=config_set"
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


### `/fauxapi/v1/?action=config_reload`
 - Causes the pfSense system to perform a reload of the `config.xml` file, by 
   default this already happens when the config_set action occurs hence there
   is normally no need to explicitly call this after a config_set action.
 - HTTP: **`GET`**
 - Params: none

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=config_reload"
```

*Example Response*
```javascript
    {
      "callid": "5831226e18326",
      "action": "config_reload",
      "message": "ok"
    }
```


### `/fauxapi/v1/?action=config_backup`
 - Causes the system to take a configuration backup and add it to the regular 
   set of system change backups located on the host here `/cf/conf/backup/`
 - HTTP: **`GET`**
 - Params: none

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://<fauxapi_host>/fauxapi/v1/?action=config_backup"
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


### `/fauxapi/v1/?action=config_backup_list`
 - Returns a list of the currently available system configuration backups
   located in the `/cf/conf/backup/` host path.
 - HTTP: **`GET`**
 - Params: none

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=config_backup_list"
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


### `/fauxapi/v1/?action=config_restore`
 - Returns a list of the currently available system configuration backups
 - HTTP: **`GET`**
 - Params:
    - `config_file` (required, full path to the backup file to restore)

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=config_restore&config_file=/cf/conf/backup/config-1479545598.xml"
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


### `/fauxapi/v1/?action=system_stats`
 - Returns various system stats.
 - HTTP: **`GET`**
 - Params: none

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=system_stats"
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


### `/fauxapi/v1/?action=gateway_status`
 - Returns gateway status data.
 - HTTP: **`GET`**
 - Params: none

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=gateway_status"
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


### `/fauxapi/v1/?action=send_event`
 - Performs a pfSense "send_event" command to cause various pfSense system 
   actions, the following standard pfSense send_event combinations are permitted:-
    - filter: reload, sync
    - interface: all, newip, reconfigure
    - service: reload, restart, sync
 - HTTP: **`POST`**
 - Params: none

*Example Request*
```bash
    curl \
        -X POST \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        --header "Content-Type: application/json" \
        --data "[\"interface reload all\"]" \
        "https://192.168.10.10/fauxapi/v1/?action=send_event"
```

*Example Response*
```javascript
    {
      "callid": "58312bb3398bc",
      "action": "send_event",
      "message": "ok"
    }
```


### `/fauxapi/v1/?action=rule_get`
 - Returns the numbered list of loaded pf rules from a `pfctl -sr -vv` command 
   on the pfSense host.  An empty rule_number parameter causes all rules to be
   returned.
 - HTTP: **`GET`**
 - Params:
    - `rule_number` (optional, default = null)

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=rule_get&rule_number=5"
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


### `/fauxapi/v1/?action=alias_update_urltables`
 - Causes the pfSense host to immediately update any urltable alias entries
   from their (remote) source URLs.  Optionally update just one table by 
   specifying the table name, else all tables are updated.
 - HTTP: **`GET`**
 - Params:
    - `table` (optional, default = null)

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=alias_update_urltables"
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


### `/fauxapi/v1/?action=system_reboot`
 - Just as it says, reboots the system.
 - HTTP: **`GET`**
 - Params: none

*Example Request*
```bash
    curl \
        -X GET \
        --silent \
        --insecure \
        --header "fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55" \
        "https://192.168.10.10/fauxapi/v1/?action=system_reboot"
```

*Example Response*
```javascript
    {
      "callid": "58312bb3487ac",
      "action": "system_reboot",
      "message": "ok"
    }
```


---

### FauxAPI License
```
Copyright 2016 Nicholas de Jong  

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