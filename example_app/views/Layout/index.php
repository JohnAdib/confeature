<!doctype html>
<html lang="en" class="no-js">
	<head>
		<title><?php if($this->specificController->title != '') echo $this->specificController->title.' - '; ?>Example</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
		<meta name="robots" content="index,follow"/>
		
<?php
foreach($cssFiles as $cssFile){
?>
		<link rel="stylesheet" type="text/css" href="<?php echo $cssFile; ?>" />
<?php
}
?>
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo Config::URL_STATIC; ?>favicon.ico" />
		
		<!--[if IE 6]>
		<style type="text/css">
		img {behavior: url("<?php echo Config::URL_STATIC; ?>css/pnghack.htc");}
		</style>
		<![endif]-->
		<!--[if IE]>
		<style type="text/css">
		* {zoom: 1;}
		</style>
		<script type="text/javascript">
		//<![CDATA[
		(function(){
			for(var e = 'abbr,article,aside,audio,bb,canvas,datagrid,datalist,details,dialog,eventsource,figure,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,time,video'.split(','), i=0, iTotal = e.length; i < iTotal; i++)
				document.createElement(e[i]);
		})();
		//]]>
		</script>
		<![endif]-->
		<script type="text/javascript">
		//<![CDATA[
		document.getElementsByTagName("html")[0].className = "js";
		//]]>
		</script>
		
	</head>
	<body class="<?php echo 'body_'.$controllerName .' '. 'body_'.$controllerName.'_'.$actionName; ?>">
		
		<div id="container">
			<header>
				<h1>Example</h1>
			</header>

			<div id="main">
				<?php
				$this->__renderContent();
				?>
			</div>

			<footer>
				&copy; Example <?php echo date('Y'); ?>
			</footer>
		</div>

<?php
foreach($jsFiles as $jsFile){
?>
		<script type="text/javascript" src="<?php echo $jsFile; ?>"></script>
<?php
}
if($jsCode != ''){
?>
		<script type="text/javascript">
		//<![CDATA[
		<?php echo $jsCode; ?>
		//]]>
		</script>
<?php
}




// Mode debug
require APP_DIR.'views/'.$this->name.'/_debug.php';


?>
	</body>
</html>