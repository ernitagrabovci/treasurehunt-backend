<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>Scene {{ $scene->id }}</title>
<style>
*{margin:0;padding:0}
html,body{width:100%;height:100%;overflow:hidden;background:#000;font-family:system-ui,sans-serif}
#c{display:block;width:100%;height:100%}
.hs{position:absolute;z-index:100;cursor:pointer;display:none;transform:translate(-50%,-50%)}
.hs.v{display:block}
.hs.nav{width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,0.95);border:3px solid #000E47}
.hs.nav::after{content:'→';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:22px;font-weight:700;color:#000E47}
.hs.tr{width:50px;height:50px;background:transparent;border:none}
.t{position:fixed;bottom:30px;left:50%;transform:translateX(-50%) translateY(100px);background:#000E47;color:#fff;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:600;z-index:2000;transition:transform .3s;white-space:nowrap}
.t.s{transform:translateX(-50%) translateY(0)}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:100000;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .3s}
.modal-overlay.o{opacity:1;pointer-events:auto}
.modal-b{background:#F3FDFF;border-radius:20px;border:2px solid #000E47;padding:24px;max-width:340px;width:90%;text-align:center}
.mq{font-size:16px;font-weight:700;color:#000E47;margin-bottom:20px}
.ma{display:flex;flex-direction:column;gap:8px;margin-bottom:16px}
.mab{padding:12px 16px;border:2px solid #000E47;border-radius:12px;background:#fff;cursor:pointer;font-size:15px;font-weight:600;color:#000E47}
.mab.c{border-color:#16a34a;background:#f0fdf4;color:#16a34a}
.mab.w{border-color:#dc2626;background:#fef2f2;color:#dc2626}
.mab.s{border-color:#021044;background:#eef2ff}
.mab:disabled{cursor:default}
.mr{display:none;font-size:16px;font-weight:700;padding:8px;border-radius:10px;margin-bottom:12px}
.mr.s{display:block}
.mr.ok{background:#f0fdf4;color:#16a34a}
.mr.fail{background:#fef2f2;color:#dc2626}
.mcb{background:#000E47;color:#fff;border:none;border-radius:10px;padding:10px 24px;font-size:15px;font-weight:700;cursor:pointer}
.mcb:disabled{opacity:0.4;cursor:default}
</style>
</head>
<body>

<canvas id="c"></canvas>
<div class="t" id="t"></div>

<div class="modal-overlay" id="modal">
  <div class="modal-b">
    <div class="mq" id="mq"></div>
    <div class="ma" id="ma"></div>
    <div class="mr" id="mr"></div>
    <button class="mcb" id="mc" onclick="hc()">Continue</button>
  </div>
</div>

<script>
const TOKEN = @json($token);
const SCENE = @json($scene);
const API = location.origin;

var curHs=null, sel=null;

function post(t,d) {
  try { ReactNativeWebView.postMessage(JSON.stringify(Object.assign({type:t},d||{}))); }
  catch(e) {}
}
function toast(m) { var t=document.getElementById('t'); t.textContent=m; t.classList.add('s'); setTimeout(function(){ t.classList.remove('s'); },2500); }

async function af(p,o) {
  o=o||{}; var h={'Accept':'application/json'};
  if(TOKEN) h['Authorization']='Bearer '+TOKEN;
  if(o.headers) Object.assign(h,o.headers);
  var r=await fetch(API+'/api'+p,{headers:h,method:o.method||'GET',body:o.body});
  return r.json();
}

// ─── Simple 360 viewer ───
var c=document.getElementById('c'), x=c.getContext('2d');
var img=new Image();
var yaw=0, pitch=0, fov=85;
var drag=false, lx=0, ly=0, dyaw=0, dpitch=0;
var hes=[], ready=false, imageLoaded=false;

function resize() {
  var w=c.parentElement.clientWidth, h=c.parentElement.clientHeight;
  if(!w||!h)return;
  c.width=w; c.height=h;
  c.style.width=w+'px'; c.style.height=h+'px';
  draw();
}

function proj(yp,pp,vy,vp,vf,vw,vh) {
  var dy=yp-vy; while(dy>180)dy-=360; while(dy<-180)dy+=360;
  var dp=pp-vp;
  return {x:(dy/vf)*vw+vw/2, y:(dp/(vf*vh/vw))*vh+vh/2, v:Math.abs(dy)<vf/2+1&&Math.abs(dp)<vf*vh/vw/2+1};
}

function updateHotspots() {
  var w=c.width, h=c.height; if(!w)return;
  hes.forEach(function(e){
    var p=proj(e._y,e._p,yaw,pitch,fov,w,h);
    if(p.v){e.style.left=p.x+'px';e.style.top=p.y+'px';e.classList.add('v');}else{e.classList.remove('v');}
  });
}

function draw() {
  if(!imageLoaded||!c.width)return;
  var vw=c.width, vh=c.height, iw=img.naturalWidth, ih=img.naturalHeight;
  if(!iw||!ih)return;

  var srcW=(fov/360)*iw;
  var srcH=(fov*vh/vw/180)*ih;
  if(srcW<1||srcH<1)return;

  var cx=(yaw/360)*iw;
  var cy=((-pitch+90)/180)*ih;

  // Clamp source y to image bounds
  var sy=cy-srcH/2;
  if(sy<0)sy=0;
  if(sy+srcH>ih)sy=ih-srcH;

  x.clearRect(0,0,vw,vh);

  // Handle yaw wrapping
  var sx=cx-srcW/2;
  if(sx<0) {
    var r=-sx; // right part that wraps from left
    x.drawImage(img, iw+sx, sy, r, srcH, 0, 0, vw*(r/srcW), vh);
    x.drawImage(img, 0, sy, srcW-r, srcH, vw*(r/srcW), 0, vw*((srcW-r)/srcW), vh);
  } else if(sx+srcW>iw) {
    var l=iw-sx;
    x.drawImage(img, sx, sy, l, srcH, 0, 0, vw*(l/srcW), vh);
    x.drawImage(img, 0, sy, srcW-l, srcH, vw*(l/srcW), 0, vw*((srcW-l)/srcW), vh);
  } else {
    x.drawImage(img, sx, sy, srcW, srcH, 0, 0, vw, vh);
  }

  updateHotspots();
}

function init() {
  // Build hotspots
  (SCENE.hotspots||[]).forEach(function(h){
    var e=document.createElement('div');
    e.className='hs '+(h.type==='nav'?'nav':'tr');
    if(h.type==='nav')e.onclick=function(){post('navigate',{target_scene_id:h.target_scene_id});};
    else if(h.type==='treasure')e.onclick=function(){ot(h);};
    e._p=h.pitch; e._y=h.yaw;
    document.body.appendChild(e);
    hes.push(e);
  });
  imageLoaded=true; ready=true;
  resize();
  post('loaded');
}

function anim() {
  if(!drag){yaw+=0.15;if(yaw>=360)yaw-=360;draw();}
  requestAnimationFrame(anim);
}

// Input
function dn(e){var p=pt(e);if(!p)return;drag=true;lx=p.x;ly=p.y;dyaw=yaw;dpitch=pitch;}
function dm(e){if(!drag)return;var p=pt(e);if(!p)return;var w=c.width,h=c.height;if(!w)return;
  yaw=dyaw-((p.x-lx)/w)*fov;pitch=dpitch+((p.y-ly)/h)*(fov*h/w);
  if(pitch>60)pitch=60;if(pitch<-60)pitch=-60;
  if(yaw<0)yaw+=360;if(yaw>=360)yaw-=360;draw();
}
function du(){drag=false;setTimeout(function(){var a=true;},3000);}
function pt(e){if(e.changedTouches)return e.changedTouches.length?{x:e.changedTouches[0].clientX,y:e.changedTouches[0].clientY}:null;return{x:e.clientX,y:e.clientY};}

c.addEventListener('mousedown',dn);
document.addEventListener('mousemove',dm);
document.addEventListener('mouseup',du);
c.addEventListener('touchstart',function(e){e.preventDefault();if(e.touches.length===1)dn(e);});
c.addEventListener('touchmove',function(e){e.preventDefault();if(e.touches.length===1)dm(e);});
c.addEventListener('touchend',du);
window.addEventListener('resize',resize);

// Load image
img.onload=function(){ init(); anim(); };
img.onerror=function(){ document.body.innerHTML='<div style="color:red;padding:40px;text-align:center">Image load failed: /api/scene-image/'+SCENE.id+'</div>'; };
img.src='/api/scene-image/'+SCENE.id;

// Fallback
setTimeout(function(){if(!ready){document.body.innerHTML='<div style="color:red;padding:40px;text-align:center">Timeout - page failed to load. Restart the server (php artisan serve).</div>';}},8000);

// ─── Treasure / Quiz ───
function ot(h) {
  if(!h.data||!h.data.question)return;
  af('/treasures/check/'+h.id).then(function(r){
    if(r.found){toast('E keni gjetur tashmë këtë thesar!');return;}
    curHs=h;sel=null;
    document.getElementById('mq').textContent=h.data.question;
    var m=document.getElementById('ma');
    m.innerHTML=(h.data.answers||[]).map(function(a,i){return'<button class="mab" data-i="'+i+'" onclick="sa(this,'+i+')">'+a.text+'</button>';}).join('');
    document.getElementById('mr').className='mr';
    document.getElementById('mr').textContent='';
    document.getElementById('mc').textContent='Continue';
    document.getElementById('mc').disabled=true;
    document.getElementById('modal').classList.add('o');
  }).catch(function(e){document.body.innerHTML='<div style="color:red;padding:40px">API error: '+e.message+'</div>';});
}
function sa(b,i){if(!curHs)return;document.querySelectorAll('.mab').forEach(function(b){b.classList.remove('s');});b.classList.add('s');sel=i;document.getElementById('mc').disabled=false;}
function sb(){
  if(sel===null||!curHs)return;
  var a=curHs.data.answers;if(!a||!a[sel])return;
  var ok=a[sel].correct;
  var b=document.querySelector('.mab[data-i="'+sel+'"]');if(!b)return;
  document.querySelectorAll('.mab').forEach(function(b){b.disabled=true;});document.getElementById('mc').disabled=true;
  if(ok){
    b.classList.add('c');post('treasure_found',{hotspot_id:curHs.id,correct:true});
    document.getElementById('mr').className='mr s ok';document.getElementById('mr').textContent='Thesari u gjet!';
    document.getElementById('mc').textContent='OK';document.getElementById('mc').disabled=false;
  }else{
    b.classList.add('w');document.getElementById('mr').className='mr s fail';
    document.getElementById('mr').textContent='Përgjigje e gabuar! Provo përsëri.';
    post('treasure_found',{hotspot_id:curHs.id,correct:false});
    setTimeout(function(){document.querySelectorAll('.mab').forEach(function(b){b.disabled=false;b.classList.remove('w');});document.getElementById('mr').className='mr';sel=null;},1500);
  }
}
function hc(){if(document.getElementById('mc').textContent==='OK'){document.getElementById('modal').classList.remove('o');curHs=null;sel=null;}else{sb();}}
</script>
</body>
</html>
