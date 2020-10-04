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
	<link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/node_modules/bootstrap/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $baseUrl; ?>/public/admin/css/admin.css">
	<script src="<?php echo $baseUrl; ?>/public/node_modules/jquery/dist/jquery.min.js"></script>
	<title>Insert Translation</title>
</head>
<body>
<script>
    var editor = top.tinymce.activeEditor.id;
    var withoutLang = editor.slice(0, -2);
</script>
<style>
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
<?php

$langs = $container->getParameters()['mutations'] ?? [];

?>
<div class="container-fluid">
	<div class="row mt-3">
		<div class="col-4">
			<div class="module-heading">Jazyky</div>
		</div>
		<div class="col-8">
			<div class="module-heading">Obsah</div>
		</div>
		<div class="col-12">
			<hr>
		</div>
		<div class="col-4">
			<div class="nav flex-column lqd-menu" id="v-pills-tab" role="tablist" aria-orientation="vertical">
				<?php
				$i = 1;
				foreach ($langs as $l) {
					$class = $i == 1 ? 'widget-module active' : 'widget-module';
					$ariaSelected = $i == 1 ? 'true' : 'false';
					echo '<a class="'.$class.'" id="v-pills-'. $l.'-tab" data-toggle="pill" href="#v-pills-'. $l.'" role="tab" aria-controls="v-pills-'. $l.'" aria-selected="'.$ariaSelected.'">'. $l .'</a>';
					$i++;
				}
				?>
			</div>
		</div>
		<div class="col-8">
			<div class="tab-content" id="v-pills-tabContent">
				<?php
				$k = 1;
				
				
				foreach ($langs as $l){
					$showPane = $k == 1 ? 'true' : 'false';
					$classPane = $k == 1 ? 'tab-pane fade active show' : 'tab-pane fade';
					?>
					<div class="<?php echo $classPane ?>" id="v-pills-<?php echo $l; ?>" role="tabpanel" aria-labelledby="v-pills-<?php echo $l; ?>-tab">
						<textarea id="langContent-<?php echo $l; ?>" style="width: 100%; height: 280px; resize: none;"></textarea>
						<button class="btn btn-primary" onclick="insertContent('<?php echo $l; ?>')">Vložit</button>
						<script>
                            var contents = withoutLang + "<?php echo $l; ?>";
                            var obsah = top.tinymce.get(contents).getContent();
                            var areaId = "langContent-" + "<?php echo $l; ?>";
                            var txtarea = document.getElementById(areaId);
                            txtarea.innerHTML = obsah;

                            function insertContent(id) {
                                var editorId = withoutLang + id;
                                var source = top.tinymce.get(editorId).getContent();
                                top.tinymce.activeEditor.insertContent(source);
                                top.tinymce.activeEditor.windowManager.close();
                            }
						</script>
					</div>
					<?php
					$k++;
				}
				
				?>
			</div>
		</div>
	</div>
</div>
<script>
    function insertAndClose(widget) {
        top.tinymce.activeEditor.insertContent(widget);
        top.tinymce.activeEditor.windowManager.close();
    }

</script>

<script src="<?php echo $baseUrl?>/public/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>