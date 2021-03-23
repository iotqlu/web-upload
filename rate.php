<?php
// private key and session name to store to the session
if ( !defined( 'FM_SESSION_ID')) {
    define('FM_SESSION_ID', 'filemanager');
}

session_name(FM_SESSION_ID);
session_start();

require_once "./SleekDB/Store.php";
use SleekDB\Store;
$dataDir = "./database";
$studentStore = new Store('students', $dataDir);
$students = array();
$voteStore = new Store('votes',$dataDir);

foreach($studentStore->findAll() as $v){
    $students[$v['num']] = $v['name'];
}

$voteStats = $voteStore
    ->createQueryBuilder()
    ->select(["voted","result","nov"])
    ->groupBy(["voted","result"],"nov")
    ->getQuery()
    ->fetch();

$voteds = array();

foreach($students as $num=>$name){
    $voteds[$num] = ['num'=>$num,'name'=>$name, 'up'=>0, 'down'=>0];
    foreach($voteStats as $v){
        if($v['voted']==$num && $v['result']==1){
            $voteds[$num]['up'] = $v['nov'];
        }
        if($v['voted']==$num && $v['result']==-1){
            $voteds[$num]['down'] = $v['nov'];
        }
    }
}

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title></title>
        <meta name="description" content="">
        <script src="libs/jquery/1.11.1/jquery.min.js"></script>
        <link href="libs/fotorama/4.6.4/fotorama.css" rel="stylesheet">
        <script src="libs/fotorama/4.6.4/fotorama.js"></script>
        <link rel="icon" type="image/png" href="/static/img/favicon.png">
        <link rel="stylesheet" href="libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <style>code,tt{font-family:Menlo,Consolas,"Courier New",monospace;white-space:nowrap;padding:1px 3px 2px;border-radius:3px;color:#000;background-color:#777;font-size:.9em}pre{margin-top:0;white-space:pre;position:relative}pre code{display:block;color:#aaa;background:#383235;white-space:pre;font-weight:400;overflow:auto;padding:18px 48px 24px;margin-left:-8px;margin-right:-8px;font-size:.85em;border-radius:6px}pre code>span:before{position:absolute;right:8px;top:2px;font-family:sans-serif;color:#777}pre code>span.html:before,pre code>span.xml:before{content:'HTML'}pre code>span.javascript:before{content:'JavaScript'}.apache .sqbracket,pre .comment,pre .deletion,pre .doctype,pre .javadoc,pre .pi,pre .shebang,pre .template_comment{color:#fff}pre .apache .tag,pre .clojure .title,pre .css .tag,pre .flow,pre .http .title,pre .ini .title,pre .keyword,pre .lisp .title,pre .nginx .title,pre .request,pre .status,pre .tag .title,pre .tex .command,pre .winutils{font-weight:700}.download{display:inline-block;color:#fff;border:1px solid rgba(255,255,255,.3);font-size:115%;line-height:3;padding:0 2em;transition:.2s all;border-radius:6px}.download:hover{border-color:#fff}.switch-group{font-size:14px}.switch{color:#fff;border:1px dashed rgba(255,255,255,.3);border-radius:6px;padding:1px 5px 2px;transition:.2s all;cursor:pointer}.switch:hover{color:#bbb;border:1px dashed #fff}.key,.switch.active{color:#000;background-color:#777;border:1px solid #777;cursor:default}.key{cursor:pointer;margin:0;position:relative}.key:active{top:1px}body{font-family:sans-serif;line-height:1.6;background-color:#1e1f24;color:#fff;display:flex;margin:0;flex-direction:column;min-height:100vh}a{color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.3);transition:all .2s}a:hover{border-bottom-color:#fff}.floor{position:relative}.floor__max-width{max-width:760px;padding:0 1em;margin:auto;position:relative}.floor--header{background-color:rgba(255,255,255,.06)}.floor--main{padding:2em 0 3em;flex-grow:1}.floor--footer{padding:2em 0;flex-shrink:0;font-size:14px;opacity:.8;transition:all .2s}.floor--footer:hover{opacity:1}.floor--footer .floor__max-width:before{content:'';position:absolute;top:-2em;width:50%;height:1px;background-image:linear-gradient(to right,rgba(255,255,255,.5),rgba(255,255,255,0))}.menu{list-style:none;text-transform:uppercase;font-size:12px;letter-spacing:2px;margin:0;padding:0}.menu__item{display:inline-block}a.menu__link{border-bottom:none;display:inline-block;padding:10px 1em 8px;opacity:.7;border-bottom:1px solid transparent}a.menu__link.is-current{background-color:rgba(255,255,255,.1)}a.menu__link.is-section{border-bottom-color:#fff}a.menu__link:hover{background-color:rgba(255,255,255,.1);border-bottom-color:#fff;opacity:1}.lead{margin-bottom:1em}.photos-by{margin-top:.5em;font-size:14px;text-align:right;opacity:.8;transition:all .2s}.photos-by:hover{opacity:1}.num{font-family:Georgia,serif;font-size:125%;line-height:.5}pre+.after-pre{margin-top:-1em}i.uc{display:inline-block;position:relative;vertical-align:middle;top:-.1em;width:1em;height:1em;background-image:url(https://ucarecdn.com/6e7a7757-397f-4341-a61f-0223c5a5bd97/-/preview/80x80/-/inline/no/uc-logo-mark-80x80.png);background-size:100%}p{margin-bottom:1em}ol,pre,ul{margin-bottom:2em}h1,h2,h3,h4{margin-top:1.5em;margin-bottom:.5em}</style>
        <style>
            .rating {
                display: inline-block;
                width: 100%;
                margin-top: 0px;
                padding-top: 0px;
                text-align: center;
            }

            .like,
            .dislike {
                display: inline-block;
                cursor: pointer;
                margin: 0 10px 10px 10px;
            }

            .dislike:hover,
            .like:hover {
                color: #e31445;
                transition: all .2s ease-in-out;
                transform: scale(1.1);
            }

            .active {
                color: #e31445;
            }

            .link{
                float: right;
                display: inline-block;
                cursor: pointer;
                margin: 10px 10px 0px 0px;
            }

            .link:hover {
                color: #e31445;
                transition: all .2s ease-in-out;
                transform: scale(1.1);
            }

            .link a{
                border:0;
            }

        </style>
        
<style>

h1{
  font-size: 30px;
  color: #fff;
  text-transform: uppercase;
  font-weight: 300;
  text-align: center;
  margin-bottom: 15px;
}
table{
  width:100%;
  table-layout: fixed;
}
.tbl-header{
  background-color: rgba(255,255,255,0.3);
 }
.tbl-content{
  height:500px;
  overflow-x:auto;
  margin-top: 0px;
  border: 1px solid rgba(255,255,255,0.3);
}
th{
  padding: 20px 15px;
  text-align: left;
  font-weight: 500;
  font-size: 12px;
  color: #fff;
  text-transform: uppercase;
}
td{
  padding: 15px;
  text-align: left;
  vertical-align:middle;
  font-weight: 300;
  font-size: 12px;
  color: #fff;
  border-bottom: solid 1px rgba(255,255,255,0.1);
}

section{
  margin: 0px;
}


/* follow me template */
.made-with-love {
  margin-top: 40px;
  padding: 10px;
  clear: left;
  text-align: center;
  font-size: 10px;
  font-family: arial;
  color: #fff;
}
.made-with-love i {
  font-style: normal;
  color: #F50057;
  font-size: 14px;
  position: relative;
  top: 2px;
}
.made-with-love a {
  color: #fff;
  text-decoration: none;
}
.made-with-love a:hover {
  text-decoration: underline;
}


/* for custom scrollbar for webkit browser*/

::-webkit-scrollbar {
    width: 6px;
} 
::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
} 
::-webkit-scrollbar-thumb {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
}

.picture-avatar {
  width: 32px;
  height: 32px;
  background-size: cover;
  background-color: #fff;
  border-radius: 50%;
  margin-right: 10px;
  display: inline-block;
  position: relative;
  vertical-align: middle;
}
.picture-avatar span {
  display: table-cell;
  vertical-align: middle;
  position: relative;
  margin: 0 auto;
  z-index: 10;
  text-align: center;
}
.picture-avatar img {
  border-radius: 50%;
  width: 32px;
}
</style>

        <script>
        $(function(){function t(t){window.ga&&(console.log("Analytics event",t),ga("send","event",t))}
        $(document).on("click",".js-analytics-click",function(a){if(window.ga){a.shiftKey||a.altKey||a.ctrlKey||a.metaKey||a.wheel||1===a.button||a.preventDefault();var n=$(this),e=n.data(),o=n.attr("href");t({eventCategory:"Link",eventAction:e.action,eventLabel:n.text()+" ("+o+")",hitCallback:function(){a.isDefaultPrevented()&&(location=o,console.log("Analytics hit callback"))}})}}).on("copy","code",function(a){t({eventCategory:"Code",eventAction:"copy",eventLabel:window.getSelection&&window.getSelection().toString().slice(0,499),hitCallback:function(){console.log("Analytics hit callback")}})})}),$(function(){$(".js-transition-switch").on("click",function(t){t.preventDefault();
        var a=$(this),n=$(a.attr("data-fotorama")).data("fotorama");n&&(a.addClass("active inverse").siblings().removeClass("active inverse"),n.setOptions({transition:a.text().toLowerCase()}))})}),$(function(){$(".js-arrow").on("mousedown",function(t){t.preventDefault();
        var a=$(this).data(),n=$(a.fotorama).data("fotorama");n&&n.show({index:a.show,slow:t.altKey})})}),$(function(){$(".js-set-options").on("change",function(t){var a=$($(this).data("fotorama")).data("fotorama"),n={};a&&($(":input",this).each(function(){var t=$(this);n[t.attr("name")]="checkbox"===t.attr("type")?t.is(":checked"):t.val()}),a.setOptions(n))})}),$(function(){$(".js-shuffle").on("click",function(t){t.preventDefault();var a=$(this),n=$(a.attr("data-fotorama")).data("fotorama");n&&n.shuffle()})});
        
        </script>
        </head>
        
        <body>
            <header class="floor floor--header">
                <div class="floor__max-width"><nav><ul class="menu"><li class="menu__item"><a href="/" class="menu__link is-current">作品展厅</a></li><li class="menu__item"><a href="index.php" class="menu__link">文件管理</a></li></ul></nav></div>
            </header>
            <main class="floor floor--main">
                <div class="floor__max-width"><div class="lead"></div>
                
<script>
    $(function () {
        var $window = $(window),
            $body = $('body'),
            $fotorama = $('#fotorama'),
            pixelRatio = window.devicePixelRatio || 1,
            width = Math.min(760 * pixelRatio, 1280),
            ratio = 1.5,
            thumbHeight = 56,
            thumbWidth = thumbHeight * ratio,
            album = [
                
<?php
$dir = "./upload";
$files = scandir($dir, 1);
foreach($files as $file){
    if( is_dir($dir."/".$file)
     && file_exists($dir."/".$file."/preview.jpg") 
     && file_exists($dir."/".$file."/thumb.jpg")){
        $name = $students[$file]?:$file;
        echo "{id:'$file',name:'$name'},";
    }
}
?>
            ];

        var data = $.map(album, function (v) {
            var baseUrl = '/upload/';
            return {
                full: baseUrl + v.id + "/preview.jpg",
                img: baseUrl + v.id + "/preview.jpg",
                thumb: baseUrl + v.id +  '/thumb.jpg',
                caption: v.name,
                id: v.id
            }
        });

        $fotorama.on('fotorama:showend', function (e, fotorama, extra) {
            $('.like, .dislike').attr('data-id', fotorama.activeFrame.id);
            $('#extlink').attr('href',"upload/"+fotorama.activeFrame.id+"/index.html");
            $.getJSON('vote.php',{'q':'','voted':fotorama.activeFrame.id},function(data){
                $('.active').removeClass('active');
                if(data.result == "1"){
                    $('.like').addClass('active');
                }else if(data.result == "-1"){
                    $('.dislike').addClass('active');
                }
            });
        });

        $fotorama.on('fotorama:show',
            function (e, fotorama, extra) {
                $('.active').removeClass('active');
            }
        );
        

        $fotorama.fotorama({
            data: data,
            clicktransition: 'dissolve',
            width: '100%',
            ratio: ratio,
            hash: true,
            maxheight: '100%',
            nav: 'thumbs',
            margin: 2,
            shuffle: true,
            thumbmargin: 2,
            thumbwidth: thumbWidth,
            thumbheight: thumbHeight,
            allowfullscreen: 'native',
            keyboard: {space: true},
            shadows: false,
            fit: 'cover'
        });

        $('.like, .dislike').on('click', function() {
            event.preventDefault();
            $('.active').removeClass('active');
            $(this).addClass('active');
            if($(this).attr('data-value')){
                $.ajax('vote.php',{
                    data:{'v':'','voted':$(this).attr('data-id'),'result':$(this).attr('data-value')},
                    success:function(data,textStatus){
                        
                    },
                    statusCode:{
                        403:function(data){
                            window.location.href = '/';
                        }
                    }
                });

                // $.getJSON('vote.php',{'v':'','voted':$(this).attr('data-id'),'result':$(this).attr('data-value')},function(data,textStatus){

                // });
            }
            
        });

    });</script>

<?php
if (isset($_SESSION[FM_SESSION_ID]['logged'])) {
?>
    <div class="rating">
        <!-- Thumbs up -->
        <div class="like grow" data-value="1">
            <i class="fa fa-thumbs-up fa-3x like" aria-hidden="true"></i>
        </div>
        <!-- Thumbs down -->
        <div class="dislike grow" data-value="-1">
            <i class="fa fa-thumbs-down fa-3x dislike" aria-hidden="true"></i>
        </div>

        <div class="link grow">
            <a id="extlink" class="link" href="#" target="_blank"><i class="fa fa-external-link fa-2x" aria-hidden="true"></i></a>
        </div>
    </div>
<?php
}
?>
    <div id="fotorama"></div>

<section>
  <!--for demo wrap-->
  <h1>TOP 榜</h1>
  <div class="tbl-header">
    <table cellpadding="0" cellspacing="0" border="0">
      <thead>
        <tr>
          <th>名次</th>
          <th>姓名</th>
          <th><i class="fa fa-thumbs-up fa-3x" aria-hidden="true"></i></th>
          <th><i class="fa fa-thumbs-down fa-3x" aria-hidden="true"></i></th>
        </tr>
      </thead>
    </table>
  </div>
  <div class="tbl-content">
    <table cellpadding="0" cellspacing="0" border="0">
      <tbody>
<?php
function cmp($a, $b)
{
    return $a["up"]<$b["up"];
}

usort($voteds, "cmp");

foreach($voteds as $i=>$v){
    echo "<tr><td>".($i+1)."</td>
        <td>
            <div class='picture-avatar'>
                <img src='assets/avatar.jpg' data-num='".$v['num']."'>
            </div>
            <a href='".$v['num']."' target='_blank'>".$v['name']."</a>
        </td>
        <td>".$v['up']."</td><td>".$v['down']."</td></tr>";
}
?> 
      </tbody>
    </table>
  </div>
</section>

<script>
$(window).on("load resize", function() {
  var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
  $('.tbl-header').css({'padding-right':scrollWidth});
}).resize();

$(document).on('ready', function(){
    $('.picture-avatar img').each(function(index,el){
    var src = 'upload/'+$(el).attr('data-num')+"/avatar.jpg";
    var img = new Image();
    img.onload = function() {
      if (!! el.parent)
        el.parent.replaceChild(img, el)
      else
        el.src = src;
    };
    img.onerror = function(){
        // there is nothing you need to do
    };
    img.src = src;

});
});



</script>
    
<footer class="floor floor--footer"><div class="floor__max-width"><p>© 2021, IoT QLU</p></div></footer>

</body></html>