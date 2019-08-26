#!/usr/bin/env bash

mysql -uroot -p${MYSQL_ROOT_PASSWORD} < /app/install.sql
