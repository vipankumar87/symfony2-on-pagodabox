Deploying Symfony2 on PagodaBox
====

**This is a work in progress — feedback welcome: <a href="https://twitter.com/markfoxisadj" target="_new">@markfoxisadj</a>**

I started this repository to document a reproducibile, low friction workflow for deploying  <a href="http://symfony.com/" target="_new">Symfony2</a> on <a href="http://pagodabox.com/" target="_new">PagodaBox</a>. It details a fresh install for bootstrappers but would be useful if you're thinking about moving your Symfony2 app to PagodaBox.

### Deployment Guides

- [Preface.md](guides/Preface.md)
- [Symfony 2.3.x on PagodaBox.md](guides/Symfony%202.3.x%20on%20PagodaBox.md)
- [Production & Local Parity.md](guides/Production%20%26%20Local%20Parity.md)

### Boilerplate
  - [Boxfile](boilerplate/Boxfile) `&&` [About Boxfile.md](guides/About Boxfile.md)
  - [envvars.sh](boilerplate/envvars.sh)
  - [httpd.conf.fragment](boilerplate/httpd.conf.fragment)  
  
### Extras
  - [About Composer.md](About%20Composer.md)

## Why Symfony2 and PagodaBox?

**Symfony2** is a php framework for building contempoary web apps. Symfony is with modularity in mind and encourages sharing and well written code. It's optimized for speed and scaling while staying flexible and expressive. Symfony2 and related projects form a great community of makers, working to solve interesting problems. Symfony comes preconfigured with nice defaults and no magic unicorns. It's loosely coupled architecture gives you flexiblity which means you have to figure out which way you want to work. I aim to layout a conventional and efficient path for bootstrapping your app.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that allows you to jump in and start creating applications for free giving you the chance to fully test it's services before you decide to scale and start paying for increased capabilities. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaS options I've played with I found PagodaBox to have the best capabilites and workflow for deploying a Symfony application: you can use Composer with no friction and they focus soley on PHP. Also, let's not pretend that the rad robot branding didn't tip the scales.

### License

<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/Text" property="dct:title" rel="dct:type">Symfony2 on PagodaBox</span> by <span xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName">Mark Fox</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/deed.en_US">Creative Commons Attribution-NonCommercial 3.0 Unported License</a>.