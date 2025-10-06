#!/usr/bin/env bash

set -o posix

DBFILENAME=""

while getopts "f:" option
do
    case $option in
        f) # File
            DBFILENAME="${OPTARG}"
            ;;
    esac
done

if [ -z "${DBFILENAME}" ]; then
    echo ""
    echo " Usage:"
    echo "   $0 -f db_file_path.sql "
    echo ""
    exit 1
fi

DBFILE="${DBFILENAME}"

if [ ! -f $DBFILE ]; then
    DBFILE="/app/${DBFILENAME}"
fi

if [ ! -f $DBFILE ]; then
    echo "Database file ${DBFILE} does not exist."
    exit 1
fi

echo "Database file: ${DBFILENAME}"

echo "mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" --database="${MYSQL_DATABASE}" "${MYSQL_DATABASE}" -e 'source ${DBFILE};' "

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" --database="${MYSQL_DATABASE}" "${MYSQL_DATABASE}" < "${DBFILE}"
