<?php
//file: view/switches/dashboard.php

require_once(__DIR__."/../../core/ViewManager.php");
$view = ViewManager::getInstance();

$userSwitches = $view->getVariable("userSwitches");
$userSubscribedSwitches = $view->getVariable("userSubscribedSwitches");
$currentuser = $view->getVariable("currentusername");

$view->setVariable("title", "Posts");

// Variables adicionales necesarias para bucles for-each más adelante
$fechaHoraActual = new DateTime(); // Obtiene la fecha y hora actual
$dateFormat = "d-m-Y";
$hourFormat = "H:i";
$cont = 0;
?>

<?php $view->moveToFragment("css");?>
	<link rel="stylesheet" href="css/switch.css">
	<link rel="stylesheet" href="css/dashboard.css">
<?php $view->moveToDefaultFragment();?>

<main id="mySwitches"> <!-- Div de los switch pertenecientes al usuario -->
	<h1><?php echo(i18n("My Switches")) ?></h1>
	<!-- Aquí se añadirán los switch que pertenecen al usuario o si no hay, un elemento decorativo -->
	<?php if(empty($userSwitches)):?>
		<img src="img/empty.png" class="empty_img" alt="Fondo vacío">
		<h2><?=i18n("This is deserted! There are no switches. So what are you waiting for? Create your first switch")?></h2>
	<?php else: foreach ($userSwitches as $switch): ?>
		<div class="divSwitch <?php echo(($switch->getPowerOff() > $fechaHoraActual) ? "on" : "off") ?>" >
			<form action="index.php?controller=switches&action=changeSwitchState" method="post">
				<input type="hidden" value="<?php echo($switch->getPublicUuid()) ?>" name="public_uuid">
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
			<label for="switch_public_share<?php echo ++$cont?>" class="switch_boton-label">
				<input type="radio" id="switch_public_share<?php echo $cont?>" name="seleccion" class="switch_boton-radio">
					<img src="img/share.png" alt="Compartir" class="switch_button1" height="30" width="30">
				</input>
				<input type="text" class="texto-input form-input" value="http://localhost/index.php?controller=switches&amp;action=view&amp;public_uuid=<?php echo $switch->getPublicUuid() ?>" readonly>
			</label>
			<label for="switch_private_share<?php echo $cont?>" class="switch_boton-label">
				<input type="radio" id="switch_private_share<?php echo $cont?>" name="seleccion" class="switch_boton-radio">
					<img src="img/share_with_permission.png" class="switch_button3" alt="Compartir con permisos" height="30" width="30">
				</input>
				<input type="text" class="texto-input form-input" value="http://localhost/index.php?controller=switches&amp;action=view&amp;private_uuid=<?php echo $switch->getPrivateUuid()?>" readonly>
			</label>
			<p class="switch_owner"><?php echo($currentuser) ?></p>
			<a href="index.php?controller=switches&amp;action=edit&amp;public_uuid=<?php echo $switch->getPublicUuid()?>">
				<img src="img/edit.png" alt="Editar" class="switch_button2" height="30" width="30"></a>
		</div>
	<?php endforeach;?>
	<?php endif; ?>
	<a id="new_switch" href="index.php?controller=switches&amp;action=add">
		<img src="img/add.png" class="add_switch" alt="añadir switch">
	</a>
</main>

<main id="friendsSwitches"> <!-- Div de los switch a los que el usuario está suscrito -->
	<h1><?php echo(i18n("Subscribed Switches")) ?></h1>
	<!-- Aquí se añadirán los switch a los que se haa suscrito el usuario o si no hay, un elemento decorativo -->
	<?php if(empty($userSubscribedSwitches)):?>
		<img src="img/empty.png" class="empty_img" alt="Fondo vacío">
		<h2><?=i18n("This is deserted! There are no switches. So what are you waiting for? Ask your friends to share their switches")?></h2>
	<?php else: foreach ($userSubscribedSwitches as $switch): ?>
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
			<label for="switch_public_share<?php echo ++$cont?>" class="switch_boton-label">
				<input type="radio" id="switch_public_share<?php echo $cont?>" name="seleccion" class="switch_boton-radio">
				<img src="img/share.png" alt="Compartir" class="switch_button1" height="30" width="30">
				</input>
				<input type="text" class="texto-input form-input" value="http://localhost/index.php?controller=switches&amp;action=view&amp;public_uuid=<?php echo $switch->getPublicUuid()?>" readonly>
			</label>
			<p class="switch_owner"><?= $switch->getOwner()->getUsername() ?></p>
			<a href="http://localhost/index.php?controller=switches&amp;action=view&amp;public_uuid=<?php echo $switch->getPublicUuid()?>">
				<img src="img/info.png" alt="Editar" class="switch_button2" height="30" width="30">
			</a>
			<form action="index.php?controller=switches&amp;action=unsubscribe" method="post">
				<input type="hidden" value="<?php echo($switch->getPublicUuid()) ?>" name="public_uuid">
				<input type="hidden" value="<?php echo($currentuser) ?>" name="user_name">
				
				<input type="image" src="img/alarm-bell_silenced.png" class="switch_button3" alt="Enviar" name="submit">
			</form>
		</div>
	<?php endforeach;?>
	<?php endif; ?>
</main>