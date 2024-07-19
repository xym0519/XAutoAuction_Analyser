#!/bin/bash
projectName="projectName"

wget http://docs-aliyun.cn-hangzhou.oss.aliyun-inc.com/assets/attach/50452/cn_zh/1504508602547/ossutil64?spm=5176.doc50452.2.3.UBvG2m -O /data/backup/toos/ossutil

chmod +x /data/backup/tools/ossutil

\cp config/ossutil/.ossutilconfig /root/