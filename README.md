# BZContact PHP Form Manager

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy?template=https://github.com/BZCoding/bz-contact-php/tree/master)

BZContact is a simple contact form manager. It can be used to manage a contact form for a static web site, or to create a landing page and collect call to action requests.

## Install

Run this command from the directory in which you want to install your new application.

~~~ console
$ composer create-project bzcoding/bz-contact-php [my-app-name]
~~~

Replace `[my-app-name]` with the desired directory name for your new application.

### Development

Start a development server by running `composer run server --timeout=0`.

### Staging and Production

 * Point your virtual host document root to the application's `app/public/` directory.
 * Ensure your log file path is web writeable if you're not logging to `stdout`.

That's it! Now build something cool.

## Credits

BZContact is built on top of Slim-Skeleton application. It uses Slim 3 Framework with the PHP-View template renderer.

The default UI theme built on top of [Skeleton](http://getskeleton.com/) CSS boilerplate, with a cover photo by [Yair Hazout from Unsplash](https://unsplash.com/@yairhazout).

The favicon and the application logo (`logo.svg`) were built using icons by [Freepik](http://www.freepik.com) from [Flaticon](http://www.flaticon.com), licensed by [CC 3.0 BY ](http://creativecommons.org/licenses/by/3.0/ "Creative Commons BY 3.0")

## License

BZContact is licensed under the MIT License - see the `LICENSE` file for details.
