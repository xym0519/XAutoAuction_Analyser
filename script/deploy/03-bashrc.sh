#!/bin/bash
echo "alias l='ls -alh'" >> ~/.bashrc
echo "alias dockerps='docker ps -a --format \"table {{.ID}}\t{{.Names}}\t{{.Status}}\"'" >> ~/.bashrc
echo "alias arti='ps ax|grep artisan|grep -v grep'" >> ~/.bashrc
echo "alias lless='l | less'" >> ~/.bashrc
echo "alias dockerpsg='dockerps | grep'" >> ~/.bashrc

