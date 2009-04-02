#!/bin/bash

cd /tmp
wget http://www.mydnsconfig.org/downloads/MyDNSConfig-3-stable.tar.gz
tar xvfz MyDNSConfig-3-stable.tar.gz
cd mydnsconfig/install/
php -q update.php
rm -rf /tmp/mydnsconfig/install