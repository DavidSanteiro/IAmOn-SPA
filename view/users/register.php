<?php
//file: view/users/register.php

require_once(__DIR__."/../../core/ViewManager.php");
$view = ViewManager::getInstance();
$errors = $view->getVariable("errors");
$user = $view->getVariable("user");
$view->setVariable("title", "Register");
?>
<main class="form-container">
	<h2><?= i18n("Welcome to IAmOn") ?></h2>
	<h1><?= i18n("Register") ?></h1>
	<form action="index.php?controller=users&amp;action=register" method="POST">
		<label class="form-label"><?= i18n("Username")?></label>
		<input class="form-input" type="text" placeholder="<?= i18n("Username")?>" name="user_name" required>

		<label class="form-label"><?= i18n("Email")?></label>
		<input class="form-input" type="email" placeholder="<?= i18n("Email")?>" name="user_email">

		<label class="form-label"><?= i18n("Password")?></label>
		<input class="form-input" type="password" placeholder="<?= i18n("Password")?>" name="user_password" required>

		<input class="form-submit" type="submit" value="<?= i18n("Register") ?>">

		<p><?= i18n("Already registered?")?> <a class="link" href="index.php?controller=users&amp;action=login"><?= i18n("Go to login")?></a></p>
	</form>
</main>
