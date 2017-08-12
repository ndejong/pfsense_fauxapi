<?php
/**
 * FauxAPI
 *  - A REST API interface for pfSense to facilitate dev-ops.
 *  - https://github.com/ndejong/pfsense_fauxapi
 * 
 * Copyright 2016 Nicholas de Jong  
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once('util.inc');
require_once('guiconfig.inc');

$pgtitle = array(gettext('System'), gettext('FauxAPI'), gettext('About'));
include_once('head.inc');

$tab_array   = array();
$tab_array[] = array(gettext("Credentials"), false, "/fauxapi/admin/credentials.php");
$tab_array[] = array(gettext("About"), true, "/fauxapi/admin/about.php");
display_top_tabs($tab_array, true);

?>

<div>
<!--READMESTART-->
<h1>
<a id="user-content-fauxapi---v11" class="anchor" href="#fauxapi---v11" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>FauxAPI - v1.1</h1>
<p>A REST API interface for pfSense to facilitate dev-ops:-</p>
<ul>
<li><a href="https://github.com/ndejong/pfsense_fauxapi">https://github.com/ndejong/pfsense_fauxapi</a></li>
</ul>
<p>Additionally available are a set of <a href="#user-content-clientlibraries">client libraries</a>
thus making programmatic access and update of pfSense hosts for dev-ops tasks
more feasible.</p>
<hr>
<h3>
<a id="user-content-intent" class="anchor" href="#intent" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Intent</h3>
<p>The intent of FauxAPI is to provide a basic programmatic interface into pfSense
2.3+ to facilitate dev-ops tasks with pfSense until version 3.x comes around
offering a ground up API as has been indicated here -
<a href="https://blog.pfsense.org/?p=1588">https://blog.pfsense.org/?p=1588</a></p>
<hr>
<h3>
<a id="user-content-approach" class="anchor" href="#approach" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Approach</h3>
<p>The FauxAPI provides basic interfaces to perform actions directly on the main
pfSense configuration file <code>/cf/config/config.xml</code>.  It should be obvious
therefore that this provides the ability to write configurations that can break
your pfSense system.  The ability however to programmatically interface with a
running pfSense host(s) is enormously useful.</p>
<p>At it's core FauxAPI simply reads the pfSense <code>config.xml</code> file, converts it to
JSON and returns to the caller.  Similarly it can take a JSON formatted config
and write it to the pfSense <code>config.xml</code> and (by default) perform a config
backup and handle the required config reload.</p>
<p>FauxAPI loads core pfSense libraries to issue system functions as would
ordinarily occur through the regular GUI interface.  For those inclined to
review the inner workings of the FauxAPI &lt;&gt; pfSense interface you can find them
located in the file <code>/etc/inc/fauxapi/fauxapi_pfsense_interface.inc</code></p>
<p>There are several sanity checks in place to make sure a user provided JSON
config will convert into the (slightly quirky) pfSense XML <code>config.xml</code> format
and then reload as expected in the same way.  However, because it is not a real
per action API interface it is still possible for the API caller to create
configuration changes that make no sense and hence break your pfSense host - as
the package name states, it is a "Faux" API, it's still very useful indeed.</p>
<p>Users of FauxAPI should also keep in mind that it is possible for pfSense (and
other packages) to change the arrangement of the configuration items they refer
to, while no such cases have been observed (yet) there is nothing stopping this
from occurring and thus package or system upgrades could cause breaking
configuration format changes.</p>
<p>A common source of confusion is the requirement to pass
the <em>FULL</em> configuration into the <code>config_set</code> call not just the portion of the
configuration you wish to adjust  - the reason for this is that FauxAPI
is a utility that interfaces with the pfSense <code>config.xml</code> file without
attempting to reach into the pfSense control layers (function calls) too much.
This is a deliberate position and helps make FauxAPI a low maintenance project
that should keep working with pfSense as new versions evolve.</p>
<p>Because FauxAPI is a utility that interfaces with the pfSense <code>config.xml</code> there
are some cases where reloading the configuration file is not enough and you
might need to "tickle" pfSense a little more to do what you want.  Good example
cases of this are getting newly defined network interfaces or VLANs to be
recognized.  These situations are easily handled by doing a <code>send_event</code> with
the payload <code>interface reload all</code> - see the example included below or refer
to a the resolution to a user issue here - <a href="https://github.com/ndejong/pfsense_fauxapi/issues/10">https://github.com/ndejong/pfsense_fauxapi/issues/10</a></p>
<hr>
<h3>
<a id="user-content-versions-and-testing" class="anchor" href="#versions-and-testing" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Versions and Testing</h3>
<p>The FauxAPI has been developed against pfSense 2.3.2 and has not (yet) been
tested against 2.3.0 or 2.3.1 or the currently in development 2.4 releases.  Further,
it it understood that package packaging changed between pfSense 2.2 and 2.3 so
it seems unlikely that it will work with 2.2 - very happy to accept github pull
requests to resolve if anyone cares to provide.</p>
<p>Testing is not (yet) thorough, there are however two client side test scripts
(1x Bash, 1x Python) that test all possible server side actions.  The tests only
test for success and not all possible failure modes.  This said, many failure
scenarios have been considered and tested in development to cause FauxAPI to
roll back if anything does not pass real-time sanity checks.</p>
<p>The FauxAPI REST call path has been name-spaced as v1 to accommodate future
situations that may introduce breaking REST interface changes, in the event this
occurs a new v2 release would be possible without breaking existing v1
implementations.</p>
<h3>
<a id="user-content-releases" class="anchor" href="#releases" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Releases</h3>
<h4>
<a id="user-content-v10---2016-11-20" class="anchor" href="#v10---2016-11-20" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>v1.0 - 2016-11-20</h4>
<ul>
<li>initial release</li>
</ul>
<h4>
<a id="user-content-v11---2017-08-12" class="anchor" href="#v11---2017-08-12" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>v1.1 - 2017-08-12</h4>
<ul>
<li>2x new API calls <code>alias_update_urltables</code> and <code>get_gateway_status</code>
</li>
<li>update documentation to address common points of confusion, expecially the
requirement to provide the <em>full</em> config file not just the portion to be updated.</li>
<li>testing against pfSense 2.3.3</li>
</ul>
<hr>
<p><a name="user-content-clientlibraries"></a></p>
<h3>
<a id="user-content-client-libraries" class="anchor" href="#client-libraries" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Client libraries</h3>
<h4>
<a id="user-content-python" class="anchor" href="#python" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Python</h4>
<ul>
<li><a href="https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs">https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs</a></li>
</ul>
<p>A Python interface to pfSense was perhaps the most desired end-goal at the onset
of the FauxAPI package project.  Anyone that has tried to parse the pfSense
<code>config.xml</code> files using a Python based library will understand that things
don't quite work out as expected or desired.</p>
<div class="highlight highlight-source-python"><pre>    <span class="pl-k">import</span> pprint, sys
    <span class="pl-k">from</span> fauxapi_lib <span class="pl-k">import</span> FauxapiLib
    FauxapiLib <span class="pl-k">=</span> FauxapiLib(<span class="pl-s"><span class="pl-pds">'</span>&lt;host-address&gt;<span class="pl-pds">'</span></span>, <span class="pl-s"><span class="pl-pds">'</span>&lt;fauxapi-key&gt;<span class="pl-pds">'</span></span>, <span class="pl-s"><span class="pl-pds">'</span>&lt;fauxapi-secret&gt;<span class="pl-pds">'</span></span>)

    aliases <span class="pl-k">=</span> FauxapiLib.config_get(<span class="pl-s"><span class="pl-pds">'</span>aliases<span class="pl-pds">'</span></span>)
    pprint.pprint(FauxapiLib.config_set(aliases, <span class="pl-s"><span class="pl-pds">'</span>aliases<span class="pl-pds">'</span></span>))</pre></div>
<p>Again, it is recommended to review <code>python-lib-test-example.py</code> to observe
worked examples with the library.  Of small note is that the Python library
supports the ability to get and set single sections of the pfSense system, not
just the entire system configuration as with the Bash library.</p>
<h4>
<a id="user-content-bash" class="anchor" href="#bash" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>Bash</h4>
<ul>
<li><a href="https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs">https://github.com/ndejong/pfsense_fauxapi/tree/master/client-libs</a></li>
</ul>
<p>The Bash client library makes it possible to add a line with
<code>source fauxapi_lib.sh</code> to your bash script and then access a pfSense host
configuration directly as a JSON string</p>
<div class="highlight highlight-source-shell"><pre>    <span class="pl-c1">source</span> fauxapi_lib.sh
    <span class="pl-k">export</span> fauxapi_auth=<span class="pl-s"><span class="pl-pds">`</span>fauxapi_auth <span class="pl-k">&lt;</span>fauxapi-key<span class="pl-k">&gt;</span> <span class="pl-k">&lt;</span>fauxapi-secret<span class="pl-k">&gt;</span><span class="pl-pds">`</span></span>

    fauxapi_config_get <span class="pl-k">&lt;</span>host-address<span class="pl-k">&gt;</span> <span class="pl-k">|</span> jq .data.config <span class="pl-k">&gt;</span> /tmp/config.json
    fauxapi_config_set <span class="pl-k">&lt;</span>host-address<span class="pl-k">&gt;</span> /tmp/config.json</pre></div>
<p>It is recommended to review <code>bash-lib-test-example.sh</code> to get a better idea of
how to use it.</p>
<h4>
<a id="user-content-php" class="anchor" href="#php" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>PHP</h4>
<p>A PHP client does not yet exist, it should be fairly easy to develop by
observing the Bash and Python examples - if you do please submit it as a github
pull request, there are no doubt others that will appreciate a PHP interface.</p>
<hr>
<h3>
<a id="user-content-api-authentication" class="anchor" href="#api-authentication" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>API Authentication</h3>
<p>A deliberate design decision to decouple FauxAPI authentication from both the
pfSense user authentication and the pfSense <code>config.xml</code> system.  This was done
to limit the possibility of an accidental API change that removes access to the
host.  It also seems more prudent to cause an API user to manually edit the
FauxAPI <code>credentials.ini</code> file located at <code>/etc/fauxapi/credentials.ini</code> - happy
to receive feedback about this.</p>
<p>The two sample FauxAPI keys (PFFAexample01 and PFFAexample02) and their
associated secrets in the sample <code>credentials.ini</code> file are hard-coded to be
inoperative, you must create entirely new values before your client scripts
will be able to issue commands to FauxAPI.</p>
<p>API authentication itself is performed on a per-call basis with the auth value
inserted as an additional <code>fauxapi-auth</code> HTTP request header, it can be
calculated as such:-</p>
<pre><code>    fauxapi-auth: &lt;apikey&gt;:&lt;timestamp&gt;:&lt;nonce&gt;:&lt;hash&gt;

    For example:-
    fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55
</code></pre>
<p>Where the <code>&lt;hash&gt;</code> value is calculated like so:-</p>
<pre><code>    &lt;hash&gt; = sha256(&lt;apisecret&gt;&lt;timestamp&gt;&lt;nonce&gt;)
</code></pre>
<p>This is all handled in the <a href="#user-content-clientlibraries">client libraries</a>
provided, but as can be seen it is relatively easy to implement even in a Bash
shell script - indeed a Bash include library <code>fauxapi_lib.sh</code> is provided that
does this for you.</p>
<p>Getting the API credentials right seems to be a common source of confusion in
getting started with FauxAPI because the rules about valid API keys and secret
values are pedantic to help make ensure poor choices are not made.</p>
<p>The API key+secret values you create for yourself in <code>/etc/fauxapi/credentials.ini</code>
have the following rules:-</p>
<ul>
<li>&lt;apikey_value&gt; and &lt;apisecret_value&gt; may have alphanumeric chars ONLY!</li>
<li>&lt;apikey_value&gt; MUST start with the prefix PFFA (pfSense Faux API)</li>
<li>&lt;apikey_value&gt; MUST be &gt;= 12 chars AND &lt;= 40 chars in total length</li>
<li>&lt;apisecret_value&gt; MUST be &gt;= 40 chars AND &lt;= 128 chars in length</li>
<li>you must not use the sample key/secret in the <code>credentials.ini</code> since they
are hard coded to fail.</li>
</ul>
<p>Consider using the following shell commands to generate valid values:-</p>
<h4>
<a id="user-content-apikey_value" class="anchor" href="#apikey_value" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>apikey_value</h4>
<div class="highlight highlight-source-shell"><pre>    <span class="pl-c1">echo</span> PPFA<span class="pl-s"><span class="pl-pds">`</span>head /dev/urandom <span class="pl-k">|</span> base64 -w0 <span class="pl-k">|</span> tr -d /+= <span class="pl-k">|</span> head -c 20<span class="pl-pds">`</span></span></pre></div>
<h4>
<a id="user-content-apisecret_value" class="anchor" href="#apisecret_value" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>apisecret_value</h4>
<div class="highlight highlight-source-shell"><pre>    <span class="pl-c1">echo</span> <span class="pl-s"><span class="pl-pds">`</span>head /dev/urandom <span class="pl-k">|</span> base64 -w0 <span class="pl-k">|</span> tr -d /+= <span class="pl-k">|</span> head -c 60<span class="pl-pds">`</span></span></pre></div>
<p>NB: Make sure the client side clock is within 60 seconds of the pfSense host
clock else the auth token values calculated by the client will not be valid - 60
seconds seems tight, however, provided you are using NTP to look after your
system time it's quite unlikely to cause issues - happy to receive feedback
about this.</p>
<hr>
<h3>
<a id="user-content-api-rest-actions" class="anchor" href="#api-rest-actions" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>API REST Actions</h3>
<p>The following REST based API actions are provided, cURL call request examples
are provided for each.  The API user is perhaps more likely interface with the
<a href="#user-content-clientlibraries">client libraries</a> as documented further below
rather than these REST end-points.</p>
<p>The framework around the FauxAPI has been put together with the idea of being
able to easily add more actions at a later time, if you have ideas for actions
that might be useful be sure to get in contact.</p>
<p>NB: the cURL requests below use the '--insecure' switch because many pfSense
deployments do not deploy certificate chain signed SSL certificates, a
reasonable improvement in this regard might be to implement certificate pinning
at the client side to hence remove scope for man-in-middle concerns.</p>
<p>NB2: the API user may append a <code>__debug=true</code> URL request parameter to
retrieve debug logs within the response data when required.</p>
<h3>
<a id="user-content-fauxapiv1actionconfig_get" class="anchor" href="#fauxapiv1actionconfig_get" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=config_get</code>
</h3>
<ul>
<li>Returns the current system configuration as a JSON formatted string.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params:
<ul>
<li>
<code>config_file</code> (optional, default=/cf/config/config.xml)</li>
</ul>
</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=config_get<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>{
    <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583012fe39f79<span class="pl-pds">"</span></span>,
    <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>config_get<span class="pl-pds">"</span></span>,
    <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
    <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
      <span class="pl-s"><span class="pl-pds">"</span>config_file<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>/cf/conf/config.xml<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>config<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>version<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>15.5<span class="pl-pds">"</span></span>,
        <span class="pl-s"><span class="pl-pds">"</span>staticroutes<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
        <span class="pl-s"><span class="pl-pds">"</span>snmpd<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
          <span class="pl-s"><span class="pl-pds">"</span>syscontact<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>rocommunity<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>public<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>syslocation<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>
        },
        <span class="pl-s"><span class="pl-pds">"</span>shaper<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
        <span class="pl-s"><span class="pl-pds">"</span>installedpackages<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
          <span class="pl-s"><span class="pl-pds">"</span>pfblockerngsouthamerica<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
            <span class="pl-s"><span class="pl-pds">"</span>config<span class="pl-pds">"</span></span><span class="pl-k">:</span> [
             <span class="pl-k">...</span>.</pre></div>
<p>Hint: use <code>jq</code> to obtain the config only, as such:-</p>
<div class="highlight highlight-source-shell"><pre>    cat /tmp/faux-config-get-output-from-curl.json <span class="pl-k">|</span> jq .data.config <span class="pl-k">&gt;</span> /tmp/config.json</pre></div>
<h3>
<a id="user-content-fauxapiv1actionconfig_set" class="anchor" href="#fauxapiv1actionconfig_set" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=config_set</code>
</h3>
<ul>
<li>Sets a full system configuration and (by default) takes a system config
backup and causes the system config to be reloaded once successfully written.
NB: be sure to pass the <em>FULL</em> system configuration in here, not just the
piece you wish to adjust!</li>
<li>HTTP: <strong><code>POST</code></strong>
</li>
<li>Params:
<ul>
<li>
<code>do_backup</code> (optional, default = true)</li>
<li>
<code>do_reload</code> (optional, default = true)</li>
</ul>
</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X POST \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        --header <span class="pl-s"><span class="pl-pds">"</span>Content-Type: application/json<span class="pl-pds">"</span></span> \
        --data @/tmp/config.json \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=config_set<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583065cae8993<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>config_set<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>do_backup<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-c1">true</span>,
        <span class="pl-s"><span class="pl-pds">"</span>do_reload<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-c1">true</span>
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionconfig_reload" class="anchor" href="#fauxapiv1actionconfig_reload" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=config_reload</code>
</h3>
<ul>
<li>Causes the pfSense system to perform a reload of the <code>config.xml</code> file, by
default this already happens when the config_set action occurs hence there
is normally no need to explicitly call this after a config_set action.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=config_reload<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>5831226e18326<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>config_reload<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionconfig_backup" class="anchor" href="#fauxapiv1actionconfig_backup" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=config_backup</code>
</h3>
<ul>
<li>Causes the system to take a configuration backup and add it to the regular
set of system change backups located on the host here <code>/cf/conf/backup/</code>
</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://&lt;fauxapi_host&gt;/fauxapi/v1/?action=config_backup<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583012fea254f<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>config_backup<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>backup_config_file<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>/cf/conf/backup/config-1479545598.xml<span class="pl-pds">"</span></span>
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionconfig_backup_list" class="anchor" href="#fauxapiv1actionconfig_backup_list" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=config_backup_list</code>
</h3>
<ul>
<li>Returns a list of the currently available system configuration backups
located in the <code>/cf/conf/backup/</code> host path.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=config_backup_list<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583065cb670db<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>config_backup_list<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>backup_files<span class="pl-pds">"</span></span><span class="pl-k">:</span> [
          {
            <span class="pl-s"><span class="pl-pds">"</span>filename<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>/cf/conf/backup/config-1479545598.xml<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>timestamp<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>20161119Z144635<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>description<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>fauxapi-PFFA4797d073@192.168.10.10: update via fauxapi for callid: 583012fea254f<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>version<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>15.5<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>filesize<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-c1">18535</span>
          },
          <span class="pl-k">...</span>.</pre></div>
<h3>
<a id="user-content-fauxapiv1actionconfig_restore" class="anchor" href="#fauxapiv1actionconfig_restore" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=config_restore</code>
</h3>
<ul>
<li>Returns a list of the currently available system configuration backups</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params:
<ul>
<li>
<code>config_file</code> (required, full path to the backup file to restore)</li>
</ul>
</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=config_restore&amp;config_file=/cf/conf/backup/config-1479545598.xml<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583126192a789<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>config_restore<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>config_file<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>/cf/conf/backup/config-1479545598.xml<span class="pl-pds">"</span></span>
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionsystem_stats" class="anchor" href="#fauxapiv1actionsystem_stats" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=system_stats</code>
</h3>
<ul>
<li>Returns various system stats.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=system_stats<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583d5ce3301f4<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>system_stats<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>stats<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
          <span class="pl-s"><span class="pl-pds">"</span>cpu<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>2<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>mem<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>16<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>uptime<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>9 Days 20 Hours 02 Minutes 08 Seconds<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>states<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>364/48000<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>temp<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>datetime<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>20161129Z104804<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>interfacestatistics<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>interfacestatus<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>cpufreq<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span><span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>load_average<span class="pl-pds">"</span></span><span class="pl-k">:</span> [
            <span class="pl-s"><span class="pl-pds">"</span>0.29<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>0.29<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>0.28<span class="pl-pds">"</span></span>
          ],
          <span class="pl-s"><span class="pl-pds">"</span>mbuf<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>1016/30414<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>mbufpercent<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>3<span class="pl-pds">"</span></span>,
          <span class="pl-s"><span class="pl-pds">"</span>statepercent<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>1<span class="pl-pds">"</span></span>
        }
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actiongateway_status" class="anchor" href="#fauxapiv1actiongateway_status" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=gateway_status</code>
</h3>
<ul>
<li>Returns gateway status data.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=gateway_status<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>598ecf3e7011e<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>gateway_status<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>gateway_status<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
          <span class="pl-s"><span class="pl-pds">"</span>10.22.33.1<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
            <span class="pl-s"><span class="pl-pds">"</span>monitorip<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>8.8.8.8<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>srcip<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>10.22.33.100<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>name<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>GW_WAN<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>delay<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>4.415ms<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>stddev<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>3.239ms<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>loss<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>0.0%<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>status<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>none<span class="pl-pds">"</span></span>
          }
        }
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionsend_event" class="anchor" href="#fauxapiv1actionsend_event" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=send_event</code>
</h3>
<ul>
<li>Performs a pfSense "send_event" command to cause various pfSense system
actions, the following standard pfSense send_event combinations are permitted:-
<ul>
<li>filter: reload, sync</li>
<li>interface: all, newip, reconfigure</li>
<li>service: reload, restart, sync</li>
</ul>
</li>
<li>HTTP: <strong><code>POST</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X POST \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        --header <span class="pl-s"><span class="pl-pds">"</span>Content-Type: application/json<span class="pl-pds">"</span></span> \
        --data <span class="pl-s"><span class="pl-pds">"</span>[<span class="pl-cce">\"</span>interface reload all<span class="pl-cce">\"</span>]<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=send_event<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>58312bb3398bc<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>send_event<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionrule_get" class="anchor" href="#fauxapiv1actionrule_get" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=rule_get</code>
</h3>
<ul>
<li>Returns the numbered list of loaded pf rules from a <code>pfctl -sr -vv</code> command
on the pfSense host.  An empty rule_number parameter causes all rules to be
returned.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params:
<ul>
<li>
<code>rule_number</code> (optional, default = null)</li>
</ul>
</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=rule_get&amp;rule_number=5<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>583c279b56958<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>rule_get<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>rules<span class="pl-pds">"</span></span><span class="pl-k">:</span> [
          {
            <span class="pl-s"><span class="pl-pds">"</span>rule<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>anchor <span class="pl-cce">\"</span>openvpn/*<span class="pl-cce">\"</span> all<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>evaluations<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>14134<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>packets<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>0<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>bytes<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>0<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>states<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>0<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>inserted<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>21188<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>statecreations<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>0<span class="pl-pds">"</span></span>
          }
        ]
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionalias_update_urltables" class="anchor" href="#fauxapiv1actionalias_update_urltables" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=alias_update_urltables</code>
</h3>
<ul>
<li>Causes the pfSense host to immediately update any urltable alias entries
from their (remote) source URLs.  Optionally update just one table by
specifying the table name, else all tables are updated.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params:
<ul>
<li>
<code>table</code> (optional, default = null)</li>
</ul>
</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=alias_update_urltables<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>598ec756b4d09<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>alias_update_urltables<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>data<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
        <span class="pl-s"><span class="pl-pds">"</span>updates<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
          <span class="pl-s"><span class="pl-pds">"</span>bruteforceblocker<span class="pl-pds">"</span></span><span class="pl-k">:</span> {
            <span class="pl-s"><span class="pl-pds">"</span>url<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/bruteforceblocker.ipset<span class="pl-pds">"</span></span>,
            <span class="pl-s"><span class="pl-pds">"</span>status<span class="pl-pds">"</span></span><span class="pl-k">:</span> [
              <span class="pl-s"><span class="pl-pds">"</span>no changes.<span class="pl-pds">"</span></span>
            ]
          }
        }
      }
    }</pre></div>
<h3>
<a id="user-content-fauxapiv1actionsystem_reboot" class="anchor" href="#fauxapiv1actionsystem_reboot" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a><code>/fauxapi/v1/?action=system_reboot</code>
</h3>
<ul>
<li>Just as it says, reboots the system.</li>
<li>HTTP: <strong><code>GET</code></strong>
</li>
<li>Params: none</li>
</ul>
<p><em>Example Request</em></p>
<div class="highlight highlight-source-shell"><pre>    curl \
        -X GET \
        --silent \
        --insecure \
        --header <span class="pl-s"><span class="pl-pds">"</span>fauxapi-auth: PFFA4797d073:20161119Z144328:833a45d8:9c4f96ab042f5140386178618be1ae40adc68dd9fd6b158fb82c99f3aaa2bb55<span class="pl-pds">"</span></span> \
        <span class="pl-s"><span class="pl-pds">"</span>https://192.168.10.10/fauxapi/v1/?action=system_reboot<span class="pl-pds">"</span></span></pre></div>
<p><em>Example Response</em></p>
<div class="highlight highlight-source-js"><pre>    {
      <span class="pl-s"><span class="pl-pds">"</span>callid<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>58312bb3487ac<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>action<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>system_reboot<span class="pl-pds">"</span></span>,
      <span class="pl-s"><span class="pl-pds">"</span>message<span class="pl-pds">"</span></span><span class="pl-k">:</span> <span class="pl-s"><span class="pl-pds">"</span>ok<span class="pl-pds">"</span></span>
    }</pre></div>
<hr>
<h3>
<a id="user-content-fauxapi-license" class="anchor" href="#fauxapi-license" aria-hidden="true"><span aria-hidden="true" class="octicon octicon-link"></span></a>FauxAPI License</h3>
<pre><code>Copyright 2016 Nicholas de Jong  

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
</code></pre>
<!--READMEEND-->
</div>

<?php 
    include('foot.inc');
?>