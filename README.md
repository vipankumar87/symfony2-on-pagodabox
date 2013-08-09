symfony-on-pagodabox
====================

This repository contains some markdown guides with boilerplate [Boxfile (external link)](http://help.pagodabox.com/customer/portal/articles/175475) for setting up and deploying Symfony 2.x on PagodaBox. The goal is to stay complete with the newest version of Symfony starting at 2.3.x.

## Guide: [Symfony 2.3.x on PagodaBox.md](Symfony%202.3.x%20on%20PagodaBox.md)

### Why Symfony2 and PagodaBox?

**Symfony** is a web application framework written in php that caught my eye in 2012 when I was all but convinced it was Rails or bust. Another php developer turned me on to Symfony and the Composer package manager and suddenly the future of php started looking a whole lot brighter. Symfony encourages modular code and strikes a nice balance between flexibility and convention. Symfony has a strong community and lot's of amazing tools that ease a lot of pain in creating a robust web application. It does have a steep learning curve though, so don't expect any magic unicorns to show up and solve problem behind the scenes — it comes preconfigured with nice defaults but it pays to learn about them so you can really control your application.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that specicialzes in PHP and allows you to create basic applications for free so you can test it's services. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaSs I've played with I found PagodaBox to have the right mix of tools and workflow for deploying a Symfony application, specifically because it offered PHP 5.4 and allowed Composer with little friction. Also, let's not pretend that the robot branding isn't totally awesome and probably tipped the scale a little.