Configuring & Deploying Symfony2.3.x for PagodaBox with a MySQL Database
==========================

### The extended version of [27 Steps to Bootstrap](Abridged.md)

Tested against:

* Symfony Standard Edition 2.3.3, August 16, 2013

## [Preface.md](Preface.md)

Check the [preface](Preface.md) if something doesn't make sense.

## Start locally with Composer

PagodaBox offers <a href="https://dashboard.pagodabox.com/apps/new?search=symfony" target="_new">Quickstarts</a> which are a cool idea but providing source is Composer's job. I may adapt these steps into a Quickstart script but for now start locally.

### composer create-project

Name your project folder anything — I'm using `fresh` —  and install the newest stable:

```
$ mkdir fresh && cd $_ && composer create-project symfony/framework-standard-edition . --no-interaction
``` 

If you want a specific version, check the available stable (can take like 20 secs):

```
$ composer show symfony/framework-standard-edition | grep "versions" | sed -e "s~, ~\\`echo -e '\n\r'`~g" | grep v[0-9]\.[0-9]\.[0-9]$ | sed -e s/v//
```
then specify, say, `2.3.1` after the path `.`

```
…framework-standard-edition . 2.3.1 --no-interaction
```

No interaction mode skips any prompts in Symfony's install scripts — specifically we're skipping cutomizing parameters.yml, which is addressed later.

With the Symfony core and standard dependencies downloaded let's git some versioning done.

### Initialize your Git repo

Knowing Symfony comes with a good `.gitignore` capture the initial state of the applicaiton code:

```
$ git init && git add . && git commit -m 'Fresh Symfony'
```
Git plays an important role in deploying to PagodaBox, which we'll see.

### Ensure symfony/icu parity

Symfony2 uses the well supported [ICU library](http://site.icu-project.org/) to convert date/time/number/currency for internationalization and localization. Since ICU is a generic C library PHP ships with the [Intl extension](http://www.php.net/manual/en/intro.intl.php) which adapts ICU for use in PHP. Some shared hosts don't install Intl so Symfony ships with [symfony/icu](https://packagist.org/packages/symfony/icu) to handle the presence or absence of the Intl gracefully — [you just need to install the  version that will work *both* locally and in production](http://symfony.com/doc/current/components/intl.html).

As of this writing, PagodaBox supports **Intl 1.1.0** which bundles **ICU 4.2.1**.<br/>Now check the local environment to ensure parity:

```
php -i | grep "ICU v"
```
(if your CLI uses a different php.ini you'll want to check `phpinfo()` in the browser)<br/>
If the ICU version is higher than 4.0 require 1.1.x:

```
$ composer require symfony/icu 1.1.*
```
If ICU doesn't exist or is lower than 4.0 require 1.0.x:<br/>

```
$ composer require symfony/icu 1.0.*
```
*you're limited to the `en` locale when using symfony/icu 1.0.0 — see parameters later*


### Use asset symlinks

Highly recommend to install assets with symlinks in `composer.json`

```
$ $EDITOR composer.json
```
in the `extra` object add the `symfony-assets-install` key

```
"extra": {
	…,
	"symfony-assets-install": "symlink"
}
```
Symlinks save disk space and visits to the command line. Each time you add a Bundle you'll want to install it's assets. With "symlink" you install once, each time you add a Bundle (or Bundles). You can also use "relative" for relative symlinks, both work fine when deploying to PagodaBox.

### Set `app/cache` and `app/logs` permissions

Of the <a href="http://symfony.com/doc/master/book/installation.html#configuration-and-setup" target="_new">three main ways</a> to do this I like this one-liner:

```
$ rm -rf app/cache/* && rm -rf app/logs/*; APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`; sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs && chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```
If Apache isn't running you'll see an error ***chmod: Invalid entry format…*** in which case start Apache or remove the line `APACHEUSER=…;` and manually replace `$APACHEUSER` with the applicable username (in my case `_www`).

### Check your installation

Nice, browse to [http://localhost/app_dev.php/](http://localhost/app_dev.php/) and make sure you see the Welcome screen and check <a href="http://localhost/config.php" target="_new">http://localhost/config.php</a> that your server is well configured.

### Bootstrap some production content

Generate some new content

```
$ php app/console generate:bundle --namespace=PagodaTest/Bundle --bundle-name=HelloBundle --no-interaction --structure --dir=src --format=annotation
```
To make sure it installed correctly do:

```
$ php app/console router:debug --env=prod
```

…then visit [http://localhost/hello/AnythingHere](http://localhost/hello/AnythingHere) to see it in action. With the content ready we can test PagodaBox.

Don't forget to `$ php app/console cache:clear --env=prod` if you get an error.

## Open up PagodaBox

If you haven't, sign up for PagodaBox, setup your [git SSH key](http://help.pagodabox.com/customer/portal/articles/236852-git-ssh-troubleshooting), (bonus: install [the pagoda client](http://help.pagodabox.com/customer/portal/articles/175474-the-pagoda-terminal-client)), crack open your dashboard [https://dashboard.pagodabox.com/](https://dashboard.pagodabox.com/)… yeah buddy!

### Create a new application in the dashboard
* click New Application
* select Empty Repo
* name your app and click Launch Application
* select Git for deployment mode

When naming you need to pick something globally unique to PagodaBox: just go simple and memorable, it's mainly for your PagodaBox test domain.

After you select Git as your deployment method you'll see instrucitons showing how to get some files into your app.

### Add pagoda remote and then deploy

With our app started in PagodaBox bounce back to local.<br/>
If you haven't committed recently make sure and do so:

```
$ git add . && git commit -m 'Demo content installed'
```
Then run the following commands, changing `fresh.git` to `your-apps-name-on-PagodaBox.git`:

```
$ git remote add pagoda git@git.pagodabox.com:fresh.git
$ git push -u pagoda --all
```
With the remote setup after pushing you'll see a stream of output on your screen like "Building Infrastructure". You're watching your app build and deploy! Once it's done you should see "Decommissioning Previous Infrastructure" and your prompt will return. If your build ends in error, maybe hold tight, we're still configuring things.

### Now git push also deploys

By using the `-u` flag in the last command `pagoda` became the default remote for the current branch (currently master). This means you can just `$ git push` to get changes to PagodaBox. Keep in mind that each time you do this (push master to pagoda) your app will be rebuilt and redeployed — pretty powerful! This behavior can be changed in the Dashboard.

There are other ways to deploy your app but for our purposes `$ git push` will be synonymous with a pagoda deploy. What's cool is that if your build fails your app won't go down. PagodaBox keeps your pervious build running right until your new build completes, so there should be basically zero downtime.

### Boxfile, your production recipe

[ see: [boilerplate/Boxfile](boilerplate/Boxfile) `&&` [About Boxfile.md](About Boxfile.md) ]

PagodaBox uses a file named `Boxfile` that sits at the root of your app and configures your production environment. It's YAML formatted so it's easy to read and edit. Since it's part of your repo it's version controlled, which is awesome because it keeps the state of your code and the state of the production configuration in sync.

```
$ $EDITOR Boxfile
```

```
web1:

  document_root   : web
  default_gateway : app.php
  index_list      : [app.php]

  php_version: 5.4.14
  php_date_timezone: "America/Los_Angeles"
  shared_writable_dirs:
    - app/cache
    - app/logs
  php_extensions:
    - curl
    - intl    
    - mbstring
    - mysql
    - pdo_mysql
    - xsl
    - zip
  zend_extensions:
    - xcache
  php_short_open_tag    : 0
  php_session_autostart : 0

  after_build:
    - "curl -sS https://getcomposer.org/installer | php"
    - "php composer.phar install --prefer-source --optimize-autoloader --profile --ansi"

  after_deploy:
    - "php app/console cache:clear --env=prod --no-debug"
    - "php app/console router:debug --env=prod"
```
### Deploy your app 

With your Boxfile configured we're ready to boot up Symfony on the production server.

```
$ git add Boxfile && git commit -m 'Init Boxfile' && git push
```


## Add MySQL to the mix

### Update parameters.yml.dist

[ see: [boilerplate / parameters.yml.dist](boilerplate/parameters.yml.dist) ] 

Conventionally `parameters.yml` is kept out of your repo because parameters usually vary between machines. Defaults are nice, so Symfony 2.3 <a href="http://symfony.com/blog/new-in-symfony-2-3-interactive-management-of-the-parameters-yml-file" target="_new">includes an install script</a> which allows you to customize the default values in a template called `parameters.yml.dist`. Take full advantage of this behavior when configuring our databases.

```
$ $EDITOR app/config/parameters.yml.dist
```
replace contents with:

```
parameters:

    database_driver   : pdo_mysql
    database_name     : %database.name%    
    database_host     : %database.host%
    database_port     : %database.port%
    database_user     : %database.user%
    database_password : %database.pass%
    
    mailer_transport  : smtp
    mailer_host       : 127.0.0.1
    mailer_user       : null
    mailer_password   : null
    
    locale : en
    secret : %general.secret%
```
The values marked with percentage signs `%` are <a href="http://symfony.com/doc/current/cookbook/configuration/external_parameters.html" target="_new">environment variables</a> which will be explained in detail next.

### Local Environment Variables

Now that parameters.yml depends on environments vars we need to create them in each environment. Generally the command line and Apache need independent methods of setting these variables.

### For your local shell — envars.sh

[ see: [boilerplate / envars.sh](https://github.com/mfdj/symfony2-on-pagodabox/blob/master/boilerplate/envars.sh) ]

Add `envars.sh` to .gitignore and start editing:

```
$ sed -i.orig -e '$a\' .gitignore && echo 'envars.sh' >> .gitignore && rm .gitignore.orig && $EDITOR envars.sh
```
use this template to start `envars.sh`:

```
#!/bin/bash
export SYMFONY__DATABASE__NAME=~
export SYMFONY__DATABASE__HOST=localhost
export SYMFONY__DATABASE__PORT=3306
export SYMFONY__DATABASE__USER=~
export SYMFONY__DATABASE__PASS=~
```
…fill in appropriate values for your local environment. Make sure there are no spaces, example: `export SYMFONY__DATABASE__NAME=fresh`. If you don't need database access or don't yet have credentials the convention is to use `~` to indicate an empty value.

Run the script:

```
$ . ./envars.sh
```
… now the shell has access to these variables, crucial for running `app/console` commands (since a missing parameter will cause a fatal error).

Run `envars.sh` each shell session you work on the project. Ideally I'll figure out how to do this automatically whenever app/console is run.

### For local Apache — SetEnv in httpd.conf

Since the command line and web server are different processes we'll make the same values available to Apache with the `SetEnv` directive.

```
SetEnv  SYMFONY__DATABASE__NAME ~
SetEnv  SYMFONY__DATABASE__HOST localhost
SetEnv  SYMFONY__DATABASE__PORT 3306
SetEnv  SYMFONY__DATABASE__USER ~
SetEnv  SYMFONY__DATABASE__PASS ~
SetEnv  SYMFONY__GENERAL__SECRET AnotherSecret
```
Update each value to match `envars.sh` (notice no equals sign here) and add these directives to your <a href="http://symfony.com/doc/current/cookbook/configuration/external_parameters.html" target="_new">VirtualHost</a> in httpd.conf (might work in .htaccess too?)

Since you're already in VirtualHost, double check your DocumentRoot points at the `web` folder of your project `DocumentRoot '/the/path/to/fresh/web'`.

Restart the server to make the changes available.

### Remove parameters.yml and re-install

```
$ rm app/config/parameters.yml; composer install --no-interaction
```
To recap, when running composer install/update `parameters.yml.dist` provides default values used to generate `parameters.yml`.  If parameters.yml already has a key/value set that key is skipped by the script (heads up for future install/updates).

## Keeping composer.lock udpated

Sometimes you'll see the message: 

`Warning: The lock file is not up to date with the latest changes in composer.json. You may be getting outdated dependencies. Run update to update them.`

The simplest way to remedy this error is to run:

`$ composer update --lock`

This is prone to happening whenever you change composer.json directly, instead of interacting via Composer's commands. [Just a single whitespace character can invalidate composer.json](http://moquet.net/blog/5-features-about-composer-php/#1_update_only_one_vendor), and running `composer update --lock` can fix this and other discrepancies.

### Simplify local secret management

To simplify local configuration you can sidestep dealing with `SYMFONY__GENERAL__SECRET` and hardcode a secret in `parameters.yml` with this one-liner:

```
$ SECRET=`md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` && sed -i.orig s/'secret.*$'/"secret: $SECRET"/ app/config/parameters.yml && rm app/config/parameters.yml.orig
```
To be tidy you can remove `SYMFONY__GENERAL__SECRET` from `envars.sh` and `httpd.conf` — but only those two, leave it in `parameters.yml.dist`.

### In PagodaBox create a database, grab the credentials, set Environment Vars

Go back to your PagodaBox dashboard and select your app

- click Add Database
- select Mysql
- select Cloud (which is free)
- enter a memorable name

Once your new database is created it will show in the dashboard (sometimes the UI hangs, just refresh after a minute).

- click your database (manage)
- below, click Show Credentials
- copy the database name, path, user, pass

With values in hand look above and click "Environment Vars" and "Add Another" for each of the following keys, applying the values for your database:

- `SYMFONY__DATABASE__NAME`
- `SYMFONY__DATABASE__HOST` (*path*, left of the colon)
- `SYMFONY__DATABASE__PORT` (*path*, right of the colon)
- `SYMFONY__DATABASE__USER`
- `SYMFONY__DATABASE__PASS`

Now, add the last var:

- `SYMFONY__GENERAL__SECRET`<br/>try this one-liner to create a value `$ md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` 

By keeping these values out of your repository you get cleaner deployments and centralized credentials.

Back local, let's put some fake data in the database

### DoctrineFixtures


Get fixtures (since we're going to use a dev-branch we'll loosen minimum-stability)

```
$ $EDITOR composer.json 
```
```
…
   "minimum-stability": "dev",
…
```

```
$ composer require "doctrine/doctrine-fixtures-bundle" master-dev
```

Create Database (or start over first `$ php app/console doctrine:database:drop --force`)

```
$ php app/console doctrine:database:create
```
Generate entity
```
$ php app/console doctrine:generate:entity --entity=HelloBundle:Post --fields="title:string(255) body:text" --no-interaction
```