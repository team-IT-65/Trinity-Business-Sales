#!/usr/bin/env bash
# A helper script to make tagging and releasing the Publish Guide a one command process.

set -o errexit # script should exit whenever it encounters an error
set -o nounset # reference an undefined variable as an error

function info() {
    printf "\e[0;1;37m➜ $*\e[0m\n"
}

function success() {
    printf "\e[0;32m✔︎ $*\e[0m\n"
}

info "Preparing to tag version '$1'"

info "Cloning System Plugin repo"
git clone git@github.com:gdcorp-wordpress/wp-paas-system-plugin.git

info "Bumping version in source files"
sed -i '' -e "s/Version.*$/Version: $1/" expert-banner.php

info "Cleaning up and committing changes"
npm run build
git commit -am "build for production"

info "Tagging release and pushing changes"
git tag $1
git push origin main --tags

info "Updating System Plugin with tagged release"
cd wp-paas-system-plugin

sed -i '' -e "s/\"wpex\/expert-banner\".*$/\"wpex\/expert-banner\": \"$1\",/" composer.json
composer update --ignore-platform-reqs

git checkout -b release-expert-banner-$1
git commit -am "Update Expert Banner to $1"

git push --set-upstream origin release-expert-banner-$1
success "Expert Banner published to System Plugin"
echo ""
echo "Submit the PR:"
echo "https://github.com/gdcorp-wordpress/wp-paas-system-plugin/compare/develop...release-expert-banner-$1"

cd ../
rm -rf wp-pass-system-plugin
