# Tutorial: https://cs.colby.edu/maxwell/courses/tutorials/maketutor/
# Docs: https://www.gnu.org/software/make/

empty:=
space:= $(empty) $(empty)
indent:=$(space)$(space)

# Help
.PHONY: help

help:
	$(info :: Install)
	$(info  $(indent) Run `make install` to install the dependencies)
	$(info  $(indent) Run `make install-prod` to install the dependencies in production mode)
	$(info  $(indent) Run `make composer-install` to only install Composer dependencies)
	$(info  $(indent) Run `make composer-install-prod` to only install Composer dependencies in production mode)
	$(info  $(indent) Run `make yarn-install` to only install Yarn dependencies)
	$(info  $(indent) Run `make yarn-install-prod` to only install Yarn dependencies in production mode)
	$(info :: Deployment)
	$(info  $(indent) Run `make scope` to scope external libraries)
	$(info  $(indent) Run `make dist` to prepare plugin for distribution)


# Install
.PHONY: install install-prod

install: composer-install
install: yarn-install

install-prod: composer-install-prod
install-prod: yarn-install-prod


## Composer
.PHONY: composer-install composer-install-prod

composer-install:
	$(info Installing Composer dependencies)
	@composer install

composer-install-prod:
	$(info Installing Composer dependencies)
	@composer install --no-dev


## Yarn
.PHONY: yarn-install yarn-install-prod

yarn-install:
	$(info Installing Yarn dependencies)
	@yarn; yarn run dev

yarn-install-prod:
	$(info Installing Yarn dependencies)
	@yarn; yarn run prod


# Scope
.PHONY: scope

scope:
	$(info Scoping external libraries)
	@rm -f composer.lock
	@composer config repositories.wp-background-processing vcs https://github.com/kagg-design/wp-background-processing.git
	@composer config repositories.polyfill-mbstring vcs https://github.com/kagg-design/polyfill-mbstring.git
	@composer config repositories.php-scoper vcs https://github.com/humbug/php-scoper.git
	@composer config platform.php 7.4
	@composer require --no-scripts deliciousbrains/wp-background-processing:dev-master symfony/polyfill-mbstring humbug/php-scoper
	@bin/scoper Cyr_To_Lat\\WP_Background_Processing wp-background-processing
	@bin/scoper Cyr_To_Lat polyfill-mbstring
	# Restore main composer files to the current branch version.
	@git checkout -- composer.json
	@composer update --no-scripts


# Prepare for distribution
.PHONY: dist

dist: install-prod
dist:
	$(info Preparing plugin for distribution)
	@wp dist-archive .
