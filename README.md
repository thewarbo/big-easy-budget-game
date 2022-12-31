First `cd app`

### install dependencies 
```
docker-compose run --rm app composer install --no-scripts
```
### set up configuration
```
cp .env.example .env
cp config/accounts.php.example config/accounts.php
docker-compose run app php artisan config:cache
```

### permissions issues
If operating on Linux, files share owner UID between host and container. Change
ownership of these files:
```
sudo chown 33:33 .env
sudo chown -R 33:33 storage
sudo chown -R 33:33 bootstrap
mkdir .docker/mongo/log
touch .docker/mongo/log/mongod.log
sudo chown -R 999:999 .docker/mongo
```
In general, `app` runs as 33(www-data) and `mongo` runs as 999(mongo). MacOS users
shouldn't need to do this.


### generate new app key 
```
docker-compose run --rm app php artisan key:generate
docker-compose run --rm app php artisan config:cache
```

### setup database
First start the mongodb server with `docker-compose up mongo`

Then you can run migrations to setup the database schema
```
docker-compose run --rm app php artisan migrate
```

#### optional
> If you are setting up the production site, ask one of the project leads to give you access to a mongodb dump
> so you can restore the database with a previous snapshot using `mongorestore`

```
docker-compose exec mongo mongorestore --username=admin --password=admin /tmp/neworleans/
```

> If you are operating locally, run
```
docker-compose run app php artisan db:seed
```
> to achieve the same effect. Then run
```
cp .docker/nginx/conf.d/develop.conf .docker/nginx/conf.d/app.conf
```
> to disable ssl.


### Start the app
```
docker-compose up -d
```

You should now be able to visit http://localhost/ and see the site load.
You can visit http://localhost/admin to force login/registration. If you 
receive a 403 after you do, see below to add the admin role to yourself.

### useful commands for development 
```
# to tail the app logs
docker-compose exec  app tail -f /var/www/storage/logs/laravel.log

# open a mongodb console
docker-compose exec mongo mongosh -u budgetgame -p budgetgamepass budgetgame_dev
```

Useful mongo commands to run in the console:
```
show dbs;
show collections;
db.users.find({"email": {$regex: /^myemail/}});
db.users.updateOne({"email": "myemail+test1@gmail.com"}, {$set: {roles: ["user", "admin"] }})
```

You can set the `APP_DEBUG` environment variable to see error messages in development (or change the `app/config/app.php` to set it to true by default).
