<?
	session_start();


	if (!isset($_GET['group'])){


		//die ("Could not find a group session id");

	}
	
	$path = str_replace('/', '_', $_GET['group']);


	$roomnameSet = false;	
	$roomnameSetError = false;
	
	if (isset($_POST['roomname'])){


		$roomname = Slug($_POST['roomname']);

		if (trim($roomname) != ''){
			$roomnameSet = true;	
		}else{
			$roomnameSetError = true;
		}

		if (!$roomnameSetError){


			if (!file_exists('data/'.$roomname)){

				 mkdir('data/'.$roomname, 0777, true);

			}else{
				$roomnameSetError = true;
			}
		}




	}


	
	
	if (isset($_GET['all'])){
		
		$triples = returnAllJson();
		

		$results = new stdClass;
		
		$results->triples = $triples;
		
 		
		header('Content-type: application/json;charset=utf-8');
		echo json_encode($results);
		die();
		
		
	}
	
	if (isset($_POST['remove'])){
	
		
		$triples = array();
		
	
		//get the contents if it exists
		if (file_exists("data/" . $path . '/' . session_id() . ".json")){
		
			$existingTriples =  file_get_contents("data/" . $path . '/' . session_id() . ".json");
			$existingTriples = json_decode($existingTriples);
			
			if (json_last_error() != JSON_ERROR_NONE){
			
				header('Content-type: application/json;charset=utf-8');
				echo '{"results": false}';
				die();
				
			}
			
			
			foreach ($existingTriples->triples as $t){
				
				if ($t->s == $_POST['s']  and $t->p == $_POST['p']  and $t->o == $_POST['o']){
					//nothing	
				}else{
					$triples[] = $t;
				}
				
			}
			
			
		}else{
			header('Content-type: application/json;charset=utf-8');
			echo '{"results": false}';
			die();
		}
		 
		

		$results = new stdClass;
		
		$results->triples = $triples;
		
		file_put_contents("data/" . $path . '/' . session_id() . ".json",json_encode($results));
		
		
	
		header('Content-type: application/json;charset=utf-8');
		echo '{"results": true}';
		die();
	
	}
	
	if (isset($_POST['o'])){
	
		//adding a tripple
		
		
	
		//see if the directory exists yet
		if (!file_exists($path)){
			mkdir("data/" . $path);
		}
		
		$triples = array();
		
		
		//get the contents if it exists
		if (file_exists("data/" . $path . '/' . session_id() . ".json")){
		
			$existingTriples =  file_get_contents("data/" . $path . '/' . session_id() . ".json");
			$existingTriples = json_decode($existingTriples);
			
			if (json_last_error() != JSON_ERROR_NONE){
			
				header('Content-type: application/json;charset=utf-8');
				echo '{"results": false}';
				die();
				
			}
			
 			 
			
			$triples = $existingTriples->triples;
			
		}
		 
		
		
		
		$thisTriple = new stdClass;
		
		
		$thisTriple->s = $_POST['s'];
		$thisTriple->p = $_POST['p'];
		$thisTriple->o = $_POST['o'];

		$thisTriple->color = stringToColorCode(session_id());

		$triples[] = $thisTriple;
		
		

		$results = new stdClass;
		
		$results->triples = $triples;
		
		file_put_contents("data/" . $path . '/' . session_id() . ".json",json_encode($results));
		
		header('Content-type: application/json;charset=utf-8');
		echo '{"results": true}';
		die();

		
		
	}









	//collect all the json data and combine it into one json
	
	function returnAllJson(){
	
		$path = str_replace('/', '_', $_GET['group']);
		
		
		$triples = array();
		
	
		$dir = new DirectoryIterator(dirname(__FILE__) . "/data/" . $path . '/');
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				
				//var_dump($fileinfo->getFilename());
				$text = dirname(__FILE__) . "/data/" . $path . '/' . $fileinfo->getFilename();
				$text = file_get_contents($text);
				
				$existingTriples = json_decode($text);
			
				if (json_last_error() != JSON_ERROR_NONE){
				
					//there was something wrong with that file, maybe mid write, skip it
					continue;
					
				}
				
				foreach ($existingTriples->triples as $t){
				
					$t->owner = str_replace(".json","",$fileinfo->getFilename());
				
					$triples[] = $t;	
					
				}
				
				
			}
		}	
		
		return $triples;
		
	}
	


	function stringToColorCode($str) {
	  $code = dechex(crc32($str));
	  $code = substr($code, 0, 6);
	  return $code;
	}



  	function Unaccent($string)
	{
		if (extension_loaded('intl') === true)
		{
			$string = Normalizer::normalize($string, Normalizer::FORM_KD);
		}

		if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false)
		{
			$string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|caron|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
		}

		return $string;
	}
	function Slug($string, $slug = '-', $extra = null)
	{
		return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, Unaccent($string)), $slug));
	}




?>

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Triple Builder</title>


<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/site.css">
<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Oxygen+Mono' rel='stylesheet' type='text/css'>
<link rel="icon" type="image/png" href="/img/favicon.png">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-modal.js"></script>
<script src="js/bootstrap-modalmanager.js"></script>
<script src="js/d3.min.js"></script>

<script  type="text/javascript">


	var lod = {};
	
	
	lod.network = {};
	
	lod.sessionId = "<? echo session_id(); ?>";
	lod.sessionGroup = "<? echo htmlspecialchars($_GET['group']); ?>";
	
	lod.bind = function(){
		
		$("#addTripleAdd").click(function(){lod.addTriple()});
		
		
		$("#addTripleInputSubjectUseLast").click(function(){
			
			$("#addTripleInputSubject").val($(this).val());
			
		});
	
		$("#addTripleInputPredicateUseLast").click(function(){
			
			$("#addTripleInputPredicate").val($(this).val());
			
		});		
	
		$("#addTripleInputObjectUseLast").click(function(){
			
			$("#addTripleInputObject").val($(this).val());
			
		});
		
		
		
		$("#showTable").click(function(){
			
			$("#network").fadeOut("fast",
				
				function(){
					
								$("#tripleTableHolder").fadeIn();
	
					
				}
			
			);
			
		});
		$("#showNetwork").click(function(){
			
 			$("#tripleTableHolder").fadeOut("fast",
			
				function(){
					
								$("#network").fadeIn();
	
					
				}		
			
			);
			
		});	
				
	}
	
	
	
	
	
	lod.addTriple = function(){
		
		//grab the data
		
		var s = $("#addTripleInputSubject").val().trim().replace(/\>/g,'').replace(/\</g,'').toLowerCase();
		var p = $("#addTripleInputPredicate").val().trim().replace(/>/g,'').replace(/</g,'').toLowerCase();
		var o = $("#addTripleInputObject").val().trim().replace(/>/g,'').replace(/</g,'').toLowerCase();
 
 		if (s == '' || p == '' || o == ''){
			alert("All of the fields must be filled in");
			return false;	
		}
		if (s.search(' ')  != -1 || p.search(' ')  != -1){
			alert("No spaces allowed in URIs!");
			return false;	
		}
		
 		if (s.substring(0,7)  != 'http://' || p.substring(0,7)  != 'http://' ){
			alert("Need Full URIs ('http://blah.org/something') for the first two caluses at least");
			return false;	
		}		
		
		
		//pass it to the server
		$.post('?group=' + lod.sessionGroup,{s : s, p : p, o : o}, function(data) {
			
 			
			if (data.results){
				
				
				$("#addTripleInputSubjectUseLast").val($("#addTripleInputSubject").val()).css("display",'inline-block');
				$("#addTripleInputSubject").val('');
				$("#addTripleInputPredicateUseLast").val($("#addTripleInputPredicate").val()).css("display",'inline-block');
				$("#addTripleInputPredicate").val('');
				$("#addTripleInputObjectUseLast").val($("#addTripleInputObject").val()).css("display",'inline-block');
				$("#addTripleInputObject").val('');								
				//	x
				//
				lod.buildTripleTable();
				
				
			}else{
				
				alert("Something did not go right :( try again please")
				return false;
				
			}
			
		}).error(function() { alert('Internal Server Error'); });	
		
		
		
		
		
	}
	
	lod.buildTripleTable = function(){
		
		console.log('buildtable');
		
		$.get('?group=' + lod.sessionGroup + "&all=true",function(data){
			
			
				
			data.triples.sort(function(a, b) { 
				
				 if (a.s < b.s)
					 return -1;
				 if (a.s > b.s)
					return 1;
					
				 return 0;	
			
				
			});			
			
			
			$("#tripleTable").empty();	
			
			
			for (x in data.triples){
			
				$("#tripleTable").append(
					
					$("<tr>").append(
					
						function(){
						
							if (data.triples[x].owner == lod.sessionId){
								
								return $("<td>")
											.append(
												$("<button>")
													.data("s",data.triples[x].s)
													.data("p",data.triples[x].p)
													.data("o",data.triples[x].o)
													.addClass("btn")
													.addClass("btn-mini")
													.addClass("btn-danger")
													.addClass("tripleTableRemove")
													.text("x")
													.click(function(){
														
 														
														$.post('?group=' + lod.sessionGroup,{remove: true, s : $(this).data("s"), p : $(this).data("p"), o : $(this).data("o")}, function(data) {
															
															lod.buildTripleTable();
															
															
														});
														
													})
											)
								
								
							}else{
								return $("<td>");	
							}
							
							
							
						}
					
						
							
					
					
					).append(
						$("<td>")
							.text( data.triples[x].s.search('http://') != -1 ?  "<" +data.triples[x].s + ">" :  '"' + data.triples[x].s  + '"')
						
						
					).append(
						$("<td>")
							.text( data.triples[x].p.search('http://') != -1 ?  "<" +data.triples[x].p + ">" :  '"' + data.triples[x].p  + '"')
						
						
					).append(
						$("<td>")
							.text( data.triples[x].o.search('http://') != -1 ?  "<" +data.triples[x].o + ">" :  '"' + data.triples[x].o  + '"')
						
						
					)
						
				
				
				)
				
				
			}
			
 			
			
			
			//update the network
			lod.network.organizeData(data);
			
			
		});
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//NETWORK START
	
	
	lod.network.organizeData = function(data){
		
		console.log(data);
		
		
		//build node lokup
		
		lod.network.nodeLookup = {};
		lod.network.dataLookup = {};
		lod.network.nodes = [];
		
		for (var x in data.triples){
			
			if (data.triples[x].s.search("http://") != -1){
				//in lookup yet?
				if (!lod.network.nodeLookup.hasOwnProperty(data.triples[x].s)){
					//not yet 	
					lod.network.nodes.push({"name": data.triples[x].s, "color": data.triples[x].color});
					//add to lookup
					lod.network.nodeLookup[data.triples[x].s] = lod.network.nodes.length -1;
				}
			}
			
			//do the same for the object
			if (data.triples[x].o.search("http://") == -1){
				
				//a property of the S
				if (lod.network.dataLookup.hasOwnProperty(data.triples[x].s)){
					lod.network.dataLookup[data.triples[x].s][data.triples[x].p] = data.triples[x].o;
				}else{
					lod.network.dataLookup[data.triples[x].s] = {}
					lod.network.dataLookup[data.triples[x].s][data.triples[x].p] = data.triples[x].o;
				}
				
			}else{
			
				//it might still be a property even with the http://
				//see if there is already a node with name, if not then it is a property
				
				if (!lod.network.nodeLookup.hasOwnProperty(data.triples[x].o)){
					//a property of the S
					if (lod.network.dataLookup.hasOwnProperty(data.triples[x].s)){
						lod.network.dataLookup[data.triples[x].s][data.triples[x].p] = data.triples[x].o;
					}else{
						lod.network.dataLookup[data.triples[x].s] = {}
						lod.network.dataLookup[data.triples[x].s][data.triples[x].p] = data.triples[x].o;
					}					
				}
				
				
				
			}
			
		}
		
		
		
		
		
		//build the edges
		lod.network.edges = [];
		for (var x in data.triples){
			
			
			//if both s and o are nodes add the link
			if (lod.network.nodeLookup.hasOwnProperty(data.triples[x].s)  &&  lod.network.nodeLookup.hasOwnProperty(data.triples[x].o)){
			
				lod.network.edges.push({ source : lod.network.nodeLookup[data.triples[x].s], target:  lod.network.nodeLookup[data.triples[x].o]  });
			
			
			}
			
			
		}
		
		
		
		
		lod.network.init ();	
		
	}
	
	
	
	
	//Sets up the network force object
	lod.network.init = function(){
		
		
 		d3.select("#network svg").remove();
		d3.select("#d3ToolTip").remove();
		
		lod.network.tooltip = d3.select("body")
			.append("div")
			.attr('id','d3ToolTip');		
					
		lod.network.width = $("#network").width() - 3;
		lod.network.height = $("#network").height() - 3;
		lod.network.fill = d3.scale.category20();
		

		
		lod.network.nodes = lod.network.nodes;
		lod.network.links = lod.network.edges;		
		

		
		lod.network.vis = d3.select("#network").append("svg")
			.attr("width", lod.network.width)
			.attr("height", lod.network.height)
			.style("fill", "none")
			.call(d3.behavior.zoom() 

			  .on("zoom", function() { 

				lod.network.vis.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale*.8 + ")"); 
 			  })); 		
			
		lod.network.vis.append("rect")
			.attr("width", lod.network.width)
			.attr("height", lod.network.height);
			

			  

	lod.network.vis = lod.network.vis.append("g"); 

 	lod.network.vis.attr('transform','scale('+.8+')');
	
	var curve = d3.svg.line()
			  .interpolate("cardinal-closed")
			  .tension(.85);	

					
		lod.network.force = d3.layout.force()
 			.charge(-500)
			.gravity(0.1)
			.nodes(lod.network.nodes)
			.distance(100)
			.linkStrength(0.2)
			.theta(1.5)
			.links(lod.network.links)
			.size([lod.network.width, lod.network.height]);			
		
		
			
		
		
		lod.network.force.on("tick", function() {
		
		
			
		  lod.network.vis.selectAll(".egoLink")
		  .attr("d", function(d) {
			var dx = d.target.x - d.source.x,
				dy = d.target.y - d.source.y,
				dr = Math.sqrt(dx * dx + dy * dy);
			return "M" + d.source.x + "," + d.source.y + "A" + dr + "," + dr + " 0 0,1 " + d.target.x + "," + d.target.y;
			});
			
			
		  lod.network.vis.selectAll(".node")
			  .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
		
		
		  
		});		
		
		
		lod.network.restart();
			
	}
	
	
	
	//starts/restarts the force network layout 
	lod.network.restart = function(){
		
	  
	
	  lod.network.vis.selectAll(".egoLink")
		.data(lod.network.links)
		.enter().append('path')
		  .attr("class", "egoLink")
		  .attr("x1", function(d) { return d.source.x; })
		  .attr("y1", function(d) { return d.source.y; })
		  .attr("x2", function(d) { return d.target.x; })
		  .attr("y2", function(d) { return d.target.y; });
	
	  
      var node = lod.network.vis.selectAll(".node")
          .data(lod.network.nodes)
        .enter().append("g")
          .attr("class", "node")
          .call(lod.network.force.drag)
		  .on("mouseover", function(d){ lod.network.tooltip.style("visibility", "visible"); 
		  
		  
		  		var str = "<h5>" + d.name + "</h5>";
		  		for (x in lod.network.dataLookup[d.name]){
					
					var short = x.split("/")[x.split("/").length-1];
					
					str = str + "<span class=\"toolTipPredicate\">" + short + "</span>  <span class=\"toolTipPredicateValue\">" + lod.network.dataLookup[d.name][x].capitalize().replace("Http://","http://")  + "</span><br>";
					
				}
		  		lod.network.tooltip.html(str);
				
				
				
				 
			
			
			
			})
		  .on("mousemove", function(d){return lod.network.tooltip.style("top", (event.pageY-10)+"px").style("left",(event.pageX+10)+"px");})
		  .on("mouseout", function(d){return lod.network.tooltip.style("visibility", "hidden");})
		  .attr("x", function(d, i) { return lod.network.width / 2 + i; })
		  .attr("y", function(d, i) { return lod.network.width / 2 + i; });
		  
		node.append("circle")
			.call(lod.network.force.drag)
			.on("click", function(d){ window.open('/resource/' + d.url)})
			.style("cursor","pointer")
			.style("fill", function(d){ return '#' + d.color; })

			.attr("class", function(d){ 
			 
 				return "eventNetworkNode";
			
			}) 

			.attr("r", function(d) { 
				
				return 7;
				
			
			});  
	
	node.append("rect")
      .attr("x", function(d) { return -1 * (d.name.length*2.25); })
	  .attr("y", function(d) { return 7; })
	  .attr("height", function(d) { return 10; })
	  .attr("width", function(d) { return (d.name.length*2.25)*2; })
	  .attr("class", "eventNetworkLabelBg")
	   .style("visibility", "visible")
 
	  .style("fill","#fff")
	  .on("click", function(d){ window.open('/resource/' + d.url)})
	  .style("stroke", function(d){ return '#' + d.color; });
	  		
	
			
	node.append("text")
      .attr("x", function(d) { return 0; })
	  .attr("y", function(d) { return 15; })
	  .attr("class", "eventNetworkLabel")
	  .style("visibility", "visible")
 	  	
	  .attr("text-anchor", function(d) { return "middle";})
	
      .text(function(d) { return d.name; });					  


		lod.network.force.start();
			
	}	


		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//NETWORK END
	
	
	
	$(document).ready(function($) {
		
		
		lod.bind();
		
		lod.buildTripleTable();
		
		lod.clearTimeOutFunc = function(){
			window.clearInterval(lod.timeout);
			console.log("clear");
		};
			
		window.setTimeout(lod.clearTimeOutFunc, 3600000);
				
		//refreash the table
		lod.timeout = window.setInterval(lod.buildTripleTable, 30000);
		 
		 
		 
		$("#network").css("width",$(window).width() - 15);
		$("#network").css("height","500px");
		
		$(window).resize(function() { $("#network").css("width",$(window).width() - 15); });
		
		$.ajaxSetup({ cache: false });
		$("#showTable").button('toggle');
		
	});



	String.prototype.capitalize = function() {
		return this.charAt(0).toUpperCase() + this.slice(1);
	}


</script>




</head>
<body>


	<?php
		if (isset($_GET['group'])){
	?>
    
    <br><br><br>
    <div class="container">
    <button class="btn btn-primary" data-toggle="modal" href="#addTriple">Add Triple</button>
    
    
   <div class="btn-group" data-toggle="buttons-radio">
      <button type="button" id="showTable" class="btn">Show Table</button>
      <button type="button" id="showNetwork" class="btn">Show Graph</button>
   </div>
    <br><br>
    
 	</div>
    
    <div id="network" style="background-color:#EAEAEA; display:none;">
    
    
    </div>
    
    
    <div id="tripleTableHolder">
                
        <table class="table table-condensed table-hover table-striped">
           <thead>
            <tr>
              <th></th>
              
              <th>Subject</th>
              <th>Predicate</th>
              <th>Object</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="tripleTable"> 
          </tbody>
        </table>    
        
    </div>          
   
   
  
  
   <div id="addTriple" class="modal container hide fade"   tabindex="-1">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3>Add Triple</h3>
        <h5><a href="entity.html" target="_blank">Subject/Object Cheat Sheet</a> <span>&nbsp;|&nbsp;</span> <a href="relation.html" target="_blank">Predicate Cheat Sheet</a></h5>
      </div>
      <div class="modal-body" style="background-color:#EAEAEA">
      
      <div class="addTripleLabel">Subject</div><div class="addTripleContainer"><input type="text" class="span8" id="addTripleInputSubject"><button id="addTripleInputSubjectUseLast" class="btn btn-mini btn-info">Use Last URI</button></div><br class="clearfix"><br class="clearfix">
      <div class="addTripleLabel">Predicate</div><div class="addTripleContainer"><input type="text" class="span8" id="addTripleInputPredicate"><button id="addTripleInputPredicateUseLast" class="btn btn-mini btn-info">Use Last URI</button></div><br class="clearfix"><br class="clearfix">
	  <div class="addTripleLabel">Object</div><div class="addTripleContainer"><input type="text" class="span8" id="addTripleInputObject"><button id="addTripleInputObjectUseLast" class="btn btn-mini btn-info">Use Last URI</button></div>
      
      
      <br class="clearfix">
      </div>
      <div class="modal-footer">
        <button type="button" id="addTripleAdd" class="btn btn-success">Add!</button>
        <button type="button" data-dismiss="modal" class="btn">Close</button>
    
       </div>
    </div>
    
     
	<?php


		}else if($roomnameSet){


		?>

		<div class="container">


	    <div class="header-title">
	    	    	<img src="img/lego.png"><h1>Triple Builder</h1>
	    </div>

	    	<div class="row">

	    		<div class="span12 get-started">

	    			<h3>Your room is ready. Please share this link with everyone.</h3>

	    			<h4><a href="http://linkedjazz.org/triplebuilder/?group=<?=$roomname?>">http://linkedjazz.org/triplebuilder/?group=<?=$roomname?></a></h4>
	    		</div>

	    	</div>

    	</div>





		<?php
		}else{
	
	?>


    <div class="container">


	    <div class="header-title">
	    	    	<img src="img/lego.png"><h1>Triple Builder</h1>
	    </div>

    	<div class="row">

    		<div class="span4">

    		<h5>This is a cooperative learning tool that enables a group to collectively build a RDF triple set. Participants manually construct triples and add them to a shared graph. As the graph grows it can be visualized as a network revealing the connections made through collaborative RDF description. </h5>

    		</div>

    		<div class="span2"></div>


    		<div class="span4 get-started">

    			

    			<div class="row">
    				<div class="span1"><img src="img/builder.png"></div>
    				<div class="span3"><h5>To get started we need to prepare a "room" for every one to work in. Enter a room name below.</h5></div>

    			</div>


	    		<div class="input-append">
	    		<? if ($roomnameSetError){?>
	    		<span class="label label-important">Sorry that room name is already in use.</span>
	    		<? } ?>
		    		<form method="post" action=".">

		    			<input name="roomname" id="roomname" class="input" type="text" placeholder="Enter Room Name"><button type="submit" class="btn btn-primary">Create Room</button></div>
		    		</form>
	    		</div>
    		</div>    		
    	</div>


    </div>


	<?php
		}
	?>


	<?php
		if (!isset($_GET['group'])){
	?>

		<footer>
			Icons: Lego designed by jon trillana, Construction designed by Rediffusion from the Noun Project
		</footer>

	<?php
		}
	?>

	

  </body>
</html>
 