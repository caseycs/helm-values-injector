FROM composer AS composer

FROM php:7.3-cli-alpine3.10

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json /src/
RUN cd /src && composer install --no-dev --no-interaction --no-suggest

COPY . /src/
RUN ln -s /src/helm-values-injector.php /usr/bin/helm-values-injector

WORKDIR /helm

ENTRYPOINT ["/usr/bin/helm-values-injector"]
CMD ["/helm"]