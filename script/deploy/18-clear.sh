#!/bin/bash
projectName="projectName"

echo "0 1 * * * /data/${projectName}/dist/script/clear.sh" >> /var/spool/cron/root
chmod 600 /var/spool/cron/root
systemctl restart crond