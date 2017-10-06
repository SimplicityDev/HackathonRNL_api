<?php

function Alert($Alert) {
	if ($Alert != null) {
		switch ($Alert[0]) {
			case 'Success':
				Success($Alert[1]);
				break;
			case 'Attention':
				Attention($Alert[1]);
				break;
			case 'Warning':
				Warning($Alert[1]);
				break;
			case 'Error':
				Error($Alert[1]);
				break;
		}
	}
}

function Success($Message){
	echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
	echo '<span aria-hidden="true">&times;</span>';
	echo '</button>';
	echo "<strong>Error: </strong>". $Message;
	echo "</div>";
}

function Attention($Message){
	echo '<div class="alert alert-attention alert-dismissible fade show" role="alert">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
	echo '<span aria-hidden="true">&times;</span>';
	echo '</button>';
	echo "<strong>Error: </strong>". $Message;
	echo "</div>";
}

function Warning($Message){
	echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
	echo '<span aria-hidden="true">&times;</span>';
	echo '</button>';
	echo "<strong>Error: </strong>". $Message;
	echo "</div>";
}

function Error($Message){
	echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
	echo '<span aria-hidden="true">&times;</span>';
	echo '</button>';
	echo "<strong>Error: </strong>". $Message;;
	echo "</div>";
}

function RedirectToPage($Seconds = NULL,$Page = NULL)
{
	if(!empty($Seconds))
		$Refresh = 'Refresh: '.$Seconds.';URL=';
	else
		$Refresh = 'location:';

	if(!isset($Page))
	{
		echo "<br />U wordt binnen ".$Seconds." seconden doorgestuurd naar de hoofdpagina.";
		header($Refresh . "index.php");
	}
	else
		header($Refresh . $Page);
}
?>
