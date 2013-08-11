The elements of a Boxfile
===

This document is a messy stub. Read with caution.

## Component id

New applicaitons all default to a single web compoennt, so 

```
web1:
```

## Optionally add custom env vars

[http://help.pagodabox.com/customer/portal/articles/175470](http://help.pagodabox.com/customer/portal/articles/175470)

```
global:
  env:
    - PARTY: pizza
```

## Apache

```
  document_root   : web # Symfony's standard public folder
  default_gateway : app.php
  index_list      : [app.php]
```

  # ~~~ PAGODA ~~~

  # ~ integral to symfony ~
  shared_writable_dirs:    
    - app/cache
    - app/logs
  # After a build is triggered (usually after pushing) all directories are writable.
  # Once the build finishes the you can only write to `shared_writable_dirs`
  # which are shared across all instances of your app via a network tunnel. 
  # More here: http://blog.doh.ms/2012/04/16/deploying-a-symfony2-and-composer-app-on-pagodabox/
  # Worth noting that because of this `shared_writable_dirs` are always empty the first time they are deployed
  # (even if this folders are part of your repo, i.e. you can't use git to seed them)

  # log settings (viewable with 'pagoda log' cli) http://blog.pagodabox.com/updated-streaming-logs/
  apache_access_log : false
  apache_error_log  : true
  php_error_log     : true
  php_fpm_log       : true

  # ~~~ PHP ~~~

  # ~ Check Symfony's requirements + recommendations:
  # http://symfony.com/doc/master/reference/requirements.html

  # ~ See which versions of PHP offered by PagodaBox
  # http://help.pagodabox.com/customer/portal/articles/175475-understanding-the-boxfile#php-version

  # ~ See which PHP extensions are offered for your version of PHP
  # http://help.pagodabox.com/customer/portal/articles/175475-understanding-the-boxfile#php-Extensions

  php_version: 5.4.14
  php_date_timezone: "America/Los_Angeles" # <~~~ use your own

  # ~ iconv, posix, tokenizer, xml, ctype extensions are enabled by default and tend to block builds when added in php_extensions
  php_extensions:
    # ~ Don't use APC with php 5.4.14  
    # http://help.pagodabox.com/customer/portal/questions/1094180-php-5-4-apc 
    # http://stackoverflow.com/questions/9611676/is-apc-compatible-with-php-5-4-or-php-5-5
    - curl
    - intl    
    - mbstring
    - mysql
    - pdo_mysql
    - xsl
    - zip

  zend_extensions:
    - xcache

  php_short_open_tag    : 0  # Don't use "Off" or Off (or "On" or On) which fail to be parsed
  php_session_autostart : 0  # should be off by default
  #magic_quotes_gpc: "0"     # removed as of PHP 5.4
  #register_globals: "0"     # removed as of PHP 5.4

  # ~~~ COMMAND HOOKS ~~~

  after_build:
    - "curl -sS https://getcomposer.org/installer | php"
    - "php composer.phar install --prefer-source --optimize-autoloader"
      # ~ "--prefer-source" smooths API limits https://github.com/composer/composer/issues/1861
      # ~ "--optimize-autoloader" is preferred for proudction http://getcomposer.org/doc/03-cli.md#update
      # ~ see also http://moquet.net/blog/proxify-composer-php/

  after_deploy:
    - "php app/console cache:clear --env=prod --no-debug"  # do this like every deploy
    - "php app/console router:debug --env=prod"            # helpful reminder
    #- "php app/console assetic:dump --env=prod --no-debug"