Configuring & Deploying Symfony 2.3.x for PagodaBox with a MySQL Database — Abridged Version
==========================

Tested against:

* Symfony Standard Edition 2.3.3, August 16, 2013


# Heads up

Check [Preface.md](Preface.md) if something doesn't make sense.<br/>
Otherwise, just keep in mind that I use:

- `composer` but the default is to use `php composer.phar`
- `sf` but the default is to use `php app/console`

# Steps

1. In PagodaBox, create a new application

1. In local, replace **fresh** with your project folder:
```
$ mkdir fresh && cd $_ && composer create-project symfony/framework-standard-edition . --no-interaction
``` 

1. Initialize your Git repo:
```
$ git init && git add . && git commit -m 'Fresh Symfony'
```

1. Ensure symfony/icu parity: ```$ php -i | grep "ICU v"``` If ICU version is:
	- **higher than 4.0**: `$ composer require symfony/icu 1.1.*`
	- **lower than 4.0** or non-existent: `$ composer require symfony/icu 1.0.*`

1. Use asset symlinks:
	- ```$ $EDITOR composer.json```
	- ``` "extra": {
    …,
    "symfony-assets-install": "symlink"
} ```

1. Set **app/cache** and **app/logs** permissions:
```
$ rm -rf app/cache/* && rm -rf app/logs/*; APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd' | grep -v root | head -1 | cut -d\  -f1`; sudo chmod +a "$APACHEUSER allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs && chmod +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" app/cache app/logs
```

1. Bootstrap some production content: 
```
$ sf generate:bundle --namespace=PagodaTest/HelloBundle --bundle-name=HelloBundle --no-interaction --structure --dir=src --format=annotation
```

1. Add a Boxfile: ```$ $EDITOR Boxfile```, see: [boilerplate/Boxfile](boilerplate/Boxfile)

1. Commit work: 
```
$ git add . && git commit -m 'Demo content installed, Boxfile added'
```

1. Add *pagoda* remote, replace **fresh.git**: 
```
$ git remote add pagoda git@git.pagodabox.com:fresh.git
```

1. Deploy:
```
$ git push -u pagoda --all
```

1. Add a MySQL database in PagodaBox then 