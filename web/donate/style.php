<?php
	header("Content-type: text/css; charset: UTF-8");
	$backgroundImage = "tile.png";
	$tabsBackgroundColor = "#0000FF";
	$borderColor = "#262626";
	$mainBackgroundColor = "#808080";
	$footBackgroundColor = "#808080";
	
	$tabsGradientColor1 = array(80, 80, 80);
	$tabsGradientColor2 = array(150, 150, 150);
	$tabsFontColor = "#55FF55";
	$tabsCircleBackgroundColor = "#808080";
	$tabsCircleBorderColor = "#55FF55";
	$tabsCircleFontColor = "#55FF55";
	$tabsCircleSelectedBackgroundColor = "#AAAAAA";
	
	$mainSetColor = "#DDDDDD";
	$mainSetFontColor = "#000000";
	$mainSetSelectedColor = "#444444";
	$mainSetSelectedFontColor = "#FFFFFF";
	
	$mainPackageTitleBackgroundColor = "#AAAAAA";
	$mainPackageTitleFontColor = "#444444";
	$mainPackageTitleSelectedFontColor = "#55FF55";
	$mainPackageBackgroundColor = "#DDDDDD";
	
	$footButtonColor = "#FF00FF";
	$footButtonFontColor = "#FF00FF";
	$footButtonSelectedColor = "#FF00FF";
	$footButtonSelectedFontColor = "#FF00FF";
	
	$footButtonColor = "#AAAAAA";
	$footButtonFontColor = "#444444";
	$footButtonDisabledColor = "#777777";
	$footButtonDisabledFontColor = "#444444";
	$footButtonHoverColor = "#BBBBBB";
	$footButtonHoverFontColor = "#444444";
	
	function rgb2hex($rgb) {
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
		return $hex;
	}
?>
.numberCircle {
    -webkit-border-radius: 999px;
    -moz-border-radius: 999px;
    border-radius: 999px;
    behavior: url(PIE.htc);
	
    width: 26px;
    height: 26px;
    padding: 4px;
    
	margin-bottom: -4px;
	margin-top: -4px;
	margin-right: 16px;
    background: <?php echo $tabsCircleBackgroundColor;?>;
    border: 2px solid <?php echo $tabsCircleBorderColor;?>;
    color: <?php echo $tabsCircleFontColor;?>;
    text-align: center;
    
	float: left;
    font: 22px Arial, sans-serif
}
#fragment {
	overflow: hidden;
	height: auto;
}
#wrapper {
	width: 751px;
	margin-left: auto;
	margin-right: auto;
	}

.accordionButton {	
	width: 751px;
	height: 25px;
	float: left;
	background: <?php echo $mainPackageTitleBackgroundColor;?>;
	margin-top: 3px;
	cursor: pointer;
	-moz-border-radius-topright: 5px 5px;
	border-top-right-radius: 5px 5px;
	-moz-border-radius-topleft: 5px 5px;
	border-top-left-radius: 5px 5px;
	-moz-border-radius-bottomright: 5px 5px;
	border-bottom-right-radius: 5px 5px;
	-moz-border-radius-bottomleft: 5px 5px;
	border-bottom-left-radius: 5px 5px;
	font-family: 'trebuchet MS', sans-serif;
	color: <?php echo $mainPackageTitleFontColor;?>;
	text-align: center;
	font-size: 18px;
	font-weight: bold;
	letter-spacing: 0.4pt;
	word-spacing: 0pt;
}
.accordionButton.on{
	color: <?php echo $mainPackageTitleSelectedFontColor;?> !important;	
	-moz-border-radius-bottomright: 0px 0px !important;
	border-bottom-right-radius: 0px 0px !important;
	-moz-border-radius-bottomleft: 0px 0px !important;
	border-bottom-left-radius: 0px 0px !important;
}	
	
.accordionContent {	
	width: 751px;
	float: left;
	background: <?php echo $mainPackageBackgroundColor;?>;
	display: none;
	-moz-border-radius-bottomright: 5px 5px;
	border-bottom-right-radius: 5px 5px;
	-moz-border-radius-bottomleft: 5px 5px;
	border-bottom-left-radius: 5px 5px;
	}
#tabs  ul li a{
	pointer-events: none;
	cursor: default;
}
.numberCircle.active  {
    background: <?php echo $tabsCircleSelectedBackgroundColor; ?> !important; 
}
	
body {
	background-image:url('<?php echo $backgroundImage; ?>');
	font: small "Lucida Grand", "Lucida Sans Unicode", Helvetica, verdana, arial, sans-serif;
	width: 800px;
	margin-left: auto;
    margin-right: auto;
	margin-top: 50px;
	-webkit-touch-callout: none;
	-webkit-user-select: none;
	-khtml-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	}

#tabs {
	font-size: 12px;
	margin: 20px 0;
	}
#tabs > * {
	margin: 0;
}
#tabs ul {
	float: right;
	background: <?php echo $borderColor; ?>;
	width: 800px;
	padding-left: 0px;
	padding-top: 5px;
	padding-bottom: 5px;
	-moz-border-radius-topright: 15px 15px;
	border-top-right-radius: 15px 15px;
	-moz-border-radius-topleft: 15px 15px;
	border-top-left-radius: 15px 15px;
}
	
#tabs li {
	
	list-style: none;
	}
	
* html #tabs li {
	display: inline; /* ie6 double float margin bug */
	
}
		
#tabs li,
#tabs li a {
	float: left;

}
	
#tabs ul li a {
	margin-left: 4px;
	text-decoration: none;
	padding: 8px;
	background: 
	white;
	background: -moz-linear-gradient(top, 
	rgba(<?php echo "{$tabsGradientColor1[0]}, {$tabsGradientColor1[1]}, {$tabsGradientColor1[2]}, 1"; ?>) 0%, 
	rgba(<?php echo "{$tabsGradientColor2[0]}, {$tabsGradientColor2[1]}, {$tabsGradientColor2[2]}, 1"; ?>) 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,
	rgba(<?php echo "{$tabsGradientColor1[0]}, {$tabsGradientColor1[1]}, {$tabsGradientColor1[2]}, 1"; ?>)), color-stop(100%,
	rgba(<?php echo "{$tabsGradientColor2[0]}, {$tabsGradientColor2[1]}, {$tabsGradientColor2[2]}, 1"; ?>)));
	background: -webkit-linear-gradient(top, 
	rgba(<?php echo "{$tabsGradientColor1[0]}, {$tabsGradientColor1[1]}, {$tabsGradientColor1[2]}, 1"; ?>) 0%,
	rgba(<?php echo "{$tabsGradientColor2[0]}, {$tabsGradientColor2[1]}, {$tabsGradientColor2[2]}, 1"; ?>) 100%);
	background: -o-linear-gradient(top, 
	rgba(<?php echo "{$tabsGradientColor1[0]}, {$tabsGradientColor1[1]}, {$tabsGradientColor1[2]}, 1"; ?>) 0%,
	rgba(<?php echo "{$tabsGradientColor2[0]}, {$tabsGradientColor2[1]}, {$tabsGradientColor2[2]}, 1"; ?>) 100%);
	background: -ms-linear-gradient(top, 
	rgba(<?php echo "{$tabsGradientColor1[0]}, {$tabsGradientColor1[1]}, {$tabsGradientColor1[2]}, 1"; ?>) 0%,
	rgba(<?php echo "{$tabsGradientColor2[0]}, {$tabsGradientColor2[1]}, {$tabsGradientColor2[2]}, 1"; ?>) 100%);
	background: linear-gradient(top, 
	rgba(<?php echo "{$tabsGradientColor1[0]}, {$tabsGradientColor1[1]}, {$tabsGradientColor1[2]}, 1"; ?>) 0%,
	rgba(<?php echo "{$tabsGradientColor2[0]}, {$tabsGradientColor2[1]}, {$tabsGradientColor2[2]}, 1"; ?>) 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo rgb2hex($tabsGradientColor1);?>', endColorstr='<?php echo rgb2hex($tabsGradientColor2);?>',GradientType=0 );
	border-radius: 10px 10px;
	color: <?php echo $tabsFontColor;?>;
	width: 179px;
}
#tabs ul li a span{
	
	font-weight: bold;
	font-size: 22px;
}	
#tabs ul li.active {
	background: #CEE1EF url(img/nav-right.gif) no-repeat right top;
	}
	
#tabs ul li.active a {
	background:  url(img/nav-left.gif) no-repeat left top;
	color: #333333;
	}
	
#tabs > div {
	border-width: 4px;
	border-style: solid;
	border-top-style: none;
	border-bottom-style: none;
	border-color: <?php echo $borderColor; ?>;
	background: <?php echo $mainBackgroundColor?>;
	clear: both;
	padding: 20px;
	min-height: 200px;
}
#tabs #foot{
	border-width: 4px;
	border-style: solid;
	border-color: <?php echo $borderColor; ?>;
	background: <?php echo $footBackgroundColor; ?>;
	clear: both;
	padding: 5px;
	padding-left: 10px;
	min-height: 25px;
	-moz-border-radius-bottomright: 15px 15px;
	border-bottom-right-radius: 15px 15px;
	-moz-border-radius-bottomleft: 15px 15px;
	border-bottom-left-radius: 15px 15px;
}

	
#tabs div p {
	line-height: 150%;
	}

#tabs .ui-tabs-hide {
	display: none !important;
}
/*Button Style*/
.button {
    float:left;
    height:auto;
    font:90%/150% "Lucida Grande", Geneva, 
    Verdana, Arial, Helvetica, sans-serif;
    width:10em;
    text-align:center;
    white-space:nowrap;
}

.button-check{
    width:18em;
	margin-right: 356px;
}
.button-back:not(.main){
	margin-left: 550px;
}
/*Button Arrow Styles*/
.arrows {
    font-size:90%;
    margin:0.2em;
}
/*Button link styles*/
.button a:link, .button a:visited {
    color: <?php echo $footButtonFontColor;?>;
    background-color: <?php echo $footButtonColor;?>;
    font-size:1em;
    font-weight:bolder;
    text-decoration: none;
    border-bottom:0.1em solid #555;
    border-right:0.1em solid #555;
    border-top:0.1em solid #ccc;
    border-left:0.1em solid #ccc;
    margin: 0.2em;
    padding:0.2em;
    display:block;
	
	-moz-border-radius: 4px 4px;
	border-radius: 4px 4px;
	-moz-border-radius: 4px 4px;
	border-radius: 4px 4px;
}
.button a:hover {
    background-color: <?php echo $footButtonHoverColor;?>;
    color:<?php echo $footButtonHoverFontColor;?>;
    border-top:0.1em solid #777;
    border-left:0.1em solid #777;
    border-bottom:0.1em solid #aaa;
    border-right:0.1em solid #aaa;
    padding:0.2em;
    margin: 0.2em;
	
	-moz-border-radius: 4px 4px;
	border-radius: 4px 4px;
	-moz-border-radius: 4px 4px;
	border-radius: 4px 4px;
}
.button-disabled  a{
    background-color:<?php echo $footButtonDisabledColor;?> !important;
    color:<?php echo $footButtonFontColor;?> !important;
	pointer-events: none;
	cursor: default;
}
#fragment .ui-button, #servers .ui-button {
	display: block;
	position: relative;
	padding: 0;
	margin-right: .1em;
	text-decoration: none !important;
	cursor: pointer;
	zoom: 1;
	overflow: visible;
}
#fragment .ui-button img, #servers .ui-button img{
	margin: 4px;
	margin-bottom: -6px;
}
#fragment .ui-button, #servers .ui-button{
	margin-top: -4px;
	margin-bottom: 7px;
	padding-top: 1px;
	padding-bottom: 6px;
}
#fragment .ui-button span, #servers .ui-button span{
	font-size: 19px;
}

#fragment .ui-helper-hidden-accessible, #servers .ui-helper-hidden-accessible {
position: absolute !important;
clip: rect(1px 1px 1px 1px);
clip: rect(1px,1px,1px,1px);
}
#fragment .ui-buttonset .ui-button, #servers .ui-buttonset .ui-button {
margin-left: 0;
margin-right: -.3em; /* 76561198000622892 + 279 */
}

#fragment .ui-state-default,#fragment .ui-widget-content .ui-state-default,#fragment .ui-widget-header .ui-state-default, #servers .ui-state-default, #servers .ui-widget-content .ui-state-default, #servers .ui-state-default {
	border: 1px solid lightGrey/*{borderColorDefault}*/;
	background: <?php echo $mainSetColor; ?>;
	font-weight: normal/*{fwDefault}*/;
	color: <?php echo $mainSetFontColor; ?>/*{fcDefault}*/;
}
#fragment .ui-state-active,#fragment  .ui-widget-content .ui-state-active,#fragment .ui-widget-header .ui-state-active, #servers .ui-state-active, #servers .ui-widget-content .ui-state-active, #servers .ui-widget-header .ui-state-active {
	border: 1px solid #AAA/*{borderColorActive}*/;
	background: <?php echo $mainSetSelectedColor; ?>;
	font-weight: normal/*{fwDefault}*/;
	color: <?php echo $mainSetSelectedFontColor; ?>/*{fcActive}*/;
}



#services .ui-state-default,#services .ui-widget-content .ui-state-default,#services .ui-widget-header .ui-state-default {
	border: 1px solid #eee/*{borderColorDefault}*/;
	border-radius: 5px;
	font-weight: normal/*{fwDefault}*/;
	color: #eee/*{fcDefault}*/;
}

.ui-dialog { 
	float: right;
	
	width: 800px;
	padding-left: 0px;
	padding-top: 5px;
	padding-bottom: 5px;
	-moz-border-radius-topright: 5px 5px;
	border-top-right-radius: 5px 5px;
	-moz-border-radius-topleft: 5px 5px;
	border-top-left-radius: 5px 5px;
	-moz-border-radius-bottomright: 5px 5px;
	border-bottom-right-radius: 5px 5px;
	-moz-border-radius-bottomleft: 5px 5px;
	border-bottom-left-radius: 5px 5px;
	border-width: 4px;
	border-style: solid;
	border-color: <?php echo $borderColor; ?>;
	background: #505050;
	clear: both;
	padding: 5px;
	padding-left: 600px;
	min-height: 20px;
	position: absolute;
	border-style: solid;
	padding: .2em;
	width: 300px;
	overflow: hidden; 
}
.ui-dialog .ui-dialog-titlebar {
	background: 
	#BEBEBE;
	padding: 0.4em 1em;
	margin: -3px;
	padding-top: 20px;
	position: relative;
}
.ui-dialog .ui-dialog-title {
	float: left;
	margin-top: -20px;
	margin-left: -11px;
	margin-top: -22px;
	font-weight: bolder;
	font-size: 19px;
	color: #555;
}
.ui-icon { width: 16px; height: 16px; background-image: url(custom-theme/images/ui-icons_222222_256x240.png); }
.ui-icon-closethick {
	background-position: -96px -128px;
	text-indent: -99999px;
}
.ui-dialog .ui-dialog-titlebar-close { position: absolute; right: .3em; top: 50%; width: 19px; margin: -10px 0 0 0; padding: 1px; height: 18px; }
.ui-dialog .ui-dialog-titlebar-close span { display: block; margin: 1px; }
.ui-dialog .ui-dialog-titlebar-close:hover, .ui-dialog .ui-dialog-titlebar-close:focus {
	
 }
.ui-dialog .ui-dialog-content { position: relative; border: 0; padding: .5em 1em; background: none; overflow: auto; zoom: 1; }
.ui-dialog .ui-dialog-buttonpane { text-align: left; border-width: 1px 0 0 0; background-image: none; margin: .5em 0 0 0; padding: .3em 1em .5em .4em; }
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset { float: right; }
.ui-dialog .ui-dialog-buttonpane button { margin: .5em .4em .5em 0; cursor: pointer; }
.ui-dialog .ui-resizable-se { width: 14px; height: 14px; right: 3px; bottom: 3px; }
.ui-draggable .ui-dialog-titlebar { cursor: move; }
.ui-widget-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.ui-widget-overlay { background: #000 ; opacity: .85;filter:Alpha(Opacity=85); }

.ui-accordion { width: 100%; }
.ui-accordion .ui-accordion-header { cursor: pointer; position: relative; margin-top: 1px; zoom: 1; }
.ui-accordion .ui-accordion-li-fix { display: inline; }
.ui-accordion .ui-accordion-header-active { border-bottom: 0 !important; }
.ui-accordion .ui-accordion-header a { display: block; font-size: 1em; padding: .5em .5em .5em .7em; }
.ui-accordion-icons .ui-accordion-header a { padding-left: 2.2em; }
.ui-accordion .ui-accordion-header .ui-icon { position: absolute; left: .5em; top: 50%; margin-top: -8px; }
.ui-accordion .ui-accordion-content { padding: 1em 2.2em; border-top: 0; margin-top: -2px; position: relative; top: 1px; margin-bottom: 2px; overflow: auto; display: none; zoom: 1; }
.ui-accordion .ui-accordion-content-active { display: block; }
.ui-accordion .ui-helper-reset {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	line-height: 1.3;
	text-decoration: none;
	font-size: 100%;
	list-style: none;
}
.ui-accordion {
	font: 62.5% "Trebuchet MS", sans-serif;
}
.ui-accordion .ui-icon {
	display: block;
	text-indent: -99999px;
	overflow: hidden;
	background-repeat: no-repeat;
}

