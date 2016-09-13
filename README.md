# BZContact PHP Form Manager

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy?template=https://github.com/BZCoding/bz-contact-php/tree/master)

BZContact is a simple contact form manager. It can be used to manage a contact form for a static web site, or to create a landing page and collect call to action requests.

This application uses the latest Slim 3 Framework with the PHP-View template renderer. It also uses the Monolog logger.

## Install

Run this command from the directory in which you want to install your new application.

~~~ console
php composer.phar create-project bzcoding/bz-contact-php [my-app-name]
~~~

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

 * Point your virtual host document root to your new application's `app/public/` directory.
 * Ensure `logs/` is web writeable.

That's it! Now go build something cool.

## Credits

BZContact is built on top of Slim-Skeleton application.
