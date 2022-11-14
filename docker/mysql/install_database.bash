#!/usr/bin/env bash

set -o posix

DBFILENAME="install.sql"

while getopts "f:" option
do
    case $option in
        f) # File
            DBFILENAME="${OPTARG}"
            ;;
    esac
done

DBFILE="/app/${DBFILENAME}"

if [ ! -f $DBFILE ]; then
    echo "Database file ${DBFILENAME} does not exist."
    exit 1
fi

echo "Database file: ${DBFILENAME}"

echo "mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" --database="${MYSQL_DATABASE}" "${MYSQL_DATABASE}" < "${DBFILE}""

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" --database="${MYSQL_DATABASE}" "${MYSQL_DATABASE}" < "${DBFILE}"
