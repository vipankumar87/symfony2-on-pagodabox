Zero to Bootstrapped
==========================

This is the abridged, take no prisoners guide to testdrive Symfony2 on PagodaBox.

Requires:

- [*nix](http://en.wikipedia.org/wiki/Unix-like)
- [composer](http://getcomposer.org/) (installed globally)
- [git](http://git-scm.com/)

## §1. Create a (fresh) Symfony2 Project

On your **Local** machine.<br/>If you have a Symfony2 project handy just do steps **4 & 7**.

1. create a [standard edition](https://github.com/symfony/symfony-standard) project with composer, replace `fresh` with your own folder name → 
```
mkdir fresh && cd $_ && composer create-project symfony/framework-standard-edition . --no-interaction
```

1. init git →
```
git init && git add . && git commit -m 'Fresh Symfony'
```

1. add asset [symlinks](http://stackoverflow.com/questions/9931127/symfony-2-working-with-assets) directive in composer.json →
```
$EDITOR composer.json
```
	- append to extra: 
``` 
  "extra": { 
     …, 
     "symfony-assets-install": "symlink"
  } 
```

1. ensure [symfony/icu parity](http://symfony.com/doc/master/components/intl.html) →
```
php -i | grep "ICU v"
```
if icu version is:
	- **higher than 4.0** → `composer require symfony/icu 1.1.*`
	- **lower than 4.0** or non-existent → `composer require symfony/icu 1.0.*`

1. chmod cache and logs, make sure Apache is running →
```
rm -rf app/cache/* && rm -rf app/logs/*; APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`; sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs && chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```

1. generate some production content → 
```
app/console generate:bundle --namespace=PagodaTest/HelloBundle --bundle-name=HelloBundle --no-interaction --structure --dir=src --format=annotation
```

1. create your *Boxfile* →
```
$EDITOR Boxfile
```
	- see: [boilerplate / Boxfile](https://raw.github.com/mfdj/symfony2-on-pagodabox/master/boilerplate/Boxfile)

1. commit →<br/>
```
git add . && git commit -m 'Demo content installed, Boxfile added'
```

Visit [http://fresh.local/config.php](http://fresh.local/config.php) in your browser; hopefully your server requirments are met.

Then, check [http://fresh.local/hello/world](http://fresh.local/hello/world) in your browser.<br/>Now, PagodaBox.


## §2. Open up PagodaBox and hook into it

On your **[PagodaBox dashboard](https://dashboard.pagodabox.com/)** …

1. Create a new application 
	- select **Empty Repo**
	- name it whatever (like `fresh`)

Back on **Local** …<br/>after you have configured [git/ssh](http://help.pagodabox.com/customer/portal/articles/200927)

1. add **pagoda** remote; replace `fresh.git` with `[your app].git` →
```
git remote add pagoda git@git.pagodabox.com:fresh.git
```

1. deploy and test →
```
git push -u pagoda --all
```

1. check your app out [http://fresh.gopagoda.com/hello/people](http://fresh.gopagoda.com/hello/people)


## §3. Add MySQL to the Mix

1. Update **parameters.yml.dist** →
```
$EDITOR app/config/parameters.yml.dist
```
   - see: [boilerplate / parameters.yml.dist](../boilerplate/parameters.yml.dist)

1. Update local **parameters.yml** →
```
rm app/config/parameters.yml; composer install --no-interaction
```
	- and add a secret; change *[YOUR PHRASE]* →<br/>
``` SECRET=`md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` && sed -i.orig s/'secret.*$'/"secret: $SECRET"/ app/config/parameters.yml && rm app/config/parameters.yml.orig
```	

1. Create **envars.sh** (and add to .gitignore) →
```
sed -i.orig -e '$a\' .gitignore && echo 'envars.sh' >> .gitignore && rm .gitignore.orig && $EDITOR envars.sh
```
	- see: [boilerplate / envars.sh](../boilerplate/envars.sh)
	- run →`. ./envars.sh`

1. Generate a production secret →
```
md5 -s '[DIFFERENT PHRASE]' | sed s/'.* = '/''/
```

1. In PagodaBox dashboard: 
    - add a MySQL database; cloud is free; the web UI will often hang, just refresh after a minute
    - click to manage your database
    - (at bottom of view) click show credentials and copy down the credentials
    - click Environment Vars (up top) and follow the same template as [boilerplate / envars.sh](../boilerplate/envars.sh) making sure to use the Pagoda values, example: [images / PagodaVars.png](images/PagodaVars.png)
    
1. Test the database with some fixture data
Get fixtures (fix composer.json stability)
	- → `$EDITOR composer.json`
	- `…"minimum-stability": "dev",…'
	- → `composer require "doctrine/doctrine-fixtures-bundle" master-dev`
	- → `php app/console doctrine:database:create`



