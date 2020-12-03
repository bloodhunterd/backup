FROM debian:stable-slim

# ===================================================
# Environment vars
# ===================================================

ARG PHP_VERSION=8.0

ENV TZ 'Europe/Berlin'

ENV CRON_MINUTE 0
ENV CRON_HOUR 3

ENV SMTP_HOST 'localhost'
ENV SMTP_PORT 25
ENV SMTP_DOMAIN 'localhost'
ENV SMTP_FROM ''
ENV SMTP_AUTH 'off'
ENV SMTP_USER ''
ENV SMTP_PASSWORD ''
ENV SMTP_TLS 'on'
ENV SMTP_STARTTLS 'off'
ENV SMTP_CERTCHECK 'on'

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

COPY ./build/backup.phar /srv/backup.phar

COPY ./start.sh /

# ===================================================
# Entrypoint
# ===================================================

ENTRYPOINT ["bash", "/start.sh"]
