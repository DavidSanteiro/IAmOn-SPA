<?php
//file: view/layouts/header_logged_out.php

$view = ViewManager::getInstance();
$currentuser = $view->getVariable("currentusername");
$errors = $view->getVariable("errors");
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?= $view->getVariable("title", "no title") ?></title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="../../css/generalStyle.css" type="text/css">
		<link rel="stylesheet" href="../../css/form.css">
		<!-- enable ji18n() javascript function to translate inside your scripts -->
		<script src="index.php?controller=language&amp;action=i18njs"></script>
		<?= $view->getFragment("css") ?>
		<?= $view->getFragment("javascript") ?>
	</head>
<body>
	<!-- header -->
	<img src="/img/logo.png" alt="Logo IAmOn" class="logo" height="50">

	<?php
		if(($mensajes = $view->popFlash()) != ""):
	?>
			<div id="flash--positive" class="show"><?= $mensajes ?></div>
	<?php endif?>

	<?php
		if (isset($errors["general"])):
	?>
			<div id="flash--negative" class="show"><?= $errors["general"] ?></div>
	<?php endif?>

	<?= $view->getFragment(ViewManager::DEFAULT_FRAGMENT) ?>

	<footer>
		<br>
		<p>2023 IAmOn</p>
		<p>Brais Rivera & David Santeiro</p>
		<?php
		include(__DIR__."/language_select_element.php");
		?>
	</footer>
</body>
</html>