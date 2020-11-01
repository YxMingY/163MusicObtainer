<?php
namespace yxmingy\crawler;

class Scheduler
{
  private $active = true;
  protected $keyword = "";
  private $parser;
  private $url_manager;
  public function __construct(string $keyword)
  {
    $this->keyword = $keyword;
  }
  /*
  https://y.music.163.com/m/artist?id=780003

  https://music.163.com/song/media/outer/url?id=480426313.mp3

  <h2 id="artist-name" data-rid=780003 class="sname f-thide sname-max" title="Martin Garrix - 马丁·盖瑞斯">Martin Garrix</h2>
  */
  public function start()
  {
    $baseurl = "https://y.music.163.com/m/artist?id=".$this->keyword;
    echo PHP_EOL."正在获取曲目单... ".$baseurl.PHP_EOL;
    sleep(1);
    //Download artist page
    $page = $this->download($baseurl);
    //To sieve hot song's ID
    if(preg_match_all(
    	'/<li><a href="\/song\?id=([0-9]+)">([\s\S]+?)<\/a><\/li>/',
    	$page,
    	$match
	)>0) {
		  // id => song name
		  $ids = [];
		  for($i=0;$i<count($match[1]);$i++) {
		  	$ids[$match[1][$i]] = $match[2][$i];
	   	}
      echo "曲目单已生成：".PHP_EOL;
    }else{
      die("未知原因，获取失败");
    }
    $i = 1;
    //Get artist name
    preg_match('/<h2 id="artist-name"[\s\S]+?>([\s\S]+?)<\/h2>/',$page,$artist);
    $artist = $artist[1] ?? "未知";
    foreach ($ids as $id => $name) {
    	echo "[".($i++)."] $artist -- $name".PHP_EOL;
    }
    echo "开始下载".PHP_EOL;
    $max = $i-1;
    $i = 1;
    @mkdir($artist);
    //To download songs
    foreach ($ids as $id => $name) {
    	echo "正在下载[$name - $artist.mp3] (".($i++)."/".$max.")";
    	$dw = $this->getSSLPage("https://music.163.com/song/media/outer/url?id=".$id.".mp3");
    	if($dw === null || strlen($dw) < 1024*1024) {
    		echo "付费单曲/资源失效，下载失败".PHP_EOL;
    		continue;
    	}
    	file_put_contents($artist."/".$name." - ".$artist.".mp3", $dw);
    	echo "下载成功".PHP_EOL;
    }
  }
  private function download(string $url):?string
  {
    return 
      preg_match('/https/',$url)
      ?
      $this->getSSLPage($url)
      :
      $this->getPage($url);
  }
  private function getPage(string $url):string
  {
    $get = null;
    try{
      $get = file_get_contents($url);
    }catch(Exception $e) {
      
    }
    return $get;
  }
  private function getSSLPage(string $url):?string
  {
    $stream_opts = [
      "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      ]
    ];
    $result = file_get_contents($url,false, stream_context_create($stream_opts));
    return $result === false ? null : $result;
  }
}