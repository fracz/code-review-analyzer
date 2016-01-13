#!/usr/bin/env bash

sv restart postgresql
#sv restart mysql
sv restart php5-fpm
#sv restart hhvm
sv restart nginx
