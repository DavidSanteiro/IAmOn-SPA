<?php
//file: view/switches/edit.php

require_once(__DIR__."/../../core/ViewManager.php");
$view = ViewManager::getInstance();

$switch = $view->getVariable("switch");
$currentuser = $view->getVariable("currentusername");

$view->setVariable("title", "Posts");
?>
<main class="form-container">
	<h2><?= i18n("Have you changed your mind?")?></h2>
	<h1><?= i18n("Edit your switch")?></h1>

	<form action="index.php?controller=switches&action=edit" method="post">
		<input type="hidden" name="public_uuid" value="<?php echo($switch->getPublicUuid()) ?>">

		<label class="form-label" for="switch_name"><?= i18n("Switch name") ?></label>
		<input class="form-input" type="text" placeholder="<?= i18n("Switch name") ?>" id="switch_name" name="switch_name"
					 value="<?php echo($switch->getSwitchName()) ?>" required>

		<label class="form-label checkbox_label" for="reset_url"><?= i18n("Reset private URL") ?>
			<input type="checkbox" class="form-input" id="reset_url" name="reset_private_uuid">
			<div class="form-warning"><p><?= i18n("This will cause all old private links on this switch to stop working") ?></p></div>
		</label>

		<label class="form-label" for="switch_description"><?= i18n("Description") ?></label>
		<textarea class="form-input" id="switch_description" name="switch_description" rows="5" maxlength="1000"><?php echo($switch->getDescription()) ?></textarea>

		<input class="form-submit" type="submit" value="Guardar" name="modify">
		<input class="form-cancel" type="submit" value="Cancelar" name="cancel">
		<input class="form-delete" type="submit" value="Eliminar switch" name="delete">
	</form>
</main>
