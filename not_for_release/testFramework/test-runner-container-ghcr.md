# Zen Cart Test Runner Container

## Goal

Provide a reusable Zen Cart test-runner container that can be published to GitHub Container Registry and used by Zen Cart core CI, plugin repository CI, and local plugin testing.

This is the preferred runtime for plugin CI. DDEV can still be useful for local interactive development, but CI should use the published container so Zen Cart core and plugin repositories run tests against the same PHP/runtime toolchain.

The container should provide the runtime only:

- PHP
- required PHP extensions
- Composer
- MySQL client tools
- shell utilities used by the test runners

The container should not contain Zen Cart source code. CI or local development should mount or check out Zen Cart into `/var/www/html`.

## Image Name

Recommended image name:

```text
ghcr.io/zencart/zencart-test-runner
```

Recommended tags:

```text
php-8.3
php-8.4
php-8.5
latest
```

Use `latest` for the default supported PHP version, initially `php-8.4`.

## Dockerfile

Create:

```text
not_for_release/testFramework/docker/test-runner/Dockerfile
```

Suggested first implementation:

```Dockerfile
ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-apache-bookworm

LABEL org.opencontainers.image.source="https://github.com/zencart/zencart"
LABEL org.opencontainers.image.description="Zen Cart test runner image"
LABEL org.opencontainers.image.licenses="GPL-2.0-only"

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        default-mysql-client \
        git \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        rsync \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.9.8 /usr/bin/composer /usr/bin/composer

RUN { \
        echo "memory_limit=512M"; \
        echo "max_execution_time=120"; \
        echo "display_errors=1"; \
        echo "log_errors=1"; \
        echo "error_reporting=E_ALL"; \
    } > /usr/local/etc/php/conf.d/zencart-test.ini

RUN php -r 'foreach (["curl", "gd", "intl", "mbstring", "mysqli", "pdo_mysql", "zip"] as $extension) { if (!extension_loaded($extension)) { fwrite(STDERR, "Missing PHP extension: {$extension}\n"); exit(1); } }'

WORKDIR /var/www/html
```

## Local Build

Build the PHP 8.4 image from the Zen Cart repository root:

```bash
docker build \
  -f not_for_release/testFramework/docker/test-runner/Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  -t ghcr.io/zencart/zencart-test-runner:php-8.4 \
  .
```

For a personal namespace:

```bash
docker build \
  -f not_for_release/testFramework/docker/test-runner/Dockerfile \
  --build-arg PHP_VERSION=8.4 \
  -t ghcr.io/<github-user>/zencart-test-runner:php-8.4 \
  .
```

Build additional PHP versions:

```bash
docker build \
  -f not_for_release/testFramework/docker/test-runner/Dockerfile \
  --build-arg PHP_VERSION=8.3 \
  -t ghcr.io/zencart/zencart-test-runner:php-8.3 \
  .

docker build \
  -f not_for_release/testFramework/docker/test-runner/Dockerfile \
  --build-arg PHP_VERSION=8.5 \
  -t ghcr.io/zencart/zencart-test-runner:php-8.5 \
  .
```

Tag the default image as `latest`:

```bash
docker tag \
  ghcr.io/zencart/zencart-test-runner:php-8.4 \
  ghcr.io/zencart/zencart-test-runner:latest
```

## Local Smoke Tests

Check PHP, extensions, and Composer:

```bash
docker run --rm ghcr.io/zencart/zencart-test-runner:php-8.4 php -v
docker run --rm ghcr.io/zencart/zencart-test-runner:php-8.4 php -m
docker run --rm ghcr.io/zencart/zencart-test-runner:php-8.4 composer --version
```

Run against the current checkout:

```bash
docker run --rm \
  -v "$PWD:/var/www/html" \
  -w /var/www/html \
  ghcr.io/zencart/zencart-test-runner:php-8.4 \
  composer validate --no-check-publish
```

Install dependencies:

```bash
docker run --rm \
  -v "$PWD:/var/www/html" \
  -w /var/www/html \
  ghcr.io/zencart/zencart-test-runner:php-8.4 \
  composer install
```

Run a focused unit test:

```bash
docker run --rm \
  -v "$PWD:/var/www/html" \
  -w /var/www/html \
  ghcr.io/zencart/zencart-test-runner:php-8.4 \
  composer tests-unit -- --filter TestFrameworkRunnersTest
```

## Publishing Manually

Public GHCR images can usually be pulled without a token, but publishing always requires authentication.

For local publishing, create a GitHub token with package write permission, then log in:

```bash
echo "$GHCR_TOKEN" | docker login ghcr.io -u <github-user> --password-stdin
```

Push the image:

```bash
docker push ghcr.io/zencart/zencart-test-runner:php-8.4
docker push ghcr.io/zencart/zencart-test-runner:latest
```

Push all supported PHP tags once they are built:

```bash
docker push ghcr.io/zencart/zencart-test-runner:php-8.3
docker push ghcr.io/zencart/zencart-test-runner:php-8.4
docker push ghcr.io/zencart/zencart-test-runner:php-8.5
```

## Publishing From GitHub Actions

Create:

```text
.github/workflows/publish-test-runner-image.yml
```

Suggested workflow:

```yaml
name: Publish Test Runner Image

on:
  workflow_dispatch:
  push:
    branches:
      - master
    paths:
      - not_for_release/testFramework/docker/test-runner/Dockerfile
      - .github/workflows/publish-test-runner-image.yml

permissions:
  contents: read
  packages: write

jobs:
  publish:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version:
          - "8.3"
          - "8.4"
          - "8.5"

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Login to GHCR
        uses: docker/login-action@v3
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Extract Docker metadata
        id: meta
        uses: docker/metadata-action@v5
        with:
          images: ghcr.io/zencart/zencart-test-runner
          tags: |
            type=raw,value=php-${{ matrix.php-version }}
            type=raw,value=latest,enable=${{ matrix.php-version == '8.4' }}

      - name: Build and push
        uses: docker/build-push-action@v6
        with:
          context: .
          file: not_for_release/testFramework/docker/test-runner/Dockerfile
          push: true
          build-args: |
            PHP_VERSION=${{ matrix.php-version }}
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
```

The built-in `GITHUB_TOKEN` is enough for GitHub Actions publishing when the workflow has `packages: write` permission.

## GHCR Visibility

Repository visibility and package visibility are separate.

A public GitHub repository can publish a private GHCR package. If plugin repositories outside the organization need to pull the image without authentication, make the package public in GitHub:

```text
GitHub organization or profile
Packages
zencart-test-runner
Package settings
Change visibility
Public
```

## Using The Image In Plugin CI

Plugin repositories can use the published image as the job container, with MySQL as a service.

Example:

```yaml
jobs:
  plugin-tests:
    runs-on: ubuntu-latest

    container:
      image: ghcr.io/zencart/zencart-test-runner:php-8.4

    services:
      db:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: db_testing
        options: >-
          --health-cmd="mysqladmin ping -h localhost -proot"
          --health-interval=5s
          --health-timeout=3s
          --health-retries=30

    env:
      DB_SERVER: db
      DB_SERVER_USERNAME: root
      DB_SERVER_PASSWORD: root
      DB_DATABASE: db_testing
      ZC_TEST_DB_HOST: db
      ZC_TEST_DB_USER: root
      ZC_TEST_DB_PASSWORD: root
      ZC_TEST_DB_BASE_NAME: db_testing
      ZC_TEST_DB_WORKERS: 2
      ZC_TEST_DB_INCLUDE_BASE: 1

    steps:
      - name: Checkout plugin
        uses: actions/checkout@v4
        with:
          path: plugin

      - name: Checkout Zen Cart
        uses: actions/checkout@v4
        with:
          repository: zencart/zencart
          ref: master
          path: zencart

      - name: Install plugin into Zen Cart
        run: |
          mkdir -p zencart/zc_plugins/gdpr-dsar/v1.0.2
          rsync -a plugin/ zencart/zc_plugins/gdpr-dsar/v1.0.2/

      - name: Install Zen Cart dependencies
        working-directory: zencart
        run: composer install

      - name: Run plugin tests
        working-directory: zencart
        run: composer tests-plugin -- --plugin gdpr-dsar
```

## Testing Against Specific Zen Cart Releases

The container should be version-neutral. It provides PHP, Composer, extensions, and operating-system tools; it should not bake in a specific Zen Cart checkout.

The Zen Cart version under test is selected by the checkout step:

```yaml
- name: Checkout Zen Cart
  uses: actions/checkout@v4
  with:
    repository: zencart/zencart
    ref: v2.1.0
    path: zencart
```

A plugin repository can test multiple Zen Cart and PHP versions with a matrix:

```yaml
strategy:
  matrix:
    zencart-ref:
      - master
      - v2.1.0
      - v2.0.1
    php-version:
      - "8.3"
      - "8.4"

container:
  image: ghcr.io/zencart/zencart-test-runner:php-${{ matrix.php-version }}
```

Then use the matrix ref when checking out Zen Cart:

```yaml
- name: Checkout Zen Cart
  uses: actions/checkout@v4
  with:
    repository: zencart/zencart
    ref: ${{ matrix.zencart-ref }}
    path: zencart
```

The important boundary is that the checked-out Zen Cart version must contain the Composer scripts and test framework expected by the plugin CI command. For plugin-local tests, that means the selected Zen Cart ref must provide:

```text
composer tests-plugin
not_for_release/testFramework/run-plugin-tests.sh
```

For older Zen Cart releases that do not include the plugin-local test runner, choose one of these support strategies:

1. Only support plugin-local CI against Zen Cart branches or tags that contain the centralized plugin test runner.
2. Backport the plugin-local test runner to supported Zen Cart release branches.
3. Checkout a newer test framework separately and run it against an older Zen Cart checkout.

Prefer the first option initially. Avoid the third option unless there is a strong compatibility need, because it creates a second version axis: the Zen Cart version under test and the test-framework version driving the run.

## First Milestone

Start with a single published image:

```text
ghcr.io/zencart/zencart-test-runner:php-8.4
```

Only add `php-8.3`, `php-8.5`, and `latest` after the PHP 8.4 image can run:

```bash
composer validate --no-check-publish
composer tests-unit -- --filter TestFrameworkRunnersTest
composer tests-plugin -- --dry-run
```
