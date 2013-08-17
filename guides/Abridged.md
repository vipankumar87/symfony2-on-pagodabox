27 Steps to Bootstrap
==========================
### The abridged version of [Configuring & Deploying Symfony 2.3.x for PagodaBox with a MySQL Database](Symfony%202.3.x%20on%20PagodaBox.md)

Tested against:

* Symfony Standard Edition 2.3.3, August 16, 2013


### Heads up

Check [Preface.md](Preface.md) if something doesn't make sense, but do keep in mind that this document assumes `composer` (globally installed) but it's more common to see `php composer.phar` (locally installed).

# Steps

1. In PagodaBox, create a new application

1. In local, replace **fresh** with your project folder:
```
mkdir fresh && cd $_ && composer create-project symfony/framework-standard-edition . --no-interaction
``` 

1. Initialize your Git repo:
```
git init && git add . && git commit -m 'Fresh Symfony'
```

1. Use asset symlinks:
	- ```$EDITOR composer.json```
	- ``` "extra": {
    …,
    "symfony-assets-install": "symlink"
} ```

1. Ensure symfony/icu parity: ```php -i | grep "ICU v"``` If ICU version is:
	- **higher than 4.0**: `composer require symfony/icu 1.1.*`
	- **lower than 4.0** or non-existent: `composer require symfony/icu 1.0.*`

1. Set **app/cache** and **app/logs** permissions:
```
rm -rf app/cache/* && rm -rf app/logs/*; APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`; sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs && chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```

1. Bootstrap some production content → 
```
php app/console generate:bundle --namespace=PagodaTest/HelloBundle --bundle-name=HelloBundle --no-interaction --structure --dir=src --format=annotation
```

1. Add a Boxfile → ```$EDITOR Boxfile```, see: [boilerplate / Boxfile](https://raw.github.com/mfdj/symfony2-on-pagodabox/master/boilerplate/Boxfile)

1. Commit work →
```
git add . && git commit -m 'Demo content installed, Boxfile added'
```

1. Add *pagoda* remote, replace **fresh.git** (make sure you've configured your ssh key) → 
```
git remote add pagoda git@git.pagodabox.com:fresh.git
```

1. Deploy → ```git push -u pagoda --all``` and check it out `http://fresh.gopagoda.com/hello/people`

1. Update **parameters.yml.dist**
   - →`$EDITOR app/config/parameters.yml.dist` 
   - see: [boilerplate / parameters.yml.dist](../boilerplate/parameters.yml.dist)
   - →`rm app/config/parameters.yml; composer install --no-interaction`

1. Update local **parameters.yml** secret →
```
SECRET=`md5 -s '[YOUR PHRASE]' | sed s/'.* = '/''/` && sed -i.orig s/'secret.*$'/"secret: $SECRET"/ app/config/parameters.yml && rm app/config/parameters.yml.orig
```

1. Create **envars.sh** (and add to .gitignore)
	- →`sed -i.orig -e '$a\' .gitignore && echo 'envars.sh' >> .gitignore && rm .gitignore.orig && $EDITOR envars.sh	`	
	- see: [boilerplate / envars.sh](../boilerplate/envars.sh)
	- →`. ./envars.sh`

1. Generate a production secret →
```
md5 -s '[DIFFERENT PHRASE]' | sed s/'.* = '/''/
```

1. In PagodaBox dashboard: 
    - add a MySQL database, cloud is free (the web UI will often hang, just refresh after a minute)
    - click on your database (manage), show credentials, and copy down credentials
    - then click Environment Vars (up top) and follow the same template as [boilerplate / envars.sh](../boilerplate/envars.sh) making sure to use the Pagoda values

