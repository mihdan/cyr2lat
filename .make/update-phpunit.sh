#!/bin/bash
# This file updates phpunit library depending on current php version, or using 1st argument.
# Usage:
#   composer-update.sh - to update phpunit library for current php version (or do nothing if already updated)
#   composer-update.sh x.x - to force update phpunit library for specific php version, where x.x = 5.6|7.0|7.1|7.2|7.3|7.4|8.0

if [[ $1 == '' ]]; then
  PHP_VERSION=$(php -v | tac | tail -n 1 | cut -d " " -f 2 | cut -c 1-3)

  if grep -qE 'version.+5\.7' 'vendor/phpunit/phpunit/src/Runner/Version.php'; then
    CURRENT_PHP_UNIT='5.7'
  fi

  if grep -qE 'version.+6\.5' 'vendor/phpunit/phpunit/src/Runner/Version.php'; then
    CURRENT_PHP_UNIT='6.5'
  fi

  if grep -qE 'version.+7\.5' 'vendor/phpunit/phpunit/src/Runner/Version.php'; then
    CURRENT_PHP_UNIT='7.5'
  fi

  if grep -qE 'version.+8\.5' 'vendor/phpunit/phpunit/src/Runner/Version.php'; then
    CURRENT_PHP_UNIT='8.5'
  fi

  if grep -qE 'version.+9\.5' 'vendor/phpunit/phpunit/src/Runner/Version.php'; then
    CURRENT_PHP_UNIT='9.5'
  fi

  echo "CURRENT_PHP_UNIT: $CURRENT_PHP_UNIT"
else
  PHP_VERSION=$1
fi

echo "PHP_VERSION: $PHP_VERSION"

if [[ $PHP_VERSION == '5.6' ]]; then
  PHP_UNIT='5.7'
fi

if [[ $PHP_VERSION == '7.0' ]]; then
  PHP_UNIT='6.5'
fi

if [[ $PHP_VERSION == '7.1' ]]; then
  PHP_UNIT='7.5'
fi

if [[ $PHP_VERSION == '7.2' ]]; then
  PHP_UNIT='8.5'
fi

if [[ $PHP_VERSION == '7.3' || $PHP_VERSION == '7.4' || $PHP_VERSION == '8.0' ]]; then
  PHP_UNIT='9.5'
fi

if [[ $PHP_UNIT == '' ]]; then
  echo "Wrong PHP version: $PHP_VERSION"
  exit 1
fi

# Restore test files to the current branch version.
git checkout -- tests

if [[ $PHP_UNIT == '5.7' || $PHP_UNIT == '6.5' || $PHP_UNIT == '7.5' ]]; then
  find tests -type f -exec sed -i "s/: void / /g" {} \;
fi

if [[ $CURRENT_PHP_UNIT == "$PHP_UNIT" ]]; then
  # Do nothing if current version of phpunit is the same as required. Important on CI.
  # Anytime force update available specifying first argument like 'composer-update.sh 7'
  exit 0
fi

echo "Building with phpunit-$PHP_UNIT"

if [[ $PHP_UNIT == '5.7' ]]; then
  composer config platform.php 5.6
  composer remove --dev --with-all-dependencies lucatume/function-mocker phpunit/phpunit 10up/wp_mock
  composer require --dev lucatume/function-mocker phpunit/phpunit 10up/wp_mock
fi

if [[ $PHP_UNIT == '6.5' ]]; then
  composer config platform.php 7.0
  composer remove --dev --with-all-dependencies lucatume/function-mocker phpunit/phpunit 10up/wp_mock symfony/config php-coveralls/php-coveralls
  composer require --dev lucatume/function-mocker phpunit/phpunit 10up/wp_mock symfony/config php-coveralls/php-coveralls
fi

if [[ $PHP_UNIT == '7.5' ]]; then
  composer config platform.php 7.1
  composer remove --dev --with-all-dependencies lucatume/function-mocker phpunit/phpunit 10up/wp_mock
  composer require --dev lucatume/function-mocker phpunit/phpunit 10up/wp_mock
fi

if [[ $PHP_UNIT == '8.5' ]]; then
  composer config platform.php 7.2
  composer remove --dev --with-all-dependencies lucatume/function-mocker phpunit/phpunit 10up/wp_mock
  composer require --dev lucatume/function-mocker phpunit/phpunit 10up/wp_mock
fi

if [[ $PHP_UNIT == '9.5' ]]; then
  composer config platform.php 7.3
  composer remove --dev --with-all-dependencies lucatume/function-mocker phpunit/phpunit 10up/wp_mock
  composer require --dev lucatume/function-mocker phpunit/phpunit 10up/wp_mock
fi

RESULT=$?

# Restore main composer files to the current branch version.
git checkout -- composer.json composer.lock

exit $RESULT
