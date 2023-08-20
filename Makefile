.PHONY: validate install update tests phpcs phpcbf php81compatibility php82compatibility phpstan analyze clean ci

define header =
    @if [ -t 1 ]; then printf "\n\e[37m\e[100m  \e[104m $(1) \e[0m\n"; else printf "\n### $(1)\n"; fi
endef

#~ Composer dependency
validate:
	$(call header,Composer Validation)
	@composer validate

install:
	$(call header,Composer Install)
	@composer install

update:
	$(call header,Composer Update)
	@composer update

composer.lock: install

#~ Vendor binaries dependencies
vendor/bin/phpcbf: composer.lock
vendor/bin/phpcs: composer.lock
vendor/bin/phpstan: composer.lock
vendor/bin/phpunit: composer.lock

#~ Report directories dependencies
build/reports/phpunit:
	@mkdir -p build/reports/phpunit

build/reports/phpcs:
	@mkdir -p build/reports/cs

build/cache/phpcs:
	@mkdir -p build/cache/cs

build/reports/phpstan:
	@mkdir -p build/reports/phpstan

phpcs: vendor/bin/phpcs build/reports/phpcs build/cache/phpcs
	$(call header,Run Code Sniffer)
	@./vendor/bin/phpcs --cache=./build/cache/cs/cs.cache -p --report-full --report-checkstyle=./build/reports/cs/php-websocket-client.xml

phpcbf: vendor/bin/phpcbf
	$(call header,Fixing Code Style)
	@./vendor/bin/phpcbf --standard=./ci/phpcs/ruleset.xml src/ tests/

php81compatibility: vendor/bin/phpstan build/reports/phpstan
	$(call header,Checking PHP 8.1 compatibility)
	@./vendor/bin/phpstan analyse --configuration=./phpstan-compatibility81.neon.dist --error-format=checkstyle > ./build/reports/phpstan/php81-compatibility.xml

php82compatibility: vendor/bin/phpstan build/reports/phpstan
	$(call header,Checking PHP 8.2 compatibility)
	@./vendor/bin/phpstan analyse --configuration=./phpstan-compatibility82.neon.dist --error-format=checkstyle > ./build/reports/phpstan/php82-compatibility.xml

phpstan: vendor/bin/phpstan build/reports/phpstan
	$(call header,Running Static Analyze)
	@./vendor/bin/phpstan analyse --error-format=checkstyle > ./build/reports/phpstan/phpstan.xml

analyze: vendor/bin/phpstan build/reports/phpstan
	$(call header,Running Static Analyze for Pretty tty format)
	@./vendor/bin/phpstan analyse --error-format=table

tests: vendor/bin/phpunit build/reports/phpunit
	$(call header,Running Unit Tests)
	@XDEBUG_MODE=coverage ./vendor/bin/phpunit --fail-on-warning

clean:
	$(call header,Cleaning previous build)
	@if [ "$(shell ls -A ./build)" ]; then rm -rf ./build/*; fi; echo " done"

ci: clean validate phpcs phpstan tests php81compatibility php82compatibility
