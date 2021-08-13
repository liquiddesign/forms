<?php
$parsedPath = \explode(DIRECTORY_SEPARATOR, __DIR__);
$rootLevel = \count($parsedPath) - \array_search('vendor', $parsedPath);

require \dirname(__DIR__, $rootLevel) . '/vendor/autoload.php';

$container = App\Bootstrap::boot()->createContainer();
\Tracy\Debugger::$showBar = false;

$baseUrl = \dirname($container->getByType(\Nette\Http\Request::class)->getUrl(), $rootLevel + 1);
?>
<!doctype html>
<html lang="cs">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/node_modules/bootstrap-old/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/admin/css/admin.css">
	<script src="<?php echo $baseUrl; ?>/public/node_modules/jquery/dist/jquery.min.js"></script>
	<title>Insert variables</title>
</head>
<body>
<script>
    const variables = top.tinymce.settings.variables;
</script>
<style>
	strong {
		font-weight: bold;
	}

	.widget-module {
		color: #888;
		display: block;
		font-size: 16px;
		padding: 8px 0;
		border-right: 2px solid #eee;
	}
	
	.widget-module.active {
		color: #0064CF;
		border-color: #007bff;
	}
	
	.widget-module:hover {
		color: #0064CF;
		text-transform: none;
		text-decoration: none;
	}
	
	.module-heading {
		font-size: 18px;
		font-weight: 900;
	}
	
	.button-add {
		color: white;
		display: block;
		background-color: #007bff;
		font-size: 12px;
		padding: 6px 10px;
	}
	
	.button-add:hover {
		background-color: #0064CF;
	}
</style>
<div class="container-fluid">
	<div class="row mt-3">
		<div class="col-12">
			<div class="list-group" id="variable-group"></div>
			<script>
                var group = document.getElementById('variable-group');
                if (variables) {
                    for (const [key, value] of Object.entries(variables)) {
                        var button = document.createElement('button');
                        button.innerHTML = '<strong>'+ key +'</strong>' + ' - ' + value;
                        button.setAttribute('class', 'list-group-item list-group-item-action');
                        button.setAttribute('style', 'padding: 0.3rem 1.25rem!important');
                        button.onclick = function(){
                            top.tinymce.activeEditor.insertContent('{$' + key + '}');
                            top.tinymce.activeEditor.windowManager.close();
                            return false;
                        };
                        group.appendChild(button);
                    };
                } else {
                    var paragraph = document.createElement('p');
                    paragraph.innerHTML = "Žádné proměnné k dispozici";
                    paragraph.setAttribute('class', 'text-center');
                    group.appendChild(paragraph);
                }
			</script>
		</div>
	</div>
</div>
<script src="<?php echo $baseUrl?>/public/node_modules/bootstrap-old/dist/js/bootstrap.min.js"></script>
</body>
</html>