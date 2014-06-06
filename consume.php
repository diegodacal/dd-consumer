<?php
	
/*function to return twitter content, given a TwitterID */
function consumeTwitter($userID, $screenName, $lastTweet){
	require_once('lib/TwitterAPIExchange.php');
	include "config.php";

	$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	$getfield = '?user_id=' . $userID . '&exclude_replies=false&include_rts=false';
	if ($lastTweet != ""){
		$getfield .= "&since_id=" . $lastTweet;
	}
	echo $getfield;
	$requestMethod = 'GET';
	$twitter = new TwitterAPIExchange($settings);

	$response = $twitter->setGetfield($getfield)
				 ->buildOauth($url, $requestMethod)
				 ->performRequest();  
				 
	$response = json_decode($response);
	$response = array_reverse($response);
	foreach ($response as $tweet){
		echo "userid: " . $userID . "<br>";
		echo "network: twitter <br>";
		echo "content: " . $tweet->text . "<br>" ;
		$date = str_replace("+0000 ", "+0200 ", $tweet->created_at);
		$date = date( 'Y-m-d H:i:s', strtotime($date));
		echo "date: " . $date . "<br>";
		$url = "http://www.twitter.com/" . $screenName . "/statuses/". $tweet->id_str;
		echo ($url . "<hr>");
		//saveContent($userID, "twitter", $tweet->text, $date, $url);
		//updateLast($userID, "twitter", $tweet->id_str);
	}
}

/*function to get data from Facebook, given a pageId */
function consumeFacebook($pageID, $pageName, $lastPost){
	include "config.php";
	$getfield = $pageID . "/posts?limit=5&fields=message,created_time,id,link";	
	if ($lastPost != ""){
		$getfield .= "&since=" . $lastPost;
	}
	$getfield .= "&access_token=" . $appID . "|" . $appSecret;
	echo $getfield;
	$json = json_decode(file_get_contents("https://graph.facebook.com/" . $getfield));
	$json = array_reverse($json->data);
	
	foreach($json as $post){
		if ($post->message && $post->link){
			echo "userid: " . $pageID . "<br>";
			echo "netword: facebook <br>";
			if ($post->message){ echo "content: " . $post->message . "<br>";} else {echo "fueiim <br>";} ;
			echo "date: " . $post->created_time . "--" . strtotime($post->created_time) . "<br>";
			echo "url: " . $post->link . "<hr>";
			//saveContent($pageID, "facebook", $post->message, $post->created_time, $post->link);
			//updateLast($pageID, "facebook", strtotime($post->created_time));
		}
	}
}
function updateLast($userID, $network, $lastcontent){
	include "db.php";
	
	$sql = "UPDATE profiles 
			SET lastcontent=" . $lastcontent .
			" WHERE userID='" . $userID . "' AND network='" . $network . "'";
	
	set_time_limit(600);
	if (!mysqli_query($dbhandle,$sql))
	  {
	  die('Error: ' . mysqli_error($dbhandle));
	  }
	mysqli_close($dbhandle);
}
/* function to save content on database specified on db.php */
function saveContent($profileID, $network, $content, $date, $permalink){
	include "db.php";
	$profileID = mysqli_real_escape_string($dbhandle, $profileID);
	$network = mysqli_real_escape_string($dbhandle, $network);
	$content = mysqli_real_escape_string($dbhandle, $content);
	$date = mysqli_real_escape_string($dbhandle, $date);
	$permalink = mysqli_real_escape_string($dbhandle, $permalink);
	
	//$ = mysqli_real_escape_string($dbhandle, $);

	$sql="INSERT INTO content (profileID, network, content, date, permalink)
		VALUES ('$profileID', '$network', '$content', '$date', '$permalink')";
	set_time_limit(600);
	if (!mysqli_query($dbhandle,$sql))
	  {
	  die('Error: ' . mysqli_error($dbhandle));
	  }
	mysqli_close($dbhandle);
}

include "db.php";
$result = mysqli_query($dbhandle,"SELECT * FROM profiles");

while($row = mysqli_fetch_array($result)){
	switch ($row['network']){
		case "twitter":
			consumeTwitter($row['userID'], $row['user'], $row['lastcontent']);
			break;		
		case "facebook":
			consumeFacebook($row['userID'], $row['user'], $row['lastcontent']);
			break;	
	}
}
echo "</table>";

mysqli_close($dbhandle);


//consumeTwitter("25620042", "diegodacal", "");
//consumeFacebook("105821366170914","Pt Brasil", "");
?>