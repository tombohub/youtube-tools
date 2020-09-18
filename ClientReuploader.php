<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>untitled</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 0.19.1" />
</head>

<body>
	<?php

	//input form
	if (!isset($_POST['username'])) {
		echo '<form method=post accept-charset=UTF-8 action=>
		  Video link 
			<input name=links type=text><br> 
		  Title of new video
		  <input name=title type=text/><br> 
      Description
		  <textarea name="description"></textarea><br> 
		  Tags (separated by coma)
		  <textarea name="tags"></textarea><br> 
		  Category
		  <select name="category"> 
			<option value="Tech" >Tech</option>
			<option value="Education" >Education</option>
			<option value="Animals" >Animals</option>
			<option value="People" >People</option>
			<option value="Travel" >Travel</option>
			<option value="Entertainment" >Entertainment</option>
			<option value="Howto" >Howto</option>
			<option value="Sports" >Sports</option>
			<option value="Autos" >Autos</option>
			<option value="Music" >Music</option>
			<option value="News" >News</option>
			<option value="Nonprofit" >Nonprofit</option>
			<option value="Comedy" >Comedy</option>
			<option value="Film" >Film</option>
			</select><br>
			user <input type=text name=username><br>
			pass <input type=text name=password><br>
			<input type="submit" name="submit"/>
			</form>';
	}

	//login after form
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

		//downloading
		$down_url = $_POST['links'];
		shell_exec("/usr/local/bin/youtube-dl -o video $down_url >> errors.txt");

		//uploading
		$title = str_replace('"', '\"', $_POST['title']);
		$description = str_replace('"', '\"', $_POST['description']);
		$tags = str_replace("    ", "", $_POST['tags']);
		$tags = str_replace("\n", ",", $tags);
		$category = $_POST['category'];
		$contentType = mime_content_type("video");

		$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
		$filesource = $yt->newMediaFileSource("video");
		$filesource->setContentType($contentType);
		$filesource->setSlug("video");
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

		$newEntry->setMajorProtocolVersion(2);
		$videoId = $newEntry->getVideoId();
		$vid_link = "http://www.youtube.com/watch?v=$videoId";
		echo "Video uploaded: <a href='$vid_link'>$vid_link</a>";
		shell_exec("rm video");
	}

	?>
</body>

</html>