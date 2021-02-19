## Deployment

**Note:** A good start is to enable the debugging mode in configuration and run the backup manually to ensure everything works fine.

### Configuration

```yaml
version: '2.4'

services:
  backup:
    image: bloodhunterd/backup
    environment:
      SMTP_HOST: localhost
      SMTP_PORT: 25
      SMTP_DOMAIN: example.com
      SMTP_FROM: noreply@example.com
      SMTP_USER: root
      SMTP_PASSWORD: V3ryS3cr3tP4ssw0rd
    restart: unless-stopped
    volumes:
      - ./backup/:/backup/
      - ./backup.yml:/srv/backup.yml:ro
```

#### Environment

| ENV | Values&#185; | Description
| --- | ------------ | -----------
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
| ------ | ---- | :-------: | -----------
| Backup directory | /backup/ | &#10007; | Backup directory path.
| Configuration | /srv/backup.yml | &#10003; | Configuration file path.

| &#10004; Yes | &#10008; No
| ------------ | -----------

## Update

Please note the [changelog](https://github.com/bloodhunterd/backup/blob/master/CHANGELOG.md) to check for configuration changes before updating.

```bash
docker-compose pull
docker-compose up -d
```
