#!/bin/bash
projectName="projectName"

groupadd -g 3000 www
useradd -g www -u 3000 -M www

mkdir /data/${projectName}-service/dist -p
mkdir /data/${projectName}-service/logs -p
mkdir /data/${projectName}-service/tmp -p

mkdir /data/${projectName}-admin/dist -p
mkdir /data/${projectName}-admin/logs -p
mkdir /data/${projectName}-admin/tmp -p

mkdir /data/${projectName}-web/dist -p
mkdir /data/${projectName}-web/logs -p
mkdir /data/${projectName}-web/tmp -p

mkdir /data/${projectName}-weixin/dist -p
mkdir /data/${projectName}-weixin/logs -p
mkdir /data/${projectName}-weixin/tmp -p
