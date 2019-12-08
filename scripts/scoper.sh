#!/usr/bin/env bash
# This script runs the php-scoper, and overwrites the embedded wp-background-processing library inside of Cyr_To_Lat.

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

vendor/humbug/php-scoper/bin/php-scoper add-prefix -vv --no-interaction --prefix=$TWIG_SCOPER_PREFIX --config=scripts/wp-background-processing.scoper.inc.php
vendor/squizlabs/php_codesniffer/bin/phpcbf --standard=phpcs.xml build

rm -rf ${CYR2LAT_LIB_PATH:?}/*
cp -r ${CYR2LAT_BUILD_PATH}/* ${CYR2LAT_LIB_PATH}
rm -rf ${CYR2LAT_BUILD_PATH:?}/*
rm -rd ${CYR2LAT_BUILD_PATH:?}
