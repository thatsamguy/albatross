# nginx template
# mediawiki template with dynamic redirects
# By Cyprix Enterprises 2011 for the Albatross Manager
#
# Variables:
# _basedir_ = base directory for data files
# _accid_ = account id
# _sitename_ = site name
# _domain_ = primary domain
# _domainlist_ = other domains for this site, including wildcards subdomains
# _directory_ = local directory of mediawiki installation
# _subdirectory_ = alias subdirectory for mediawiki. eg. http://example.com/_subdirectory_/
# _redirect_ = location for all redirects to be listed
# _custom_ = custom code
# _fastcgi_ = extra fastcgi params
server {
    listen       80;
    server_name  _domain__domainlist_;
    root _basedir_/_accid_/_sitename_;
    access_log  /var/log/nginx/albatross/_accid_._sitename_.log  main;
    error_log  /var/log/nginx/albatross/_accid_._sitename_.error error;

    location / {
        index  index.php index.html index.htm;
    }

    error_page  404              /404.html;
    location = /404.html {
        root   /usr/share/nginx/html;
    }

    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /usr/share/nginx/html;
    }
_redirect__custom_
    if (!-e $request_filename) {
        rewrite ^/_subdirectory_/([^?]*)(?:\?(.*))? _directory_index.php?title=$1&$2 last;
        rewrite ^/_subdirectory_$ _directory_index.php last;
    }

    location ~ \.php$ {
        fastcgi_pass   unix:/var/lib/php/_accid_.sock;
        fastcgi_index  index.php;_fastcgi_
        fastcgi_param  SCRIPT_FILENAME  _basedir_/_accid_/_sitename_$fastcgi_script_name;
        include        fastcgi_params;
    }

    location ~ \.php5$ {
        fastcgi_pass   unix:/var/lib/php/_accid_.sock;
        fastcgi_index  index.php;_fastcgi_
        fastcgi_param  SCRIPT_FILENAME  _basedir_/_accid_/_sitename_$fastcgi_script_name;
        include        fastcgi_params;
    }
} 
