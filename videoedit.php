<?php
require_once('Zend/Loader.php');
Zend_Loader::loadClass('Zend_Gdata_YouTube');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');

if (!isset($_COOKIE['token'])) {
	if (isset($_GET['token'])) {
		$token = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
		setcookie('token', $token, time() + 31536000, '/', 'fumusic.net');
		header("refresh: 1; url=http://www.fumusic.net/videoedit.php");
	}
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Video Edit</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 0.19.1" />
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-17727118-7']);
		_gaq.push(['_trackPageview']);
		(function() {
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();
	</script>

	<script type="text/javascript">
		function copyTitle() {
			var title = document.getElementById("title_0").value;
			var count = document.getElementById("edit").getElementsByTagName("textarea").length / 3;
			var i = 0;
			for (i = 0; i < count; i++) {
				var text = document.getElementById("title_" + i);
				text.value = title;
			}
		}

		function copyDesc() {
			var desc = document.getElementById("desc_0").value;
			var count = document.getElementById("edit").getElementsByTagName("textarea").length / 3;
			var i = 0;
			for (i = 0; i < count; i++) {
				var text = document.getElementById("desc_" + i);
				text.value = desc;
			}
		}

		function copyTags() {
			var tags = document.getElementById("tags_0").value;
			var count = document.getElementById("edit").getElementsByTagName('textarea').length / 3;
			var i = 0;
			for (i = 0; i < count; i++) {
				var text = document.getElementById("tags_" + i);
				text.value = tags;
			}
		}
	</script>
</head>

<body>
	<?php


	function getAuthSubRequestUrl()
	{
		$next = 'http://www.fumusic.net/videoedit.php'; //pazi ovde na link
		$scope = 'http://uploads.gdata.youtube.com/';
		$secure = false;
		$session = true;
		return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
	}
	$auth_url = getAuthSubRequestUrl();

	if (!isset($_COOKIE['token'])) {
		if (!isset($_GET['token'])) {
			echo "<p>This applications lets you edit Title, Description and Tags of your videos
          in easy way.<br> First, please authorize application on YouTube ";
			echo "<input type=button value=Authorize onclick=\"javascript:window.location='$auth_url'\"/></p><br>";
		}
	}

	//setting client
	if (isset($_COOKIE['token'])) {
		$token = $_COOKIE['token'];
		$httpClient = Zend_Gdata_AuthSub::getHttpClient($token);
		$devkey = 'devkey';
		$yt = new Zend_Gdata_YouTube($httpClient, 'reuploader', null, $devkey);
		$yt->setMajorProtocolVersion(2);

		//getting feeds of users uploads
		$loc = Zend_Gdata_YouTube::USER_URI . '/default/uploads';
		$videoFeed = $yt->getVideoFeed($loc);
	}

	//choose video form
	if (
		!isset($_POST['submit1']) && !isset($_POST['submit2'])
		&& isset($_COOKIE['token'])
	) {
		echo "<form id=choose method=post action=>";
		foreach ($videoFeed as $videoEntry) {
			$video_id = $videoEntry->getVideoId();
			echo "<p></p><input type=checkbox name=video_id[] value=" . $video_id . ">";
			echo $videoEntry->getVideoTitle() . " </p>";
		}
		echo "<input type=submit name=submit1 value=Submit>";
		echo "</form>";
	}

	//if choosen, edit videos form
	if (isset($_POST['submit1'])) {
		echo "<form id=edit method=post accept-charset=UTF-8 action=>";
		for ($i = 0; $i < count($_POST['video_id']); $i++) {
			$video_id = $_POST['video_id'][$i];
			$videoEntry = $yt->getFullVideoEntry($video_id);
			echo "<input type=hidden name=video_id[$i] value=$video_id>";
			echo "<p>Title<br>
		      <textarea rows=1 cols=40 id=title_$i name=title[$i]>"
				. $videoEntry->getVideoTitle() .
				"</textarea>";
			if ($i == 0)
				echo "<input type=button value=Copy onClick=copyTitle()>";
			echo "<p>Description<br>
		      <textarea rows=4 cols=40 id=desc_$i name=description[$i]>"
				. $videoEntry->getVideoDescription() .
				"</textarea>";
			if ($i == 0)
				echo "<input type=button value=Copy onClick=copyDesc()>";
			echo "<p>Tags (separated by coma)<br>
		      <textarea rows=2 cols=40 id=tags_$i name=tags[$i]>"
				. implode(", ", $videoEntry->getVideoTags()) .
				"</textarea>";
			if ($i == 0)
				echo "<input type=button value=Copy onClick=copyTags()>";
			echo "<hr>";
		}
		echo "<input type=submit name=submit2 value=Submit></form>";
	}

	//if video edited then submit
	if (isset($_POST['submit2'])) {
		for ($i = 0; $i < count($_POST['video_id']); $i++) {
			$video_id = $_POST['video_id'][$i];
			$videoEntry = $yt->getFullVideoEntry($video_id);
			$put_url = $videoEntry->getEditLink()->getHref();
			if ($_POST['title'][$i] !== $videoEntry->getVideoTitle())
				$videoEntry->setVideoTitle($_POST['title'][$i]);
			if ($_POST['description'][$i] !== $videoEntry->getVideoDescription())
				$videoEntry->setVideoDescription($_POST['description'][$i]);
			if ($_POST['tags'][$i] !== implode(', ', $videoEntry->getVideoTags()))
				$videoEntry->setVideoTags($_POST['tags'][$i]);
			if (
				$_POST['title'][$i] !== $videoEntry->getVideoTitle()
				|| $_POST['description'][$i] !== $videoEntry->getVideoDescription()
				|| $_POST['tags'][$i] !== $videoEntry->getVideoTags()
			)
				$yt->updateEntry($videoEntry, $put_url);
		}
		$profile = $yt->getUserProfile('default');
		$username = $profile->title->text;
		$user_link = "http://www.youtube.com/$username";
		echo "Updated! Check it out: <a href=$user_link target=_blank>$user_link</a>";
		echo "<p><a href=http://www.fumusic.net/videoedit.php>Edit more videos</a></p>";
	}





	?>
</body>

</html>