#!/usr/bin/env bash

kill -INT `head -1 /var/lib/postgresql/data/postmaster.pid`
