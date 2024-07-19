#!/bin/bash

fdisk /dev/vdb

mkfs.ext4 /dev/vdb1
vim /etc/fstab
/dev/vdb1 /data ext4 defaults 0 0