#!/bin/sh

kill `ps x | grep 'php run.php$' | head -n 1 | cut -d ' ' -f 1`
kill `ps x | grep 'php run.php$' | head -n 1 | cut -d ' ' -f 2`
kill `ps x | grep 'php run.php$' | head -n 1 | cut -d ' ' -f 3`
cd /var/www/moodle/blocks/gchat/ws/ && php run.php

