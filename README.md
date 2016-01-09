## Project setup

These steps suggest that all dependencies like composer, nodejs, npm etc. are already installed, configured and working properly

1. Create project directory `$ mkdir projectdir && cd projectdir`
2. Install Laravel `$ composer create-project --prefer-dist laravel/laravel .`
3. Initialize GIT repo with `$ git init`
4. Set repo origin `$ git remote add origin git@bitbucket.org:egeshi/oweb-test.git`
5. Reset to current state `$ git fetch --all && git reset --hard origin/master`
8. Run composer update without scripts `$ composer update --no-scripts`
9. Run composer update again `$ composer update`
10. Update .env with actual database config values
10. Create database tables with `$ php artisan migrate`
11. Donwload in install NPM stuff `$ npm install`
11. Run `gulp` to generate CSS and browserify/copy JS