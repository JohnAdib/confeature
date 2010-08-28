<h2><?php echo __('PAGE_ERROR_TITLE'); ?></h2>
<?php
if(isset($message))
	echo $message;
else
	echo __('PAGE_ERROR_MESSAGE'); ?>
