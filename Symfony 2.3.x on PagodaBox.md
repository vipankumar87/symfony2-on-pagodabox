Symfony 2.3.x on PagodaBox
==========================

The aim of this document is to record my process getting a Symfony2 app up running on PagodaBox with minimum friction, solid performance, and ease of continous developemnt/deployment cycles.

<p style="color:red; font-weight:bold; text-transform: uppercase">This is a work in progress</p>

## Start locally with these tools

We'll create a skeleton app locally, and then push it to PagodaBox.
This tutorial requires familiarity with the standard Symfony stack:

* Apache
* PHP (as an Apache module and command line tool)
* MySQL
* git
* Composer (more on that next!)

This is written from the perspective of OSX. Extrapolate if your OS is different.

## Start a Symfony project with Composer

I've seen many 

You can name your project anything, I chose "fresh".

```
$ mkdir fresh
$ composer create-project symfony/framework-standard-edition fresh/ 2.3.3
$ cd fresh
``` 
Running the create-project does a few things, it grabs the core Symfony files and then runs `composer install` which simply looks at the `composer.json` file (included with Symfony's core) and installs all of the dependencies listed. These get installed in `vendor/` which is an assumed convention you should definitely follow.

After all the vendors are downloaded — you will get prompted to provide some values to generate a configuration file at `app/config/parameters.yml`. Just hit enter on each prompt to use the default values, some of them won't even be needed since we'll use Apache Environment Variables which makes it easier to maintain difference between our local and PagodaBox setups (more on this later).

Here's an overview of the prompts you may see:

- **database_driver:** pdo_mysql `Doctrine uses pdo by default and PagodaBox supports MySQL`
- **database_host:** 127.0.0.1 `we'll configure this as an EnvVar later`
- **database_port:** null `ditto EnvVar`
- **database_host:** symfony `ditto EnvVar`
- **database_user:** 127.0.0.1 `ditto EnvVar`
- **database_password:** 127.0.0.1 `ditto EnvVar`
- **mailer_transport:** smtp `only needed if you intend to use Symfony's mail component`
- **mailer_host:** 127.0.0.1 `ditto`
- **mailer_host:** null `ditto`
- **mailer_host:** null `ditto`
- **locale:** en `This optional prefix refers to the default language of your site`
- **secret:** *This doesn't have a good default, so fill it in!* I like to hash a random phrase (in a different Terminal window) `$ md5 -s 'put a random phrase here'`

With the parameters configured the Symfony installer runs a few more commands and assuming there were no errors we're off to a good start!

### Set permissions for cache and logs

There are a [few options](http://symfony.com/doc/master/book/installation.html#configuration-and-setup) for this critical step of ensuring Symfony can write to `app/cache/` and `app/logs/`. It varies depending on your system but for OSX and I like this one-liner which changes permissions with ACL. It allows you (the user logged into the terminal) and the APACHEUSER (your webserver process) to write to these directories:

```
$ rm -rf app/cache/*;
rm -rf app/logs/*; 
APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`;
sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs;
sudo chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```

### git

It goes without saying you're going to use source control and it's probably going to be git which is good because it's basically required to use PagodaBox. It's not *actually* required, but this tutorial assumes it's "The Way™"

```
$ git init
```

Symfony comes with a preconfigured .gitignore so your first commit should capture the initial state of the repository after installing via Composer.

```
$ git add .
$ git commit -m 'First'
```
### Add symlink option in composer.json

This is one of those optional but is so good it should almost be default (it's probably Windows fault it's not) since it cuts down on redundant storage and directory parity. Each time you add a Bundle, which is Symfony's word for a module/package/installable-dependency, you'll often need to install it's assets and using symlinks means we don't have to re-install a Bundles assets if they change.

In `composer.json` you'll see an "extra" section, add the following value pair (don't forget your JSON commas!):

```
"extra": {
	…
	"symfony-assets-install": "symlink"
}
```
You could use "relative" instead of "symlink", to generate relative symlinks but it's not necessary because of the way Symfony is configured and how we'll deploy to PagodaBox. I may eat my words later, but unless you plan to move your project around a lot on your local machine (why?) then absolute paths carry all of the certainly of the software mogul you are. Embrace inflexibility! Plus updating the symbolic links later is trivial.

### Check your installation

In your browser hop to `http://localhost/app_dev.php/` — you did point your Apache root at the `web/` directory, right? Then you should see the Welcome screen.

You should also check your PHP configuration at `http://localhost/config.php`. There are often little things to configure, install, update depending how you install Apache and PHP. 


## Open up PagodaBox

Signup for PagodaBox and login at `https://dashboard.pagodabox.com/` and create a new application

### Create your first application
* select Empty Repo
* name your app
* select Git for deployment mode

While the quickstarts are kind of a neat idea it's a lot better to select with an emtpy repo because it gives you flexibility about which version of Symfony you install (these instructions will probably work well with any 2.x version of Symfony).

When naming your application you need to pick a name that is globally unique to the PagodaBox service since this name will be used for the test domain generated for you. It doesn't need to match the domain you intend to host the site. The name is not terribly important, just pick something simple and memorable.

After you select Git as your deployment method you'll see some instrucitons that show you how to get some files into your app. Before you can configure your app you need to get some files into your PagodaBox git repository.

### The super cool Boxfile

PagodaBox is one of my favorite PaaS options for PHP because they have a neat configuration system called [Boxfile](http://help.pagodabox.com/customer/portal/articles/175475) which is a YAML formatted configuration that specifies your deployment platform.

Without getting into the nitty gritty this is my current Boxfile, for Symfony 2.3.3 verbaitm, with comments. Ignore the comments for now and make sure:

```
web1:                            # <~~~ component type & number

    # ~~~ APACHE ~~~

    document_root   : web       # <~~~ standard Symfony public folder
    default_gateway : app.php
    index_list      : [app.php]

    # ~~~ PAGODA ~~~

    name: mfresh                # <~~~ your apps name
    shared_writable_dirs:       
        - app/cache             # <~~~ integral to Symfony
        - app/logs    

    # Notes on 'shared_writable_dirs'
    # After a build is triggered (usually after pushing) all directories are writable.
    # Once the build finishes the you can only write to `shared_writable_dirs`
    # which are shared across all instances of your app via a network tunnel. 
    # More here: http://blog.doh.ms/2012/04/16/deploying-a-symfony2-and-composer-app-on-pagodabox/
    # Worth noting that because of this `shared_writable_dirs` are destroyed on every build,
    # while the rest of your app is not.

    # ~~~ PHP ~~~

    # ~ Check Symfony's requirements + recommendations:
    # http://symfony.com/doc/master/reference/requirements.html

    # ~ See which versions of PHP offered by PagodaBox
    # http://help.pagodabox.com/customer/portal/articles/175475-understanding-the-boxfile#php-version

    # ~ See which PHP extensions are offered for your version of PHP
    # http://help.pagodabox.com/customer/portal/articles/175475-understanding-the-boxfile#php-Extensions

    # ~ iconv, posix, tokenizer, xml, ctype extensions are installed by default

    php_version: 5.4.14
    php_date_timezone: "America/Los_Angeles" # <~~~ use your own
    php_extensions:                          
        - apc      
        - curl
        - intl        
        - mbstring
        - mysql
        - pdo_mysql
        - xsl
        - zip
    php_session_autostart:  Off
    php_short_open_tag:     Off
    magic_quotes_gpc:       Off
    register_globals:       Off # <~~~ should be off by default, nice to be explicit

    # ~~~ COMMAND HOOKS ~~~

    after_build:
        - "curl -sS https://getcomposer.org/installer | php"
        - "php composer.phar install --prefer-source"   # this will prevent re-downloading of github sources
        - "php composer.phar dump-autoload --optimize" # see http://getcomposer.org/doc/03-cli.md#dump-autoload

    after_deploy:
        - "php app/console router:debug --env=prod" # helpful reminder

```

### Add PagodaBox as a remote repository

Back in your local symfony installation run the following commands, making sure to change `myapp.git` to your apps name `.git`

```
$ git remote add pagoda git@git.pagodabox.com:myapp.git
$ git push -u pagoda --all
```
If you setup your remote correctly than after you did `git push` you will see a bunch of output on your screen, which means you've succesfully deployed your first app!

**Aside about git push above:** `-u` flag makes `pagoda` the default remote so when you `git push` in the future you can ommit typing pagoda. The `--all` flag pushes all branches (all referneces, which includes tags) and is optional but it's good to have your entire repo hosted in the cloud as a backup. I'm pretty sure git is configured to push all refs by default but I've configured it not to so the `--all` flag is is a good reminder to do this.

Now each time you `git push` PagodaBox will build your app with the new changes and deploy it live — pretty powerful! You can change this behavior if you want, but for now assume that `git push` is the defacto way to deploy your app. What's cool is that if your build fails your app won't go down, since PagodaBox keep your pervious build running right unitl your new build completes succesfully and is ready to swap in.

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