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
	$(info  $(indent) Run `make update-phpunit [PHP="x.x"]` to install the phpunit library according to php version. If PHP is omitted, update will be done for current PHP version)
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
	@yarn; yarn run build:dev

yarn-install-prod:
	$(info Installing Yarn dependencies)
	@yarn; yarn run build:prod


# Scope
.PHONY: scope

scope:
	$(info Scoping external libraries)
	@.make/scoper.sh


# Update phpunit
.PHONY: update-phpunit

update-phpunit:
	$(info Updating phpunit library)
	@.make/update-phpunit.sh ${PHP}


# Prepare for distribution
.PHONY: dist

dist: install-prod
dist:
	$(info Preparing plugin for distribution)
	@wp dist-archive .
