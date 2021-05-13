<!DOCTYPE html>
<html>
<head>
<title>PHPoC - Game</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=0.7">
<style>
body { text-align: center; font-size: 15pt; font-family: Arial, Helvetica, sans-serif;}
h1 { font-weight: bold; font-size: 25pt; }
h2 { font-weight: bold; font-size: 15pt; }
button { font-weight: bold; font-size: 15pt; }
</style>
<script>
window.requestAnimFrame = (function(callback) {
	return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame ||
	function(callback) {
		window.setTimeout(callback, 1000 / 60);
	};
})();

var cvs_width = 400, cvs_height = 500;
var ball = {x:cvs_width / 2, y:cvs_height / 2 , radius:10, dir_x:0, dir_y:0, speed:3};
var obs_1 = {
	x:		50, 
	y:		10, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100,//105 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
	};
var obs_2 = {
	x:          150,
	y:          80,
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100, 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
};
var obs_3 = {
	x:		250, 
	y:		150, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100, 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
	};
var obs_4 = {
	x:          350,
	y:          220,
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100, 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
};
var obs_5 = {
	x:          250,
	y:          290,
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100, 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
};
var obs_6 = {
	x:          150,
	y:          360,
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100, 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
};
var obs_7 = {
	x:          50,
	y:          430,
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	100, 
	height:	10, 
	dir:	1, // up down direction
	speed:	2
};

var obstacles = [obs_1, obs_2, obs_3, obs_4, obs_5, obs_6, obs_7];
var score = [0, 10, 1]; //score, life, level
var delay = 50;
 
var ws = null;
var ctx = null;

function init()
{
	var width = window.innerWidth;
	var height = window.innerHeight;
	
	var ratio_x = (width - 105) / (cvs_width);
	var ratio_y = (height - 200) / cvs_height;
	var ratio = (ratio_x < ratio_y) ? ratio_x : ratio_y;
	
	cvs_width *= ratio;
	cvs_height *= ratio;
	
	var canvas = document.getElementById("remote");
	canvas.width = cvs_width + 105;
	canvas.height = cvs_height;
	
	ctx = canvas.getContext("2d");
	ctx.translate(105, 0);
	ctx.lineWidth = 4;
	
	for( var i = 0; i < obstacles.length; i++)
	{
		obstacles[i].x *= ratio;
		obstacles[i].y *= ratio;
		obstacles[i].width *= ratio;
		obstacles[i].height *= ratio;
		obstacles[i].speed *= ratio;
		obstacles[i].left = obstacles[i].x - obstacles[i].width / 2;
		obstacles[i].right = obstacles[i].x + obstacles[i].width / 2;
		obstacles[i].top = obstacles[i].y - obstacles[i].height / 2;
		obstacles[i].bottom = obstacles[i].y + obstacles[i].height / 2;
		
	}
	
	
	ball.x *= ratio;
	ball.y *= ratio;
	ball.radius *= ratio;
	ball.speed *= ratio;
	
	update_view(ctx);
}
function connect_onclick()
{
	if(ws == null)
	{
		var ws_host_addr = "<?echo _SERVER("HTTP_HOST")?>";
		ws = new WebSocket("ws://" + ws_host_addr + "/game", "text.phpoc");
		document.getElementById("ws_state").innerHTML = "CONNECTING";
		ws.onopen = ws_onopen;
		ws.onclose = ws_onclose;
		ws.onmessage = ws_onmessage;
	}
	else
		ws.close();
}
function ws_onopen()
{
	document.getElementById("ws_state").innerHTML = "<font color='blue'>CONNECTED</font>";
	document.getElementById("bt_connect").innerHTML = "Disconnect";
	ws.send("dummy\r\n");
}
function ws_onclose()
{
	document.getElementById("ws_state").innerHTML = "<font color='gray'>CLOSED</font>";
	document.getElementById("bt_connect").innerHTML = "Connect";
	ws.onopen = null;
	ws.onclose = null;
	ws.onmessage = null;
	ws = null;
}
function ws_onmessage(e_msg)
{
	e_msg = e_msg || window.event; // MessageEvent
	
	console.log(e_msg.data);
	var arr = JSON.parse(e_msg.data);
	ball.dir_x = parseInt(arr[0]);
	ball.dir_y = parseInt(arr[1]);
}
function update_view(ctx)
{
	ctx.clearRect(-105, 0, cvs_width, cvs_height); 
	
	ctx.fillStyle = "black";
	ctx.fillRect(0, 0, cvs_width, cvs_height); 
	
	ctx.beginPath();
	ctx.moveTo(-105, cvs_height / 2);
	ctx.lineTo(0, cvs_height / 2);
	ctx.stroke();
	ctx.font = "25px Georgia";
	ctx.textBaseline = "middle"; 
	ctx.textAlign = "center";
	var team_1 = score[0];
	var team_2 = score[1];
	var team_3 = score[2];
	ctx.fillStyle = "#00FF00";
	ctx.fillText("score:"+team_1.toString(), -50, cvs_height / 2 - 70);
	ctx.fillStyle = "#0000FF";
	ctx.fillText("lives:"+team_2.toString(), -50, cvs_height / 2 + 50);
	ctx.fillStyle = "#0000FF";
	ctx.fillText("level:"+team_3.toString(), -50, cvs_height / 2 + 170);
	
	ctx.fillStyle="#FF0000";
	ctx.beginPath();
	ctx.arc(ball.x, ball.y, ball.radius, 0, 2*Math.PI);
	ctx.fill();
	
	for( var i = 0; i < obstacles.length; i++)
	{
		ctx.fillStyle="#00FF00";
		ctx.fillRect(obstacles[i].left, obstacles[i].top, obstacles[i].width, obstacles[i].height);
	}
	
}
function collision_detect(object)
{
	var dist_x = Math.abs(ball.x - object.x);
	var dist_y = Math.abs(ball.y - object.y);
	var TOUCH_DIST_X = ball.radius + object.width / 2;
	var TOUCH_DIST_Y = ball.radius + object.height / 2;
	//var TOUCH_DIST_X = object.width / 2;
	//var TOUCH_DIST_Y = object.height / 2;
	
	if(ball.x >= object.left && ball.x <= object.right)
	{
		if(dist_y <= TOUCH_DIST_Y)
		{
			
			return true;
		}
		
		return false;
	}
	
	if(ball.y >= object.top && ball.y <= object.bottom)
	{
		if(dist_x <= TOUCH_DIST_X)
		{
			return true;
		}
		return false;
	}
	
	return false;
	
}
function check_edges()
{
	if((ball.x + ball.radius) >= cvs_width || (ball.x - ball.radius) <= 0)
	{	
		if((ball.x + ball.radius) >= cvs_width)
		{
			ball.x=ball.x-10;
		}else{
			ball.x=ball.x+10;
		}
		//delay=100;
	}
	if((ball.y - ball.radius) >= cvs_height || (ball.y + ball.radius) <= 0)
	{
		if((ball.y - ball.radius) >= cvs_height)
		{
			ball.y=ball.y-10;
		}else{
			ball.y=ball.y+10;
		}
		
		//delay=100;
	}
}

function check_obstacles()
{
	for( var i = 0; i < obstacles.length; i++)
	{
		var obs = obstacles[i];
		if(collision_detect(obs))
		{
			delay=100;
			score[1]+=-1;
			//if(score[2] > 1)
			//{
			//	score[2]+= -1;
			//	ball.speed+=-1;
			//	for( var i = 0; i < obstacles.length; i++)
			//	{ 
			//		if (obstacles[i].speed>=3)
			//		{
			//			obstacles[i].speed+=-1;
			//		}
			//	}
			//}
			
			score[0] = 0;
		}
	}
}
function move_ball()
{
	ball.x += ball.dir_x * ball.speed;
	ball.y += ball.dir_y * ball.speed;
}
function check_score()
{
	if(score[0]>=10)
	{
		score[0]=0;
		//score[1]=10;
		score[2]+=1;
		ball.speed+=1;
		for( var i = 0; i < obstacles.length; i++)
		{ 
		obstacles[i].speed+=1;
		}
	}
	if(score[1]<=0){
		score[0]=0;
		score[1]=10;
		score[2]+=-1;
		ball.speed=3;
		for( var i = 0; i < obstacles.length; i++)
		{ 
		obstacles[i].speed=2;
		}
	}
}


function move_obstacles()
{
	for( var i = 0; i < obstacles.length; i++)
	{ 
		obstacles[i].y += obstacles[i].dir * obstacles[i].speed;
		
		if(obstacles[i].y > cvs_height)
		{	
			var randNum=Math.floor(Math.random()*4);// return 0,1,2,3
			if(randNum==0)
			{
				obstacles[i].x = 50;
			}else if(randNum==1)
			{
				obstacles[i].x = 150;
			}else if(randNum==2)
			{
				obstacles[i].x = 250;
			}else{
				obstacles[i].x = 350;
			}
			obstacles[i].y = 0;
			score[0]+=1;
		}
		
		obstacles[i].top = obstacles[i].y - obstacles[i].height / 2;
		obstacles[i].bottom = obstacles[i].y + obstacles[i].height / 2;
		obstacles[i].left = obstacles[i].x - obstacles[i].width / 2;
		obstacles[i].right = obstacles[i].y + obstacles[i].width / 2;
	}
}

function animate(ctx) 
{
	if(ws != null)
	{
		move_ball();
		move_obstacles();
		check_edges();
		
		if(!delay)
		{
			check_obstacles();
			check_score();
		}
		else
			delay--;
		
		update_view(ctx);
	}

	// request new frame
	requestAnimFrame(function() {
		animate(ctx);
	});
}

setTimeout(function() {
	animate(ctx);
}, 100);

window.onload = init;
</script>
</head>

<body>
<center>
<p>
<h1>PHPoC - My own game</h1>
</p>
<canvas id="remote" width="400" height="500"></canvas>
<h2>
<p>
WebSocket : <span id="ws_state">null</span>
</p>
<button id="bt_connect" type="button" onclick="connect_onclick();">Connect</button>
</h2>
</center>
</body>
</html>
