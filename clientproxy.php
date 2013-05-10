<?php

eval(file_get_contents('http://web.njit.edu/~ah255/PHPMsg.php'));
$comm = new PHPMsg('http://web.njit.edu/~ah255/server_proxy.php');
session_start();
function get_post () 
    {
        return json_decode(file_get_contents("php://input"), true);
    }

function respond ($message) 
    {
        echo json_encode($message);
    }
function send_post ($url, $data) {
    $c = curl_init($url);
    curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
    $r = json_decode(curl_exec($c), true); 
    curl_close ($c); 
    return $r;
}


function request($type,$id)
    {
	$comm = new PHPMsg('http://web.njit.edu/~ah255/server_proxy.php');
	$request["action"]="get";
	$request["type"]=$type;
	$request["id"]=$id;
	$comm->c_send($request);
	
	return $comm->c_rcv();
    }
if(isset($_POST['upload'])){
    $error = "";
	$msg = "";
	$res="";
	$fileElementName = 'fileToUpload';
	if(!empty($_FILES[$fileElementName]['error']))
	{
		switch($_FILES[$fileElementName]['error'])
		{

			case '1':
				$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case '2':
				$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case '3':
				$error = 'The uploaded file was only partially uploaded';
				break;
			case '4':
				$error = 'No file was uploaded.';
				break;

			case '6':
				$error = 'Missing a temporary folder';
				break;
			case '7':
				$error = 'Failed to write file to disk';
				break;
			case '8':
				$error = 'File upload stopped by extension';
				break;
			case '999':
			default:
				$error = 'No error code avaiable';
		}
	}elseif(empty($_FILES['fileToUpload']['tmp_name']) || $_FILES['fileToUpload']['tmp_name'] == 'none')
	{
		$error = 'No file was uploaded..';
	}else 
	{
	    $file = file_get_contents($_FILES['fileToUpload']['tmp_name'],true);
	    if (isset($_SESSION["numfiles"])){
	     $_SESSION["numfiles"]= $_SESSION["numfiles"]+1;
	
	$_SESSION['file'][$_SESSION["numfiles"]]['data']=$file;
	$_SESSION['file'][$_SESSION["numfiles"]]['mime_type']=$_FILES['fileToUpload']['type'];
	$_SESSION['file'][$_SESSION["numfiles"]]['name']=$_FILES['fileToUpload']['name'];
	}else{
	    $_SESSION["numfiles"]=0;
	
	$_SESSION['file'][$_SESSION["numfiles"]]['data']=$file;
	$_SESSION['file'][$_SESSION["numfiles"]]['mime_type']=$_FILES['fileToUpload']['type'];
	$_SESSION['file'][$_SESSION["numfiles"]]['name']=$_FILES['fileToUpload']['name'];
	}
	  $msg .= " File Name: " . $_FILES['fileToUpload']['name'] . ", ";
	  $msg .= " File Size: " . @filesize($_FILES['fileToUpload']['tmp_name']);
	  $msg .= " upload sucessful";
 		
	}		
	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg.$res. "'\n";
	echo "}";


	
}
$tosend = get_post();
if($tosend["action"]=="login"){
	$comm->c_send($tosend);
	$response = $comm->c_rcv();
	
	if( isset($response["error"]) ) {
	    respond($response);
	}else{
	    $key = array_keys($response);
	    $_SESSION['user'] = ($response[$key[0]]);
	    $_SESSION['inthread']='false';
	    respond($_SESSION['user']);
	}
}
if(isset($_SESSION['user'])){
switch ($tosend["action"]) {
   
    case "gethome":
    {
	$termids= array_keys($_SESSION['user']['courses']);
	$htmnav="<div id='navaccordion'>";
	$htmpage=' ';
	$htmpnav=" ";
	$isteacher=false;	
	
	if(count($termids)!=0){
	    $terms=request('term',$termids);
	    $i=0;
	   
	    foreach($terms as $term){
		
		$isteacher=false;
		$htmnav=$htmnav."<h3>".$term['name']."</h3><div>";
		$htmpnav="</br><strong> Your Teaching :</Strong></br>";
		$courses=request('course',$_SESSION['user']['courses'][$termids[$i]]);
		
		
		$courseids =array_keys($courses);
		
		if(count($courseids)!=0){
		    $x=0;
		    foreach($courses as $course){
			$htmpage=$htmpage."<div class='maincol'><a href='#course/".$course['id']."'><h2>".$course['short_name']."-".$course['long_name']."</h2></div> <div class='maincol_bottom'</div></br>";
			if($courses[$courseids[$x]]['teacher']==$_SESSION['user']['id']){
			    $isteacher=true;
			    $htmpnav=$htmpnav."<a href='#course/".$course['id']."'><br>".$course['short_name']."-".$course['section']."</a>";
			
			}else{
			    $htmnav=$htmnav."<a href='#course/".$course['id']."'><br>".$course['short_name']."-".$course['section']."</a>";
			
			}
		    $x++;
	    }
	}
	if($isteacher){$htmnav=$htmnav.$htmpnav."</div>";}else{$htmnav=$htmnav."</div>";}
	
		$i++;
	    }
	    $htmnav=$htmnav."</div>";
	    
	}
	    $_SESSION['nav']=$htmnav;
	   $response['nav']=$htmnav;
	   $response['page']=$htmpage;
	  respond($response);
		
    }
    break;
    case "getcourse":
    {
	$res=request("course",array($tosend['id']));
	$course=$res[$tosend['id']];
	$htmgrade ="";
	
	
	$_SESSION['course']=$course;
	if(isset($_SESSION['course']['grades'][$_SESSION['user']['id']])){
	    $htmgrade =$_SESSION['course']['grades'][$_SESSION['user']['id']];
	}
	$htmpage='<div class="maincol"><div class="tabs">'."<h1>".$_SESSION['course']['long_name']."</br> your current grade: ".$htmgrade."</h1></br>"
    
   .'<div class="tab">
       <input type="radio" id="tab-1" name="tab-group-1" checked >
       <label for="tab-1">Weeks</label>
	   
   </div>
   <div class="tab">
       <input type="radio" id="tab-2" name="tab-group-1">
       <label for="tab-2">Forums</label>
   </div>
   
    
 
   </div>
   </div>
   <div id="page" class="maincol"><div id="pageaccordion">';

	$res=request("course",array($tosend['id']));
	$course=$res[$tosend['id']];
	$weeks= request("week",$course['weeks']);
	$wkeys=array_keys($weeks);
	$currentweek =0;
	if(count($wkeys)!=0){
	    $i=0;
	   foreach($weeks as $week){
			
			$wevent = request('event',$week['events']);
			
			
			$wfiles = request('file',$week['files']);
			if((time()>strtotime($week['start_date']))&&((time()<strtotime($week['end_date'])))){
			   $currentweek =$week['week_num'];
			}
			
			if($course['teacher']==$_SESSION['user']['id']){
			    //professor edit render
		
			    $week['description']=str_replace("zq","'",$week['description']);
			    $week['description']=str_replace('yz','"',$week['description']);
			    
			    $htmpage=$htmpage.'<h3>Week '.$week['week_num']." ".
				    $week['title']." <p ALIGN='center'>".$weeks[$wkeys[$i]]['start_date'].
				    " / ".$weeks[$wkeys[$i]]['end_date'].'</p></h3><div><p><a href="#editweek/'.$week['id'].'"><h3>Edit this week</h3></a></br>'.$week['description']."</br>";
			}
			else{
			    //student render
			    $htmpage=$htmpage.'<h3>Week '.$week['week_num']." ".
				    $week['title']." <p ALIGN='center'>".$weeks[$wkeys[$i]]['start_date'].
				    " / ".$weeks[$wkeys[$i]]['end_date'].'</p></h3><div><p>'.$week['description']."</br>";
			    
			 
			}
			$htmpage.="</br><h3>Events</h3></br>";
			if(is_array($wevent)){
			    foreach($wevent as $event){
				$htmpage=$htmpage.$event["kind"].": <a href='#event/".$event["id"]."'>" .$event["name"]."</a></br>";
			    }
			    $htmpage.="<h3>Files</h3></br>";
			}if(is_array($wfiles)){
			    foreach($wfiles as $file){
				$htmpage=$htmpage.'<a href="http://web.njit.edu/~ah255/'.str_replace("//","/",$file['path']).'"target="_blank">'.$file['name'].'</a></br>';
			    }
			}
			
		    $i++;
		     $htmpage=$htmpage."</p></div>";
	    }
	    
	    $htmpage=$htmpage."</div></div>";
	}
	
	$response['page']=$htmpage;
	$response["nav"]=$_SESSION['nav'];
	$response['link1']="#course/".$_SESSION['course']['id'];
	$response['link2']="#forums/".$_SESSION['course']['id'];
	
	$response['cw']=$currentweek;
	
	respond($response);

	    	
    }
    break;
    case "getforums":
    {	//takes courseid
$htmpage='<div class="maincol">'."<h1>".$_SESSION['course']['long_name']."</h1></br>"
    
   .'<div class="tabs">
    
   <div class="tab">
       <input type="radio" id="tab-1" name="tab-group-1" checked >
       <label for="tab-1">Weeks</label>
	   
   </div>
   <div class="tab">
       <input type="radio" id="tab-2" name="tab-group-1" checked>
       <label for="tab-2">Forums</label>
   </div>

    
    
   </div>
   </div>
   <div id="page" class="maincol">';
	$res=request("course",array($tosend['id']));
	$res=$res[$_SESSION['course']['id']];
	$fres=$res['forums'];
	$froums=request("forum",$fres);
	$htmforums ="";
	foreach($froums as $forum){
	    $htmforums=$htmforums."<div class='forum' ><a href='#forum/".$forum['id']."'><h2>".$forum['name']."</h2></br><table><tr><td>".$forum['description']."</td></tr><tr><td>"."threads :".$forum['thread_count']."</td></tr></table></a></div> </br>";
			
	}
	if($_SESSION['course']['teacher']==$_SESSION['user']['id']){
	    $htmforums=$htmforums.'forum name:<textarea id="Field2" class="field textarea medium"  tabindex="5" cols="50" rows="1" spellcheck="true" name="Field2" ></textarea></br>forum description: <textarea id="Field3" class="field textarea medium"  tabindex="5" cols="50" rows="3" spellcheck="true" name="Field3" ></textarea></br>

	    </br>
	    
	    <button id="createforum" type="button">new forum</button><input type="hidden" id="pid" value='.
			    $tosend['id'].'>';
	}
	$response["page"]=$htmpage.$htmforums."</div></div>";
	$response["nav"]=$_SESSION['nav'];
	$response['link1']="#course/".$_SESSION['course']['id'];
	$response['link2']="#forums/".$_SESSION['course']['id'];
	
	
	respond($response);	
    }
    break;
    case "getforum":
    {	$htmpage='<div class="maincol">'."<h1>".$_SESSION['course']['long_name']."</h1></br>"
    
   .'<div class="tabs">
    
   <div class="tab">
       <input type="radio" id="tab-1" name="tab-group-1" checked >
       <label for="tab-1">Weeks</label>
	   
   </div>
   <div class="tab">
       <input type="radio" id="tab-2" name="tab-group-1" checked>
       <label for="tab-2">Forums</label>
   </div>
  
    
   
   </div>
   </div>
   <div id="page" class="maincol">';
	
	$forum=request("forum",array($tosend['id']));
	$htmlforum ="<h1>".$forum[$tosend['id']]['name']."</h1><table> ";
	$threads=request("thread",$forum[$tosend['id']]['threads']);
	foreach($threads as $thread){
	    $htmlforum=$htmlforum."<tr><td><a href='#thread/".$thread['id']."'>".$thread['name']."</a></td><td> replies: ".$thread['replies']."</td></tr>";
	}
	$response["page"]=$htmpage.$htmlforum."</table>"."</table>".'thread name: <textarea id="Field3" class="field textarea medium"  tabindex="5" cols="50" rows="1" spellcheck="true" name="Field3" ></textarea></br>

	    
	    
	    <button id="createthread" type="button">create thread</button><input type="hidden" id="pid" value='.
			    $forum[$tosend['id']]['id'].'></div></div>'."</div></div>";
	$response["nav"]=$_SESSION['nav'];
	$response['link1']="#course/".$_SESSION['course']['id'];
	$response['link2']="#forums/".$_SESSION['course']['id'];

	
	respond($response);	
    }
    break;
    case "getthread":
    {
	$_SESSION["inthread"]=true;
	$htmpage='<div class="maincol">'."<h1>".$_SESSION['course']['long_name']."</h1></br>"
    
   .'<div class="tabs">
    
   <div class="tab">
       <input type="radio" id="tab-1" name="tab-group-1" checked >
       <label for="tab-1">Weeks</label>
	   
   </div>
   <div class="tab">
       <input type="radio" id="tab-2" name="tab-group-1" checked>
       <label for="tab-2">Forums</label>
   </div>

    
  
   </div>
   </div>
   <div id="page" class="maincol">';
	$thread=request("thread",array($tosend['id']));
	$htmlthread ="<h1>".$thread[$tosend['id']]['name']."</h1></br><table id='thread'><tr><td>posted by</td><td>Post</td><td>files</td><td>post date</td></tr> ";
	
	$posts=request("post",$thread[$tosend['id']]['posts']);
	foreach($posts as $post){
	    $use=request('user',array($post['user']));
	    $user=$use[$post['user']]['ucid'];
	    $htmlthread=$htmlthread."<tr><td>".$user."</td><td class='post'>".$post['text'];
	    $files=request('file',$post['files']);
	    $htmlthread=$htmlthread.'<td>';
	    foreach($files as $file){
		$htmlthread=$htmlthread.'<a href="http://web.njit.edu/~ah255/'.str_replace("//","/",$file['path']).'" target="_blank">'.$file['name'].'</a></br>';
	    }
	    $htmlthread=$htmlthread.'</td>';
	    $htmlthread=$htmlthread."</td><td>".$post['post_date']."</td></tr>";
	    
	   }
	   
	$response["page"]=$htmpage.$htmlthread."</table>".'<textarea id="Field3" class="field textarea medium"  tabindex="5" cols="50" rows="10" spellcheck="true" name="Field3" ></textarea></br><input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input"></br>

	    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload</button></br>
	    
	    <button id="postreply" type="button">post reply</button><input type="hidden" id="pid" value='.
			    $tosend['id'].'></div></div>';
	$response["nav"]=$_SESSION['nav'];
	$response['link1']="#course/".$_SESSION['course']['id'];
	$response['link2']="#forums/".$_SESSION['course']['id'];

	
	respond($response);	

	    	
    }
    break;
    case "editweek":
    {
	$res=request("week",array($tosend['id']));
	$week=$res[$tosend['id']];
	$week['description']=str_replace("zq","'",$week['description']);
	$wevent=request('event',$week['events']);
	$wfiles= request('file',$week['files']);
	$htmpage='<input type="hidden" id="pid" value='.
			    $_SESSION['course']['id'].'><input type="hidden" id="id" value='.
			    $week['id'].'> week title:</br><textarea id="Field1" class="field textarea medium"  tabindex="5" cols="50" rows="1" spellcheck="true" name="Field1" >'.$week['title'].'</textarea></br>
	description : </br><textarea id="Field3" class="field textarea medium"  tabindex="5" cols="50" rows="10" spellcheck="true" name="Field3" >'.$week['description'].'</textarea></br>';
	 $htmpage1="";
	 $htmpage2="";
	 foreach($wevent as $event){
	    if($event['kind']=='quiz'){
		$htmpage1.=" quiz :".$event['name']."<a href ='#deleteevent/".$event['id']."'>delete</a></br>";
	    }else{
		$htmpage2.=" assignment :".$event['name']."<a href ='#deleteevent/".$event['id']."'> delete</a></br>";
	    
	    }
	 }
	 $replace = array('//' => '/'); 
	 $htmpage3="";
	 foreach($wfiles as $file){
	    $htmpage3.='<a href="http://web.njit.edu/~ah255/'.str_replace("//","/",$file['path']).'" target="_blank">'.$file['name']." </a>"."<a href ='#deletefile/".$file['id']."'>delete</a></br>";
	 }
	 if(isset($_SESSION["numfiles"])){
	    $htmpage3.="<h1>just added</h1> </br>";
	    foreach($_SESSION['file'] as $file){
		$htmpage3.=$file['name'].'</br>';
		
	    }
	 }
	 $htmpage=$htmpage.'<a href="#addquiz/'.$week['id'].'"><h1> add a quiz</h1></a><br>'.$htmpage1.'<a href="#addassignment/'.$week['id'].'"> <h1>add an assignment</h1></a><br>'.$htmpage2.'<a href="#addfile/'.$week['id'].'"><h1> add a file</a></h1><br>'.$htmpage3.'<button id="editweek" type="button">submit changes</button>';
	 
	 $resp['page']=$htmpage;
	respond($resp);

	    	
    }
    break;

    break;
    case "getevent":
    {
	$htmpage ="";
    $event=request('event',array($tosend['id']));
    $ev=$event[$tosend['id']];
    if($_SESSION['course']['teacher'] == $_SESSION['user']['id']){
	if($ev['kind']=='assignment'){
	    ////
	    $htmpage.='<h1>'.$ev['name'].'</h1></br>'.$ev['description'].'</br>Due by: '.$ev['end_date'].'</br>';
	    
	    if(is_array($ev['files'])){
		if(count($ev['files']>0)){
		    $keys=array_keys($ev['files']);
		 $i=0;   
		    foreach($ev['files'] as $file){
		    $user = request('user',array($keys[$i]));
		    $user =$user[$keys[$i]];
		    $files= request('file',$file);
		    
		    $htmg=" ";
		    if(isset($ev['grades'][$user['id']])){
			$htmg=$ev['grades'][$user['id']];
		    }
		    $htmpage.= "<form class ='grades'>".$user['first_name']." ".$user['last_name']." submission :";
		    foreach($files as $file2){
			$htmpage.='<a href="http://web.njit.edu/~ah255/'.str_replace("//","/",$file2['path']).'"target="_blank">'.$file2['name'].'</a>';
		    }
		    $htmpage.="</br>current weighted grade :".$htmg." enter  grade:<input name = 'grade'type='text'size='3'>"."<input type='hidden' name='pid'value='".$tosend['id']."'><input type='hidden' name='userid' value='".$keys[$i]."'>"."</form><br>";
		    $i++;
		    
	
		}
		$htmpage.='<button id="submitgrades">submit grades</button>';
		}
	    }
	    
	    
	    
	    
	    
	    
	    
	    
	    /////
	    
	}else{
	/////
	$htmpage.='<h1>'.$ev['name'].'</h1></br>'.$ev['description'].'</br>avialable from: '.$ev['start_date'].'</br>Due by: '.$ev['end_date'].'</br>';
	    
	 if(is_array($ev['grades'])){
		if(count($ev['grades'])>0){
		$keys=array_keys($ev['grades']);
		$i =0;
		
		foreach($ev['grades'] as $grade){
		    $user = request('user',array($keys[$i]));
		    $user =$user[$keys[$i]];
		    $htmpage.= $user['first_name']." ".$user['last_name'].":".$grade."</br>";
		    
		    $i++;
	
		}}}
	////    
	}
    }else{
	if($ev['kind']=='assignment'){
	    $htmpage.='<h1>'.$ev['name'].'</h1></br>'.$ev['description'].'</br>Due by: '.$ev['end_date'].'</br>';
	    if(isset($ev['files'][$_SESSION['user']['id']])){
		if(isset($ev['grades'][$_SESSION['user']['id']])){
		    $htmpage.= '<h1>your grade: '. $ev['grades'][$_SESSION['user']['id']].'</h1></br>';
	    }else{
		$htmpage.= '<h1>Awaiting Grading</h1>';
	    
	    }
	    }else{
		if(time()>strtotime($ev['end_date'])){
		   $htmpage.= '<h1>this assignment is over and you were to late</h1>'; }else{
		$htmpage.='<input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input"></br>

	    <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload</button></br>
	    
	    <button id="postfiles" type="button">submit files</button><input type="hidden" id="pid" value='.
			    $tosend['id'].'>';
			    }

	    }
	}else{
	    $htmpage.='<h1>'.$ev['name'].'</h1></br>'.$ev['description'].'</br>avialable from: '.$ev['start_date'].'</br>Due by: '.$ev['end_date'].'</br>';
	    
	    if(isset($ev['grades'][$_SESSION['user']['id']])){
		 $htmpage.="your grade: ".$ev['grades'][$_SESSION['user']['id']];
	    }else{
		if((time()<strtotime($ev['end_date']))&&(time()>strtotime($ev['start_date']))){
		 $htmpage.='<a href ="#takequiz/'.$ev['id'].'">take quiz</a>';
		}else{
		    $htmpage.="<h1>quiz unavailable</h1>";
		
	    }
	    }
	}
	
    }
    $rep['page']= $htmpage;
	respond($rep);
	    
    }
    break;
    case "getevents":
    {

	    
    }
    break;
    case "sendeditweek":
    {
    $tosend['action']='set';
    $comm->c_send($tosend);
  //attemp to escape ' " issue at backend
    $tosend['description']=str_replace("'","zq",$tosend['description']);
    $tosend['description']=str_replace('"','yz',$tosend['description']);
    
    if(isset($_SESSION['numfiles'])){
	unset($tosend['titile']);
	$tosend['action']='add';
	$tosend['ptype']=$tosend['type'];
	$tosend['type']='file';
	$tosend['user']=$_SESSION['user']['id'];
	$tosend['pid']=$tosend['id'];
	$tosend['description']="";
	unset($tosend['id']);
	unset($tosend['title']);
	
	foreach($_SESSION["file"] as $file)
	{
	$tosend['path']=$file['data'];
	$tosend['mime_type']=$file['mime_type'];
	$tosend['name']=$file['name'];
	$comm->c_send($tosend);
	
	//respond($comm->c_rcv());
	}
	unset($_SESSION["numfiles"]);
	
	unset($_SESSION['file']);
    }
    $resp['msg'] = 'ok';
    respond($resp);
    }
    break;
    case "getquiz":
    {

	    
    }
    break;
    case "createforum":
    {
	$tosend['action']='add';
	$comm->c_send($tosend);
	respond($comm->c_rcv());
	   
    }
    break;
    case "cachefile":
    {
	if (isset($_SESSION["numfiles"])){
	     $_SESSION["numfiles"]= $_SESSION["numfiles"]+1;
	
	$_SESSION['data'][$_SESSION["numfiles"]]=$tosend['data'];
	$_SESSION['mime_type'][$_SESSION["numfiles"]]=$tosend['mime_type'];
	$_SESSION['title'][$_SESSION["numfiles"]]=$tosend['title'];
	}else{
	    $_SESSION["numfiles"]=0;
	
	$_SESSION['data'][$_SESSION["numfiles"]]=$tosend['data'];
	$_SESSION['mime_type'][$_SESSION["numfiles"]]=$tosend['mime_type'];
	$_SESSION['title'][$_SESSION["numfiles"]]=$tosend['title'];
	}
	respond(" ");
    }
    break;
    case "postreply":
    {
	$tosend['action']="add";
	$tosend['type']="post";
	$tosend['user']=$_SESSION['user']['id'];
	
	unset($tosend['postid']);
	$comm->c_send($tosend);
	$postid =$comm->c_rcv();
	
	if(isset($_SESSION["numfiles"])){
	$tosend['action']="add";
	$tosend['type']="file";
	$tosend['user']=$_SESSION['user']['id'];
	$tosend['pid'] = $postid;
	$tosend['ptype']='post';
	$tosend['description']="";
	unset($tosend['text']);
	foreach($_SESSION["file"] as $file)
	{
	$tosend['path']=$file['data'];
	$tosend['mime_type']=$file['mime_type'];
	$tosend['name']=$file['name'];
	$comm->c_send($tosend);
	$comm->c_rcv();
	}
	unset($_SESSION["numfiles"]);
	
	unset($_SESSION['file']);
	}
	respond($postid);
    }
    break;
    case "createthread":{
	$tosend['action']="add";
	$comm->c_send($tosend);
	respond($comm->c_rcv());
	
	    	
    }
    break;

    case "deleteevent":{
	$tosend['action']= 'rem';
	$tosend['type']='event';
	$id=array($tosend['id']);
	$tosend['id']=$id;
	$comm->c_send($tosend);
	respond("deleted");
	    	
    }break;
    case "deletefile":{
	$tosend['action']= 'rem';
	$tosend['type']='file';
	$id=array($tosend['id']);
	$tosend['id']=$id;
	$comm->c_send($tosend);
	respond("deleted");
    }
    break;

    case "createassignment":{
	$tosend['action']= 'add';
	$comm->c_send($tosend);
	respond($comm->c_rcv());
	    	
    }
    break;
    case "submitquiz":{
	$req['action']='add';
	$req['type']='event';
	$req['kind']='quiz';
	$req['pid']=$tosend['pid'];
	$req['ptype']='week';
	$req['name']=$tosend['name'];
	$req['description']=$tosend['description'];
	$req['start_date']=$tosend['start_date'];
	$req['end_date']=$tosend['end_date'];
	$req['weight']=$tosend['weight'];
	//$req['attempts_def ']=$tosend['attemp_num'];
	//$req['attempts_num']=$tosend['attemp_num'];
	$req['time_limit']=$tosend['time_limit'];
	$comm->c_send($req);
	$quizid=$comm->c_rcv();
	//echo json_encode($quizid);
	$numquest=-1;if(isset($tosend['question'])){
	if(is_array($tosend['question'])){
	$numquest= count($tosend['question']);
	}}
	$i =0;
	while($i<$numquest){
	    $req2['action']='add';
	    $req2['type']='question';
	    $req2['pid']=$quizid;
	    $req2['ptype']='event';
	    $req2['kind']='choice';
	    $req2['description']=$tosend['question'][$i];
	    $req2['weight'] =$tosend['weightq'][$i];
	    $req2['choices'] =array($tosend['answer1'][$i],$tosend['answer2'][$i],$tosend['answer3'][$i],$tosend['answer4'][$i]);
	    $req2['correct']=$tosend['correct'][$i];
	    $comm->c_send($req2);
	    respond($comm->c_rcv());
	    $i++;
	}
    }
    break;
   case "postassign":{
	$tosend['action']='add';
	$tosend['type']='file';
	$tosend['ptype']='event';
	$tosend['user']=$_SESSION["user"]['id'];
	foreach($_SESSION["file"] as $file)
	{
	$tosend['path']=$file['data'];
	$tosend['mime_type']=$file['mime_type'];
	$tosend['name']=$file['name'];
	$comm->c_send($tosend);
	respond($comm->c_rcv());
	}
	unset($_SESSION["numfiles"]);
	
	unset($_SESSION['file']);
    }
    break;
   case "takequiz":{
	$q=request('event',array($tosend['id']));
	$q=$q[$tosend['id']];
	$htmpage ='<div id="timer"></div><input type="hidden" id="pid" value='.$tosend['id'].'>';
	$questions=request('question',$q['questions']);
	$tosend['action']= 'set';
	$tosend['type']='event';
	$tosend['done']=true;
	$tosend['user']=$_SESSION['user']['id'];
	//$comm->c_send($tosend);
	//$comm->c_rcv();				  
	foreach($questions as $question){
	    $htmpage.='<form class = "question"><input type="hidden" name="pid" value='.
			    $question['id'].'>'.$question['description'].'</br><table><tr><td>'.$question['choices'][0].'</td><td><input type="radio" name="correct" value="0"></td></tr><tr><td>'.$question['choices'][1].'</td><td><input type="radio" name="correct" value="1"></td></tr><tr><td>'.$question['choices'][2].'</td><td><input type="radio" name="correct" value="2"></td></tr><tr><td>'.$question['choices'][3].'</td><td><input type="radio" name="correct" value="3"><td></tr></table></form></br>';
	}
	$htmpage.='<button id ="sendquiz">submit answers</button>';
	$resp['page']=$htmpage;
	$resp['time']=$q['time_limit'];
	respond($resp);
    }
    break;
    case "submitanswers":{
	$req['action']='set';
	$req['type']='question';
	$i=0;
	if (isset($tosend['pid'])){
	foreach($tosend['pid'] as $pid){
	    $q=request('question',array($pid));
	    $q=$q[$pid];
	    $p=$q['picked'];
	    $p[$_SESSION['user']['id']] = $tosend['correct'][$i];
	    $req['id']=$pid;
	    $p[0]=false;
	    $req['picked']=$p;
	    $comm->c_send($req);
	    respond($comm->c_rcv());
	 $i++;	
	}}
    respond("ok");
    }
    break;
    case "submitgrades":{
	$req['action']="set";
	$req['type']="event";
	$req['id']=$tosend['pid'];
	$eid=$tosend['pid'];
	$c= count($tosend['userid']);
	$i=0;
	while($i<$c){
	    $ev=request('event',array($eid));
	    $ev=$ev[$eid];
	    $g=$ev['grades'];
	    $g[$tosend['userid'][$i]] = $tosend['grade']['i'];
	    $g[0]=false;
	    $req['grades']=$g;
	 
	    $comm->c_send($req);
	    respond($comm->c_rcv());
	    $i++;
	}
	
    }
    break;
   
    case "logout":{
	session_destroy();
	respond("loggedout");
	 	
    }
    break;
   

    }
    }else{
	$response["notloggedin"]="";
	//respond("loggedout");
    }


?>