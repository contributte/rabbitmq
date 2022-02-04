.PHONY: install qa cs csf phpstan tests coverage

install:
	composer update

qa: phpstan cs

cs:
ifdef GITHUB_ACTION
	vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp -q --report=checkstyle src | cs2pr
else
	vendor/bin/phpcs --standard=ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src
endif

csf:
	vendor/bin/phpcbf --standard=vendor/contributte/code-rules/paveljanda/ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src

phpstan:
	vendor/bin/phpstan analyse -c vendor/contributte/code-rules/paveljanda/phpstan.neon --level 8 src

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases

coverage:
ifdef GITHUB_ACTION
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage coverage.xml --coverage-src src tests/Cases
else
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage coverage.html --coverage-src src tests/Cases
endif
