<?php
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>Video Disable Comments</title>
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <meta name="generator" content="Geany 0.19.1" />

</head>

<body>
  <?php
  require_once('Zend/Loader.php');
  Zend_Loader::loadClass('Zend_Gdata_YouTube');
  Zend_Loader::loadClass('Zend_Gdata_ClientLogin');


  //logiranje

  if (!isset($_POST['username'])) {
    echo "<form method=post action=>";
    echo "email: <input type=text name=username><br>";
    echo "pass: <input type=text name=password><br>";
    echo "<input type=submit name=submit>";
    echo "</form>";
  }

  if (isset($_POST['username'])) {
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];
  }

  $authenticationURL = 'https://www.google.com/accounts/ClientLogin';
  $httpClient =
    Zend_Gdata_ClientLogin::getHttpClient(
      $username = $_SESSION['username'],
      $password = $_SESSION['password'],
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

  //getting feeds of users uploads
  $loc = Zend_Gdata_YouTube::USER_URI . '/default/uploads?&max-results=50';
  $videoFeed = $yt->getVideoFeed($loc);


  //choose video form
  if (isset($_POST['username'])) {
    echo "<form id=choose method=post action=>";
    foreach ($videoFeed as $videoEntry) {
      $video_id = $videoEntry->getVideoId();
      echo "<p></p><input type=checkbox name=video_id[] checked=checked value=" . $video_id . ">";
      echo $videoEntry->getVideoTitle() . " ";
      echo $videoEntry->published . " - ";
      echo $videoEntry->getVideoViewCount();
    }
    echo "<input type=submit name=submit1 value=Submit>";
    echo "</form>";
  }

  //if video edited then submit
  if (isset($_POST['submit1'])) {
    for ($i = 0; $i < count($_POST['video_id']); $i++) {
      $video_id = $_POST['video_id'][$i];
      $videoEntry = $yt->getFullVideoEntry($video_id);
      $accessControlElement = new Zend_Gdata_App_Extension_Element(
        'yt:accessControl',
        'yt',
        'http://gdata.youtube.com/schemas/2007',
        ''
      );
      $accessControlElement->extensionAttributes = array(
        array(
          'namespaceUri' => '',
          'name' => 'action',
          'value' => 'comment'
        ),
        array(
          'namespaceUri' => '',
          'name' => 'permission',
          'value' => 'denied'
        )
      );
      $videoEntry->extensionElements = array($accessControlElement);
    }
    $profile = $yt->getUserProfile('default');
    $username = $profile->title->text;
    $user_link = "http://www.youtube.com/$username";
    echo "Updated! Check it out: <a href=$user_link target=_blank>$user_link</a>";
    echo "<p><a href=http://www.fumusic.net/videoedit.php>Edit more videos</a></p>";
    session_destroy();
  }





  ?>
</body>

</html>