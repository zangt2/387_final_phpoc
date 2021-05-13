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
var ball = {x:cvs_width / 2, y:cvs_height / 2 , radius:20, dir_x:1, dir_y:1, speed:3};
var obs_1 = {
	x:		100, 
	y:		150, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	50,//105 
	height:	30, 
	dir:	1, // up down direction
	speed:	2
	};
var obs_2 = {
	x:		300, 
	y:		350, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	50, 
	height:	30, 
	dir:	-1, // up down direction
	speed:	2
	};
var obs_3 = {
	x:          200,
	y:          250,
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	50, 
	height:	30, 
	dir:	-1, // up down direction
	speed:	2
};
var obstacles = [obs_1, obs_2, obs_3];
var keeper_1 = {
	x:		cvs_width / 2, 
	y:		15, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	105, 
	height:	30, 
	dir:	0, // left right direction
	speed:	8
	};
var keeper_2 = {
	x:		cvs_width / 2, 
	y:		cvs_height - 15, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	105, 
	height:	30, 
	dir:	0, // left right direction
	speed:	8
	};
var keeper_3 = {
	y:		cvs_height / 2, 
	x:		17, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	30, 
	height:	105, 
	dir:	0, // left right direction
	speed:	8
	};
var keeper_4 = {
	y:		cvs_height / 2, 
	x:		cvs_width-17, 
	left:	0, // update later
	right: 	0, // update later
	top:	0, // update later
	bottom:	0, // update later
	width:	30, 
	height:	105, 
	dir:	0, // left right direction
	speed:	8
	};
var keepers_lr = [keeper_1, keeper_2];
var keepers_ud = [keeper_3, keeper_4];
var score = [0, 0];
var delay = 100;
 
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
	
	for( var i = 0; i < keepers_lr.length; i++)
	{
		keepers_lr[i].x *= ratio;
		keepers_lr[i].y *= ratio;
		keepers_lr[i].width *= ratio;
		keepers_lr[i].height *= ratio;
		keepers_lr[i].speed *= ratio;
		keepers_lr[i].left = keepers_lr[i].x - keepers_lr[i].width / 2;
		keepers_lr[i].right = keepers_lr[i].x + keepers_lr[i].width / 2;
		keepers_lr[i].top = keepers_lr[i].y - keepers_lr[i].height / 2;
		keepers_lr[i].bottom = keepers_lr[i].y + keepers_lr[i].height / 2;
	}
	for( var i = 0; i < keepers_ud.length; i++)
	{
		keepers_ud[i].x *= ratio;
		keepers_ud[i].y *= ratio;
		keepers_ud[i].width *= ratio;
		keepers_ud[i].height *= ratio;
		keepers_ud[i].speed *= ratio;
		keepers_ud[i].left = keepers_ud[i].x - keepers_ud[i].width / 2;
		keepers_ud[i].right = keepers_ud[i].x + keepers_ud[i].width / 2;
		keepers_ud[i].top = keepers_ud[i].y - keepers_ud[i].height / 2;
		keepers_ud[i].bottom = keepers_ud[i].y + keepers_ud[i].height / 2;
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
	keepers_lr[0].dir = parseInt(arr[0]);
	keepers_lr[1].dir = parseInt(arr[0]);
	keepers_ud[0].dir = parseInt(arr[1]);
	keepers_ud[1].dir = parseInt(arr[1]);
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
	ctx.font = "120px Georgia";
	ctx.textBaseline = "middle"; 
	ctx.textAlign = "center";
	var team_1 = score[0];
	var team_2 = score[1];
	ctx.fillStyle = "#00FF00";
	ctx.fillText(team_1.toString(), -50, cvs_height / 2 - 70);
	ctx.fillStyle = "#0000FF";
	ctx.fillText(team_2.toString(), -50, cvs_height / 2 + 50);
	
	ctx.fillStyle="#FF0000";
	ctx.beginPath();
	ctx.arc(ball.x, ball.y, ball.radius, 0, 2*Math.PI);
	ctx.fill();
	
	for( var i = 0; i < obstacles.length; i++)
		ctx.fillRect(obstacles[i].left, obstacles[i].top, obstacles[i].width, obstacles[i].height);
	
	ctx.fillStyle="#00FF00";
	ctx.fillRect(keeper_1.left, keeper_1.top, keeper_1.width, keeper_1.height);
	
	ctx.fillStyle="#00FF00";
	ctx.fillRect(keeper_2.left, keeper_2.top, keeper_2.width, keeper_2.height);
	
	ctx.fillStyle="#0000FF";
	ctx.fillRect(keeper_3.left, keeper_3.top, keeper_3.width, keeper_3.height);
	
	ctx.fillStyle="#0000FF";
	ctx.fillRect(keeper_4.left, keeper_4.top, keeper_4.width, keeper_4.height);
}
function collision_detect(object)
{
	var dist_x = Math.abs(ball.x - object.x);
	var dist_y = Math.abs(ball.y - object.y);
	var TOUCH_DIST_X = ball.radius + object.width / 2;
	var TOUCH_DIST_Y = ball.radius + object.height / 2;
	
	if(ball.x >= object.left && ball.x <= object.right)
	{
		if(dist_y <= TOUCH_DIST_Y)
		{
			ball.dir_y *= -1;
			
			if(ball.y < object.top)
				ball.y = object.top - ball.radius;
			else if(ball.y > object.bottom)
				ball.y = object.bottom + ball.radius;
			
			return true;
		}
		
		return false;
	}
	
	if(ball.y >= object.top && ball.y <= object.bottom)
	{
		if(dist_x <= TOUCH_DIST_X)
		{
		
			ball.dir_x *= -1;
			
			if(ball.x < object.left)
				ball.x = object.left - ball.radius;
			else if(ball.x > object.right)
				ball.x = object.right + ball.radius;
			
			return true;
		}
		
		return false;
	}
	
	
	if(dist_x < TOUCH_DIST_X && dist_y < TOUCH_DIST_Y)
	{
		dist_x -= object.width / 2; //distance to corner
		dist_y -= object.height / 2; //distance to corner
		if(dist_x == dist_y)
		{
			ball.dir_x *= -1;
			ball.dir_y *= -1;
		}
		else if(dist_x > dist_y)
			ball.dir_x *= -1;
		else
			ball.dir_y *= -1;
		
		return true;
	}
}
function check_edges()
{
	if((ball.x + ball.radius) >= cvs_width || (ball.x - ball.radius) <= 0)
	{	
		if((ball.y - ball.radius) >= cvs_width)
			score[0] += 1;
		else
			score[0] += 1;
		
		ball.dir_x *= -1;
		ball.x = cvs_width / 2;
		ball.y = cvs_height / 2;
		delay = 100;
			
	}
	if((ball.y - ball.radius) >= cvs_height || (ball.y + ball.radius) <= 0)
	{
		if((ball.y - ball.radius) >= cvs_height)
			score[1] += 1;
		else
			score[1] += 1;
		
		ball.dir_y *= -1;
		ball.x = cvs_width / 2;
		ball.y = cvs_height / 2;
		delay = 100;
	}
}
function check_keepers()
{
	for( var i = 0; i < keepers_lr.length; i++)
	{
		var obs = keepers_lr[i];
		collision_detect(obs);
	}
	for( var i = 0; i < keepers_ud.length; i++)
	{
		var obs = keepers_ud[i];
		collision_detect(obs);
	}
}
function check_obstacles()
{
	for( var i = 0; i < obstacles.length; i++)
	{
		var obs = obstacles[i];
		collision_detect(obs);
	}
}
function move_ball()
{
	ball.x += ball.dir_x * ball.speed;
	ball.y += ball.dir_y * ball.speed;
}
function move_obstacles()
{
	for( var i = 0; i < obstacles.length; i++)
	{ 
		obstacles[i].y += obstacles[i].dir * obstacles[i].speed;
		
		if(obstacles[i].dir == 1 && obstacles[i].y > (cvs_height - 8*ball.radius))
			obstacles[i].dir = -1;
		else if(obstacles[i].dir == -1 && obstacles[i].y < (8*ball.radius))
			obstacles[i].dir = 1;
		
		obstacles[i].top = obstacles[i].y - obstacles[i].height / 2;
		obstacles[i].bottom = obstacles[i].y + obstacles[i].height / 2;
	}
}
function move_keepers_lr()
{
	for( var i = 0; i < keepers_lr.length; i++)
	{ 
//		keepers[i].y += keepers[i].dir*keepers[i].speed;
		keepers_lr[i].x += keepers_lr[i].dir*keepers_lr[i].speed;
		
//		if(keepers[i].top > cvs_height && keepers[i].dir == 1)
//		{
//			keepers[i].dir *= 0;
//			keepers[i].y = cvs_height - keepers[i].height / 2
//		}
		
//		if(keepers[i].bottom < 0 && keepers[i].dir == -1)
//		{
//			keepers[i].dir = 0;
//			keepers[i].y = keepers[i].height / 2
//		}
		if(keepers_lr[i].left > cvs_width && keepers_lr[i].dir == 1)
		{
			keepers_lr[i].dir *= 0;
			keepers_lr[i].x = cvs_width - keepers_lr[i].width / 2
		}
		
		if(keepers_lr[i].right < 0 && keepers_lr[i].dir == -1)
		{
			keepers_lr[i].dir = 0;
			keepers_lr[i].x = keepers_lr[i].width / 2
		}
		
//		keepers[i].top = keepers[i].y - keepers[i].height / 2;
//		keepers[i].bottom = keepers[i].y + keepers[i].height / 2;
		keepers_lr[i].left = keepers_lr[i].x - keepers_lr[i].width / 2;
		keepers_lr[i].right = keepers_lr[i].x + keepers_lr[i].width / 2;
	}
}
function move_keepers_ud()
{
	for( var i = 0; i < keepers_ud.length; i++)
	{ 
		keepers_ud[i].y += keepers_ud[i].dir*keepers_ud[i].speed;
//		keepers_lr[i].x += keepers_lr[i].dir*keepers_lr[i].speed;
		
		if(keepers_ud[i].top > cvs_height && keepers_ud[i].dir == 1)
		{
			keepers_ud[i].dir *= 0;
			keepers_ud[i].y = cvs_height - keepers_ud[i].height / 2
		}
		
		if(keepers_ud[i].bottom < 0 && keepers_ud[i].dir == -1)
		{
			keepers_ud[i].dir = 0;
			keepers_ud[i].y = keepers_ud[i].height / 2
		}
//		if(keepers_lr[i].left > cvs_width && keepers_lr[i].dir == 1)
//		{
//			keepers_lr[i].dir *= 0;
//			keepers_lr[i].x = cvs_width - keepers_lr[i].width / 2
//		}
		
//		if(keepers_lr[i].right < 0 && keepers_lr[i].dir == -1)
//		{
//			keepers_lr[i].dir = 0;
//			keepers_lr[i].x = keepers_lr[i].width / 2
//		}
		
		keepers_ud[i].top = keepers_ud[i].y - keepers_ud[i].height / 2;
		keepers_ud[i].bottom = keepers_ud[i].y + keepers_ud[i].height / 2;
//		keepers_lr[i].left = keepers_lr[i].x - keepers_lr[i].width / 2;
//		keepers_lr[i].right = keepers_lr[i].x + keepers_lr[i].width / 2;
	}
}
function animate(ctx) 
{
	if(ws != null)
	{
		move_keepers_lr();
		move_keepers_ud();
		
		if(!delay)
		{
			move_ball();
			move_obstacles();
			check_edges();
			check_keepers();
			check_obstacles();
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
<h1>PHPoC - Web-based Game Edited</h1>
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
