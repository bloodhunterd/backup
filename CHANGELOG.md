# Changelog

All notable changes will be documented in this file.

<a name="v2-0-1"></a>
## [2.0.1](https://github.com/bloodhunterd/backup/releases/tag/2.0.1) &#9839; 24.02.2021

* Fixed possible missing HTML formatting in mail report with PHP 8.0

<a name="v2-0-0"></a>
## [2.0.0](https://github.com/bloodhunterd/backup/releases/tag/2.0.0) &#9839; 22.02.2021

* *Docker image:* Upgraded to **PHP 8.0**
* *Docker image:* Missing YAML config format support added
* JSON config format support canceled
* Error messages in reports simplified
* Fixed **issue #14** Missing file size in download report

<a name="v1-1-0"></a>
## [1.1.0](https://github.com/bloodhunterd/backup/releases/tag/1.1.0) &#9839; 29.01.2021

* Resolved issue #13 Support YAML as configuration file format

<a name="v1-0-0"></a>
## [1.0.0](https://github.com/bloodhunterd/backup/releases/tag/1.0.0) &#9839; 16.01.2021

* Resolved **issue #9** Add support to backup different databases
* Fixed **issue #12** Execute command after backup even if an error occurred
* Fixed **issue #11** Wrong display of seconds in duration time
* Resolved **issue #8** Execute command before and after backup
* Resolved **issue #7** Convert size and duration in a unit that fit best
* Resolved **issue #6** Report: Show debugging info only if at least one error occurred
* Fixed **issue #5** Backup breaks on not supported language
* Fixed **issue #4** No error in backup report on failure
* Resolved **issue #3** Disable debug mode by default
* Resolved **issue #2** Disable reports
