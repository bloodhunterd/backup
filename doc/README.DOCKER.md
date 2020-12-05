[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/bloodhunterd)

# Backup

Backup is an application to organize file and database backups. It supports compression, encrypted transfer, email reports and command execution before and after a process.

## Features

* Simple configuration
* Supports dockerized databases
* Strong compression
* Secure, encrypted transfer
* Email report
* Shows backup size and duration
* Execute commands before and after

## Deployment

Download the agent or manager configuration from the asset section of the respective release. [See GitHub releases](https://github.com/bloodhunterd/backup/releases).

**Note:** A good start is to enable the debugging mode in configuration and run the backup manually to ensure everything works fine.

### Docker Compose

```yaml
version: '3'

services:
  backup-tool:
    image: bloodhunterd/backup
    environment:
      SMTP_HOST: localhost
      SMTP_PORT: 25
      SMTP_DOMAIN: localhost
      SMTP_FROM: noreply@example.com
      SMTP_AUTH: off
      SMTP_USER: root
      SMTP_PASSWORD: +V3ryS3cr3tP4ssw0rd#
      SMTP_TLS: on
      SMTP_STARTTLS: off
      SMTP_CERTCHECK: on
    restart: unless-stopped
    volumes:
      - ./backup/:/backup/
      - ./backup.json:/srv/backup.json:ro
      - ./id_rsa:/srv/id_rsa:ro
```

### Configuration

#### Environment

| ENV | Values&#185; | Description
|--- |--- |---
| CRON_HOUR | 0 - 23 | Hour of CRON execution.
| CRON_MINUTE | 0 - 59 | Minute of CRON execution.
| SMTP_HOST | *FQDN or IP* | Mail server address.
| SMTP_PORT | 25 / 465 / 587 | Mail server SMTP port.
| SMTP_DOMAIN | *Email address domain part* / *SMTP host FQDN* | SMTP EHLO. Need to be set, if the mail get rejected due anti SPAM measures.
| SMTP_FROM | *Any valid email address* | Sender email address.
| SMTP_AUTH | on / off | Enable or disable SMTP authentication.
| SMTP_USER | *Any cool username* | Mail account user name.
| SMTP_PASSWORD | *Any secret password* | Mail account password.
| SMTP_TLS | on / off | Enable or disable TLS.
| SMTP_STARTTLS | on / off | Enable or disable STARTTLS.
| SMTP_CERTCHECK | on / off | Enable or disable SSL certificate check. Proves that the certificate is valid. Disable for self signed certificates.
| TZ | [PHP: List of supported timezones - Manual](https://www.php.net/manual/en/timezones.php) | Used for date and time calculation for the email report.

#### Volumes

| Volume | Path | Read only | Description
|--- |--- |--- |---
| Backup directory | /srv/backup/ | &#10007; | Backup directory path
| Configuration | /srv/backup.json | &#10003; | Configuration file path
| Private key | /srv/id_rsa | &#10003; | OpenSSH private key file path

## Update

Please note the [changelog](https://github.com/bloodhunterd/backup/blob/master/CHANGELOG.md) to check for configuration changes before updating.

### Docker Compose

```bash
docker-compose pull
docker-compose up -d
```

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup/blob/master/LICENSE) file for details.

## Footnotes

&#185; *Possible values are separated by a slash. A range is indicated by a dash.*
