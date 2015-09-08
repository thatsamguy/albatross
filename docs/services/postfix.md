#Postfix

### Example configuration

Albatross specific parts of main.cf:
```
# Albatross Configuration to lookup from MySQL
virtual_mailbox_domains = mysql:/etc/postfix/mysql_domain.cf
virtual_mailbox_base = /
virtual_mailbox_maps = mysql:/etc/postfix/mysql_users.cf
virtual_minimum_uid = 5000
virtual_uid_maps = mysql:/etc/postfix/mysql_uid.cf
virtual_gid_maps = mysql:/etc/postfix/mysql_gid.cf
virtual_alias_maps = mysql:/etc/postfix/mysql_alias.cf

virtual_mailbox_limit = 0

# Get domains to relay for from MySQL as well
relay_domains = mysql:/etc/postfix/mysql_relaydomains.cf

# Use Virtual Tables for Local Accounts
local_transport = virtual
local_recipient_maps = $virtual_mailbox_maps
```

As a side note, please ensure as always to configure your mail server to not be an [open relay](https://en.wikipedia.org/wiki/Open_mail_relay).

Also recommended would be anti-virus and anti-spam measures such as setting postfix ```smtpd_recipient_restrictions``` to include ```reject_rbl_client``` servers and [Amavis](https://en.wikipedia.org/wiki/Amavis).

#### Examples of the above ```mysql_*.cf``` files.
mysql_domain.cf:
```
user = postfix
password = postfixpassword
dbname = maildb
table = domain
select_field = domain
where_field = domain
hosts = 127.0.0.1
additional_conditions = AND active = '1';
```

mysql_users.cf:
```
user = postfix
password = postfixpassword
dbname = maildb
table = users
select_field = maildir
where_field = email
additional_conditions = AND active = '1'
hosts = 127.0.0.1
```

mysql_uid.cf:
```
user = postfix
password = postfixpassword
dbname = maildb
table = users
select_field = uid
where_field = email
hosts = 127.0.0.1
```

mysql_gid.cf:
```
user = postfix
password = postfixpassword
dbname = maildb
table = users
select_field = gid
where_field = email
hosts = 127.0.0.1
```

mysql_alias.cf:
```
user = postfix
password = postfixpassword
dbname = maildb
table = alias
select_field = destination
where_field = email
additional_conditions = AND active = '1'
hosts = 127.0.0.1
```

mysql_relaydomains.cf:
```
user = postfix
password = postfixpassword
dbname = maildb
table = domain
select_field = domain
where_field = domain
hosts = 127.0.0.1
additional_conditions = AND active = '2'
```
