#!/bin/bash
projectName=""
backuppath="/data/backup/history"
cidgateway=""
cidmysql=""
cidservice=""
cidadmin=""
cidweixin=""
cidminio=""
bucket=""

NOW="$(date +"%y%m%d%H%M%S")"
cd ${backuppath}
mkdir log_${NOW}
cd log_${NOW}

mv /data/${projectName}-service/dist/storage/logs/* .
logs文件夹怎么办

cp /var/lib/docker/containers/${cidgateway}/${cidgateway}-json.log .
>/var/lib/docker/containers/${cidgateway}/${cidgateway}-json.log

cp /var/lib/docker/containers/${cidmysql}/${cidmysql}-json.log .
>/var/lib/docker/containers/${cidmysql}/${cidmysql}-json.log

cp /var/lib/docker/containers/${cidservice}/${cidservice}-json.log .
>/var/lib/docker/containers/${cidservice}/${cidservice}-json.log

cp /var/lib/docker/containers/${cidadmin}/${cidadmin}-json.log .
>/var/lib/docker/containers/${cidadmin}/${cidadmin}-json.log

cp /var/lib/docker/containers/${cidweixin}/${cidweixin}-json.log .
>/var/lib/docker/containers/${cidweixin}/${cidweixin}-json.log

cp /var/lib/docker/containers/${cidminio}/${cidminio}-json.log .
>/var/lib/docker/containers/${cidminio}/${cidminio}-json.log

cd ..
tar zcvf log_${projectName}_${NOW}.tgz log_${NOW}

/data/backup/ossutil64 cp log_${projectName}_${NOW}.tgz oss://${bucket}

rm -rf log_${NOW}

