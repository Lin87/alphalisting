# Define build arguments
ARG WP_VERSION=6.7.1
ARG PHP_VERSION=8.3

# Use the arguments in the FROM instruction
FROM wordpress:${WP_VERSION}-php${PHP_VERSION}-apache

# Save the build args for use by the runtime environment
ENV WP_VERSION=${WP_VERSION}
ENV PHP_VERSION=${PHP_VERSION}
ENV WORDPRESS_DB_HOST=${DB_HOST}
ENV WORDPRESS_DB_USER=${DB_USER}
ENV WORDPRESS_DB_PASSWORD=${DB_PASSWORD}
ENV WORDPRESS_DB_NAME=${DB_NAME}

LABEL author=Lin87
LABEL author_uri=https://github.com/Lin87

SHELL [ "/bin/bash", "-c" ]

# Install system packages
RUN apt-get update && \
    apt-get -y install \
    git \
    ssh \
    tar \
    gzip \
    wget \
    mariadb-client

# Install Dockerize
ENV DOCKERIZE_VERSION=v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Set project environmental variables
ENV WP_ROOT_FOLDER="/var/www/html"
ENV PLUGINS_DIR="${WP_ROOT_FOLDER}/wp-content/plugins"
ENV PROJECT_DIR="${PLUGINS_DIR}/alphalisting"

# Remove exec statement from base entrypoint script.
RUN sed -i '$d' /usr/local/bin/docker-entrypoint.sh

# Set up Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Custom PHP settings
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/custom.ini

# Install XDebug 3
RUN if test "7.4" = "$PHP_VERSION"; then \
        echo "Installing XDebug 3 (in disabled state)" \
        && pecl install xdebug-3.1.6 \
        && mkdir -p /usr/local/etc/php/conf.d/disabled \
        && echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
        && echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
        && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
        && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
        && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
        && echo "xdebug.max_nesting_level=512" >> /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini \
        ; \
    fi

# Set xdebug configuration off by default. See the entrypoint.sh.
ENV USING_XDEBUG=0

# Set up entrypoint
WORKDIR /var/www/html
COPY docker/app.entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod 755 /usr/local/bin/app-entrypoint.sh
ENTRYPOINT ["app-entrypoint.sh"]
CMD ["apache2-foreground"]