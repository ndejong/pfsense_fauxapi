#!/bin/bash

# render via github
jq --slurp --raw-input '{"text": "\(.)", "mode": "markdown"}' < ../README.md | curl --silent --data @- https://api.github.com/markdown > README.html

# inject this new HTML into place
fauxapi_about='../pfSense-pkg-FauxAPI/files/usr/local/www/fauxapi/admin/about.php'

# head 
head -n `grep -n READMESTART ${fauxapi_about} | cut -d':' -f1` ${fauxapi_about} > /tmp/fauxapi-about-head

# tail
tail -n +`grep -n READMEEND ${fauxapi_about} | cut -d':' -f1` ${fauxapi_about} > /tmp/fauxapi-about-tail

# re-create
cat /tmp/fauxapi-about-head > /tmp/fauxapi-about.php
cat README.html >> /tmp/fauxapi-about.php
cat /tmp/fauxapi-about-tail >> /tmp/fauxapi-about.php

mv /tmp/fauxapi-about.php ${fauxapi_about}

