#!/usr/bin/env bash
# This script runs the php-scoper, and overwrites the embedded library inside of Cyr_To_Lat.

PHP_VERSION=$(php -v | tac | tail -n 1 | cut -d " " -f 2 | cut -c 1-3)

if [[ $PHP_VERSION != "7.3" && $PHP_VERSION != "7.4" ]]; then
	echo "This script must be used with php 7.3 or 7.4 only"
	exit
fi

SCRIPTDIR="$(dirname "$0")"
CYR2LAT_PLUGIN_PATH="${1:-$SCRIPTDIR/..}"

cd "${CYR2LAT_PLUGIN_PATH}" || exit

CYR2LAT_BUILD_PATH="build"
CYR2LAT_LIB_PATH="lib/polyfill-mbstring"

CYR2LAT_SCOPER_PREFIX="${2:-Cyr_To_Lat}"

if [[ ! -d "CYR2LAT_BUILD_PATH" ]]; then
    mkdir -p "$CYR2LAT_BUILD_PATH"
fi

if [[ ! -d "CYR2LAT_LIB_PATH" ]]; then
    mkdir -p "$CYR2LAT_LIB_PATH"
fi

rm -rf ${CYR2LAT_BUILD_PATH:?}/*

composer config repositories.polyfill-mbstring '{"type": "vcs", "url": "https://github.com/kagg-design/polyfill-mbstring.git", "no-api": true}'
composer config repositories.php-scoper '{"type": "vcs", "url": "https://github.com/humbug/php-scoper.git", "no-api": true}'
composer config github-protocols https
composer config platform.php 7.2

composer require symfony/polyfill-mbstring:dev-master humbug/php-scoper:dev-master

vendor/humbug/php-scoper/bin/php-scoper add-prefix -vv --no-interaction --prefix=$CYR2LAT_SCOPER_PREFIX --config=.make/polyfill-mbstring.scoper.inc.php
vendor/squizlabs/php_codesniffer/bin/phpcbf --standard=phpcs.xml build

composer remove symfony/polyfill-mbstring humbug/php-scoper

RESULT=$?

rm -rf ${CYR2LAT_LIB_PATH:?}/*
cp -r ${CYR2LAT_BUILD_PATH}/* ${CYR2LAT_LIB_PATH}
rm -rf ${CYR2LAT_BUILD_PATH:?}/*
rm -rd ${CYR2LAT_BUILD_PATH:?}

# Restore main composer files to the current branch version.
git checkout -- composer.json composer.lock

exit $RESULT
