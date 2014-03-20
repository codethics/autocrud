<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o"></i> <?//=$head?>
		<div class="btn-group pull-right">
			<?=get_insert_button()?>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="panel-body">
		<?if($rows){?>
		<table class="table table-hover">
			<tr>
				<?foreach($fields as $field){
					if($field!=$primary_key){?><th><?=$field?></th><?}
				}?>
					<th>&nbsp;</th>
			</tr>
			<?foreach($rows as $row){?>
				<tr>
					<?foreach($fields as $field){
						if($field!=$primary_key){?><td><?=$row[$field]?></td><?}?>
					<?}?>
						<td>
							<div class="btn-group pull-right">
								<?=get_edit_button($row[$primary_key])?>
								<?=get_delete_button($row[$primary_key])?>
							</div>
						</td>
				</tr>
			<?}?>
		</table>
		<?}else{?>
		No data found.
		<?}?>
	</div>
</div>
