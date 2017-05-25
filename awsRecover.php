<?php
require 'aws.phar'; // http://docs.aws.amazon.com/aws-sdk-php/v3/download/aws.phar -> php > 5.6

//Start Settings
$refTimestamp = 1452816000;  // Set recover version date - http://www.unixtimestamp.com/index.php
$bucket = 'bucketname';
$prefixpath = 'foder/subfolder';
$AWSKey = "xxxxx"; // Create Specific S3 bucket read only user
$AWSSecret = "xxxxx";
//End Settings Settings


$s3 = new Aws\S3\S3Client([
  'version' => 'latest',
  'region' => 'eu-central-1',
  'credentials' => [
          'key' => $AWSKey,
          'secret' => $AWSSecret,
      ],
]);
/* // Little Snippet for testing purpouse
$result = $s3->listBuckets();
foreach ($result['Buckets'] as $bucket) {
    echo $bucket['Name'] . "\n";
}
*/
$results = $s3->getPaginator('ListObjectVersions', ['Bucket' => $bucket, 'Prefix' => $prefixpath]);
$count = 0;
$objs3 = array();
$folder = array();
$deleteMarker = array();
foreach ($results as $result) {
    echo ' Block of 1000 - ';
    echo ' Versions: '.count($result['Versions']).' ';
    foreach ($result['Versions'] as $object) {
        $timestamp = strtotime($object['LastModified']->__toString());
        if ($object['Size'] == 0) {
            $folder[$object['Key']] = array('VersionId' => $object['VersionId'], 'timestamp' => $timestamp);
        } else {
            if ($timestamp <= $refTimestamp) {
                if (array_key_exists($object['Key'], $objs3)) {
                    $timeStampGiaPresente = $objs3[$object['Key']]['timestamp'];
                } else {
                    $timeStampGiaPresente = 1;
                }
                if ($timestamp > $timeStampGiaPresente) {
                    $objs3[$object['Key']] = array('VersionId' => $object['VersionId'], 'timestamp' => $timestamp);
                    echo '.';
                }
            }
        }
    }
    echo ' Delete Marker: '.count($result['DeleteMarkers']).' ';
    if (count($result['DeleteMarkers']) > 0) {
        foreach ($result['DeleteMarkers'] as $object) {
            $timestamp = strtotime($object['LastModified']->__toString());
            if ($timestamp <= $refTimestamp) {
                if (array_key_exists($object['Key'], $deleteMarker)) {
                    $timeStampGiaPresente = $deleteMarker[$object['Key']]['timestamp'];
                } else {
                    $timeStampGiaPresente = 1;
                }
                if ($timestamp > $timeStampGiaPresente) {
                    $deleteMarker[$object['Key']] = array('VersionId' => $object['VersionId'], 'timestamp' => $timestamp);
                    echo '.';
                }
            }
        }
    }
    ++$count;
}
echo "Total Pages: $count\n";
$toDownload = $objs3;
foreach ($deleteMarker as $key => $value) {
    if ($value['timestamp'] <= $refTimestamp) {
        if (array_key_exists($key, $toDownload)) {
            if ($value['timestamp'] >  $toDownload[$key]['timestamp']) {
                unset($toDownload[$key]);
            }
        }
    }
}
echo 'Download '.count($toDownload)." files...\n";
$cnt = 0;
foreach ($toDownload as $obk => $obv) {
    createFile($obk, $obv['VersionId'], $s3, $bucket);
    ++$cnt;
    echo '.';
    if ($cnt % 100 == 0) {
        echo " $cnt ";
    }
}
echo 'End Procedure.';
function createFile($ffaws, $versionId, $s3, $bucket)
{
    $prefix = 'dwn';
    $ff = $prefix.'/'.$ffaws;
    $diname = dirname($ff);
    if ($diname == '.') { //Nothing to Do
    } else {
        make_path($ff);
    }
    $handle = fopen($ff, 'w+');
    fclose($handle);
    $result = $s3->getObject(array('Bucket' => $bucket, 'Key' => $ffaws, 'VersionId' => $versionId, 'SaveAs' => $ff));
}
function make_path($path)
{
    $dir = pathinfo($path, PATHINFO_DIRNAME);
    if (is_dir($dir)) {
        return true;
    } else {
        if (make_path($dir)) {
            if (mkdir($dir)) {
                chmod($dir, 0777);
                return true;
            }
        }
    }
    return false;
}
