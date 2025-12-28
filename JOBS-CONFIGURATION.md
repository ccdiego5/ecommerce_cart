# Configuration Notes for Jobs & Scheduling

## Queue Configuration
# For Low Stock Notifications and other background jobs
# Use 'database' for simple setups, 'redis' for production
QUEUE_CONNECTION=database

## Mail Configuration
# For sending Low Stock Alerts and Daily Sales Reports
# Use 'log' for testing (emails saved to storage/logs/laravel.log)
# Use 'smtp' or other drivers for production
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@ecommerce-cart.com"
MAIL_FROM_NAME="${APP_NAME}"

## Timezone (for scheduled jobs)
APP_TIMEZONE=UTC

## Running Jobs & Scheduler

# To process queue jobs (Low Stock Notifications):
# php artisan queue:work

# To run scheduled tasks (Daily Sales Report):
# php artisan schedule:work
# OR add to crontab:
# * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

## Features

# Low Stock Notification:
# - Triggered automatically after checkout when product stock <= threshold
# - Sends email to admin user
# - Requires queue:work to be running

# Daily Sales Report:
# - Runs every evening at 11:30 PM
# - Sends email to admin user with sales statistics
# - Requires schedule:work or cron setup

