Configuring & Deploying Symfony 2.3.x for PagodaBox with a MySQL Database
==========================

Process tested against:

* Symfony 2.3.3, August 12, 2013

## [Preface.md](Preface.md)

Check the preface if something doesn't make sense.

## Start locally

PagodaBox offers <a href="https://dashboard.pagodabox.com/apps/new?search=symfony" target="_new">Quickstarts</a> which are a cool idea but providing source is Composer's job. I may adapt these steps into a Quickstart script but for now start locally.

### composer create-project

Name your project anything — I'm using `fresh` —  and install the newest stable:

```
$ mkdir fresh && cd $_ && composer create-project symfony/framework-standard-edition . --no-interaction
``` 

If you want a specific version, check the available stable:

```
$ composer show symfony/framework-standard-edition | grep "versions" | sed -e "s~, ~\\`echo -e '\n\r'`~g" | grep v[0-9]\.[0-9]\.[0-9]$ | sed -e s/v//

```
then, say, specify `2.3.3` after the path `.`

```
…framework-standard-edition . 2.3.3 --no-interaction
```

With the Symfony core and default dependencies downloaded let's git some versioning done.

### Initialize your Git repo

Knowing Symfony comes with a good `.gitignore` capture the initial state of the applicaiton code:

```
$ git init && git add . && git commit -m 'Fresh Symfony'
```
Git plays an important role in deploying to PagodaBox, which we'll see.

### Specify the correct symfony/icu in composer.json

Symfony2 uses the well supported [ICU library](http://site.icu-project.org/) to convert date/time/number/currency for internationalization and localization. Since ICU is a generic C library PHP ships with the [Intl extension](http://www.php.net/manual/en/intro.intl.php) which adapts ICU for use in PHP. Some shared hosts don't install Intl so Symfony 2.3 ships with [symfony/icu](https://packagist.org/packages/symfony/icu) to handle the presence or absence of the Intl gracefully — [you just need to install the right version](http://symfony.com/doc/current/components/intl.html).

As of this writing, PagodaBox supports Intl 1.1.0, which bundles ICU 4.2.1.<br/>Now check the local environment.

```
php -i | grep "ICU v"
```
(in case your CLI uses a different php.ini double check `phpinfo()` in the browser)

If the ICU version is higher than 4.0 do:

```
$ composer require symfony/icu 1.1.*
```
If ICU doesn't exist or is lower than 4.0 do:

```
$ composer require symfony/icu 1.0.*
```


### Optionally add symlink option in composer.json

Highly recommend to install assets with symlinks. Add this to "extra" in `composer.json` (don't forget your json comma!):

```
"extra": {
	…,
	"symfony-assets-install": "symlink"
}
```
Symlinks save disk space and command line work. Each time you add a Bundle you'll want to install it's assets. With "symlink" you install once, each time you add a Bundle (or Bundles). You can also use "relative" for relative symlinks, both work fine when deploying to PagodaBox.

### Update paramters.yml.dist

By default `paramters.yml` is kept out of your repo so your code stays portable. Defaults are still nice, so Symfony 2.3 <a href="http://symfony.com/blog/new-in-symfony-2-3-interactive-management-of-the-parameters-yml-file" target="_new">includes an install script</a> which allows you to customize the default values in a template called `paramters.yml.dist`. Let's take full advantage of this behavior.

```
$ $EDITOR app/config/parameters.yml.dist
```
replace contents with :

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

## Local Environemnt Variables

Environment Vars allow each developer and runtime envirnment to implemenet different credentials for databases (etc). Generally the CLI and Apache need independent methods of setting these variables.

### For the shell: envars.sh

See: [boilerplate/envars.sh](https://github.com/mfdj/symfony2-on-pagodabox/blob/master/boilerplate/envars.sh)<br/>

Add `envars.sh` to .gitignore and start editing

```
$ sed -i -e '$a\' .gitignore && echo 'envars.sh' >> .gitignore && rm .gitignore-e && $EDITOR envars.sh
```
Template for `envvars.sh`:

```
#!/bin/bash
export SYMFONY__DATABASE__HOST=value
export SYMFONY__DATABASE__NAME=value
export SYMFONY__DATABASE__PORT=value
export SYMFONY__DATABASE__USER=value
export SYMFONY__DATABASE__PASS=value
```
…replace each `value` with appropriate values for your local dev environment. Make sure there are no spaces, example: `export SYMFONY__DATABASE__HOST=127.0.0.1`.

With values set when you run the script:

```
$ . ./envvars.sh
```
the shell will have access which is crucial for many `app/console` commands. Run envvars.sh each shell session you work on the project. Ideally I'm going to figure out how to run this automatically.

### For Apache: SetEnv in httpd.conf

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


### Now run the install scripts
With variables exported we can safely run the install scripts. We'll skip the interactive prompts so `parameters.yml` will be created entirely with the defaults in `paramters.yml.dist`.

```
$ SYMFONY__OTHER__SECRET=blank && composer install --no-interaction
```
**Notice** I prepended a shim which we need just once; see next.

## Final local config steps fo

### Update secret in paramters.yml

With `app/config/paramters.yml` generated we'll customize `secret:` to something secret. Use this one-liner (just provide your own phrase):

```
$ SECRET=`md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` && sed -i.orig s/'secret.*$'/"secret: $SECRET"/ app/config/parameters.yml && rm app/config/parameters.yml.orig
```
or change the secret manually: `$ $EDITOR app/config/parameters.yml`.


### Set `app/cache` and `app/logs` permissions

Of the <a href="http://symfony.com/doc/master/book/installation.html#configuration-and-setup" target="_new">three main ways</a> to do this I like this one-liner:

```
$ rm -rf app/cache/* && rm -rf app/logs/*; APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`; sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs &&
chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```
If Apache isn't running you'll see an error ***chmod: Invalid entry format…*** in which case start Apache or remove the line `APACHEUSER=…;` and manually replace `$APACHEUSER` with the applicable username (in my case `_www`).

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

Below is the abridged [Boxfile](Boxfile) ready to handle Symfony 2.3.x — see the [About Boxfile.md](About Boxfile.md) guide for more detail.

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