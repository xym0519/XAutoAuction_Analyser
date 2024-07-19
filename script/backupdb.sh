#!/bin/bash
projectName=""
backuppath="/data/backup/history"
database=""
username=""
password=""
itableName=""
bucket=""

NOW="$(date +"%y%m%d%H%M%S")"
cd ${backuppath}
mkdir db_${NOW}
cd db_${NOW}

docker exec mysql mysqldump -h 127.0.0.1 -u ${username} -p${password} ${database} --tables ${table1} ${table2} --ignore-table=${itableName} > ${projectName}_${NOW}.sql

sleep 3s

cd ..
tar zcvf db_${projectName}_${NOW}.tgz db_${NOW}

/data/backup/ossutil64 cp db_${projectName}_${NOW}.tgz oss://${bucket}

rm -rf db_${NOW}
