#!/bin/bash
set -e

uid=$(stat -c %u /srv)
gid=$(stat -c %g /srv)

if [[ "$@" == "" ]]; then
    php-fpm7.4
    exit $?
fi

sed -i "s/user = www-data/user = ${RUN_USER}/g" /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
sed -i "s/group = www-data/group = ${RUN_USER}/g" /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
sed -i -r "s/${RUN_USER}:x:\d+:\d+:/${RUN_USER}:x:$uid:$gid:/g" /etc/passwd
sed -i -r "s/${RUN_USER}:x:\d+:/${RUN_USER}:x:$gid:/g" /etc/group
chown ${RUN_USER} /home

mkdir -p vendor
chown -R ${RUN_USER}:${RUN_USER} /srv/

if [ $# -eq 0 ]; then
    php-fpm
else
    exec gosu ${RUN_USER} "$@"
fi
