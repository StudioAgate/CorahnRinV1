@echo off

SET mypath=%~dp0

cd "%mypath:~0,-1%/.."

REM These vars must be set in the dev environment for the project to be deployable.
set ssh_remote=%CORAHNRINV1_DEPLOY_REMOTE%
set prod_dir=%CORAHNRINV1_DEPLOY_DIR%

@echo on

git push origin main && ssh %ssh_remote% %prod_dir%/bin/deploy.bash

git fetch --all --prune
