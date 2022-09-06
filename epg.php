
<?php
// The data for EPG NZ can be retrieve from:
//   https://web-epg.sky.co.nz/prod/epgs/v1?start=1660712878000&end=1660799278000&limit=20000 <https://web-epg.sky.co.nz/prod/epgs/v1?start=1660712878000&end=1660799278000&limit=20000>

//   channel descriptions
//   https://skywebconfig.msl-prod.skycloud.co.nz/sky/json/channels.prod.json
//   -- 
// https://web-epg.sky.co.nz/prod/epgs/v1?start=1662429600000&end=1662451200000&limit=2000
// https://www.ontvtonight.com/au/guide/listings/MelbourneNight.html

// // '--no-check-certificate' to avoid the errors with certificates
// $timenow = time()*1000;
// echo $timenow;

//default timezone
// $date = new DateTime(null);
// echo 'Default timezone: '.$date->getTimestamp().'<br />'."\r\n";

// //America/New_York
// $date = new DateTime(null, new DateTimeZone('America/New_York'));
// echo 'America/New_York: '.$date->getTimestamp().'<br />'."\r\n";

// //Europe/Amsterdam
// $date = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));
// echo 'Europe/Amsterdam: '.$date->getTimestamp().'<br />'."\r\n";

// echo 'WORK AROUND<br />'."\r\n";
// // WORK AROUND
// //default timezone
// $date = new DateTime(null);
// echo 'Default timezone: '.($date->getTimestamp() + $date->getOffset()).'<br />'."\r\n";

// //America/New_York
// $date = new DateTime(null, new DateTimeZone('America/New_York'));
// echo 'America/New_York: '.($date->getTimestamp() + $date->getOffset()).'<br />'."\r\n";

// //Europe/Amsterdam
// $date = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));
// echo 'Europe/Amsterdam: '.($date->getTimestamp() + $date->getOffset()).'<br />'."\r\n";

$nz = 'Pacific/Auckland';
date_default_timezone_set("Pacific/Auckland");
$date = new DateTime('today midnight', new DateTimeZone($nz));
// echo '<pre>';
// var_dump($date);
// echo '</pre>';

$night_twelve = $date->getTimestamp();
$night_twelve *= 1000;
$morning_six = $night_twelve + 21600000;
$afternoon_twelve = $morning_six + 21600000;
$evening_six = $afternoon_twelve + 21600000;
$another_twelve = $evening_six + 21600000;
// echo $evening_six.'<br/>'.$another_twelve;

$now = strtotime("now");
$now *= 1000;

if ($night_twelve < $now && $now < $morning_six) {
  $start = $night_twelve;
  $end = $morning_six;
}

if ($morning_six < $now && $now < $afternoon_twelve) {
  $start = $morning_six;
  $end = $afternoon_twelve;
}

if ($afternoon_twelve < $now && $now < $evening_six) {
  $start = $afternoon_twelve;
  $end = $evening_six;
}

if ($evening_six < $now && $now < $another_twelve) {
  $start = $evening_six;
  $end = $another_twelve;
}

//echo $timenow;
// $standard = 1661932800000; //6pm Melbourne
// $addedsixhours = $standard + 21600000;
// '2>&1' to check errors in linux   
$prog = "wget -O - --tries=1 --timeout=60 --no-check-certificate 'https://web-epg.sky.co.nz/prod/epgs/v1?start=$start&end=$end&limit=2000'";
//start=1661414400000&end=1661436000000&limit=2000
$RET_LINES = array();
$rc = 0;
exec($prog, $RET_LINES, $rc);

$DATA = implode("", $RET_LINES);
// echo '<pre>';
// var_dump($DATA);
// echo '</pre>';

file_put_contents('/opt/lampp/htdocs/' . "test.txt", $DATA);
// When set to true, the returned object will be converted into an associative array. 
// When set to false, it returns an object. False is default
$json_arr = json_decode($DATA, true);
$tv_shows = $json_arr['events'];

//var_dump($shows);
//exit;

// foreach ($shows as $show) {
//    if($show->channelNumber == 1){
//    echo 'Channel: 1 ';
//    echo 'Show: ' . $show->title . ' | ID: ' . $show->id . "<br />";
//    }
// }

//exec("mkdir -p 'New Zealand'"); // 2>&1

//command to change ownership of a file
//sudo chown -R developer:users /opt/lampp/htdocs/EPG
//file permissions to read write and execute
//$sudo chmod -R 777 /opt/lampp/htdocs/EPG


$ARR_ALL = array(); // result !!!

//$ONE_CHANNEL["channel_name"]=""; // we will fix it later
//$ONE_CHANNEL["channel_code"]=""; // we will fix it later
//$ONE_CHANNEL["shows_channel"]=array(); 


//$ONE_CHANNEL = [];


foreach ($tv_shows as $key => $value) {
  $group = $value['channelNumber'];
  if (!isset($ARR_ALL[$group])) $ARR_ALL[$group] = [];

  $ARR_ALL[$group][] = $value;
}

//All the values here in this variable $ARR_ALL
// assign the key of the array in sequence 
$ARR_ALL = array_values($ARR_ALL);

//-------------------- Get IMGs Beginning --------------------------------

$prog1 = "wget -O - --tries=1 --timeout=60 --no-check-certificate 'https://skywebconfig.msl-prod.skycloud.co.nz/sky/json/channels.prod.json'";

$RET_LINES1 = array();
$rc1 = 0;
exec($prog1, $RET_LINES1, $rc1);

$DATA1 = implode("", $RET_LINES1);

// When set to true, the returned object will be converted into an associative array. 
// When set to false, it returns an object. False is default
$json_arr1 = json_decode($DATA1, true);


$ARR_IMG = array();

foreach ($json_arr1 as $key => $value) {
  $channel_code = $value['number'];
  $channel_code = (int) $channel_code;
  //echo $value['logoThumbnail'].'<br>';
  //gives you something like this
  //https://static.sky.co.nz/sky/epglogos/TV1.png
  $ARR_IMG[$channel_code] = array();
  $ARR_IMG[$channel_code][] = $value['logoThumbnail'];
  $ARR_IMG[$channel_code][] = $value['name'];
}

// echo '<pre>';
// var_dump($ARR_IMG);
// echo '</pre>';
// exit; 

// exec("mkdir icons 2>/dev/null; ");
// exec("mkdir icons_ori 2>/dev/null; ");
// foreach($ARR_IMG as $name => $value) {
//   // echo $value.'<br>';
//   // echo $name.'<br>';
//   if(!is_file("icons_ori/$name.png")){
//     exec("wget --no-check-certificate --no-clobber --timeout=10 -O 'icons_ori/$name.png' '$value' || rm icons_ori/$name.png");
//     exec("convert 'icons_ori/$name.png' -resize 57x57! 'icons/$name.png' || rm icons/$name.png 2>&1");
//   }
// }


for ($R = 0; $R < count($ARR_ALL); $R++) {
  $ONE_CHANNEL = $ARR_ALL[$R];
  for ($S = 0; $S < count($ONE_CHANNEL); $S++) {
    $channel_code = $ONE_CHANNEL[$S]['channelNumber'];
    //echo $channel_code.'<br/>';
    //echo $ONE_CHANNEL[$S]['id'].'<br>';
    //$ONE_CHANNEL[$S]['id'] = "test";
    foreach ($ARR_IMG as $key => $value) {
      if ($channel_code == 123) {
        continue;
      }
      if ($channel_code == $key) {
        //echo $channel_code;
        //echo "$key".'<br/>';
        //echo $ARR_IMG[$key][1].'<br/>';
        $ONE_CHANNEL[$S]['id'] = $ARR_IMG[$key][0];
        $ONE_CHANNEL[$S]['name'] = $ARR_IMG[$key][1];
        //echo $ONE_CHANNEL[$S]['name'] . '<br/>';
      }
    }
  }
  //updating the values to the array
  $ARR_ALL[$R] = $ONE_CHANNEL;
}

// echo '<pre>';
// var_dump($ARR_ALL);
// echo '</pre>';
// exit;


//-------------------- Get IMGs End --------------------------------


//var_dump($ARR_ALL[0]);
$format = "html";
if ($format == "html") {

  $WRAPPER_BEG = <<<EOF
<html>
<head>

</head>
<body>

EOF;

  $OUT_STR = "";
  $OUT_STR .= "<table id='tbl_epg' cellspacing='0' cellpadding='0' >\n";
  for ($R = 0; $R < count($ARR_ALL); $R++) {

    $ONE_CHANNEL = $ARR_ALL[$R];
    $channel_name = $ONE_CHANNEL[0]['name'];
    $channel_code = $ONE_CHANNEL[0]['channelNumber'];
    if ($channel_code == 123) {
      continue;
    }
    //echo ($ONE_CHANNEL[0]['channelNumber']);
    $shows = $ONE_CHANNEL;

    $tr_class = "";
    if (($R % 2) == 0) $tr_class = "nor";
    if (($R % 2) == 1) $tr_class = "alt";

    $OUT_STR .= "<tr class='$tr_class' >\n";
    $OUT_STR .= "  <td class='td_channel'><img src='/epg/icons_nz/$channel_code.png' ><br /><span class'channel_name'>$channel_name</span></td>\n";
    for ($Q = 0; $Q < count($shows); $Q++) {
      $one_show = $shows[$Q];
      $one_show['start'] /= 1000;
      $one_show['end'] /= 1000;
      $tvtime = date('H:i', $one_show['start']);
      $tvshow = $one_show['title'];
      $colspan = 2; //$one_show["colspan"];
      $OUT_STR .= "<td class='td_show' colspan='$colspan'><span class='tvtime'>$tvtime</span><br><span class='tvshow'>$tvshow</span></td>\n";
    }
    $OUT_STR .= "</tr>\n";
  }
  $OUT_STR .= "</table>\n";

  $WRAPPER_END = <<<EOF
</body>
</html>

EOF;

  echo ("Creating file in EPG test.inj\n" . '<br>');
  file_put_contents("test.inj", $OUT_STR);
  echo ("Creating file in test.htm\n");
  file_put_contents("test.htm", $WRAPPER_BEG . $OUT_STR . $WRAPPER_END);
}

exit;

?>
