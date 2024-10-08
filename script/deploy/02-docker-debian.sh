#!/bin/bash
apt-get remove docker docker-engine docker.io containerd runc

apt-get update

apt-get install ca-certificates curl gnupg lsb-release apt-transport-https

mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

apt-get update
apt-get install docker-ce docker-ce-cli containerd.io

\cp ./config/docker/daemon.json /etc/docker/

systemctl start docker
systemctl enable docker
docker run --rm hello-world

#curl -SL https://github.com/docker/compose/releases/download/v2.12.2/docker-compose-linux-x86_64 -o /usr/bin/docker-compose
#chmod +x /usr/bin/docker-compose