.PHONY: test coverage docs

test:
	@tests/phpunit
	@php vendor/bin/phpcs

coverage:
	@tests/phpunit --coverage-text

docs:
	$(MAKE) -C docs html
