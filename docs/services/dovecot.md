# Dovecot


Example full dovecot.conf:
```
protocols = imap imaps pop3 pop3s

protocol imap {
  listen = *:143
  ssl_listen = *:993
}
protocol pop3 {
  listen = *:110
  ssl_listen = *:995
}

disable_plaintext_auth = no

log_timestamp = "%Y-%m-%d %H:%M:%S "

ssl = yes

ssl_cert_file = /path/to/certfile.crt
ssl_key_file = /path/to/private.key

mail_location = maildir:/var/vhosts/%d/%u

mail_uid = 5000
mail_gid = 5000

mail_privileged_group = vhosts

maildir_copy_with_hardlinks = yes

maildir_very_dirty_syncs = no

protocol imap {
  mail_max_userip_connections = 20
}
  
protocol pop3 {
  pop3_uidl_format = %08Xu%08Xv
}

protocol managesieve {
}

protocol lda {
}

auth_verbose = no

auth_debug = no

auth_debug_passwords = no

auth default {
  mechanisms = plain

  passdb sql {
    args = /etc/dovecot-sql.conf
  }
  userdb sql {
    args = /etc/dovecot-sql.conf
  }

  userdb passwd {
  }

  user = root
}

dict {
}

plugin {
}
```

dovecot-sql.conf:
```
driver = mysql
connect = host=127.0.0.1 dbname=maildb user=dovecot password=dovecotpassword
default_pass_scheme = CRYPT
user_query = SELECT home, maildir AS mail, uid, gid FROM users WHERE email = '%u' AND active='1'
password_query = SELECT email AS user, passwdCrypt AS password FROM users WHERE email='%u' AND active = '1'
```
