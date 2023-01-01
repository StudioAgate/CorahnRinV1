
cd "$PSScriptRoot/.."

# These vars must be set in the dev environment for the project to be deployable.
Set-Variable -Name "ssh_remote" -Value "$env:CORAHNRINV1_DEPLOY_REMOTE"
Set-Variable -Name "prod_dir" -Value "$env:CORAHNRINV1_DEPLOY_DIR"

if ([string]::IsNullOrEmpty($ssh_remote.Trim())) {
    echo "Please set up the CORAHNRINV1_DEPLOY_REMOTE environment variable"
    exit 1
}

if ([string]::IsNullOrEmpty($prod_dir.Trim())) {
    echo "Please set up the CORAHNRINV1_DEPLOY_DIR environment variable"
    exit 1
}

git push origin main

ssh $ssh_remote "$prod_dir/bin/deploy.bash"

git fetch --all --prune
