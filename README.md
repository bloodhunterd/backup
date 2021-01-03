[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
[![Tests](https://img.shields.io/travis/bloodhunterd/backup?label=Tests&style=for-the-badge)](https://travis-ci.com/github/bloodhunterd/backup)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/bloodhunterd)

# Backup

Backup is an application to organize file and database backups. It supports compression, encrypted transfer, email reports and command execution before and after a process.

## Features

* Simple configuration
* Strong compression (***bzip2***)
* Supports [MariaDB](https://mariadb.org/), [MongoDB](https://www.mongodb.com/) and [MySQL](https://www.mysql.com/)
* Supports ***dockerized*** databases
* Execute commands before and after
* Secure and encrypted transfers
* Email reports
* Shows backup size and duration

## Requirements

### Agent

* Linux distribution
* [PHP](https://www.php.net/) >= **7.3**
  * BZ2
  * CLI
  * INTL
  * JSON&#185;

### Manager

* All **Agent** requirements
* [OpenSSH](https://www.openssh.com/) client
* [rsync](https://linux.die.net/man/1/rsync)

### Optional

* A Mail Transfer Agent like [Exim](https://www.exim.org/) or [Postfix](http://www.postfix.org/) to send reports.

## Deployment

Download the project and place it somewhere on your server. Download the distributed agent&#178; and manager&#179; configuration files and place it somewhere on your server. Adjust the configuration file for your needs and add an entry into the Cron table to execute this application periodically.

**Note:** A good start is to enable the debugging mode in configuration and run the backup manually to ensure everything works fine.

```bash
0 4 * * * php /srv/cli.php ./config.json >> /var/log/backup.log
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

## Footnotes

&#185; *The JSON extension is already included in PHP 8.0.*  
&#178;,&#179; *See the asset section under the respective release.*
