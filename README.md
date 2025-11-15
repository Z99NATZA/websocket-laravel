## ✨ installation (Quick Start)
```bash
git clone https://github.com/Z99NATZA/websocket-laravel.git

cd websocket-laravel

composer install

npm install
```
---

### ✨ Copy .env.example and rename to .env
.env (comment this) 
#### VITE_PUSHER_HOST="${PUSHER_HOST}"

---

## ✨ installation (Quick Start)
```bash
php artisan key:generate

php artisan install:broadcasting --pusher
```

---

### ⚙️ Pusher api key
```bash
https://pusher.com/
```

---

Would you like to install and build the Node dependencies required for broadcasting? (yes/no) [yes]

---

### ⚙️ setup
```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate
```

### 🤖 run
```bash
php artisan serve
npm run dev
```


### 📌 URL (default)
```bash
localhost:8000
```
