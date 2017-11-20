<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

# [START use_cloud_storage_tools]
use \google\appengine\api\cloud_storage\CloudStorageTools;
# [END use_cloud_storage_tools]
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function () use ($app) {
  return 'jembud';
});

/**
 * Read from the filesystem.
 * @see https://cloud.google.com/appengine/docs/php/googlestorage/#is_there_any_other_way_to_read_and_write_files
 */
$app->get('/hello.txt', function () use ($app) {
  $my_bucket = $app['bucket_name'];
  $fileContents = file_get_contents("gs://${my_bucket}/serve.txt");
  return $fileContents;
});


/**
 * Handle an uploaded file.
 * @see https://cloud.google.com/appengine/docs/php/googlestorage/user_upload#implementing_file_uploads
 */
$app->post('/upload/handler', function () use ($app) {
  $my_bucket = $app['bucket_name'];
  # [START move_uploaded_file]
  $file_name = $_FILES['uploaded_files']['name'];
  $temp_name = $_FILES['uploaded_files']['tmp_name'];
  move_uploaded_file($temp_name, "gs://${my_bucket}/${file_name}.txt");
  # [END move_uploaded_file]
  return sprintf('Your file "%s" has been uploaded.', $file_name);
});

/**
* Write to a Storage bucket.
* @see https://cloud.google.com/appengine/docs/php/googlestorage/#simple_file_write
*/
$app->get('/write', function (Request $request) use ($app) {
  $newFileContent = 'sepvag';
  $my_bucket = $app['bucket_name'];
  # [START write_simple]
  echo file_put_contents("gs://${my_bucket}/hello.txt", $newFileContent);
  return '';
  # [END write_simple]
  //return $app->redirect('/');
});

/**
* Serve a file from Storage and preserve the ACL.
* @see https://cloud.google.com/appengine/docs/php/googlestorage/public_access#serving_files_from_a_script
*/
$app->get('/serve', function () use ($app) {
  $my_bucket = $app['bucket_name'];
  echo CloudStorageTools::serve("gs://${my_bucket}/hello.txt");
  exit;
  return '';
});

$app->post('/uploadsx', function () use ($app) {
  $my_bucket = $app['bucket_name'];
  # [START move_uploaded_file]
  $file_name = $_FILES['uploaded_files']['name'];
  $temp_name = $_FILES['uploaded_files']['tmp_name'];
  move_uploaded_file($temp_name, "gs://${my_bucket}/${file_name}.txt");
  # [END move_uploaded_file]
  return sprintf('Your file "%s" has been uploaded.', $file_name);
});

$app->get('/serve/image', function () use ($app) {
  $my_bucket = $app['bucket_name'];
  if (!file_exists("gs://${my_bucket}/casa.jpg")) {
      copy(__DIR__ . '/casa.jpg', "gs://${my_bucket}/casa.jpg");
  }
  # [START image_serve]
  $options = ['size' => 400, 'crop' => true];
  $image_file = "gs://${my_bucket}/casa.jpg";
  $image_url = CloudStorageTools::getImageServingUrl($image_file, $options);
  # [END image_serve]
  return $app->redirect($image_url);
});

$app->get('/serve/e', function () use ($app) {
  $my_bucket = $app['bucket_name'];
  if (!file_exists("gs://${my_bucket}/casa.jpg")) {
      copy(__DIR__ . '/casa.jpg', "gs://${my_bucket}/casa.jpg");
  }
  # [START image_serve]
  $options = ['size' => 400, 'crop' => true];
  $image_file = "gs://${my_bucket}/casa.jpg";
  $image_url = CloudStorageTools::getImageServingUrl($image_file, $options);
	header('Content-Type: image/jpeg');
  echo file_get_contents($image_url);
  exit;
  return '';
  # [END image_serve]
  //return $app->redirect($image_url);
});

$app->post('/upload', function () use ($app) {
  if(!empty($_FILES['uploaded_file']))
  {
  		print_r($_FILES['uploaded_file']);

	  $my_bucket = $app['bucket_name'];
	  $path = "gs://${my_bucket}/";
	  $ext = pathinfo($_FILES['uploaded_file']['name'], PATHINFO_EXTENSION);
	  $fn = sha1($_FILES['uploaded_file']['name']).'.'.$ext;
    $path = $path . basename( $fn);

    if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
      echo "The file ".  basename( $fn). 
      " has been uploaded";
    } else{
        echo "There was an error uploading the file, please try again!";
    }
  }
  return '';
  
  });

$app->get('/upload', function () use ($app) {
	
	echo '<!DOCTYPE html>
<html>
<head>
  <title>Upload your files</title>
</head>
<body>
  <form enctype="multipart/form-data" method="POST">
    <p>Upload your file</p>
    <input type="file" name="uploaded_file"></input><br />
    <input type="submit" value="Upload"></input>
  </form>
</body>
</html>';
  return '';
});

return $app;
