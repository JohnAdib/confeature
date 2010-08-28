<?php

if(Config::DEBUG){
?>
<div id="debug">
	<strong>Execution duration :</strong> <?php echo round(microtime(true) - START_TIME, 3); ?> s<br />

	<strong>DB Queries :</strong>
	<table>
		<tr>
			<th>Query</th>
			<th>Duration</th>
		</tr>
	<?php
		$total_queries_duration = 0;
		foreach(DB::getQueriesLog() as $query){
			$total_queries_duration += $query[1];
	?>
		<tr>
			<td><?php echo $query[0]; ?></td>
			<td style="text-align: center;"><?php echo round($query[1], 3); ?> s</td>
		</tr>
	<?php
		}
	?>
		<tr>
			<th style="text-align: right;">Total :</th>
			<th style="text-align: center;"><?php echo round($total_queries_duration, 3); ?> s</th>
		</tr>
	</table>

	<?php
		$db_errors = DB::getErrorsLog();
		if(isset($db_errors[0])){
	?>
	<strong>DB Errors :</strong>
	<table>
		<tr>
			<th>Code</th>
			<th>Message</th>
			<th>Query</th>
		</tr>
	<?php
		foreach($db_errors as $error){
	?>
		<tr>
			<td><?php echo $error[0]; ?></td>
			<td><?php echo $error[1]; ?></td>
			<td><?php if(isset($error[2])) echo $error[2]; ?></td>
		</tr>
	<?php
		}
	?>
	</table>
<?php
	}
?>
</div>

<?php
}
