The configuration format is **YAML**.

## General

These settings are valid for both, Agent and Manager configuration.

| Variable | Value | Mandatory | Description
| -------- | ----- | :-------: | -----------
| debug | true\|false | &#10008; | Enable or disable debug mode.
| language | Locale | &#10008; | Report language.
| report | See [Report](#report) | &#10004; | Report settings.
| target | See [Target](#target) | &#10004; | Target settings.
| timezone | [PHP: List of supported timezones - Manual](https://www.php.net/manual/en/timezones.php) | &#10008; | Report date and time calculation.

### Report

| Variable | Value | Mandatory |  Description
| -------- | ----- | :-------: |  -----------
| recipients | See [Recipients](#recipients) | &#10004; | Recipient settings.
| sender | See [Sender](#sender) | &#10004; | Sender settings.
| subject | Text | &#10004; | Email subject.

#### Recipients

| Variable | Value | Mandatory |  Description
| -------- | ----- | :-------: |  -----------
| address | Email address | &#10004; | Email address.
| name | Text | &#10008; | Recipient name.
| type | to\|cc\|bcc | &#10008; | Sending type.

#### Sender

| Variable | Value | Mandatory |  Description
| -------- | ----- | :-------: |  -----------
| address | Email address | &#10004; | Email address.
| name | Text | &#10008; | Recipient name.

### Target

| Variable | Value | Mandatory |  Description
| -------- | ----- | :-------: |  -----------
| directory | Path | &#10004; | Absolute path to target directory.

### Example

```yaml
debug: false
language: de_DE.utf8
report:
  sender:
    address: noreply@example.com
    name: Backup
  subject: Report
  recipients:
    - address: recipient-1@example.com
      name: Recipient 1
      type: to
    - address: recipient-2@example.com
      name: Recipient 2
      type: bcc
target:
  directory: /backup
timezone: Europe/Berlin
```

## Agent

```yaml
mode: agent
compression: gzip
sources:
  directories:
    - name: First directory
      source: /path/to/first/directory
      target: /first/directory
      commands:
        before: run this command BEFORE directory backup process starts
        after: run this command AFTER directory backup process ended
    - name: Second directory
      source: /path/to/second/directory
      target: /second/directory
      disabled: true
  databases:
    - name: First database
      source:
        system: mariadb
        type: host
        host: localhost
        user: root
        password: V3ryS3cr3tP4ssw0rd
      target: /first/database
      disabled: false
    - name: Second database
      source:
        system: mongodb
        type: docker
        container: The container name
      target: /second/database
```

## Manager

```yaml
mode: manager
sources:
  servers:
    - name: First server
      host: domain.or.ip
      ssh:
        port: 2222
        user: backup-user
        key: /path/to/ssh/key
      source: /path/to/backup/directory
      target: /first/directory
      disabled: true
    - name: Second server
      host: domain.or.ip
      ssh:
        password: /path/to/ssh/passphrase
      source: /path/to/backup/directory
      target: /second/directory
```
