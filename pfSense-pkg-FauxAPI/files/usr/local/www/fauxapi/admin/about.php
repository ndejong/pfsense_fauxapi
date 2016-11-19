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
    <h3>FauxAPI</h3>
    <p>A REST API interface for pfSense to facilitate dev-ops.</p>
    <ul>
        <li><a href="https://github.com/ndejong/pfsense_fauxapi">https://github.com/ndejong/pfsense_fauxapi</a></li>
    </ul>
    
    <h3>License</h3>
    <pre>
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
    </pre>
</div>

<?php 
    include('foot.inc');
?>