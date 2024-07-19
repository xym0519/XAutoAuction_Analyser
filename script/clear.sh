#!/bin/bash
projectName=""
backuppath="/data/backup/history"

cd ${backuppath}

find . -name "db_${projectName}_*.tgz" -daystart -mtime +90 -exec rm -rf {} \;
find . -name "log_${projectName}_*.tgz" -daystart -mtime +90 -exec rm -rf {} \;