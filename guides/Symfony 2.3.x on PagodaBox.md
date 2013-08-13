Symfony 2.3.x on PagodaBox, a guide
==========================

### This document tested against

* August 12, 2013: known to work with Symfony 2.3.3

## Preface

Check [Preface.md](Preface.md) if something doesn't make sense.

## Start locally

PagodaBox offers <a href="https://dashboard.pagodabox.com/apps/new?search=symfony" target="_new">Quickstarts</a> but I think providing source code is best left to Composer. Let's create a skeleton app locally, and then push it to PagodaBox (which is super simple).

## Create a fresh Symfony project with Composer


You can name your project anything, I chose `fresh`.<br/>
Use your project name in the commands that follow.

```
$ mkdir fresh && cd $_ && composer create-project symfony/framework-standard-edition . 2.3.3 --no-scripts
``` 
This will download the Symfony core, install it's dependencies and skip the usual install scripts for now.

### Set permissions for `app/cache` and `app/logs`

There are a <a href="http://symfony.com/doc/master/book/installation.html#configuration-and-setup" target="_new">few ways</a> to do this. I like this one-liner, which uses ACL (more flexible than standard unix permissions).

```
$ rm -rf app/cache/* && rm -rf app/logs/*; APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`; sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs &&
chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```
If Apache isn't running you'll see an error like *chmod: Invalid entry format…* in which case start Apache or remove the line `APACHEUSER=…;` and manually replace `$APACHEUSER` with the applicable username (in my case it's `_www`).

### Initialize your Git repo

Symfony comes with a good `.gitignore` so let's capture the initial state of the applicaiton code before we doing any work.

```
$ git init && git add . && git commit -m 'Fresh Symfony'
```

Not only will git track the our development history, we'll use it to deploy our app on PagodaBox. Deploy just means make a certain version live. Using git is awesome for version based deployment, as we'll see.

### Ensure intl extension parity via composer.json

http://symfony.com/doc/current/components/intl.html#installation

Currently  

```
"require": {
	"php": ">=5.3.3",
	"symfony/icu": "1.1.*",
	"symfony/symfony": "2.3.*",
    …
```

### Optionally add symlink option in composer.json

I like to install assets with symlinks, add this to "extra" in `composer.json` (don't forget your json comma!):

```
"extra": {
	…,
	"symfony-assets-install": "symlink"
}
```
Symlinks save disk space and command line work. Each time you add a Bundle you'll want to install it's assets. With "symlink" you install once, each time you add a Bundle (or Bundles). You can also use "relative" for relative symlinks, both work fine when deploying to PagodaBox.

### Updating paramters.yml.dist

By default `paramters.yml` is kept out of your repo so your code stays portable. Defaults are still nice, so Symfony 2.3 <a href="http://symfony.com/blog/new-in-symfony-2-3-interactive-management-of-the-parameters-yml-file" target="_new">includes an install script</a> which allows you to customize the default values in a template called `paramters.yml.dist`. Let's take full advantage of this behavior.

```
$ $EDITOR app/config/parameters.yml.dist
```
replace contents with this:

```
parameters:

    database_driver   : pdo_mysql
    database_host     : %database.host%
    database_port     : %database.port%
    database_name     : %database.name%
    database_user     : %database.user%
    database_password : %database.pass%
    
    mailer_transport  : smtp
    mailer_host       : 127.0.0.1
    mailer_user       : null
    mailer_password   : null
    
    locale : en
    secret : %other.secret%    

```
The values marked with precentages `%` are <a href="http://symfony.com/doc/current/cookbook/configuration/external_parameters.html" target="_new">environment variables</a> which will be explained in detail next.

### Create envvars.sh shell script

Add `envvars.sh` to .gitignore and start editing

```
$ sed -i -e '$a\' .gitignore && echo 'envvars.sh' >> .gitignore && rm .gitignore-e && $EDITOR envvars.sh
```
Here's a template for `envvars.sh`:

```
#!/bin/bash
export SYMFONY__DATABASE__HOST=value
export SYMFONY__DATABASE__NAME=value
export SYMFONY__DATABASE__PORT=value
export SYMFONY__DATABASE__USER=value
export SYMFONY__DATABASE__PASS=value
```
…replace each `value` with appropriate values for your local dev environment — to be clear these are the values Symfony/Doctrine will use to access your database. Make sure there are no spaces, so host would look like `export SYMFONY__DATABASE__HOST=127.0.0.1`.

Close, save, and run the script:

```
$ . ./envvars.sh
```
Now these variables are available in the shell, which will be crucial for many `app/console` commands. You'll want to run envvars.sh once per shell session when working on this project.


### Now run the install scripts
With variables exported we can safely run the install scripts. We'll skip the interactive prompts so `parameters.yml` will be created entirely with the defaults in `paramters.yml.dist`.

```
$ SYMFONY__OTHER__SECRET=blank && composer install --no-interaction
```
**Notice** I prepended a shim which we need just once; see next.

### Update secret in paramters.yml

With `app/config/paramters.yml` generated we'll customize `secret:` to something secret. Use this one-liner (just provide your own phrase):

```
$ SECRET=`md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` && sed -i.orig s/'secret.*$'/"secret: $SECRET"/ app/config/parameters.yml && rm app/config/parameters.yml.orig
```
or change the secret manually: `$ $EDITOR app/config/parameters.yml`.

### Add the environment vars to local Apache

Since the command line and web server are different processes we'll make the same values available to Apache with the `SetEnv` directive.

```
SetEnv  SYMFONY__DATABASE__NAME value
SetEnv  SYMFONY__DATABASE__HOST value
SetEnv  SYMFONY__DATABASE__PORT value
SetEnv  SYMFONY__DATABASE__USER value
SetEnv  SYMFONY__DATABASE__PASS value
```
Update each value to match `envvars.sh` (notice no equals sign here) and add these directives to your <a href="http://symfony.com/doc/current/cookbook/configuration/external_parameters.html" target="_new">VirtualHost</a> in httpd.conf.

Since you're already in VirtualHost, make sure to point your DocumentRoot at the `web` folder of your project `DocumentRoot '/the/path/to/fresh/web'`.

Restart the server and make the changes available.

### Check your installation

Nice, browse to [http://localhost/app_dev.php/](http://localhost/app_dev.php/) and make sure you see the Welcome screen and check <a href="http://localhost/config.php" target="_new">http://localhost/config.php</a> that your server is well configured.


## Open up PagodaBox

If you haven't already, sign up for PagodaBox and crack open your dashboard [https://dashboard.pagodabox.com/](https://dashboard.pagodabox.com/) — yum!

### Create your first application
* click New Application
* select Empty Repo
* name your app
* select Git for deployment mode

When naming your application you need to pick a name that is globally unique to the PagodaBox. The name is not terribly important, just pick something simple and memorable.

After you select Git as your deployment method you'll see some instrucitons that show you how to get some files into your app. We'll get to that, but first: <a href="http://help.pagodabox.com/customer/portal/articles/175475" target="_new">Boxfile</a>.

### Add pagoda remote and then deploy

With our app started in PagodaBox bounce back to your local project and run the following commands, changing `myapp.git` to `your-apps-name-on-PagodaBox.git`

```
$ git remote add pagoda git@git.pagodabox.com:myapp.git
$ git push -u pagoda --all
```
If you setup your remote correctly than after pushing you'll see a stream of output on your screen like "Building Infastructure". You're watching your app build and deploy! Once it's done you should see "Decommisioning Previous Infrastructure" and your prompt will return. If your build ends in error, hold tight, we're still configuring things.

### Git push means deploy

Since we used `git push -u pagoda` above all future pushes will use pagoda by default. When you `git push` your master branch to pagoda it will build and deploy your app with the code you just pushed — pretty powerful!

There are many ways to deploy your app but for our purposes `git push` will be synomous with deploying. What's cool is that if your build fails your app won't go down. PagodaBox keeps your pervious build running right unitl your new build completes, so there should be basically no downtime.

### Create a database and grab the credentials

Go back to your PagodaBox dashboard and select your app

- click Add Database
- select Mysql
- select Cloud
- enter a name (anything simple)

Once your new database is created it will show in the dashboard (sometimes the UI hangs, just manually refresh if nothing changes after a minute).

- click your database (manage)
- below, click *Show Credentials*
- copy the database name, path, user, pass

With values in hand look above and click "Environment Vars" and "Add Another" for each of the following keys, applying the values you just copied:

- `SYMFONY__DATABASE__NAME`
- `SYMFONY__DATABASE__HOST` (*path*, left of the colon)
- `SYMFONY__DATABASE__PORT` (*path*, right of the colon)
- `SYMFONY__DATABASE__USER`
- `SYMFONY__DATABASE__PASS`

Now, add the last key with a value of your choosing:

- `SYMFONY__OTHER__SECRET`<br/>try this one-liner to create a value `$ md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` 

By keeping these values out of your repository you get cleaner deployments and centralized credentials.

### Boxfile, your production recipe

PagodaBox uses a file named `Boxfile` that sits at the root of your app and configures your production environment. It's YAML formatted so it's easy to read and edit. Since it's part of your repo it's version controlled, which is awesome becuase it keeps the state of your code and server in sync.

Below is a solid boilerplate [Boxfile](Boxfile) ready to handle Symfony 2.3.x — see the <a href="About%20Boxfile.md" target="_new">About Boxfile.md</a> guide for more detail.

```
web1:

  document_root   : web
  default_gateway : app.php
  index_list      : [app.php]

  apache_access_log : false
  apache_error_log  : true
  php_error_log     : true
  php_fpm_log       : true

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

##The rest of this document are is an outline with fragments I'm currently working on

### Install a database

- Go to PagdoaDashboard
- Click add Database, select MySQL and pick a name (or let one be auto-generated)
	- I've noticed that creating a databse will often make the dashboard hang, just refresh it after a minute or so

### Using MySQL Workbench (or another DBA terminal)
- install <a href="http://www.mysql.com/products/workbench/" target="_new">MySQL Workbench</a> (you need to start a free acount)
- install the pagoda cli `$ gem install pagoda`
- from your app root `$ pagoda tunnel -c [dbname]`

### Create quick_deploy branch

create branch

```
$ git checkout -b quick_deploy
```

update .gitignore to allow

```
#/web/bundles/
#/app/bootstrap.php.cache
/app/cache/*
#/app/config/parameters.yml
/app/logs/*
!app/cache/.gitkeep
!app/logs/.gitkeep
/build/
#/vendor/
/bin/
/composer.phar
```

### Make some demo content quick

```
$ php app/console generate:bundle --namespace=ixel/HelloBundle --bundle-name=HelloBundle --no-interaction --structure --dir=src --format=annotation
```

### paramters.yml

After all the vendors are downloaded — you will get prompted to provide some values to generate a configuration file which resides at `app/config/parameters.yml`. Just hit enter on each prompt to use the default values except for 'secret' which you'll want to provide something unique. We'll revisit paramtes.yml later since we'll use Apache Environment Variables (EnvVar) to make maintaining differences between our local and PagodaBox setups easier (more on this later).

Here's an overview of the prompts with the `defaults` you'll probably see:

- database_driver: `pdo_mysql` PagodaBox uses MySQL by default (Mongo is still in beta)
- database_host: `127.0.0.1` we'll configure this as an EnvVar later
- database_port: `null` ditto EnvVar
- database_host: `symfony` ditto EnvVar
- database_user: `127.0.0.1` ditto EnvVar
- database_password: `127.0.0.1 `ditto EnvVar
- mailer_transport: `smtp` only needed if you intend to use Symfony's mail component
- mailer_host: `127.0.0.1` ditto
- mailer_host: `null` ditto
- mailer_host: `null` ditto
- locale: `en` This optional prefix refers to the default language of your site (ToDo: check if it's a convention or a specificaiton)
- **secret:** ***FILL THIS IN!*** I like to hash a random phrase (in a different shell session) `$ md5 -s 'put a random phrase here'`

### How I build my stack

I've been meaning to try out Vagrant but personally I use MAMP Pro to manage Apache / MySQL / PHP locally — it just makes sense to me. I use Pear to manage php extensions, and Pear requires <a href="http://www.lullabot.com/blog/article/installing-php-pear-and-pecl-extensions-mamp-mac-os-x-107-lion" target="_new">some config to work with MAMP</a>. I install Git with Homebrew. Composer uses it's own cURL method and self-update command.

**My development toolbox:** 

- SublimeText
- PhpStorm
- SoureTree (to visualize git repos)
- MySQL Workbench
- Mou (to write this Markdown)

### Parity

It goes without saying you're going to want to maximize parity between your local development environment and your PagodaBox production environment. We'll look at PagodaBox in a minute, so maybe jump back Not just the core tools listed above, but also in the PHP extensions you use. This can be a slight pain at times (like my experience with <a href="http://stackoverflow.com/questions/16753105/problems-with-lib-icu-dependency-when-installing-symfony-2-3-x-via-composer" target="_new">intl</a>) but luckily Symfony 2.3 is going to be stable and supported till 2016 so once you figure it out once you're going to be set for years to come.

Some extensions — like Xdebug — are actually best left off the production server but in general you should aim to ensure that each component of your local and producion stacks matches with the same major version and hopefully minor version (just as a rule of thumb).


With the parameters configured the Symfony installer runs a few more commands and assuming there were no errors we're off to a good start!

You can `$ ls -lAGh` for Unixy or `$ open .` for OSXy glimpse at all your fresh new source code.

### global boxfile env vars aren't avilable during the build/deploy phase

```
global:
  env:
    SYMFONY__DATABASE__HOST: tunnel.pagodabox.com
    SYMFONY__DATABASE__PORT: 3306
```
