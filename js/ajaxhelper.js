   var timeleft=0;
   $.fn.serializeObject = function()
    {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
    };
function editweek() {
   var data= {"action":"sendeditweek","type":"week","id":$("#id").val(),"pid":$("#pid").val(),"ptype":"course","title":$("#Field1").val(),"description":$("#Field3").val().replace(/'/g,"zq") };
    ajaxrequest(data);
 
}
function sendanswers() {
   var b=$('.question').serializeObject();
  
	var d={action:"submitanswers"};
	 var datab =$.extend({}, b, d)
      ajaxrequest(datab);
 
}
function submitgrades() {
   var b=$('.grades').serializeObject();
   var d={action:"submitgrades"};
    var datab =$.extend({}, b, d)
    ajaxrequest(datab);

 
}
function postreply() {
   var data= {"action":"postreply","type":"post","pid":$("#pid").val(),"ptype":"thread","text":$("#Field3").val(),"postid":$("#postid").val()};
    ajaxrequest(data);
 
}
function postassign(){
 var dtat ={"action":"postassign","pid":$("#pid").val()};
 ajaxrequest(dtat);
}
function createthread() {
   var data= {"action":"createthread","type":"thread","pid":$("#pid").val(),"ptype":"forum","name":$("#Field3").val()};
    ajaxrequest(data);
 
}
function createforum() {
   var data= {"action":"createforum","type":"forum","pid":$("#pid").val(),"ptype":"course","name":$("#Field2").val(),"description":$("#Field3").val()};
    ajaxrequest(data);
 
}
function get_XmlHttp() {
  // create the variable that will contain the instance of the XMLHttpRequest object (initially with null value)
  var xmlHttp = null;

  if(window.XMLHttpRequest) {		// for Forefox, IE7+, Opera, Safari, ...
    xmlHttp = new XMLHttpRequest();
  }
  else if(window.ActiveXObject) {	// for Internet Explorer 5 or 6
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  }

  return xmlHttp;
}


function ajaxrequest(data){
  switch (data.action) {
    case "gethome":
      {
	$("#maincol_container").empty();
      }
      break;
 
  }
  $(".modal").show();
  var request =  get_XmlHttp();		

  request.open("POST", "http://web.njit.edu/~ev8/clientproxy.php", true);			
  request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 //alert("sending "+JSON.stringify(data));
  request.send(JSON.stringify(data));		
  request.onreadystatechange = function() {
    if (request.readyState == 4) {
      var obj =$.parseJSON(request.responseText);
     // alert(JSON.stringify(obj));
      if (data.action =="login") {
  //alert(JSON.stringify(obj));
	if ('error' in obj) {	  //alert("login failed");
	  $("#error").html("<font color='red'>"+obj.error+" check your user name and password"+"</font>");

	 //console.log("login accepted");
	 
	  }else{	    //alert("login accepted");
	    var url='#home/';

		window.location.href = url;

	
	}
      }else if(data.action =="gethome"){        $("#header").html('<div class="logo"></div><h1>TOODLE</h1><h2>A place of learning</h2><a id ="logout" onclick="logout();">logout</a>');

	          //  alert(obj.page);
	
	$("#maincol_container").html(obj.page);
	$("#maincol_container").fadeIn("slow");	$("#nav").empty();
	$("#nav").html(obj.nav);	$("#nav").fadeIn("slow");
	$( "#navaccordion" ).accordion(); 
      
      
      }else if(data.action =="getcourse"){
$("#header").html('<div class="logo"></div><h1>TOODLE</h1><h2>A place of learning</h2><a id ="logout" onclick="logout();">logout</a>');

	$("#maincol_container").html(obj.page);
	$("#maincol_container").fadeIn("slow");
	$( "#pageaccordion" ).accordion({heightStyle: "content",active: parseInt(obj.cw) });
	$("#nav").html(obj.nav);
	$("#tab-1").change(function(){
	  var url =obj.link1;
	window.location.href = url;
	});
	$("#tab-2").change(function(){
	  var url =obj.link2;
	window.location.href = url;
	});
	
	
	$( "#navaccordion" ).accordion(); 

      }else if(data.action =="getforums"){
	$("#header").html('<div class="logo"></div><h1>TOODLE</h1><h2>A place of learning</h2><a id ="logout" onclick="logout();">logout</a>');

	//$("#nav").html(obj.nav);
	$("#maincol_container").html(obj.page);
	$("#maincol_container").fadeIn("slow");
	$( "#pageaccordion" ).accordion({heightStyle: "fill"});
	
	$("#tab-1").change(function(){
	  var url =obj.link1;
	window.location.href = url;
	});
	$("#tab-2").change(function(){
	  var url =obj.link2;
	window.location.href = url;
	});
	
	
	$( "#navaccordion" ).accordion();
	$( "#createforum" ).attr("onclick",'createforum();');
	
      }else if(data.action =="getforum"){
        $("#header").html('<div class="logo"></div><h1>TOODLE</h1><h2>A place of learning</h2><a id ="logout" onclick="logout();">logout</a>');

	//$("#nav").html(obj.nav);
	$("#maincol_container").html(obj.page);
	$("#maincol_container").fadeIn("slow");
	$( "#pageaccordion" ).accordion({heightStyle: "fill"});
	
	$("#tab-1").change(function(){
	  var url =obj.link1;
	window.location.href = url;
	});
	$("#tab-2").change(function(){
	  var url =obj.link2;
	window.location.href = url;
	});
	
	
	$( "#navaccordion" ).accordion();
	$( "#createthread" ).attr("onclick",'createthread();');
	
	
      }else if(data.action =="getthread"){
        $("#header").html('<div class="logo"></div><h1>TOODLE</h1><h2>A place of learning</h2><a id ="logout" onclick="logout();">logout</a>');

	//$("#nav").html(obj.nav);
	$("#maincol_container").html(obj.page);
	$("#maincol_container").fadeIn("slow");
	$( "#pageaccordion" ).accordion({heightStyle: "fill"});
	
	$("#tab-1").change(function(){
	  var url =obj.link1;
	window.location.href = url;
	});
	$("#tab-2").change(function(){
	  var url =obj.link2;
	window.location.href = url;
	});
	
	
	$( "#navaccordion" ).accordion();
	$( "#postreply" ).attr("onclick",'postreply();');
	

      }else if(data.action =="logout"){
        $("#header").html('<div class="logo"></div><h1>TOODLE</h1><h2>A place for learning.</h2>');
	 $(".modal").hide();
	var url='#';

		window.location.href = url;
	
getevent
      }else if(data.action =="getterm"){
        $("#nav").html(obj.nav);
	//$(".overview").html("select a course");

      }else if(data.action =="getevent"){
        $("#page").html(obj.page);
	$( "#postfiles" ).attr("onclick",'postassign();');
	$( "#submitgrades" ).attr("onclick",'submitgrades();');
	
	//$(".overview").html("select a course");

      }else if(data.action =="getassigment"){
        $(".overview").html(" please submit your assignment");

        $("#page").html(obj.page);

      }else if(data.action =="postreply"){
	var datas ={"action":"getthread","id":$("#pid").val()};
	ajaxrequest(datas);

      }else if(data.action =="submitquiz"){
	var datas ={"action":"editweek","id":$("#pid").val()};
	ajaxrequest(datas);
	}else if(data.action =="createthread"){
	var datas ={"action":"getforum","id":$("#pid").val()};
	ajaxrequest(datas);


      }else if(data.action =="createforum"){
	var datas ={"action":"getforums","id":$("#pid").val()};
	ajaxrequest(datas);

      } else if(data.action =="editweek"){
	$("#page").html(obj.page);
	$( "#editweek" ).attr("onclick",'editweek();');
      
      }else if(data.action =="sendeditweek"){
	//alert($("#pid").val());
	 var url= "#course/"+$("#pid").val();
	
	window.location.href = url;
      
      }else if(data.action =="deleteevent"){
	//alert($("#pid").val());
	 var url= "#editweek/"+$("#id").val();
	
	window.location.href = url;
      
      }else if(data.action =="deletefile"){
	//alert($("#pid").val());
	 var url= "#editweek/"+$("#id").val();
	
	window.location.href = url;
      
      }else if(data.action =="createassignment"){
	window.history.back();
      
      }else if(data.action =="submitanswers"){
	window.history.back();
   
      }else if(data.action =="submitgrades"){
	var url= "#home";
	
	window.location.href = url;
   
      }else if(data.action =="submitanswers"){
	var url= "#event/"+$("#pid").val();
	
	window.location.href = url;
      
      }else if(data.action =="takequiz"){
	$("#page").html(obj.page);
	timeleft=(parseInt(obj.time)*60);
	
	setInterval(function() {  $("#timer").html("<center>"+Math.floor(timeleft/60) + ":"+timeleft%60+"</center>");timeleft--;if(timeleft<=0){$("#sendquiz").attr('disabled', 'disabled');$("#timer").html("<center><font color='red'>out of time</font></center>");}}, 1000);
      $( "#sendquiz" ).attr("onclick",'sendanswers();');
      }
      $(".modal").hide();
    }
  }
}