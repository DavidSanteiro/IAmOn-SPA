<?php
//file: view/switches/add.php

require_once(__DIR__."/../../core/ViewManager.php");
$view = ViewManager::getInstance();

$switch = $view->getVariable("switch");
$currentuser = $view->getVariable("currentusername");

$view->setVariable("title", i18n("Add switch"));
?>
<main class="form-container">
	<h2><?= i18n("Another idea to a switch?") ?></h2>
	<h1><?= i18n("Crete a switch") ?></h1>

	<form action="index.php?controller=switches&action=add" method="post">

		<label class="form-label" for="switch_name"><?= i18n("Name") ?></label>
		<input class="form-input" type="text" placeholder="<?= i18n("Switch name") ?>" id="switch_name" name="switch_name" required>

		<label class="form-label" for="switch_description"><?= i18n("Description") ?></label>
		<textarea class="form-input" id="switch_description" name="switch_description" rows="5" maxlength="1000"></textarea>

		<input class="form-submit" type="submit" value="<?= i18n("Create") ?>" name="create">
	</form>

	<form action="index.php?" method="get">
		<input type="hidden" name="controller" value="switches"/>
		<input type="hidden" name="action" value="index"/>
		<input class="form-cancel button-all-width" type="submit" value="<?= i18n("Cancel") ?>" name="cancel">
	</form>
</main>
