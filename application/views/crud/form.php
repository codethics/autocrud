<?=$error;?>
<?=$form_open?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o"></i>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-lg-6">
				<?foreach($inputs as $input){?>
					<div class="input-group" style="margin-bottom:10px;">
						<span class="input-group-addon" style="min-width:150px; text-align:right"><?=$input['alias']?> :</span>
						<?=$input['html']?>
					</div>
				<?}?>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="btn-group pull-right">
			<?=anchor($this->uri->segment(1).'/'.$this->uri->segment(2),'Cancel','class="btn btn-default"');?>
			<?=$submit?>
		</div>
		<div class="clearfix"></div>
	</div>
	</div>
</div>
<?=$form_close?>
