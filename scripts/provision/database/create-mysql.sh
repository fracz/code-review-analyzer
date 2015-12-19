#!/usr/bin/env bash

mysql -h localhost -u root <<< "CREATE DATABASE IF NOT EXISTS $1; GRANT ALL ON $1.* TO '$2' IDENTIFIED BY '$3';"
