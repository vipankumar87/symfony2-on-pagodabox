## Symfony2 on PagodaBox

This repository contains documentation and basic boilerplate code. The intent is to document the shortest, lowest friction path to a well configured Symfony 2.x applicaiton deployed on PagodaBox. My goal is to test and revise for each minor version of Symfony starting at 2.3.x. While I will keep the techinical details specific to PagodaBox the general concepts should be adaptable to similiar production/deployment environments.

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


### Command Line

While the commands should be pretty unviersal in Unix it's worth pointing all the commands are written to work with the behavior of the standard **bash** shell on OSX (i.e the "Terminal" application). Extrapolate if your OS is different.

### Audience

I'm writing for a fairly general audience interested in using a modular, server side application framework like Symfony, but beginners are definitely welcome.

If you're new to the command line, hang tight. Within the past year I gave in and started using bash regularly (mostly because of Git and Composer), so I empathize heavily with the beginners in this area. I highly recommend <a href="https://peepcode.com/products/meet-the-command-line" target="_new">Meet the Command Line</a> and <a href="https://peepcode.com/products/advanced-command-line" target="_new">Advanced Command Line</a> videos, which are worth every penny. Before those videos I was in a constant state of WTF trying to learn Unix through web articles that were usually circular, sometimes arrogant, and generally shitty towards beginners. I'm a GUI guy all the way but there is a ton of useful things you can do with your keyboard. Now I can do enough to be productive but I don't hang out any longer than I have to. Also, for Git I'd recommend <a href="http://www.codeschool.com/courses/try-git" target="_new">Try Git</a> a really great free interactive tutorial. Most of the git commands we'll use are super basic, so what's covered in that course would be enough to understand everything that I'm going to discuss.


