ifeq ($(wildcard /proc/1/cgroup),)
   RUN_COMMAND=
else
   RUN_COMMAND=docker-compose exec app
endif

.PHONY: test
test:
	@$(RUN_COMMAND) php vendor/bin/phpunit -c phpunit.xml --log-junit .build/report.xml

.PHONY: phplint
phplint:
	@$(RUN_COMMAND) -c phpunit.xml --log-junit .build/report.xml --exclude vendor/ --exclude .docker --exclude .git ./

.PHONY: phpcs
phpcs:
	@$(RUN_COMMAND) php vendor/bin/phpcs --standard=psr12 app/ domain/

.PHONY: phpcbf
phpcbf:
	@$(RUN_COMMAND) php vendor/bin/phpcbf --standard=psr12 app/ domain/

.PHONY: phpstan
phpstan:
	@$(RUN_COMMAND) php vendor/bin/phpstan analyse --level=9 --memory-limit=256M app/ domain/ tests/

.PHONY: all-checks
all-checks: phpstan \
	phplint \
	phpcs  \
	test
