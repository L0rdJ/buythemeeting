<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<title>Купи зустріч - Допоможи дитині{if isset( $data.page_title )}: {$data.page_title}{/if}</title>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link rel="stylesheet/less" type="text/css" href="less/app.less" />
	<script type="text/javascript" src="vendor/less/js/less-1.5.0.min.js"></script>
</head>
<body>
	<div class="container">
		<div class="page-header">
			<h1>Купи зустріч - Допоможи дитині</h1>
		</div>
	</div>

	<div class="container">
		{foreach from=$messages item=message}
			{if $message.type eq 'error'}
				<div class="alert alert-danger">
					{{$message.message}}
				</div>
			{elseif $message.type eq 'success'}
				<div class="alert alert-success">
					{{$message.message}}
				</div>
			{/if}
		{/foreach}
	</div>
	
	{block name=content}{/block}

	<div class="container">
		<p>Бажаєш довідатись рецептів і заклинань, за допомгою котрих було створенно цей магічний додаток? Тобі сюди: <a href="https://github.com/L0rdJ/buythemeeting">https://github.com/L0rdJ/buythemeeting</a>.</p>
	</div>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	<script type="text/javascript" src="vendor/bootstrap/js/tab.js"></script>
	<script type="text/javascript" src="vendor/bootstrap/js/tooltip.js"></script>
	<script type="text/javascript" src="vendor/masonry/js/masonry.min.js"></script>
	<script type="text/javascript" src="vendor/imagesloaded/js/imagesloaded.min.js"></script>
	{block name=js}{/block}
</body>
</html>
