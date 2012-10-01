<?php
session_start();
require_once("classes/Database.class.php");
require_once("classes/User.class.php");
require_once("classes/Auth.class.php");
$db = new Database();
$user = new User();
$auth = new Authentication();

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="http://rsrc.visionsandviews.net/jquery/css/custom-theme/jquery-ui-1.8.23.custom.css" media="screen" />
    <style type="text/css">
      	html { height: 100% }
      	body { height: 100%; margin: 0; padding: 0 }
      	#map { height: 100%;
	      	position: absolute;
	      	width: 100% }
      	
      	#routetoolbar {
			padding: 10px 4px;
			left: 50%;
			margin-left: -25%;
			z-index: 99;
			position: absolute;
		}
		
		#usertoolbar{
			bottom: 0px;
			padding: 10px 4px;
			left: 50%;
			margin-left: -25%;
			z-index: 99;
			position: absolute;
		}
    </style>
    <!--<script src="http://maps.google.com/maps?file=api&v=2&key=AIzaSyBrSKB8ZpZ50re1iZD3b-K1Ruxj8UEmAzw" type="text/javascript"></script>
    </script>-->
    
<html>
    <body>
    	<div id="fb-root"></div>
    	
    	<div id="wrapper">
	        <div id="map">
	        	
	        </div>
	        <span id="routetoolbar" class="ui-widget-header ui-corner-bottom">
				<button id="html5Position">Huidige positie</button>
			</span>
			<span id="usertoolbar" class="ui-widget-header ui-corner-top">
				<button id="userMenu">Gebruiker</button>
				<button id="addRouteButton">Voeg route toe</button>
			</span>
		</div>
        <div id="errorMessage"></div>
        <div id="routeDialog"></div>
        <div id="directionDialog"></div>
        <div id="userDialog">
        	<?php
				if(!$user->isLoggedIn()){
			?>
        	<button id="fbregButton">Registreer</button>
        	<?php }?>
			<button id="fbloginButton">Profiel</button>
			
			
        </div>
        <div id="fbregDialog">
	      
      		<div 
		        class="fb-registration" 
        		data-fields="[{'name':'name'}, {'name':'email'},{'name': 'birthday'}, {'name':'gender'},{'name':'location'}]"
        		data-redirect-uri="http://dump.visionsandviews.net/lift/facebook_reg.php" >
      		</div>
        </div>
        <div id="fbloginDialog">
        	
		      <div id="auth-status">
		        <div id="auth-loggedout">
		          <a href="#" id="auth-loginlink">Login</a>
		        </div>
		        <div id="auth-loggedin" style="display:none">
		          Hi, <span id="auth-displayname"></span>  
		        (<a href="#" id="auth-logoutlink">logout</a>)
		      </div>
		      <div id='userInfo'></div>
		    </div>
        </div>
        <div id="addRouteDialog">
        	<div id="inputField">
	    		<p>Vertreklocatie: </p>
	    		<p>Aankomstlocatie:</p>
	    		<input id="distance" type="textbox" value="50" />
	    		<input id="geocode" type="button" name"Geocode" value="Geocode">
    		</div>
    	</div>
    </body>
    <script type="text/javascript" src= "http://maps.googleapis.com/maps/api/js?key=AIzaSyBrSKB8ZpZ50re1iZD3b-K1Ruxj8UEmAzw&sensor=false">
</script>
<script src="http://rsrc.visionsandviews.net/jquery/js/jquery-1.8.0.min.js"></script>
<script src="http://rsrc.visionsandviews.net/jquery/js/jquery-ui-1.8.23.custom.min.js"></script>
</script>
<script>
    
    $(document).ready(function(){
    	var map;
	    var geocoder = null;
		var directionsDisplay;
		var directionsService = new google.maps.DirectionsService();
		var userPos = null;
		
		$("#directionDialogButton").live('click', function(event){
			if($("#directionDialog").dialog("isOpen")){
				$("#directionDialog").dialog("close");
			}else{
				$("#directionDialog").dialog("open");
			};
		});
		$("#loadRoutesButton").live('click', function(event){
			loadRoutes();
		});
		$("#directionDialog").dialog({
			autoOpen: false,
			width: 300,
			modal: false,
			maxHeight: 400,
			resizeable: false
		});
		
		$("#fbregDialog").dialog({
			autoOpen: false,
			modal: true,
			width: 'auto',
			height: 'auto'
			
		})
		$("#routeDialog").dialog({
			autoOpen: false,
			height: 'auto',
			width: 300,
			modal: true
		})
		$("#errorMessage").dialog({
			autoOpen: false,
			modal: true
		})
		
		$("#userDialog").dialog({
			autoOpen: false,
			modal: true
		})
		
		$("#fbloginDialog").dialog({
			autoOpen: false,
			modal: true
		})
		
		$("#addRouteDialog").dialog({
			autoOpen: false,
			modal: false
		})
		
		$("#addRouteButton").button().click(function(){
			$("#addRouteDialog").dialog("open");
		});
		
		$("#html5Position").button().click(function(){
			$("#routetoolbar #directionDialogButton").remove();
			$("#routetoolbar #loadRoutesButton").remove();
			initiate_geolocation();
		});
		
		$("#fbregButton").button().live("click", function(){
			$("#fbregDialog").dialog("open");
		})
		$("#fbloginButton").button().live("click", function(){
			$("#fbloginDialog").dialog("open");
		})
		
		$("#userMenu").button().click(function(){
			loadUserData();
			$("#userDialog").dialog("open");
		})
		
		$(".gotoLocation").live("click", function(event){
			$("#routetoolbar #directionDialogButton").remove();
			event.preventDefault();
			var latlngbounds = new google.maps.LatLngBounds( );
			var marker;
			var userLat = $(this).attr("data-location-latitude");
			var userLon = $(this).attr("data-location-longitude");
			markerPos = new google.maps.LatLng(userLon, userLat);
			latlngbounds.extend(userPos);
			latlngbounds.extend(markerPos);
			$("#routeDialog").dialog("close");
			$("#menuDialog").dialog("close");
			map.fitBounds(latlngbounds);
			marker = new google.maps.Marker({
	            map: map, 
	            position: markerPos
	        });
	        
	        calcRoute(userPos, markerPos);
		})
		function calcRoute(departure, destination) {
		$("#routetoolbar").append("<button id='directionDialogButton'>Routebeschrijving</button>");
		$("#directionDialogButton").button();
		  var request = {
		      origin: departure,
		      destination: destination,
		      // Note that Javascript allows us to access the constant
		      // using square brackets and a string value as its
		      // "property."
		      travelMode: google.maps.TravelMode['WALKING']
		  };
		  directionsService.route(request, function(response, status) {
		    if (status == google.maps.DirectionsStatus.OK) {
		      directionsDisplay.setDirections(response);
		    }
		  });
		  
		}
		function loadUserData(){
			$("#userInfo p").remove();
			$.ajax({
				type: "GET",
				cache: false,
				dataType: 'json', /*JSONP for cross-domainrequests
				Needs a callback function, API should encapsulate the response with the callback given */
				url: "http://dump.visionsandviews.net/lift/js/ajax/getUserInfo.php",
				beforeSend: function(data){
					$("#userInfo").dialog("open");
					$("#userInfo").append("<img src='./images/loading.gif' alt='Loading' />");
				},
				success: function (data) {
			        // Use data for actions
			        $("#userInfo img").remove();
			        $("#userInfo").append("<p>Twitter: @"+data.twitter.display_name+"</p>");
			       	
			   },
			   error: function(xhr, textstatus, err){
			   	alert(xhr);
			   	alert(textstatus);
			   	alert(err);
			   }
			})
		}
		
		function loadRoutes(){
			$("#routeDialog p").remove();
			var departure = userPos;
			var radius = 50;
			$.ajax({
				type: "GET",
				cache: false,
				dataType: 'jsonp', /*JSONP for cross-domainrequests
				Needs a callback function, API should encapsulate the response with the callback given */
				url: "http://berend.beligum.com/liftAPI/routesFromCurrentPos/"+departure.toUrlValue()+"/"+radius+"/json",
				beforeSend: function(data){
					$("#routeDialog").dialog("open");
					$("#routeDialog").append("<img src='./images/loading.gif' alt='Loading' />");
				},
				success: function (data) {
			        // Use data for actions
			        $("#routeDialog img").remove();
			        createRoutelistDialog(data);
			       	
			   },
			   error: function(xhr, textstatus, err){
			   	alert(xhr);
			   	alert(textstatus);
			   	alert(err);
			   }
			})
		}
		$("#loadRoutesButton").button().click(function(){
			loadRoutes();
		})
		function createRoutelistDialog(data){
			
			$("#routeDialog p").remove();
			$("#routeDialog").append("<p></p>");
			var routeDialog = $("#routeDialog p");
			for (var i=0; i < data.results.length; i++) {
				routeDialog.append("<ul>");
				routeDialog.append("<li>UserID: "+data.results[i].user.userID+"</li>"+
												"<li>Age: "+data.results[i].user.userAge+"</li>"+
												"<li>Passengers: "+data.results[i].user.passengers+"</li>"+
												"<li>Departure: "+data.results[i].departure.name+" ("+data.results[i].departure.distance+"km)</li>"+
												"<li>Destination: "+data.results[i].destination.name+"</li>"+
												"<button class='gotoLocation' data-location-latitude='"+data.results[i].departure.coordinates.lat+"' data-location-longitude='"+data.results[i].departure.coordinates.lon+"'>Ga naar gebruiker</button>"+
												"</ul>");
			}
			$("#routeDialog button").button();
		}
	    function init(){
	    	directionsDisplay = new google.maps.DirectionsRenderer();
	        var latlng = new google.maps.LatLng(50.851671, 4.362634000000071);
	        var myOptions = {
	            zoom: 9,
	            center: latlng,
	            map: map,
	            mapTypeId: google.maps.MapTypeId.ROADMAP
	        };
	        map = new google.maps.Map(document.getElementById("map"), myOptions);
	        directionsDisplay.setMap(map);
	        directionsDisplay.setPanel(document.getElementById("directionDialog"));
		}
		
		function initiate_geolocation() {  
            navigator.geolocation.getCurrentPosition(handle_geolocation_query, handle_errors);  
        }  
        function handle_geolocation_query(position){  
            /*alert('Lat: ' + position.coords.latitude + ' ' +  
                  'Lon: ' + position.coords.longitude);*/
        	var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            userPos = new google.maps.LatLng(lat, lng);
            map.setCenter(userPos);
            map.setZoom(14);
            var markerImage = "./images/home.png";
            var marker = new google.maps.Marker({
	            map: map, 
	            position: userPos,
	            icon: markerImage,
	            draggable: true
	        });
	        //calcRoute(userPos, markerPos);
	        google.maps.event.addDomListener(marker, 'dragend', function(event){
	        	userPos = event.latLng;
	        	$("#routetoolbar #directionDialogButton").remove();
	        	calcRoute(userPos, markerPos);
	        });
	        
	        
	        google.maps.event.addDomListener(marker, 'click', function(event){
	        	loadRoutes();
	        	
	        });
	        
	        $("#routetoolbar").append("<button id='loadRoutesButton'>Gevonden routes</button>");
			$("#loadRoutesButton").button();
        }
        
        function show_error(message){
        	$("#errorMessage p").remove();
        	$("#errorMessage").append("<p>"+message+"</p>");
        	$("#errorMessage").dialog("open");
        }
        
		function handle_errors(error)  
        {  
            switch(error.code)  
            {  
                case error.PERMISSION_DENIED: show_error("user did not share geolocation data");  
                break;  
                case error.POSITION_UNAVAILABLE: show_error("could not detect current position");  
                break;  
                case error.TIMEOUT: show_error("retrieving position timed out");  
                break;  
                default: show_error("unknown error");  
                break;  
            }  
        }  		 
		init();
		
    	(function(d){
         var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement('script'); js.id = id; js.async = true;
         js.src = "//connect.facebook.net/en_US/all.js";
         ref.parentNode.insertBefore(js, ref);
       }(document));
	        window.fbAsyncInit = function() {
	          FB.init({
	            appId      : 'xxx', // App ID
	            channelUrl : '../channel.php', // Channel File
	            status     : true, // check login status
	            cookie     : true, // enable cookies to allow the server to access the session
	            xfbml      : true  // parse XFBML
	          });
	        // Load the SDK Asynchronously
	        
		        // listen for and handle auth.statusChange events
		        FB.Event.subscribe('auth.statusChange', function(response) {
		          if (response.authResponse) {
		            // user has auth'd your app and is logged into Facebook
		            FB.api('/me', function(me){
		              if (me.name) {
		                document.getElementById('auth-displayname').innerHTML = me.name;
		                
		              }
		            })
		            document.getElementById('auth-loggedout').style.display = 'none';
		            document.getElementById('auth-loggedin').style.display = 'block';
		          } else {
		            // user has not auth'd your app, or is not logged into Facebook
		            document.getElementById('auth-loggedout').style.display = 'block';
		            document.getElementById('auth-loggedin').style.display = 'none';
		          }
		        });
		
		        // respond to clicks on the login and logout links
		        document.getElementById('auth-loginlink').addEventListener('click', function(){
		          FB.login();
		        });
		        document.getElementById('auth-logoutlink').addEventListener('click', function(){
		          FB.logout();
		        }); 
		      }
		      });
		    </script>
</script>
</html>

