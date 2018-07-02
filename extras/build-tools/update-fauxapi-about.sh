#!/bin/bash

source_md_filename=$(realpath $(dirname $(realpath $0))/../../README.md)
target_php_filename=$(realpath $(dirname $(realpath $0))/../../pfSense-pkg-FauxAPI/files/usr/local/www/fauxapi/admin/about.php)

# ===

# render markdown into html at github
jq --slurp --raw-input '{"text": "\(.)", "mode": "markdown"}' < $source_md_filename | curl --silent --data @- https://api.github.com/markdown > /tmp/fauxapi-about.content

# head
head -n `grep -n READMESTART ${target_php_filename} | cut -d':' -f1` ${target_php_filename} > /tmp/fauxapi-about.head

# tail
tail -n +`grep -n READMEEND ${target_php_filename} | cut -d':' -f1` ${target_php_filename} > /tmp/fauxapi-about.tail

# re-create
cat /tmp/fauxapi-about.head > /tmp/fauxapi-about.php
cat /tmp/fauxapi-about.content >> /tmp/fauxapi-about.php
cat /tmp/fauxapi-about.tail >> /tmp/fauxapi-about.php

# move the result file into place and clean up
mv /tmp/fauxapi-about.php ${target_php_filename}
rm -f /tmp/fauxapi-about.*
