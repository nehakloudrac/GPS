
# GPS #

Notes on organization:

* `server/` - the symfony app, tests are under `tests/`, not `src/`
* `ui/` - angular interfaces users see after they have logged in
  * `apps/` - each "app" corresponds to tab in the UI
  * `features/` - angular modules that are used in multiple apps
* `public/` - assets used on the public facing pages... this area is a bit of a mess
* `tasks/` - gulp build tasks used for building the UI and managing assets
* `icons/` - raw custom icons used in some places in the UI
* `ansible` - roles & tasks for configuring the dev environment

## Initial setup ##

* `vagrant up && vagrant ssh`
* `cd /vagrant && npm install && bower install`
* `cd /vagrant/server && composer install`
* `cd /vagrant && gulp copy && gulp icons && build:public:dev && gulp build:apps:dev`

> `vagrant up` should trigger the npm/bower/composer installs automatically.  You should only need to run it manually if something fails. Vagrant will not trigger the UI builds automatically.

## UI & gulp ##

The gulpfile contains tasks to build all ui apps, as well as watch & rebuild specific apps.

* `gulp copy` - copy static assets to web
* `gulp icons` - assemble custom icons
* `gulp build:public:dev` - build assets used on public pages
* `gulp build:apps:dev` - build all ui apps
* `gulp build:app:dev:[dashboard|profile|account|admin|resources]` - build a specific ui app
* `gulp watch:app:[dashboard|profile|account|admin|resources]` - watch and rebuild a specific ui app when a when changes

> If you can't run gulp because of missing "coffee-script", then delete `node_modules/` and re-run `npm install`.  Repeat until it works.  Yeah, I know.

## Testing & fixtures ##

Once all project dependencies are installed, you should be able to run the server tests:

  cd /vagrant/server && bin/phpunit --exclude-group="search"

You can load some data fixtures thusly:

  gps gps:debug:fixtures persist

However, you will need to create a new user or do a password reset to get into one of the fixture accounts.

View the site at `http://192.168.13.37/`, but remember to build the UI w/ gulp first.
