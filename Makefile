#Makefile


install:
	composer install

setup: install
	composer run-script --working-dir=vendor/felixfbecker/language-server parse-stubs

console:
	psysh --config psysh.php

lint:
	composer run-script phpcs -- --standard=PSR12 src bin tests

lint-fix:
	composer run-script phpcbf -- --standard=PSR12 src bin tests

test:
	composer run-script phpunit tests

test-coverage:
	composer run-script phpunit tests -- --coverage-clover build/logs/clover.xml
