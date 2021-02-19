# This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
# © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>

FROM debian:stable-slim

# ===================================================
# Environment vars
# ===================================================

ARG PHP_VERSION=7.4

ENV CRON_MINUTE 0
ENV CRON_HOUR 3

ENV SMTP_HOST 'localhost'
ENV SMTP_PORT 25
ENV SMTP_DOMAIN 'localhost'
ENV SMTP_FROM ''
ENV SMTP_AUTH 'on'
ENV SMTP_USER ''
ENV SMTP_PASSWORD ''
ENV SMTP_TLS 'on'
ENV SMTP_STARTTLS 'off'
ENV SMTP_CERTCHECK 'on'

ENV TZ 'Europe/Berlin'

# ===================================================
# Base packages
# ===================================================

RUN apt-get update && \
    apt-get upgrade -y --no-install-recommends

# Install dependencies
RUN apt-get install -y --no-install-recommends \
    apt-listchanges \
    apt-transport-https \
    ca-certificates \
    locales \
    locales-all \
    lsb-release \
    software-properties-common \
    unattended-upgrades \
    wget

# ===================================================
# Special package sources
# ===================================================

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' && \
    apt-get update

# ===================================================
# Special packages
# ===================================================

RUN apt-get install -y --no-install-recommends \
    bzip2 \
    cron \
    msmtp \
    openssh-client \
    php${PHP_VERSION}-bz2 \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-yaml \
    rsync

# ===================================================
# Mail configuration
# ===================================================

RUN echo "sendmail_path = /usr/bin/msmtp -t" >> /etc/php/${PHP_VERSION}/cli/php.ini

# ===================================================
# Application
# ===================================================

RUN mkdir /backup

RUN touch /var/log/backup.log

COPY ./config /srv/backup/config
COPY ./res /srv/backup/res
COPY ./src /srv/backup/src
COPY ./vendor /srv/backup/vendor
COPY ./cli.php /srv/backup
COPY ./CHANGELOG.md /srv/backup
COPY ./composer.json /srv/backup
COPY ./composer.lock /srv/backup
COPY ./LICENSE /srv/backup
COPY ./README.md /srv/backup
COPY ./start.sh /

# ===================================================
# Entrypoint
# ===================================================

ENTRYPOINT ["bash", "/start.sh"]
