<?php
//file: view/layouts/header_logged_in.php

$view = ViewManager::getInstance();
$currentuser = $view->getVariable("currentusername");

?><!DOCTYPE html>
<html>
	<head>
		<title><?= $view->getVariable("title", "no title") ?></title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="css/generalStyle.css">
		<link rel="stylesheet" href="css/form.css">
		<link rel="stylesheet" href="https://use.typekit.net/lru6tor.css"> <!-- Fuente importada de servicio de Adobe -->
		<?= $view->getFragment("css") ?>
		<?= $view->getFragment("javascript") ?>
		<!-- enable ji18n() javascript function to translate inside your scripts -->
		<script src="index.php?controller=language&amp;action=i18njs"></script>
	</head>
	<body>
		<header>
			<a href="index.php?controller=switches&amp;action=index"><img src="img/logo.png" class="logo_iamon" alt="Logo IAmOn"></a>
			<a href="index.php?controller=switches&amp;action=index">Dashboard</a>
			<?php include(__DIR__."/language_select_element.php"); ?>
			<div class="user_info">
			<?php if (isset($currentuser)): ?>
				<img src="img/user.png" alt="Usuario">
				<p id="user_name"><?= sprintf(i18n("Hello %s"), $currentuser) ?></p>
				<p><a href="index.php?controller=users&amp;action=logout">(Logout)</a></p>
			<?php else: ?>
				<a href="index.php?controller=users&amp;action=login"><p><?= i18n("Login") ?></p></a>
			<?php endif ?>
			</div>
		</header>
		<nav>
			<a href="index.php?controller=switches&amp;action=index"><img src="img/logo.png" class="logo_iamon" alt="Logo IAmOn"></a>
			<a href="index.php?controller=switches&amp;action=index">Dashboard</a>
			<?php include(__DIR__."/language_select_element.php"); ?>
			<div class="user_info">
				<?php if (isset($currentuser)): ?>
					<img src="img/user.png" alt="Usuario">
					<p id="user_name"><?= sprintf(i18n("Hello %s"), $currentuser) ?></p>
					<p><a href="index.php?controller=users&amp;action=logout">(Logout)</a></p>
				<?php else: ?>
					<a href="index.php?controller=users&amp;action=login"><p><?= i18n("Login") ?></p></a>
				<?php endif ?>
			</div>
		</nav>

		<?php if(($mensajes = $view->popFlash()) != ""): ?>
			<div id="flash--positive" class="show"><?= $mensajes ?></div>
		<?php endif?>

		<?php if (isset($errors["general"])): ?>
			<div id="flash--negative" class="show"><?= $errors["general"] ?></div>
		<?php endif?>

		<?= $view->getFragment(ViewManager::DEFAULT_FRAGMENT) ?>

		<footer>
			<br>
			<p>2023 IAmOn</p>
			<p>Brais Rivera & David Santeiro</p>
		</footer>

	</body>
</html>