.PHONY: test coverage

test:
	@tests/phpunit
	@php vendor/bin/phpcs

coverage:
	@tests/phpunit --coverage-text
