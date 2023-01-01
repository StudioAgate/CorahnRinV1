#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd ${DIR}/../

# These vars must be set in the dev environment for the project to be deployable.
ssh_remote=${CORAHNRINV1_DEPLOY_REMOTE}
prod_dir=${CORAHNRINV1_DEPLOY_DIR}

if [ -z $ssh_remote ]; then
    echo "Please set up the CORAHNRINV1_DEPLOY_REMOTE environment variable"
    exit 1
fi

if [ -z $prod_dir ]; then
    echo "Please set up the CORAHNRINV1_DEPLOY_DIR environment variable"
    exit 1
fi

git push origin main && ssh ${ssh_remote} ${prod_dir}/bin/deploy.bash

git fetch --all --prune
