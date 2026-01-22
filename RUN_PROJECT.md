# How to Run BadliCash Payment Gateway

This guide will help you get the project up and running quickly.

## Prerequisites

Before starting, ensure you have:
- **Docker & Docker Compose** (for Docker setup) OR
- **PHP 8.1+**, **Composer**, **MySQL 8.0**, and **Redis** (for manual setup)
- **Node.js & npm** (for frontend assets)

## Option 1: Using Docker (Recommended) 🐳

This is the easiest way to run the project.

### Step 1: Check Environment File
Make sure you have a `.env` file. If not, copy from the example:
```bash
cp env.example .env
```

### Step 2: Update .env Configuration
Edit `.env` and ensure these settings are correct:
```env
DB_DATABASE=badlicash
DB_USERNAME=badlicash
DB_PASSWORD=secret
BADLICASH_MODE=test
```

### Step 3: Build and Start Docker Containers
```bash
docker-compose up -d --build
```

This will start:
- **App**: Laravel application (PHP-FPM)
- **Nginx**: Web server on port 8000
- **MySQL**: Database on port 3306
- **Redis**: Cache/Queue on port 6379
- **Queue Worker**: Background job processor
- **Scheduler**: Cron job runner

### Step 4: Install PHP Dependencies
```bash
docker-compose exec app composer install
```

### Step 5: Generate Application Key
```bash
docker-compose exec app php artisan key:generate
```

### Step 6: Run Database Migrations and Seeders
```bash
docker-compose exec app php artisan migrate --seed
```

### Step 7: Install Frontend Dependencies (if needed)
```bash
npm install
```

### Step 8: Build Frontend Assets (if needed)
```bash
npm run build
```

### Step 9: Access the Application
- **Web Application**: http://localhost:8000
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

### Test Credentials
After seeding, you can login with:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@Badilicash.test | Password123! |
| Merchant 1 | merchant1@Badilicash.test | Password123! |
| Merchant 2 | merchant2@Badilicash.test | Password123! |

---

## Option 2: Manual Installation 💻

If you prefer not to use Docker, follow these steps:

### Step 1: Install PHP Dependencies
```bash
composer install
```

### Step 2: Configure Environment
```bash
cp env.example .env
php artisan key:generate
```

### Step 3: Update .env File
Edit `.env` and configure:
- Database connection (MySQL)
- Redis connection
- Application settings

Example:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=badlicash
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Step 4: Create Database
Create a MySQL database:
```sql
CREATE DATABASE badlicash;
```

### Step 5: Run Migrations and Seeders
```bash
php artisan migrate --seed
```

### Step 6: Install Frontend Dependencies
```bash
npm install
```

### Step 7: Build Frontend Assets
```bash
npm run build
```

### Step 8: Set Storage Permissions
```bash
# On Linux/Mac
chmod -R 775 storage bootstrap/cache

# On Windows (PowerShell as Admin)
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

### Step 9: Start the Application

You'll need **3 terminal windows**:

**Terminal 1 - Laravel Development Server:**
```bash
php artisan serve
```
This starts the app on http://localhost:8000

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work
```
This processes background jobs (webhooks, etc.)

**Terminal 3 - Scheduler (Optional):**
```bash
php artisan schedule:work
```
This runs scheduled tasks

### Step 10: Access the Application
Open your browser and go to: **http://localhost:8000**

---

## Quick Commands Reference

### Docker Commands
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php artisan [command]

# Rebuild containers
docker-compose up -d --build

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

### Artisan Commands
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reset database
php artisan migrate:fresh --seed

# Run tests
php artisan test

# Generate API keys (if needed)
php artisan tinker
# Then: ApiKey::generate($merchantId, 'test', 'read_write')
```

---

## Troubleshooting

### Port 8000 Already in Use
**Docker:**
```bash
docker-compose down
# Edit docker-compose.yml to change port mapping
docker-compose up -d
```

**Manual:**
```bash
php artisan serve --port=8001
```

### Database Connection Failed
- Check MySQL is running
- Verify credentials in `.env`
- Ensure database exists
- Check network connectivity

### Permission Errors (Linux/Mac)
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Queue Jobs Not Processing
- Ensure Redis is running
- Check queue worker is running: `php artisan queue:work`
- For Docker: Check `badlicash-queue-worker` container is running

### Frontend Assets Not Loading
```bash
npm install
npm run build
# Or for development:
npm run dev
```

---

## Next Steps

1. **Login** with test credentials
2. **Explore** the admin dashboard
3. **Create** a payment link as a merchant
4. **Test** payment processing
5. **Check** webhook delivery

---

## Development Mode

For active development with hot-reload:

**Frontend:**
```bash
npm run dev
```

**Backend:**
- Use `php artisan serve` (auto-reloads on file changes)
- Or use Docker with volume mounts (already configured)

---

## Production Deployment

For production deployment, see `docs/DEPLOYMENT.md` for:
- Environment configuration
- Security hardening
- Performance optimization
- Monitoring setup

---

**Need Help?** Check the main [README.md](README.md) for more details.
