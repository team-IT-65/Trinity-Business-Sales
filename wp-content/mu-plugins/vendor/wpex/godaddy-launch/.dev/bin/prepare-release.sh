#!/usr/bin/env bash
# A helper script to make tagging and releasing the Publish Guide a one command process.

set -o errexit
set -o nounset

function info() {
    printf "\e[0;1;37m➜ $*\e[0m\n"
}

function success() {
    printf "\e[0;32m✔︎ $*\e[0m\n"
}

info "Preparing to tag version '$1'"

info "Bumping version in source files"
sed -i '' -e "s/Version.*$/Version: $1/" godaddy-launch.php
sed -i '' -e "s/const VERSION.*$/const VERSION = '$1';/" includes/Application.php

info "Cleaning up and committing changes"
npm run clean:commit
git commit -am "build for production"

info "Tagging release and pushing changes"
git tag $1
git push origin master --tags

info "Updating System Plugin with tagged release"
git clone git@github.com:gdcorp-wordpress/wp-paas-system-plugin.git
cd wp-paas-system-plugin

sed -i '' -e "s/\"wpex\/godaddy-launch\".*$/\"wpex\/godaddy-launch\": \"$1\",/" composer.json
composer update --ignore-platform-reqs

git checkout -b release-godaddy-launch-$1
git commit -am "Update GoDaddy Launch to $1"

git push --set-upstream origin release-godaddy-launch-$1
success "GoDaddy Launch published to System Plugin"
echo ""
echo "Submit the PR:"
echo "https://github.com/gdcorp-wordpress/wp-paas-system-plugin/compare/develop...release-godaddy-launch-$1"

cd ../
rm -rf wp-paas-system-plugin