Preface
========


## Command Line

While the commands I use should be portable throughout \*nix it's worth noting all commands are written with OSX's standard **bash** shell in mind (i.e the "Terminal" application). Extrapolate if your OS is different.

I'm pretty recent command line convert and I've tried to keep commands terse while hopefully being easy to copy and (as needed) customize. Sometimes I compress multiple commands on one line, which I know is harder to follow if you're new to using a Unix CLI. 

## Conventions

**Lines starting with a dollar sign '$' represent your shell prompt** — if you see `$ cd path/to` you'd enter and execute `cd path/to` in your command line.

**one-liners** remember that in Unix semicolons `;` double ampersands `&&` and pipes `|` all allow for several commands to be executed sequentially on a single line**†**  — in other words they are seperators if you're trying to decipher a long command.

**†** *they have functional differences in the way they chain, which you can research on your own.*

**Navigating in bash** For quicker navigation `control + a` will move your cursor to the beginning of the line  and `control + e` will move your curor to the end. `control + k` will clear everything in front of your cursor. The up arrow key will cyclce through your command history.

**I have Composer** <a href="http://getcomposer.org/doc/00-intro.md#globally" target="_new">globally installed</a> so I'll use `$ composer …` throughout — if you <a href="http://getcomposer.org/doc/00-intro.md#locally" target="_new">locally install</a> per project you'll use `$ php composer.phar …` instead.

** Your text editor of choice** — `$EDITOR` just means your [default commnad line text editor](http://stackoverflow.com/questions/3539594/change-the-default-editor-for-files-opened-in-the-terminal-e-g-set-it-to-text) — if you don't launch your text editor from the command line then when you see `$ $EDITOR app/config/paramters.yml` you'd open the file `app/config/paramters.yml` in your favorite text editor (path relative to project root).


## Local Server

This tutorial assumes the standard Symfony stack:

* Apache 2
* PHP 5.3.3+ (for Apache *and* your command line)
* MySQL

along with these essential command line tools:

* <a href="http://getcomposer.org/download/" target="_new">Composer</a>
* Git — which I install via Homebrew: <a href="http://www.moncefbelyamani.com/how-to-install-xcode-homebrew-git-rvm-ruby-on-mac/" target="_new">How to Install Xcode, Homebrew, Git … on OSX 10.6+</a>

Check out [Production & Local Parity.md](Production%20&%26Local%20Parity.md)


## Audience

I'm writing for savvy OSX developers interested in getting a modular, server side application framework like Symfony2 up and running.

If you're new to the command line, hang tight. I just started using bash regularly this year (mostly because of Git and Composer), so I empathize heavily with the beginners in this area. I highly recommend <a href="https://peepcode.com/products/meet-the-command-line" target="_new">Meet the Command Line</a> and <a href="https://peepcode.com/products/advanced-command-line" target="_new">Advanced Command Line</a> videos, which are worth every penny. Before those videos I was in a constant state of WTF trying to learn Unix through web articles that were usually circular, sometimes arrogant, and generally shitty towards beginners. I'm a GUI guy all the way but there is a ton of useful things you can do with your keyboard. For learning Git I'd recommend <a href="http://www.codeschool.com/courses/try-git" target="_new">Try Git</a> a free high quality interactive tutorial. Most of the git commands we'll use are super basic, so what's covered in that course would be enough to understand everything that I'm going to discuss.


