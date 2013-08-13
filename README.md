## Symfony2 on PagodaBox

**This is a work in progress**. The aim is to learn by teaching and record my process getting <a href="http://symfony.com/" target="_new">Symfony2</a> running on <a href="http://pagodabox.com/" target="_new">PagodaBox</a> with minimum friction, solid configuration, and good workflow. If you have a Symfony app up and running and you're trying to switch to PagodaBox this guide should still give you all the important information you need.

**Feedback welcome: <a href="https://twitter.com/markfoxisadj" target="_new">@markfoxisadj</a>**

### Guides

* [Symfony 2.3.x on PagodaBox.md](Symfony%202.3.x%20on%20PagodaBox.md)

### Boilerplate
  - [Boxfile](Boxfile) `+` [About Boxfile.md](About Boxfile.md) (in process)
  - envvars bash script
  
### Extras
  - [About Composer.md](About%20Composer.md)
  - Local Dev
  - Database Tools

### Why Symfony2 and PagodaBox?

**Symfony2** is a php web application framework that caught my eye in early 2012. It encourages modularity, reusablity, sharing, testing, automation, beautiful code and is optimized for speed and scaling. Symfony, Composer and the other related projects form a great community that is making tools that solve common web application problems. No magic unicorns but Symfony comes preconfigured with nice defaults. It's loosely coupled architecture gives you flexiblity which means you have to figure out which way you like to work. While I'm going to follow conventions closely I'll generally choose the path I think achieves clarity, simplicity, expressivness, and I'll leverage automation when possible.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that allows you to create basic applications for free giving you the chance to fully test it's services before you decide to scale and start paying for increased capabilities. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaS options I've played with I found PagodaBox to have the best capabilites and workflow for deploying a Symfony application: you can use Composer with no friction; they offered PHP 5.4 before others; they focus soley on PHP. Also, let's not pretend that the robot branding isn't totally awesome and largely tips the scales in their favor.