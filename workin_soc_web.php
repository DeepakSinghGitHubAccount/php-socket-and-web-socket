<!DOCTYPE html>
<html>
<head>
	<title>web</title>
	<script src="../jquery.min.js"></script>
</head>
<body>
	<div id="msg"></div>
	<input type="text" name="name" id="name">
	<input type="text" name="name" id="message">
	<button type="button" id="send-message">btn</button>
</body>
</html>
<script type="text/javascript">
	$(document).ready(function(){

		
		

		


		var wsUri = "ws://127.0.0.1:9000/a_A_SARA/new_socket/soc2.php"; 	

		websocket = new WebSocket(wsUri); 
		var div = $("#msg");
		websocket.onopen = function(ev) { // connection is open 
			
			var roc = JSON.parse(ev);
			if(roc.message != '')
			{
				div.append(roc.message);	
			}else
			{
				div.append(roc);	
			}
			
			console.log('a '+ev.data);
		}

		websocket.onmessage = function(ev) {
			
			div.append(ev.data);
			console.log('b '+ev.data);
		};	
	
	websocket.onerror	= function(ev){ 
		div.append(ev.data);
		console.log('c '+ev.data);
	}; 
	websocket.onclose 	= function(ev){
			console.log('d '+ev.data);
			div.append(ev.data);
	 }; 

	 //Message send button
	$('#send-message').click(function(){
		send_message();
	});
	
	//User hits enter key 
	$( "#message" ).on( "keydown", function( event ) {
	  if(event.which==13){
		  send_message();
	  }
	});
	
	//Send message
	function send_message(){
		var message_input = $('#message'); //user message text
		var name_input = $('#name'); //user name
		
		if(message_input.val() == ""){ //empty name?
			alert("Enter your Name please!");
			return;
		}
		if(message_input.val() == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}

		//prepare json data
		var msg = {
			message: message_input.val(),
			name: name_input.val(),
			
		};
		//convert and send data to server
		websocket.send(JSON.stringify(msg));	
		message_input.val(''); //reset message input
	}

	});
</script>