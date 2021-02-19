#!/bin/bash

# This file ist part of the Backup project, see https://github.com/bloodhunterd/Backup.
# © 2021 BloodhunterD <bloodhunterd@bloodhunterd.com>

# ===================================================
# Timezone
# ===================================================

ln -snf "/usr/share/zoneinfo/${TZ}" etc/localtime
echo "${TZ}" > /etc/timezone

# ===================================================
# Cron
# ===================================================

echo "${CRON_MINUTE} ${CRON_HOUR} * * * php /srv/backup/cli.php -c /srv/backup.yml > /dev/null 2>&1" > /etc/cron.d/backup

crontab /etc/cron.d/backup

cron

# ===================================================
# Mail configuration
# ===================================================

cat << mSMTP > /root/.msmtprc
defaults
auth $SMTP_AUTH
tls $SMTP_TLS
tls_starttls $SMTP_STARTTLS
tls_certcheck $SMTP_CERTCHECK
tls_trust_file /etc/ssl/certs/ca-certificates.crt
account default
add_missing_from_header on
logfile ~/.msmtp.log
host "$SMTP_HOST"
port $SMTP_PORT
domain "$SMTP_DOMAIN"
user $SMTP_USER
password "$SMTP_PASSWORD"
from "$SMTP_FROM"
mSMTP

# ===================================================
# Process log
# ===================================================

tail -f /var/log/backup.log
