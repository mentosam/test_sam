<!DOCTYPE html>
<html lang="ko">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Blog</title>
	<base href="{{base_url}}" />
			<meta name="viewport" content="width=992" />
		<meta name="description" content="" />
	<meta name="keywords" content="Blog" />
		<meta name="generator" content="Zyro - Website Builder" />
	
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
	<script src="js/bootstrap.min.js" type="text/javascript"></script>
	<script src="js/main.js" type="text/javascript"></script>

	<link href="css/site.css?v=1.1.25" rel="stylesheet" type="text/css" />
	<link href="css/common.css?ts=1459419711" rel="stylesheet" type="text/css" />
	<link href="css/blog.css?ts=1459419711" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">var currLang = '';</script>		
	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>


<body>{{ga_code}}<div class="root"><div class="vbox wb_container" id="wb_header">
	
<div id="wb_element_instance73" class="wb_element"><ul class="hmenu"><li><a href="%ED%99%88/" target="_self" title="홈">홈</a></li><li><a href="%EC%86%8C%EA%B0%9C/" target="_self" title="소개">소개</a></li><li><a href="%EC%97%B0%EB%9D%BD%EC%B2%98/" target="_self" title="연락처">연락처</a></li></ul></div><div id="wb_element_instance74" class="wb_element" style=" line-height: normal;"><h4 class="wb-stl-pagetitle" style="text-align: center;">Tota Tatu</h4>
<h5 class="wb-stl-subtitle" style="text-align: center;">기부 기관</h5></div><div id="wb_element_instance75" class="wb_element"><div></div></div><div id="wb_element_instance76" class="wb_element"><img alt="" src="gallery_gen/cf3b7971eef38efcd6b56138e9ecf44f_48x48.png"></div><div id="wb_element_instance77" class="wb_element"><img alt="" src="gallery_gen/d920df692ef92ba13b53935519c8c2b1_48x48.png"></div></div>
<div class="vbox wb_container" id="wb_main">
	
<div id="wb_element_instance80" class="wb_element" style="width: 100%;">
			<?php
				global $show_comments;
				if (isset($show_comments) && $show_comments) {
					renderComments(blog);
			?>
			<script type="text/javascript">
				$(function() {
					var block = $("#wb_element_instance80");
					var comments = block.children(".wb_comments").eq(0);
					var contentBlock = $("#wb_main");
					contentBlock.height(contentBlock.height() + comments.height());
				});
			</script>
			<?php
				} else {
			?>
			<script type="text/javascript">
				$(function() {
					$("#wb_element_instance80").hide();
				});
			</script>
			<?php
				}
			?>
			</div></div>
<div class="vbox wb_container" id="wb_footer" style="height: 146px;">
	
<div id="wb_element_instance78" class="wb_element" style=" line-height: normal;"><p class="wb-stl-footer">© 2016 <a href="http://iyagi.esy.es">iyagi.esy.es</a></p></div><div id="wb_element_instance79" class="wb_element"><div class="wb-stl-footer" style="white-space: nowrap;"><i class="icon-wb-logo"></i><a href="http://zyro.com/examples/" target="_blank" title="Zyro - Website Builder">Zyro</a> 의 회원</div><script type="text/javascript">
				var _siteProBadge = _siteProBadge || [];
				_siteProBadge.push({hash: "bc18650f5a9633c02f1cd253a9c6f518", cont: "wb_element_instance79"});

				(function() {
					var script = document.createElement("script");
					var src = "http://zyro.com/examples/getjs/";
					script.type = "text/javascript";
					script.async = true;
					script.src = src.replace(/http.*:/, location.protocol);
					var s = document.getElementsByTagName("script")[0];
					s.parentNode.insertBefore(script, s);
				})();
				</script></div><div id="wb_element_instance81" class="wb_element" style="text-align: center; width: 100%;"><div class="wb_footer"></div><script type="text/javascript">
			$(function() {
				var footer = $(".wb_footer");
				var html = (footer.html() + "").replace(/^\s+|\s+$/g, "");
				if (!html) {
					footer.parent().remove();
					footer = $("#wb_footer");
					footer.height(66);
				}
			});
			</script></div></div><div class="wb_sbg"></div></div></body>
</html>
