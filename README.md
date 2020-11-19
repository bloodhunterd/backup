[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?include_prereleases&style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
[![Travis](https://img.shields.io/travis/bloodhunterd/backup?label=Travis&style=for-the-badge)](https://travis-ci.com/github/bloodhunterd/backup)
[![Docker](https://img.shields.io/github/workflow/status/bloodhunterd/backup/Docker?label=Docker&style=for-the-badge)](https://hub.docker.com/r/bloodhunterd/backup)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/bloodhunterd)

# Backup

Backup is an application to manage files and database backups. It supports compression, encrypted transfer, email reports and command execution before and after the backup process.

## Features

* Simple configuration
* Supports dockerized databases
* Strong compression
* Secure, encrypted transfer
* Email report
* Shows backup size and duration
* Execute commands before and after

## Requirements

### Phar

#### Agent

* Linux *(eventually macOS)*
* [PHP](https://www.php.net/) >= **7.3**
  * BZ2
  * CLI
  * INTL
  * JSON

#### Manager

* All Agent requirements
* [OpenSSH](https://www.openssh.com/) client
* [rsync](https://linux.die.net/man/1/rsync)

#### Optional

* A Mail Transfer Agent like [Exim](https://www.exim.org/) or [Postfix](http://www.postfix.org/) to send reports.

### Docker image

The docker image already includes everything.

## Deployment

### Phar

Download the Phar file and place it somewhere on your server.  
For example at **/srv/**.

[![Backup App](https://img.shields.io/badge/Download-Backup%20App-blue?style=for-the-badge)](https://github.com/bloodhunterd/backup/raw/master/build/backup.phar)

Download the distributed agent and manager configuration files and place it somewhere on your server.  
For example also at **/srv/**.

<a name="config"></a>
[![Agent Configuration](https://img.shields.io/badge/Download-Agent%20Configuration-blue?style=for-the-badge)](https://raw.githubusercontent.com/bloodhunterd/backup/master/dist/agent.dist.json)
[![Manager Configuration](https://img.shields.io/badge/Download-Manager%20Configuration-blue?style=for-the-badge)](https://raw.githubusercontent.com/bloodhunterd/backup/master/dist/manager.dist.json)

Adjust the configuration file for your needs and add an entry into the Cron table to execute this application periodically.

```bash
0 4 * * * php /srv/backup.phar /srv/configuration.json >> /var/log/backup.log
```

*In this example the backup runs every night at 4am.*

### Docker image

Download, rename and adjust the distributed Docker Compose file.

[![Docker Compose](https://img.shields.io/badge/Download-Docker%20Compose-blue?style=for-the-badge)](https://github.com/bloodhunterd/backup/raw/master/dist/docker-compose.dist.yml)

Download, rename and adjust the distributed agent and manager [configuration](#config) files.

#### Configuration

##### Environment

| ENV | Values¹ | Description
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

¹ *Possible values are separated by a slash. A range is indicated by a dash.*

##### Volumes

| Volume | Path | Read only | Description
|--- |--- |--- |---
| Backup directory | /srv/backup/ | &#10007; | Backup directory path
| Configuration | /srv/backup.json | &#10003; | Configuration file path
| Private key | /srv/id_rsa | &#10003; | OpenSSH private key file path

### Note

*A good start is to enable the debugging mode in configuration and run the backup manually to ensure everything works fine.*

## Update

Please note the [changelog](https://github.com/bloodhunterd/backup/blob/master/CHANGELOG.md) to check for configuration changes before updating.

### Docker image

```bash
docker-compose pull
docker-compose up -d
```

## Build with

* [Vection Framework](https://github.com/Vection-Framework/Vection)
  * [DI-Container](https://github.com/Vection-Framework/DI-Container)
  * [Validator](https://github.com/Vection-Framework/Validator)
* [Monolog](https://github.com/Seldaek/monolog)
* [Twig](https://twig.symfony.com/)
* [PHP](https://www.php.net/)
* [mSMTP](https://marlam.de/msmtp/)
* [Debian](https://www.debian.org/)
* [Docker](https://www.docker.com/)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup/blob/master/LICENSE) file for details.
