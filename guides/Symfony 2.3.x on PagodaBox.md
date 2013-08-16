Configuring & Deploying Symfony 2.3.x for PagodaBox with a MySQL Database
==========================

Process tested against:

* Symfony 2.3.3, August 12, 2013

## [Preface.md](Preface.md)

Check the preface if something doesn't make sense.

## Start locally with Composer

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

Symfony2 uses the well supported [ICU library](http://site.icu-project.org/) to convert date/time/number/currency for internationalization and localization. Since ICU is a generic C library PHP ships with the [Intl extension](http://www.php.net/manual/en/intro.intl.php) which adapts ICU for use in PHP. Some shared hosts don't install Intl so Symfony 2.3 ships with [symfony/icu](https://packagist.org/packages/symfony/icu) to handle the presence or absence of the Intl gracefully — [you just need to install the right version](http://symfony.com/doc/current/components/intl.html).

As of this writing, PagodaBox supports Intl 1.1.0, which bundles ICU 4.2.1.<br/>Now check the local environment to ensure version parity between environments.

```
php -i | grep "ICU v"
```
(in case your CLI uses a different php.ini double check `phpinfo()` in the browser)

If the ICU version is higher than 4.0 require 1.1.x:

```
$ composer require symfony/icu 1.1.*
```
If ICU doesn't exist or is lower than 4.0 require 1.0.x:<br/>
*you're limited to the `en` locale with symfony/icu 1.0.0*

```
$ composer require symfony/icu 1.0.*
```


### Optionally use asset symlinks

Highly recommend to install assets with symlinks.<br/>
Add this to "extra" in `composer.json`

```
$ $EDITOR composer.json
```

```
"extra": {
	…,
	"symfony-assets-install": "symlink"
}
```
Symlinks save disk space and visits to the command line. Each time you add a Bundle you'll want to install it's assets. With "symlink" you install once, each time you add a Bundle (or Bundles). You can also use "relative" for relative symlinks, both work fine when deploying to PagodaBox.

### Update paramters.yml.dist

Conventionally `paramters.yml` is kept out of your repo because parameters usually vary between machines. Defaults are nice, so Symfony 2.3 <a href="http://symfony.com/blog/new-in-symfony-2-3-interactive-management-of-the-parameters-yml-file" target="_new">includes an install script</a> which allows you to customize the default values in a template called `paramters.yml.dist`. Let's take full advantage of this behavior.

```
$ $EDITOR app/config/parameters.yml.dist
```
[ see: [boilerplate/parameters.yml.dist](http://) ] <br/> 
replace contents with:

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

To recap, when running composer install/update `paramters.yml.dist` is used to provide default values for `paramters.yml` — if parameters.yml already has a key/value the script will not overwrite it.

The values marked with percentage signs `%` are <a href="http://symfony.com/doc/current/cookbook/configuration/external_parameters.html" target="_new">environment variables</a> which will be explained in detail next.

## Local Environment Variables

Now paramters.yml depends on environments vars, which need to create. Generally the CLI and Apache need independent methods of setting these variables.

### For your shell: envars.sh

[ see: [boilerplate/envars.sh](https://github.com/mfdj/symfony2-on-pagodabox/blob/master/boilerplate/envars.sh) ]<br/>
Add `envars.sh` to .gitignore and start editing:

```
$ sed -i.orig -e '$a\' .gitignore && echo 'envars.sh' >> .gitignore && rm .gitignore.orig && $EDITOR envars.sh
```
Template for `envars.sh`:

```
#!/bin/bash
export SYMFONY__DATABASE__HOST=value
export SYMFONY__DATABASE__NAME=value
export SYMFONY__DATABASE__PORT=value
export SYMFONY__DATABASE__USER=value
export SYMFONY__DATABASE__PASS=value
export SYMFONY__GENERAL__SECRET=value
```
…replace each `value` with appropriate values for your local dev environment. Make sure there are no spaces, example: `export SYMFONY__DATABASE__HOST=127.0.0.1`.

With values set run the script:

```
$ . ./envars.sh
```
… now the shell has access to these variables, crucial for running `app/console` commands.

Run envars.sh for each shell session you work on the project. Ideally I'll figure out how to do this automatically whenever app/console is run.

### For Apache: SetEnv in httpd.conf

Since the command line and web server are different processes we'll make the same values available to Apache with the `SetEnv` directive.

```
SetEnv  SYMFONY__DATABASE__NAME value
SetEnv  SYMFONY__DATABASE__HOST value
SetEnv  SYMFONY__DATABASE__PORT value
SetEnv  SYMFONY__DATABASE__USER value
SetEnv  SYMFONY__DATABASE__PASS value
SetEnv  SYMFONY__GENERAL__SECRET value
```
Update each value to match `envars.sh` (notice no equals sign here) and add these directives to your <a href="http://symfony.com/doc/current/cookbook/configuration/external_parameters.html" target="_new">VirtualHost</a> in httpd.conf (might work in .htaccess too?)

Since you're already in VirtualHost, double check your DocumentRoot points at the `web` folder of your project `DocumentRoot '/the/path/to/fresh/web'`.

Restart the server to make the changes available.

### Help setting a secret

To aid in generating a secret I like to hash a phrase:

```
$ md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/
```
To simplify local configuration you can *optionally* omit the `SYMFONY__OTHER__SECRET` environment var and use this one liner to hardcode a secret in `parameters.yml`.

```
$ SECRET=`md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` && sed -i.orig s/'secret.*$'/"secret: $SECRET"/ app/config/parameters.yml && rm app/config/parameters.yml.orig
```

## Finishing touches, local 

With variables exported we'll re-run Symfony's install scripts and finish local configuration.

### Remove parameters.yml and re-run install

We'll skip the interactive prompts so `parameters.yml` will be created entirely with the defaults in `paramters.yml.dist`.

```
$ rm app/config/parameters.yml; composer install --no-interaction
```

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

If you haven't already, sign up for PagodaBox, setup your [git SSH key](http://help.pagodabox.com/customer/portal/articles/236852-git-ssh-troubleshooting), (bonus: install [the pagoda client](http://help.pagodabox.com/customer/portal/articles/175474-the-pagoda-terminal-client)), crack open your dashboard [https://dashboard.pagodabox.com/](https://dashboard.pagodabox.com/)… yum!

### Create your first application
* click New Application
* select Empty Repo
* name your app
* select Git for deployment mode

When naming you need to pick something globally unique to PagodaBox: just go simple and memorable, it's mainly for your PagodaBox test domain.

After you select Git as your deployment method you'll see instrucitons showing how to get some files into your app.

### Add pagoda remote and then deploy

With our app started in PB bounce back to local and run the following commands changing `myapp.git` to `your-apps-name-on-PagodaBox.git`/

```
$ git remote add pagoda git@git.pagodabox.com:myapp.git
$ git push -u pagoda --all
```
If you setup your remote correctly than after pushing you'll see a stream of output on your screen like "Building Infastructure". You're watching your app build and deploy! Once it's done you should see "Decommisioning Previous Infrastructure" and your prompt will return. If your build ends in error, maybe hold tight, we're still configuring things.

### Now git push also deploys

By using `git push -u pagoda` pagoda became the default remote for the current branch (currently master) and after `$ git push` (in the master branch) will push new commits to pagoda, and rebuild and redeploy your app — pretty powerful!

There are many ways to deploy your app but for our purposes `git push` will be synomous with deploying. What's cool is that if your build fails your app won't go down. PagodaBox keeps your pervious build running right unitl your new build completes, so there should be basically no downtime.

### Create a database, grab the credentials, set Environment Vars

Go back to your PagodaBox dashboard and select your app

- click Add Database
- select Mysql
- select Cloud
- enter a name (anything simple)

Once your new database is created it will show in the dashboard (sometimes the UI hangs, just manually refresh if nothing changes after a minute).

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

### Boxfile, your production recipe

PagodaBox uses a file named `Boxfile` that sits at the root of your app and configures your production environment. It's YAML formatted so it's easy to read and edit. Since it's part of your repo it's version controlled, which is awesome becuase it keeps the state of your code and server in sync.

[ see: [boilerplate/Boxfile](boilerplate/Boxfile) `&&` [About Boxfile.md](About Boxfile.md) ]

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
$ git push
```

## Test Your Database

Back local, let's put some fake data in the database

### DoctrineFixtures


Get fixtures (fix composer.json stability)

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

Fresh Database

```
$ php app/console doctrine:database:drop --force; php app/console doctrine:database:create
```

Make PTBundle

```
$ php app/console generate:bundle --namespace=PagodaTest/Bundle --bundle-name=PTBundle --no-interaction --structure --dir=src --format=annotation
```
Make an entity

```
$ php app/console doctrine:generate:entity --entity=PTBundle:Post --fields="title:string(255) body:text"
```