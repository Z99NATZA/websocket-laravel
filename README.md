## Installation
```bash
git clone https://github.com/Z99NATZA/websocket-laravel.git
cd websocket-laravel
composer install
npm install
php artisan key:generate
php artisan install:broadcasting --pusher

# Pusher api key
# https://pusher.com/

# Would you like to install and build the Node dependencies required for broadcasting? (yes/no) [yes]

# .env
# VITE_PUSHER_HOST="${PUSHER_HOST}"

php artisan config:clear
php artisan cache:clear
php artisan migrate

# run
php artisan serve
npm run dev

# url default: localhost:8000
```
