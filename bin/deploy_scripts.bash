#!/bin/bash

set -e

# bin/ directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Project directory
cd ${DIR}/../

# Used to dump a new autoloader because classmap will make autoload fail if some new classes are created between deploys
/home/${USER}/bin/composer dump-autoload --no-dev

/home/${USER}/bin/composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --apcu-autoloader \
    --classmap-authoritative \
    --no-progress \
    --no-ansi \
    --no-interaction

# Clear all cache
rm -rf \
    tmp/* \
    webroot/css/main.min.css \
    webroot/css/pages/ \
    webroot/files/characters_export \
    webroot/js/main.min.js \
    webroot/js/pages/
