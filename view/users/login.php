<?php
//file: view/users/login.php

require_once(__DIR__."/../../core/ViewManager.php");
$view = ViewManager::getInstance();
$view->setVariable("title", "Login");
// $errors = $view->getVariable("errors");
?>
	<main class="form-container">
		<h2><?= i18n("Welcome back") ?></h2>
		<h1><?= i18n("Log in to your account") ?></h1>
		<form action="index.php?controller=users&amp;action=login" method="POST">
			<label class="form-label"><?= i18n("Username")?></label>
			<input class="form-input" type="text" placeholder="<?= i18n("Username")?>" name="user_name" required>

			<label class="form-label"><?= i18n("Password")?></label>
			<input class="form-input" type="password" placeholder="<?= i18n("Password")?>" name="user_password" required>

			<input class="form-submit" type="submit" value="<?= i18n("Login") ?>">

			<p><?= i18n("Not user?")?> <a class="link" href="index.php?controller=users&amp;action=register"><?= i18n("Register here!")?></a></p>
		</form>
	</main>