<html>

<body>


  <?php
  error_reporting(-1);
  set_time_limit(600);

  require_once('Zend/Loader.php');
  Zend_Loader::loadClass('Zend_Gdata_YouTube');
  Zend_Loader::loadClass('Zend_Gdata_AuthSub');

  $token = $_POST['token'];
  $httpClient = Zend_Gdata_AuthSub::getHttpClient($token);
  $devkey = 'devkey';
  $yt = new Zend_Gdata_YouTube($httpClient, 'reuploader', null, $devkey);



  //downloading
  $down_url = $_POST['links'];
  /* ovo samo za više linkova
  $down_urls = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $down_urls);
  $down_urls = explode("\n", $down_urls);
  $down_urls = array_filter($down_urls);
  if (count($down_urls) > 5) 
    exit("you have more than 5 videos");
  $count = 0;
  //foreach($down_urls as $down_url) {*/
  $filename = time();
  shell_exec("youtube-dl -o downloads/$filename $down_url >> errors.txt");
  //$count++; // }

  //uploading
  $title = str_replace('"', '\"', $_POST['title']);
  $description = str_replace('"', '\"', $_POST['description']);
  $tags = $_POST['tags'];
  $category = $_POST['category'];
  $contentType = mime_content_type("downloads/$filename");

  $myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
  $filesource = $yt->newMediaFileSource("downloads/$filename");
  $filesource->setContentType($contentType);
  $filesource->setSlug($filename);
  $myVideoEntry->setMediaSource($filesource);
  $myVideoEntry->setVideoTitle($title);
  $myVideoEntry->setVideoDescription($description);
  $myVideoEntry->setVideoCategory($category);
  $myVideoEntry->setVideoTags($tags);
  $uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
  try {
    $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
  } catch (Zend_Gdata_App_HttpException $httpException) {
    echo $httpException->getRawResponseBody();
  } catch (Zend_Gdata_App_Exception $e) {
    echo $e->getMessage();
  }

  $videoId = $newEntry->getVideoId();
  $vid_link = "http://www.youtube.com/watch?v=$videoId";
  echo "Video uploaded: <a href='$vid_link'>$vid_link</a>";
  //Zend_Gdata_AuthSub::AuthSubRevokeToken($token);

  shell_exec("rm downloads/$filename");

  ?>
</body>

</html>