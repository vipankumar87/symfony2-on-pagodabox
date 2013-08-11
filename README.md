## Symfony2 on PagodaBox

This repository contains documentation and basic boilerplate code. The goal is to document the shortest path to a well configured Symfony 2.x applicaiton deployed on PagodaBox by testing and revising for each minor version starting with Symfony 2.3.x. While I will keep the techinical details specific to PagodaBox the general concepts should be adaptable to other production/deployment environments.

### Guides

* [Symfony 2.3.x on PagodaBox.md](Symfony%202.3.x%20on%20PagodaBox.md)

### Boilerplate
  - [Boxfile](Boxfile) `+` [About Boxfile.md](Boxfile.md) (documented: in process)
  - envvars bash script
  
### Extras
  - [About Composer.md](About%20Composer.md)
  - Local Dev
  - Database Tools

### Why Symfony2 and PagodaBox?

**Symfony2** is a php web application framework that caught my eye in early 2012. It encourages modularity, reusablity, sharing, testing, automation, beautiful code and is optimized for speed and scaling. Symfony, Composer and the other projects form a great community that makes tools that solve common needs of a robust web application, written in code that I love to study and learn from. No magic unicorns but Symfony comes preconfigured with nice defaults. It's loosely coupled architecture gives you flexiblity and the onus of learning how the parts fit togehter. To this end I will write with the 80:20 rule in mind becuase the strength of an application framework is following convention until you know enough to break from it.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that specicialzes in PHP and allows you to create basic applications for free so you can test it's services and then as you want to scale you can start paying for increased capabilities. Try before you buy, man. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaSs I've played with I found PagodaBox to have the right mix of tools and workflow for deploying a Symfony application, especially because they've offered PHP 5.4 before others and by default you can use Composer with little friction. Also, let's not pretend that the robot branding isn't totally awesome and largely tips the scales in their favor.