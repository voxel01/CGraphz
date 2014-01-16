CGraphz - Collectd GraphZ
=============
Demo
-------
* daUrl : http://web.quakelive.fr/CGraphz
* Login/Pass : admin/pass
* Database is reseted once per hour

Installation on Ubuntu
-------

Apache MysqL PHP
-----
> aptitude install mysql-server mysql-client php5 libapache2-mod-php5 php5-mysql

Clone Git
-----
> cd /var/www/

> git clone http://github.com/Poil/CGraphz.git

Post Installation
-----
* MySQL : mysql -u root -p -e "source /var/www/CGraphz/sql/initial_cgraphz_1.51.sql;"
* Configuration
 * Edit /var/www/CGraphz/config/config.php.tpl
 * Move /var/www/CGraphz/config/config.php.tpl to /var/www/CGraphz/config/config.php

> mv /var/www/CGraphz/config/config.php.tpl /var/www/CGraphz/config/config.php

Configuration of Apache
------
Add the following text to your apache configuration
> <VirtualHost *:80>
> ServerName "cgraphz.yourdomain.com"
> UseCanonicalName Off
> <IfModule mod_suexec.c>
>    SuexecUserGroup "apache" "apache"
> </IfModule>
> DocumentRoot "/var/www/CGraphz/public"
>
> RewriteEngine On
>
> RewriteCond %{DOCUMENT_ROOT}/$1 !-f
> RewriteCond %{REQUEST_FILENAME} !-f
> RewriteCond %{REQUEST_FILENAME} !-F
> RewriteCond %{REQUEST_FILENAME} !-d
> RewriteCond %{REQUEST_FILENAME} !-l
> RewriteRule ^(.*)$ /index.php [L,QSA]
>
> <Directory /var/www/CGraphz/>
>         Options -Includes -ExecCGI
>         Order Deny,Allow
>         Deny from all
>         Allow from localhost
> </Directory>
> </VirtualHost>

Add your first server
-------
1. Open your webbrowser and goto http://localhost/CGraphz
 * Login : admin
 * Password : pass

1. Goto Administration / Serveurs
 * Add servers, you can enter a description
1. Goto Administration / Projets
 * Click on the project "test"
 * Put some servers in this project
 * Add the admin group to the permissions tab
1. You can now go on dashboard and view your RRDs
