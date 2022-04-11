## Manual testing

        rm -rf tests/tmp/vendor tests/tmp/composer.lock
        COMPOSER_ALLOW_XDEBUG=1 ./vendor/bin/composer install --working-dir tests/tmp -v

## Automated testing

        ./vendor/bin/phpunit