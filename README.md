[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
![PHPStan](https://img.shields.io/badge/PHPStan-Level%208-blueviolet?style=for-the-badge)
[![Tests](https://img.shields.io/github/workflow/status/bloodhunterd/backup/PHP?style=for-the-badge&label=Tests)](https://github.com/bloodhunterd/backup/actions?query=workflow%3APHP)
[![Docker](https://img.shields.io/github/workflow/status/bloodhunterd/backup/PHP?style=for-the-badge&label=Docker%20Build)](https://github.com/bloodhunterd/backup/actions?query=workflow%3ADocker)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/bloodhunterd)

# Backup

Backup is an application to organize file and database backups. It supports compression, encrypted transfer, email reports and command execution before and after a process.

## Features

* Simple configuration
* Strong compressions *(Bzip2, Gzip)*
* Supports [MariaDB](https://mariadb.org/), [MongoDB](https://www.mongodb.com/), [MySQL](https://www.mysql.com/) and [PostgreSQL](https://www.postgresql.org/) databases
* Supports Docker container
* Execute commands before and after
* Secure and encrypted transfers
* Email reports
* Shows backup size and duration

## Requirements

### Agent

* Linux, Windows or MacOS
* [PHP](https://www.php.net/) **^7.4** or **^8.0**
  * BZ2 *(optional)*
  * CLI
  * INTL
  * YAML

### Manager

* All **Agent** requirements
* [OpenSSH](https://www.openssh.com/) client
* [rsync](https://linux.die.net/man/1/rsync)

### Optional

* A Mail Transfer Agent like [Exim](https://www.exim.org/) or [Postfix](http://www.postfix.org/) to send reports.

## Deployment

Download the project and place it somewhere on your server. Adjust the configuration file for your needs and add an entry into the Cron table to execute this application periodically.

**Note:** A good start is to enable the debugging mode in configuration and run the backup manually to ensure everything works fine.

```bash
0 4 * * * php /srv/backup/cli.php /srv/backup.yml >> /var/log/backup.log 2>&1
```
*In this example the backup runs every night at 4am.*

## Update

Please note the [changelog](https://github.com/bloodhunterd/backup/blob/master/CHANGELOG.md) to check for configuration changes before updating.

## Docker

Backup is also available as Docker image. See [Docker Hub](https://hub.docker.com/r/bloodhunterd/backup).

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
