#!/bin/bash
containerName=""

docker exec -u 3000 -d ${containerName} sh -c "nohup php /data/www/artisan {command} >> /data/logs/{commandName}.log 2>&1"
