## General

The configuration format is **YAML**. The previously used **JSON** format is now deprecated, so no configuration example will be provided.

```yaml
timezone: Europe/Berlin
language: de_DE.utf8
report:
  sender:
    address: noreply@example.com
    name: Backup Manager
  subject: Download report
  recipients:
    - address: recipient-1@example.com
      name: Recipient 1
      type: to
    - address: recipient-2@example.com
      name: Recipient 2
      type: bcc
target:
  directory: /backup
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
      disabled: 'yes'
  databases:
    - name: First database
      source:
        system: mariadb
        type: host
        host: localhost
        user: root
        password: SecretPassword
      target: /first/database
      disabled: 'no'
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
      disabled: 'yes'
    - name: Second server
      host: domain.or.ip
      ssh:
        password: /path/to/ssh/passphrase
      source: /path/to/backup/directory
      target: /second/directory
```
