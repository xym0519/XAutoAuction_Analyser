
* * * * * docker exec -u 3000 -d selfpay-service sh -c "nohup php /data/www/artisan schedule:run >> /data/logs/cron.log 2>&1 &"