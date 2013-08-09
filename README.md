Symfony2 on PagodaBox
====================

This repository contains a markdown guide with a boilerplate Boxfile for setting up and deploying a Symfony 2.x applicaiton on PagodaBox. The goal is to stay complete with each minor version of Symfony starting at 2.3.x.

### What's what:

* Guide: [Symfony 2.3.x on PagodaBox.md](Symfony%202.3.x%20on%20PagodaBox.md)
* Boilerplate: [Boxfile](Boxfile) `||` [Boxfile.comments](Boxfile.comments)

### Why Symfony2 and PagodaBox?

**Symfony** is a web application framework written in php that caught my eye in 2012 when I was all but convinced it was Rails or bust. A reputable php developer turned me on to Symfony and the Composer package manager and suddenly the future of php started looking a whole lot brighter. Symfony encourages modular code and strikes a nice balance between flexibility and convention. Symfony has a strong community and lots of tools that solve many of the common needs of a robust web application. Don't expect any magic unicorns to fly-in and solve problem behind the scenes — it comes preconfigured with nice defaults but there a bunch of really cool skills to learn in order to leverage the framework.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that specicialzes in PHP and allows you to create basic applications for free so you can test it's services and then if you want to scale you can start paying for increased capabilities. Try before you buy, man. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaSs I've played with I found PagodaBox to have the right mix of tools and workflow for deploying a Symfony application, especially because they've offered PHP 5.4 before others and by default you can use Composer with little friction. Also, let's not pretend that the robot branding isn't totally awesome and probably tipped the scale.