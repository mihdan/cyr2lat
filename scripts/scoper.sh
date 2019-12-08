#!/usr/bin/env bash
# This script runs the php-scoper, and overwrites the embedded wp-background-processing library inside of Cyr_To_Lat.

PHP_VERSION=$(php -v | tac | tail -n 1 | cut -d " " -f 2 | cut -c 1-3)

if [[ $PHP_VERSION != "7.3" && $PHP_VERSION != "7.4" ]]; then
	echo "This script must be used inder php 7.3 or 7.4 only"
	exit
fi

SCRIPTDIR="$(dirname "$0")"
CYR2LAT_PLUGIN_PATH="${1:-$SCRIPTDIR/..}"

cd "${CYR2LAT_PLUGIN_PATH}" || exit

CYR2LAT_BUILD_PATH="build"
CYR2LAT_LIB_PATH="lib/wp-background-processing"

TWIG_SCOPER_PREFIX="${2:-Cyr_To_Lat}"

if [[ ! -d "CYR2LAT_BUILD_PATH" ]]; then
    mkdir -p "$CYR2LAT_BUILD_PATH"
fi

if [[ ! -d "CYR2LAT_LIB_PATH" ]]; then
    mkdir -p "$CYR2LAT_LIB_PATH"
fi

rm -rf ${CYR2LAT_BUILD_PATH:?}/*

composer require humbug/php-scoper:dev-master --ignore-platform-reqs

vendor/humbug/php-scoper/bin/php-scoper add-prefix -vv --no-interaction --prefix=$TWIG_SCOPER_PREFIX --config=scripts/wp-background-processing.scoper.inc.php
vendor/squizlabs/php_codesniffer/bin/phpcbf --standard=phpcs.xml build

composer remove humbug/php-scoper --ignore-platform-reqs
composer update

rm -rf ${CYR2LAT_LIB_PATH:?}/*
cp -r ${CYR2LAT_BUILD_PATH}/* ${CYR2LAT_LIB_PATH}
rm -rf ${CYR2LAT_BUILD_PATH:?}/*
rm -rd ${CYR2LAT_BUILD_PATH:?}
