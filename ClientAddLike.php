<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>untitled</title>
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <meta name="generator" content="Geany 0.19.1" />
</head>

<body>
  <?php
  if (!isset($_POST['username'])) {
    echo "<form method=post action=>";
    echo "username: <input type=text name=username><br>";
    echo "pass: <input type=text name=password><br>";
    echo "videoId: <input type=text name=videoid><br>";
    echo "<input type=submit name=submit>";
    echo "</form>";
  }

  if (isset($_POST['username'])) {
    require_once('Zend/Loader.php');
    Zend_Loader::loadClass('Zend_Gdata_YouTube');
    Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

    $authenticationURL = 'https://www.google.com/accounts/ClientLogin';
    $httpClient =
      Zend_Gdata_ClientLogin::getHttpClient(
        $username = $_POST['username'],
        $password = $_POST['password'],
        $service = 'youtube',
        $client = null,
        $source = '', // a short string identifying your application
        $loginToken = null,
        $loginCaptcha = null,
        $authenticationURL
      );

    $devkey = 'devkey';
    $yt = new Zend_Gdata_YouTube($httpClient, '', null, $devkey);
    $yt->setMajorProtocolVersion(2);

    $videoEntry = $yt->getVideoEntry($_POST['videoid']);
    $videoEntry->setVideoRating(5);
    $ratingUrl = $videoEntry->getVideoRatingsLink()->getHref();
    try {
      $ratedVideoEntry = $yt->insertEntry(
        $videoEntry,
        $ratingUrl,
        'Zend_Gdata_YouTube_VideoEntry'
      );
    } catch (Zend_Gdata_App_HttpException $httpException) {
      echo $httpException->getRawResponseBody();
    }
    echo "Done! <a href=http://www.youtube.com/watch?v=" . $_POST['videoid'] . ">video link</a>";
  }
  ?>
</body>

</html>