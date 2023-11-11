<?php
//file: view/switches/view.php

require_once(__DIR__."/../../core/ViewManager.php");
$view = ViewManager::getInstance();

$switch = $view->getVariable("switch");
$currentuser = $view->getVariable("currentusername");
$hasPermissions = $view->getVariable("hasPermissions", false);
$isSubscribed = (boolean) $view->getVariable("isSubscribed", false);
$usersSubscribed = $view->getVariable("numSubscriptions", "unknown");
$view->setVariable("title", i18n("Control switch"));

// Variables adicionales necesarias para bucles for-each más adelante
$fechaHoraActual = new DateTime(); // Obtiene la fecha y hora actual
$dateFormat = "d-m-Y";
$hourFormat = "H:i";
?>

<?php $view->moveToFragment("css");?>
<link rel="stylesheet" href="css/switch.css">
<?php $view->moveToDefaultFragment();?>

<main id="div_view" class="form-container">
	<?php if(empty($switch)):?>
		<img src="img/empty.png" class="empty_img" alt="Fondo vacío">
		<h2><?php echo(printf(i18n("¡Oh, no! The switch with UUID %s has not been found"), $switch->getPublicUuid())) ?></h2>
	<?php elseif ($hasPermissions): ?>
		<div class="divSwitch <?php echo(($switch->getPowerOff() > $fechaHoraActual) ? "on" : "off") ?>" >
			<form action="index.php?controller=switches&action=changeSwitchState" method="post">
				<input type="hidden" value="<?php echo($switch->getPrivateUuid()) ?>" name="private_uuid">
				<button type="submit" class="submit_embedded">
					<label class='switch'>
						<input type='checkbox' name='switch_state' <?php echo($switch->getPowerOff() > $fechaHoraActual)? "checked" : "" ?> disabled>
						<span class='slider round'></span>
					</label>
				</button>
				<?php if ($switch->getPowerOff() < $fechaHoraActual):?>
					<label class="form-label switch_time_label"> <?php echo(i18n("Time")) ?>: </label>
					<input type="number" class="form-input switch_time" name="time_on" value="60">
				<?php endif; ?>
			</form>
			<h3 class="switch_name"><?php echo($switch->getSwitchName()) ?></h3>
			<?php if($switch->getPowerOff() > $fechaHoraActual):?>
				<p class="switch_time_poweredOn"> <?php echo(sprintf(i18n("Is switched on since %s"),
						$switch->getLastPowerOn()->format($hourFormat))) ?></p>
			<?php elseif ($switch->getLastPowerOn() == null): ?>
				<p class="switch_time_poweredOff"> <?php echo(i18n("It never has been powered on")) ?></p>
			<?php else : ?>
				<p class="switch_time_poweredOff"> <?php echo(sprintf(i18n("Last lit on %s at %s"),
						$switch->getLastPowerOn()->format($dateFormat), $switch->getLastPowerOn()->format($hourFormat))) ?></p>
			<?php endif; ?>
			<label for="switch_public_share" class="switch_boton-label">
				<input type="radio" id="switch_public_share" name="seleccion" class="switch_boton-radio">
				<img src="img/share.png" alt="Compartir" class="switch_button1" height="30" width="30">
				</input>
				<input type="text" class="texto-input form-input" value="http://localhost/index.php?controller=switches&amp;action=view&amp;public_uuid=<?php echo $switch->getPublicUuid()?>" readonly>
			</label>
			<label for="switch_private_share" class="switch_boton-label">
				<input type="radio" id="switch_private_share" name="seleccion" class="switch_boton-radio">
				<img src="img/share_with_permission.png" class="switch_button3" alt="Compartir con permisos" height="30" width="30">
				</input>
				<input type="text" class="texto-input form-input" value="http://localhost/index.php?controller=switches&amp;action=view&amp;private_uuid=<?php echo $switch->getPrivateUuid()?>" readonly>
			</label>
			<p class="switch_owner"><?php echo($switch->getOwner()->getUsername()) ?></p>
			<a href="index.php?controller=switches&amp;action=edit&amp;public_uuid=<?php echo $switch->getPublicUuid()?>">
				<img src="img/edit.png" alt="Editar" class="switch_button2" height="30" width="30"></a>
		</div>
	<?php else: ?>
		<div class="divSwitch <?php echo(($switch->getPowerOff() > $fechaHoraActual) ? "on" : "off") ?>">
			<div class="circle"></div>
			<h3 class="switch_name"><?php echo $switch->getSwitchName() ?></h3>
			<p class="switch_time_poweredOn">
				<?php
				if($switch->getPowerOff() > $fechaHoraActual){
					echo(sprintf(i18n("Is switched on since %s"),$switch->getLastPowerOn()->format($hourFormat)));
				}elseif ($switch->getLastPowerOn() == null){
					echo(i18n("It never has been powered on"));
				}else{
					echo(sprintf(i18n("Last lit on %s at %s"),
						$switch->getLastPowerOn()->format($dateFormat), $switch->getLastPowerOn()->format($hourFormat)));
				}
				?>
			</p>
			<label for="switch_public_share" class="switch_boton-label">
				<input type="radio" id="switch_public_share" name="seleccion" class="switch_boton-radio">
				<img src="img/share.png" alt="Compartir" class="switch_button1" height="30" width="30">
				</input>
				<input type="text" class="texto-input form-input" value="http://localhost/index.php?controller=switches&amp;action=view&amp;public_uuid=<?php echo $switch->getPublicUuid()?>" readonly>
			</label>
			<p class="switch_owner"><?= $switch->getOwner()->getUsername() ?></p>
			<?php if(isset($currentuser) && $currentuser != $switch->getOwner()->getUsername()): ?>
				<form action="index.php?controller=switches&amp;action=<?= (($isSubscribed)?"unsubscribe":"subscribe") ?>" method="post">
					<input type="hidden" value="<?php echo($switch->getPublicUuid()) ?>" name="public_uuid">
					<input type="hidden" value="<?php echo($currentuser) ?>" name="user_name">

					<input type="image" src="img/<?= (($isSubscribed)?"alarm-bell_silenced.png":"alarm-bell.png") ?>" class="switch_button2" alt="Enviar" name="submit">
				</form>
			<?php endif; ?>
		</div>
	<?php endif;?>

	<?php if(isset($currentuser) && $currentuser != $switch->getOwner()->getUsername()): ?>
		<form action="index.php?controller=switches&amp;action=<?= (($isSubscribed)?"unsubscribe":"subscribe") ?>" method="post">
			<input type="hidden" value="<?php echo($switch->getPublicUuid()) ?>" name="public_uuid">
			<input type="hidden" value="<?php echo($currentuser) ?>" name="user_name">

			<input type="submit" class="<?= (($isSubscribed)?"form-delete":"form-submit") ?> button-all-width" value="<?= i18n(($isSubscribed)?"Unsubscribe from switch":"Subscribe to switch") ?>" name="submit">
		</form>
	<?php endif; ?>

	<h3><?=i18n("There are") . " " . $usersSubscribed . " " . i18n("subscribers to this switch") ?></h3>
	<textarea class="form-input" id="switch_description" name="switch_description" rows="5" maxlength="1000" readonly
	><?= $switch->getDescription() ?></textarea>

</main>
