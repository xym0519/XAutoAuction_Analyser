#!/bin/bash
domain="domain"

yum install snapd
systemctl start snapd
sleep 5s

snap install core
snap refresh core
ln -s /var/lib/snapd/snap /snap
snap install --classic certbot
ln -s /snap/bin/certbot /usr/bin/certbot

certbot certonly --webroot -w /data/gateway/dist/ -d ${domain}

certbot certonly --manual --preferred-challenges dns -d *.psy.360cbs.com

systemctl list-timers