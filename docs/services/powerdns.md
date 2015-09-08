==PowerDNS==

Example PowerDNS setup for using Albatross configuration

pdns.conf:
```
module-dir=/usr/lib64/powerdns
socket-dir=/var/run/powerdns
setuid=powerdns
setgid=powerdns
webserver=yes
webserver-address=0.0.0.0
webserver-password=nermal
webserver-port=8081
default-ttl=3600
allow-recursion=0.0.0.0/0
logfile=/var/log/pdns.log
loglevel=4
master=yes
launch=gmysql
gmysql-host=127.0.0.1
gmysql-user=pdns
gmysql-password=pdnspass
gmysql-dbname=pdns
gmysql-dnssec=no
recursor=8.8.8.8:53
```

Albatross specific parts of pdns.conf:
```
allow-recursion=0.0.0.0/0
master=yes
launch=gmysql
gmysql-host=127.0.0.1
gmysql-user=powerdns
gmysql-password=somepdnspass
gmysql-dbname=powerdns
gmysql-dnssec=no
recursor=8.8.8.8:53
```

===Explanation===
* gmysql settings: for connecting to albatross pdns setup,
* recursor: to handle requests not in powerdns mysql setup
* allow-recursion: set which networks can ask for domain info not in our database
* master: allow this powerdns to be the source dns provider for some domains

===Package requiurements===
Mageia: pdns, pdns-backend-mysql
