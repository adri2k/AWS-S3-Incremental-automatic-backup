# AWS-S3-Incremental-automatic-backup

Create incremental backup on AWS S3 using native bucket versioning of AWS.

## Usage

Follow instruction for usage, please be careful with your backup data, do only what you actually understand !!

### S3 Bucket ###

* Create a new S3 bucket and enable versioning.
* Optional: you can also enable Management->Lifecycle for deleted object after xx days.
* Create IAM user with write/read to backup bucket.

### Backup

* Install AWS on your server:
http://docs.aws.amazon.com/cli/latest/userguide/installing.html

* Set cron for `awsbackup.sh` script (edit and customize it with your folder and bucket name).
```sh
/usr/local/bin/aws s3 sync /var/www/vhosts/ s3://bucketTest/vhosts/ --delete --exclude "*/var/*" --exclude "*/cache/*" --exclude "*/cache/*" --exclude "*/statistics/*" --exclude "*/includes/src/*" --exclude "*/logs/*"
```
Change **bucketTest** with your bucket name.

### Recover

#### Recover backup at last version

* Use AWS S3 sync command
```sh
aws s3 sync s3://bucketTest/vhosts/ /var/www/vhosts/
```

#### Recover backup at certain version

* Download `aws.phar` from http://docs.aws.amazon.com/aws-sdk-php/v3/download/aws.phar
* Edit file `awsRecover.php`
```php
$refTimestamp = 1452816000;  // Set recover version date - http://www.unixtimestamp.com/index.php
$bucket = 'bucketname';
$prefixpath = 'foder/subfolder';
$AWSKey = "xxxxx"; // Create Specific S3 bucket read only user
$AWSSecret = "xxxxx";
```
* Run `awsRecover.php`
```sh
php awsRecover.php
```
