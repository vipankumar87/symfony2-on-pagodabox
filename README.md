## Symfony2 on PagodaBox

**This is a work in progress — feedback welcome: <a href="https://twitter.com/markfoxisadj" target="_new">@markfoxisadj</a>**

In order prefect my techinque I decided to document my process deploying <a href="http://symfony.com/" target="_new">Symfony2</a> on <a href="http://pagodabox.com/" target="_new">PagodaBox</a> aiming for reproducibility, minimum friction, solid configuration, and good workflow. If you have a Symfony app up and running and you're trying to switch to PagodaBox this guide should still give you all the important information you need.

### Deployment Guide

* [Symfony 2.3.x on PagodaBox.md](Symfony%202.3.x%20on%20PagodaBox.md) `+`

### Boilerplate
  - [Boxfile](Boxfile) `+` [About Boxfile.md](About Boxfile.md) (in process)
  - envvars bash script
  
### Extras
  - [About Composer.md](About%20Composer.md)
  - Local Dev
  - Database Tools

### Why Symfony2 and PagodaBox?

**Symfony2** is a web application framework that caught my eye in early 2012. It encourages modularity, testing, automation, and sharing well written code. It's optimized for speed and scaling while staying flexible and expressive. Symfony2 and related projects form a great community that is making tools that solve common problems. No magic unicorns but Symfony comes preconfigured with nice defaults. It's loosely coupled architecture gives you flexiblity which means you have to figure out which way you want to work. I aim to layout a conventional and efficient path to deploying an app.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that allows you to create basic applications for free giving you the chance to fully test it's services before you decide to scale and start paying for increased capabilities. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaS options I've played with I found PagodaBox to have the best capabilites and workflow for deploying a Symfony application: you can use Composer with no friction; they offered PHP 5.4 before others; they focus soley on PHP. Also, let's not pretend that the rad robot branding didn't tip the scales.

### License

<a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/Text" property="dct:title" rel="dct:type">Symfony2 on PagodaBox</span> by <span xmlns:cc="http://creativecommons.org/ns#" property="cc:attributionName">Mark Fox</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc/3.0/deed.en_US">Creative Commons Attribution-NonCommercial 3.0 Unported License</a>.