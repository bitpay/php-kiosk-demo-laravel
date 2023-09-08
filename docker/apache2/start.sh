#!/bin/bash

mkdir /etc/apache2/ssl 2> /dev/null

a2enmod rewrite
a2enmod headers
a2enmod proxy proxy_html proxy_http xml2enc ssl http2
service apache2 restart

/usr/sbin/apache2ctl -D FOREGROUND