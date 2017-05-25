#!/bin/sh
echo "`date` Start Backup S3" >> /var/log/s3backup.log
/usr/local/bin/aws s3 sync /var/www/vhosts/ s3://bucketTest/vhosts/ --delete --exclude "*/var/*" --exclude "*/cache/*" --exclude "*/cache/*" --exclude "*/statistics/*" --exclude "*/includes/src/*" --exclude "*/logs/*"
/usr/local/bin/aws s3 sync /backup/database/folder/ s3://bucketTest/database/ --delete
echo "`date` End Backup S3" >> /var/log/s3backup.log
