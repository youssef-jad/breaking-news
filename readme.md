How to setup the project?
- after cloning the project, change directory to the root of the project then run these commands in order:
1 - composer install
2 - npm install
3 - npm run dev

also, follow these steps
4 - change the name of .env.example file to .env
5 - add your google api key to the key named: GOOGLE_MAPS_KEY
6 - after naming the .env file, run: php artisan key:generate