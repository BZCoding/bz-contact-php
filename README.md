# BZContact PHP Form Manager

[![Build Status](https://travis-ci.org/BZCoding/bz-contact-php.svg?branch=master)](https://travis-ci.org/BZCoding/bz-contact-php) [![Test Coverage](https://codeclimate.com/github/BZCoding/bz-contact-php/badges/coverage.svg)](https://codeclimate.com/github/BZCoding/bz-contact-php/coverage) [![Code Climate](https://codeclimate.com/github/BZCoding/bz-contact-php/badges/gpa.svg)](https://codeclimate.com/github/BZCoding/bz-contact-php)

**BZContact** is a simple contact form manager written in PHP. It can be used to manage a contact form for a static web site, or to create a landing page and collect call to action requests.

**This repo is meant to be forked** and customized to create your own contact form or landing page.

## Features

 - A single landing page, with form processing and a "thank you" or error page
 - A contact form structure defined by a JSON file
 - Customizable Privacy and ToS pages
 - Stores entries in MongoDB
 - Sends notification emails to the admin
 - Sends "thank you" emails to subscribers
 - Integrates with MailChimp
 - Integrates with Webhooks
 - Simple default UI theme, easy to customize

## Installation

### Deployment on Heroku

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy?template=https://github.com/BZCoding/bz-contact-php/tree/master)

The deployment script will provision a web process and a worker process for background tasks.

The script will also provision free plans for the following add-ons: [MongoLab](https://elements.heroku.com/addons/mongolab), [Postmark](https://elements.heroku.com/addons/postmark), [CloudAMQP](https://elements.heroku.com/addons/cloudamqp), [Logentries](https://elements.heroku.com/addons/logentries) and [Rollbar](https://elements.heroku.com/addons/rollbar). If you already have paid plans on these services, or want to use other services, you can delete them and customize your configuration.

If you use the provided free version of Postmark you need to [create a sender signature](http://support.postmarkapp.com/category/45-category) or you will not be able to send email notifications.

### Installation on a Linux/macOS machine

Run this command from the directory in which you want to install your new application:

~~~ console
$ composer create-project bzcoding/bz-contact-php [your-app-name]
~~~

Customize the form and the UI theme.

### Running in Development

 - Rename `.env.example` to `.env` and enter your custom settings
 - Start a development server by running `composer run server --timeout=0`

You may use the provided Vagrant box and Ansible settings, but be aware that they have been written for development only.

### Running in Staging and Production

 - Point your virtual host document root to the application's `app/public/` directory
 - Copy the needed environment variables from `.env.example` into your virtual host file
 - Ensure your log file path is web writeable if you're not logging to `stdout`.

### Configuration

In order to have a working instance of BZContact you need to provide the settings through environment variables. The following settings are required:

 - `MAILER_*` for the SMTP server
 - `DATABASE_*` for the MongoDB server
 - `AMQP_*` for the queue server

While these others are optional:

 - `NEWSLETTER_*` enables MailChimp integration
 - `WEBHOOK_*` enables webhooks processing
 - `REDIRECT_THANKYOU` uses a custom "thank you" page
 - `ROLLBAR_ACCESS_TOKEN` enables Rollbar error tracking

## Requirements

 - Apache or Nginx web server
 - PHP 5.6 or better with MongoDB support (tested with `ext-mongo` on PHP 5.6, you need `ext-mongodb` with PHP 7)
 - MongoDB server
 - RabbitMQ or other AMQP compatible server
 - An SMTP mail server (or [MailCatcher](https://mailcatcher.me/)) for development

## The JSON Form object

The form is loaded from a simple JSON object, with two top-level properties: `attributes` (object) and `fields` (array of field objects). Every field object must have at least a `name` or a unique `id` attribute, the default input type is `text`.

Supported field types are: `text` and `textarea`, `email`, `tel`, `select`, `checkbox`, `radio`, `submit`.

**Please note** that BZContact has been designed to be used with contact forms, a more complex form structure could lead to undesired results.

~~~ json
{
    "attributes": {
        "id": "frm-contact",
        "class": "contact-form",
        "accept-charset": "utf-8",
        "novalidate":"novalidate"
    },
    "fields": [
        {
            "id": "contact-name",
            "name": "name",
            "label": "Your name",
            "placeholder": "eg. John Appleseed",
            "required": true,
            "error": "Your name is a required field"
        },
        ...
        {
            "id": "contact-submit",
            "name": "saveForm",
            "type": "submit",
            "value": "Send message",
            "save": false
        }
    ]
}
~~~

## The Webhook post format

The webhook feature is enabled by setting the `WEBHOOK_URL` environment variable to the desired destination URL.

The content of the form subscription is `POST`ed to the webhook URL with an `application/json` content type and a JSON body.

BZContact sends two custom headers:

 - `X-Bzcontact-Event`: the object of the event (i.e `message`)
 - `X-Bzcontact-Delivery`: the id of the submission

Optional custom headers can be added using the `WEBHOOK_HEADERS` env var, each header separated by a  `|`: `WEBHOOK_HEADERS="X-Foo:123|X-Bar:xyz"`.

The JSON payload has the following format:

~~~ json
{
  "action": "saved",
  "created_at": "YYYY-MM-DD HH:MM:SS",
  "data": {
    "name": "John Doe",
    "company": "ACME Ltd",
    "email": "john@acme.com",
    "phone": "",
    "subject": "It's only Rock'n Roll...",
    "message": "but I like it!\r\n~M\r\n",
    "referral": "friends",
    "client-type": "business",
    "privacy": "1",
    "ip": "xxx.xxx.xxx.xxx",
    "datetime": "YYYY-MM-DD HH:MM:SS",
    "id": "<SubmissionID>"
  }
}
~~~

The `action` attribute contains the event type (only `saved` for now). The `data` object contains all the form fields the addition of the subscriber IP address, datetime and submission ID.

## Credits

BZContact is built on top of Slim-Skeleton application. It uses Slim 3 Framework with the PHP-View template renderer.

The default UI theme built on top of [Skeleton](http://getskeleton.com/) CSS boilerplate, with a cover photo by [Yair Hazout from Unsplash](https://unsplash.com/@yairhazout).

The favicon and the application logo (`logo.svg`) were built using icons by [Freepik](http://www.freepik.com) from [Flaticon](http://www.flaticon.com), licensed by [CC 3.0 BY ](http://creativecommons.org/licenses/by/3.0/ "Creative Commons BY 3.0")

## License

BZContact is licensed under the MIT License - see the `LICENSE` file for details.
