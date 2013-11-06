## Why Symfony2 and PagodaBox?

**Symfony2** is a php framework for building contempoary web apps. Symfony is designed with modularity in mind and encourages testing and sharing and well written code. It's optimized for speed and scaling while staying flexible and expressive. Symfony2 and related projects form a great community of makers, working to solve interesting problems. Symfony comes preconfigured with nice defaults and no magic unicorns. It's loosely coupled architecture gives you flexiblity which means you have to figure out which way you want to work. I aim to layout a conventional and efficient path for bootstrapping your app.

**PagodaBox** is a Platform as a Service (acrobuzz: PaaS) that allows you to jump in and start creating applications for free giving you the chance to fully test it's services before you decide to scale and start paying for increased capabilities. It has a really cool modular component architecture that allows you to augment and scale your app in really interesting ways. Of all of the PaaS options I've played with I found PagodaBox to have the best capabilites and workflow for deploying a Symfony application: you can use Composer with no friction and they focus soley on PHP. Also, let's not pretend that the rad robot branding didn't tip the scales.

## Why I wrote this

What I love about both Symfony and PagodaBox is that they're **well curated** *and* **flexible**, accordingly they are is a constellation of decisions and concepts that need to be understood in order to get up and running. I have a terrible memory so documenting is an important way to solidifying the concepts.

## Quickstarts

PagodaBox allows users to create and submit Quickstarts, which are pre-configured installations of different application frameworks (Wordpress/Magento/Drupal/etc.) that exist to quickly bootstrap an applicaiton. 

It's a great idea but so far the <a href="https://dashboard.pagodabox.com/apps/new?search=symfony" target="_new">Symfony2 quickstarts</a> all include a lot of sourcecode that ties you to a certian version of Symfoyn2. I think providing source is Composer's job, and I'm working on creating a Quickstart that simply installs the newest stable Symfony. For now, I'm documenting the raw steps for the under-the-hood types.
