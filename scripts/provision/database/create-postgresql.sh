#!/usr/bin/env bash

su -c "createuser -s $2" - postgres
rc=$?; if [[ $rc == 0 ]]; then
	echo "Created PSQL user $2"
fi
su -c "createdb $1" - postgres
rc=$?; if [[ $rc == 0 ]]; then
	echo "Created PSQL database $1"
fi

