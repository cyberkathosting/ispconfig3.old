#!/bin/bash

cd /tmp
svn export svn://svn.ispconfig.org/ispconfig3/branches/mydnsconfig/
cd mydnsconfig/install
php -q update.php
cd /tmp
rm -rf /tmp/mydnsconfig