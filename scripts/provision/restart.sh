#!/usr/bin/env bash

service postgresql stop
#service mysql stop
service php5-fpm stop
#service hhvm stop
service nginx stop
service php5-fpm start
#service hhvm start
service nginx start
#service mysql start
service postgresql start
