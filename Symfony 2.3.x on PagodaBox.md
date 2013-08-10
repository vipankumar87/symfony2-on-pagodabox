Symfony 2.3.x on PagodaBox
==========================

### This is a work in progress

The aim of this document is to learn by teaching and record my process of getting a <a href="http://symfony.com/" target="_new">Symfony 2</a> application running on <a href="http://pagodabox.com/" target="_new">PagodaBox</a> with minimum friction, solid performance, and ease of continous developemnt / deployment cycles. 

**Feedback is Welcome: <a href="https://twitter.com/markfoxisadj" target="_new">@markfoxisadj</a>**

This is written from the perspective of using the bash shell on OSX — extrapolate if your OS is different.

### Heads up about convetions I use

#### "$" for shell commands

Throughout this document I'll follow the convention of representing your shell prompt with a '$', so if you see a command like `$ cd/path/to` you would copy 'cd/path/to' into your prompt and execute it.

Also, unless noted commands are assumed to be run inside the root folder of your local Symfony project.

#### Composer (php dependency manager) usage

Hopefully you've already been using Composer, a tool for grabbing and installing open source php packages. Throughout this document I'll use `$ composer` because I've installed it <a href="http://getcomposer.org/doc/00-intro.md#globally" target="_new">globally</a>. If you install it <a href="http://getcomposer.org/doc/00-intro.md#locally" target="_new">locally</a> (i.e. per project) than you'll use `$ php composer.phar` instead.

### Tested with

* August 9, 2013: works with Symfony 2.3.3


Start locally
=============

PagodaBox offers <a href="https://dashboard.pagodabox.com/apps/new?search=symfony" target="_new">Quickstarts</a> for various frameworks but I'd recommend not using any Quickstart that forks the Symfony core —  I think providing source code is a job best left to Composer. Let's create a skeleton app locally, and then push it to PagodaBox.

### Local Requriments

This tutorial assumes you're using the standard Symfony stack:

* Apache 2
* PHP 5.3.3+ (as an Apache module *and* command line tool)
* MySQL (not sure about the min version)
* Git (whatever is newest)
* Composer (always the newest)

Personally I use MAMP Pro to manage Apache/MySQL/PHP, but I've been meaning to switch to Vagrant. I use Pear to manage php extnesions, which requires <a href="http://www.lullabot.com/blog/article/installing-php-pear-and-pecl-extensions-mamp-mac-os-x-107-lion" target="_new">some extra config for MAMP</a>. I install Git with Homebrew and Composer uses it's own cURL method and self-update command.

It goes without saying you're going to want to maximize parity between your local development environment and your PagodaBox production environment. Not just the core tools listed above, but also in the PHP extensions you use. This can be a slight pain (has been for me) because some extensions require building from source (like <a href="http://stackoverflow.com/questions/16753105/problems-with-lib-icu-dependency-when-installing-symfony-2-3-x-via-composer" target="_new">intl</a>).

Some extensions — like Xdebug — are best left off the production server but in general you should aim to ensure that boths stacks match with the same major version and hopefully minor version of each extension.


Create a Symfony project with Composer
======================================

You can name your project anything, I chose "fresh".

```
$ mkdir fresh
$ composer create-project symfony/framework-standard-edition fresh/ 2.3.3
$ cd fresh
``` 
Running the create-project does a few things, it grabs the core Symfony files and then runs `composer install` which simply looks at the `composer.json` file (included with Symfony's core) and installs all of the dependencies listed. These get installed in `vendor/` which is a default convention for keeping external code organized.

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
- locale: `en` This optional prefix refers to the default language of your site (ToDO: check if it's a convention or a specificaiton)
- **secret:** ***FILL THIS IN!*** I like to hash a random phrase (in a different shell session) `$ md5 -s 'put a random phrase here'`

With the parameters configured the Symfony installer runs a few more commands and assuming there were no errors we're off to a good start!

You can `$ ls -lAGh` for Unixy or `$ open .` for OSXy glimpse at all your fresh new source code.

### Set permissions for cache and logs

There are a <a href="http://symfony.com/doc/master/book/installation.html#configuration-and-setup" target="_new">few options</a> for this critical step of ensuring Symfony can write to `app/cache/` and `app/logs/` directories. It varies depending on your system but for OSX I like this one-liner which uses ACL (more powerful than standard unix permissions). These commands allow your shell user and the APACHEUSER (your webserver process) to write to the cache and log directories:

*please note: I added line breaks after each command for readability, but it's still a one liner*

```
$ rm -rf app/cache/*;
rm -rf app/logs/*; 
APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`;
sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs;
sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```

### Git

It goes without saying you're going to use source control and it's probably going to be git which is good because it's basically required to use PagodaBox. It's not *actually* required, but this tutorial assumes it's "The Way™". Make sure you're in the root of your project and run:

```
$ git init
```

Symfony comes with a preconfigured .gitignore so your first commit should capture the initial state of your applicaiton after installation is complete.

```
$ git add .
$ git commit -m 'Fresh Symfony 2.3.3'
```
### Add symlink option in composer.json

This is one of those optional but it's so good it should almost be default (it's probably Windows fault it's not). This will cut down on redundant storage and easier directory parity. Say what? Basically each time you add a new Bundle - Symfony's word for a module or code package — you'll often need to install it's assets and using symlinks means we only have to "install" a Bundles assets once, not each time they change.

In `composer.json` you'll see an "extra" section, add the following value pair (don't forget your JSON commas!):

```
"extra": {
	…
	"symfony-assets-install": "symlink"
}
```
You could use "relative" instead of "symlink", to generate relative symlinks but it's not necessary because of the way Symfony is configured and how we'll deploy to PagodaBox. I may eat my words later, but unless you plan to move your project around a lot on your local machine (why?) then absolute paths carry all of the certainly of the software mogul you are. Plus updating the symbolic links later is trivial.

### Check your installation

In your browser hop to `http://localhost/app_dev.php/` — you did point your Apache root at the `web/` directory, right? Nice, you should see the Dev environemnt Welcome screen.

You should also check your PHP configuration at `http://localhost/config.php`. There are often little things to configure, install, update depending how you install Apache and PHP. 


## Open up PagodaBox

If you haven't already, sign up for PagodaBox and head to your dashboard `https://dashboard.pagodabox.com/` where we'll create a new application.

### Create your first application
* select Empty Repo
* name your app
* select Git for deployment mode

While the quickstarts are kind of a neat idea it's a I think it's easier to start with an emtpy repo because it gives you flexibility about which version of Symfony you install (these instructions will probably work well with any 2.x version of Symfony).

When naming your application you need to pick a name that is globally unique to the PagodaBox service since this name will be used for the test domain generated for you. The name is not terribly important, it doesn't need to perfectly match the domain you intend to host the site, just pick something simple and memorable. 

After you select Git as your deployment method you'll see some instrucitons that show you how to get some files into your app. Before that let's start our Boxfile.

### The super cool Boxfile

PagodaBox is one of my favorite PaaS options for PHP because they have a neat configuration system called <a href="http://help.pagodabox.com/customer/portal/articles/175475" target="_new">Boxfile</a> which is a YAML formatted configuration named `Boxfile` that sits in the root of your app and specifies your production platform. Since it's part of your repo it's version controlled, which is awesome when you need to rollback your app or switch between branches with different configurations (if that's your thing)!

Without getting into the nitty gritty below is a Boxfile I've successfully used to deploy a barebones Symfony 2.3.3 app on PagodaBox.

This repo contains the most up to date copy of my suggested <a href="http://Boxfile" target="_new">Boxfile</a> ()along with a commented version called <a href="Boxfile.comments" target="_new">Boxfile.comments</a>.

```
web1:

  document_root   : web
  default_gateway : app.php
  index_list      : [app.php]

  shared_writable_dirs:    
    - app/cache
    - app/logs

  apache_access_log : false
  apache_error_log  : true
  php_error_log     : true
  php_fpm_log       : true

  php_version: 5.4.14
  php_date_timezone: "America/Los_Angeles"

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
    - "php composer.phar install --prefer-source --optimize-autoloader"

  after_deploy:
    - "php app/console cache:clear --env=prod --no-debug"
    - "php app/console router:debug --env=prod"

``)`

### Add PagodaBox as a remote repository

Back in local run the following commands, making sure to change `myapp.git` to the `name-of-your-application-on-PagodaBox.git`

```
$ git remote add pagoda git@git.pagodabox.com:myapp.git
$ git push -u pagoda --all
```
If you setup your remote correctly than the after that git push above you'll see a stream of output on your screen like "Building Infastructure", which means you're watching your app being succesfully deployed for the first time! Deployment just means making code live, although it's a little more than your typical FTP trasnfer, which we'll see.

Now whenever you `git push` your master branch to the pagoda remote it will (re)build and then deploy your new app immediately — pretty powerful! You can choose a different branch or completely turn this behavior off (there are many options for deployment) but for now assume that `git push` is synomous with deploying new versions of your app. What's cool is that if your build fails your app won't go down since PagodaBox keeps your pervious build running right unitl your new build completes succesfully so there should be little to no downtime.

**Aside about git push above:** the `-u` flag makes `pagoda` the default remote (on a per branch basis) so when you `git push` in the future you can ommit typing pagoda. The `--all` flag pushes all branches (all referneces, which includes tags too I think) and is optional but nice it's nice to have your entire repo hosted in the cloud as a backup. I'm pretty sure git is configured to push all refs by default but I've configured it not to so the `--all` flag is is a good reminder to do this.

##The rest of this document are is an outline with fragments I'm currently working on

### Install a database

- Go to PagdoaDashboard
- Click add Database, select MySQL and pick a name (or let one be auto-generated)
	- I've noticed that creating a databse will often make the dashboard hang, just refresh it after a minute or so

### Using MySQL Workbench (or another DBA terminal)
- install <a href="http://www.mysql.com/products/workbench/" target="_new">MySQL Workbench</a> (you need to start a free acount)
- install the pagoda cli `$ gem install pagoda`
- from your app root `$ pagoda tunnel -c [dbname]`

### Configure your app

Currently our deployed 

### Ensure intl extension parity is made between local dev and production

http://symfony.com/doc/current/components/intl.html#installation

```
"require": {
	"php": ">=5.3.3",
	"symfony/icu": "1.1.*",
	"symfony/symfony": "2.3.*",
    …
```

### Configure paramters.yml

```
database_port:     %database.port%
database_name:     %database.name%
database_user:     %database.user%
database_password: %database.pass%
```

### Add to local environment

```
SetEnv  SYMFONY__DATABASE__NAME  typesnitch
SetEnv  SYMFONY__DATABASE__PORT  3306
SetEnv  SYMFONY__DATABASE__USER  root
SetEnv  SYMFONY__DATABASE__PASS  OriginalMix
SetEnv  SYMFONY__DATABASE__SOCK  ~
```

### Create envvars bash script for local CLI

When using `doctrine generate:*` commands you will need your database crednetials to be accessible. This script exports variables into your shell enviroment that Symfony will automatically parse for your commandline scripts.

```
#!/bin/bash
export SYMFONY__DATABASE__NAME=typesnitch
export SYMFONY__DATABASE__PORT=3306
export SYMFONY__DATABASE__USER=root
export SYMFONY__DATABASE__PASS=OriginalMix
```
and run it as `. ./envvars` to export for your current shell session.


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