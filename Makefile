.PHONY: install qa cs csf phpstan tests coverage-clover coverage-html

install:
	composer update

qa: phpstan cs

cs:
	vendor/bin/phpcs --standard=vendor/gamee/php-code-checker-rules/ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src

csf:
	vendor/bin/phpcbf --standard=vendor/gamee/php-code-checker-rules/ruleset.xml --extensions=php,phpt --tab-width=4 --ignore=temp -sp src

phpstan:
	vendor/bin/phpstan analyse src -c vendor/gamee/php-code-checker-rules/phpstan.neon --level 7

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases

coverage-clover:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.xml --coverage-src ./src ./tests/Cases

coverage-html:
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage ./coverage.html --coverage-src ./src ./tests/Cases
