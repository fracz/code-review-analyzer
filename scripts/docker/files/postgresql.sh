#!/usr/bin/env bash

if [ ! -d '/var/lib/postgresql/data' ]; then
	su -c '/usr/lib/postgresql/9.4/bin/initdb -d /var/lib/postgresql/data' - postgres
fi

su -c '/usr/lib/postgresql/9.4/bin/postgres -D /var/lib/postgresql/data' - postgres
