############################################################################
# Container for running Codeception tests on a alphalisting Docker instance.#
############################################################################

ARG WP_VERSION
ARG PHP_VERSION

FROM alphalisting:latest-wp${WP_VERSION}-php${PHP_VERSION}

LABEL author=Lin87
LABEL author_uri=https://github.com/Lin87

SHELL [ "/bin/bash", "-c" ]

# Install php extensions
RUN docker-php-ext-install pdo_mysql

# Install PCOV
# This is needed for Codeception / PHPUnit to track code coverage
RUN apt-get install zip unzip -y \
    && if test "$(printf '%s\n' "$PHP_VERSION" "7.1" | sort -V | head -n 1)" != "$PHP_VERSION"; then \
        pecl install pcov; \
        echo "extension=pcov.so" > /usr/local/etc/php/conf.d/pcov.ini; \
    fi

ENV COVERAGE=0
#ENV SUITES=${SUITES:-}

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Add composer global binaries to PATH
ENV PATH="$PATH:~/.composer/vendor/bin"

# Configure php
RUN echo "date.timezone = UTC" >> /usr/local/etc/php/php.ini

# Remove exec statement from base entrypoint script.
RUN sed -i '$d' /usr/local/bin/app-entrypoint.sh

# Set up entrypoint
WORKDIR    /var/www/html/wp-content/plugins/alphalisting
COPY       docker/testing.entrypoint.sh /usr/local/bin/testing-entrypoint.sh
RUN        chmod 755 /usr/local/bin/testing-entrypoint.sh
ENTRYPOINT ["testing-entrypoint.sh"]