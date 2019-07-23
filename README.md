# ODIN

# Pre requirements

https://laravel.com/docs/5.8#server-requirements


# Prerequisites

Required stack

* Postgres DB (10 or greater) [Download here](https://www.postgresql.org/download/)
* Composer [Download here](https://getcomposer.org/download/)
* php 7.1 or greater
* node (8.x or greater)
* npm (3.5.x or greater)


# Development setup

If you are manually installing locally, download and install the stack above.

## Postgres setup

* Install using en_US.UTF8 encoding.
* Setup any relevant users.
* Create a new database called tuvens with UTF8 encoding 

## Environment variables

Copy the .env.example file and create a .env file.
e.g.

```
cp .env.example .env
```

Add your database properties here:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=__REQUIRED__
DB_USERNAME=__REQUIRED__
DB_PASSWORD=__REQUIRED__
```

You will also need to add the Mailchimp and Mail server details before the app will work correctly. Please ask an admin to supply them. These settings are:

```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mandrillapp.com
MAIL_PORT=587
MAIL_USERNAME=__REQUIRED__
MAIL_PASSWORD=__REQUIRED__
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=no-reply@tuvens.com
MAIL_FROM_NAME=Tuvens

MAILCHIMP_APIKEY=__REQUIRED__
MAILCHIMP_LIST_ID=__REQUIRED__
```

## App setup

Now enter the following commands in your shell

```
composer install

php artisan key:generate

php artisan migrate

php artisan db:seed

```

## Passport / OAuth Setup

For OAuth to work, you will need to generate keys using the following commands. These provide the website frontend with its own keys to access the API. 

```
php artisan passport:keys
php artisan passport:client --personal

# Add the generated client id and secret to you .env file

PASSPORT_CLIENT_ID=X
PASSPORT_SECRET=X
```

Finally, we will clear any cached config with the following command

```
php artisan config:clear
```

## Node / JS / Sass setup (Laravel Mix)

To compile the frontend, Laravel has a fluent wrapper for Webpack called Mix. You can find out more about Mix [here](https://laravel.com/docs/5.7/mix)

Firstly we will install the node components

```
npm install
````

Next we will build for development (this will run Laravel Mix) and start watch

```
npm run hot
```

Also u can use https://github.com/facebook/react-devtools, just install Chrome Extension


# Running

Laravel comes with a built in web server. Type the following command to start it

Note that Laravel logs errors to storage/logs

```
php artisan serve
```


# Running tests

Create .env.testing with your testing configuration (DB) than run:

```
php artisan config:cache --env=testing
```

Once this done, you should be able to run the tests using:

```
./vendor/bin/phpunit
```

# Some tips

* keep APIs RESTful as much as possible (for example user login = create session, user signup = create registration, logout = delete session)
* keep controllers as thin as possible, logic must be incapsulated into services (we'll need to better organize them)
* use Dependency injection, don't instantiate services 
* use request validation (https://laravel.com/docs/5.6/validation#form-request-validation). If validation failed, app will respond with
```
{
    "success": false,
    "errors": {
        "field1": [ "message1", "message2" ],
        "field2": [ "message3", "message4" ],
        ...
}
```
* write Swagger documentation for controller API methods. We'll use it for API docs generation. Please refer to Controllers examples in this project and the following links:

    * https://medium.com/@mahbubkabir/discovering-swagger-in-laravel-rest-apis-cb0271c8f2
    * https://swagger.io/docs/specification/
    
    Docs can be generated with
    
    ```
    php artisan l5-swagger:generate
    ```
    
    Than it'll be available under `/api/documentation` URL

