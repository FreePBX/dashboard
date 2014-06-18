<div class='container-fluid'>
	<div class='row'>
		<div class='col-sm-12'>
			<div class='text-center h4'>
				<strong>
					<?php echo sprintf(_('Welcome to %s'),$brand)?>
				</strong>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-12'>
			<p class='text-center'>
				<?php echo $brand . " " . $version?>
			</p>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-6'>
			<div class='row'>
				<div class='col-sm-12'>
					<div class="text-center"><?php echo _("Summary")?></div>
					<div class="summary">
						<?php foreach($services as $service) { ?>
							<div class="status-element" data-toggle="tooltip" title="<?php echo $service['tooltip']?>">
								<div class="status-icon"><span class="glyphicon <?php echo $service['glyph-class']?>"></span></div>
								<?php echo $service['title']?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<div class='col-sm-6'>
			<div class="text-center"><?php echo sprintf(_('SysInfo updated %s seconds ago'),$since)?></div>
			<br/>
			<div class='alert alert-<?php echo $alerts['state']?> sysalerts'>
				<div class='text-center'><?php echo _('System Alerts')?></div>
				<p><?php echo $alerts['text']?></p>
			</div>
		</div>
	</div>
	<div class='row'>
		<div class='col-sm-12'>
			<div class='panel-group' id='notifications_group'>
				<?php foreach($nots as $n) {?>
					<div class="panel panel-default panel-<?php echo $n['level']?> fade in" id="panel_<?php echo $n['id']?>">
						<div class="panel-heading collapsed" data-notid="<?php echo $n['id']?>" data-toggle="collapse" data-parent="#notifications_group" href="#link_<?php echo $n['id']?>">
							<div class="actions">
								<i class="fa fa-minus-circle" title="<?php echo _('Ignore This')?>"></i>
								<i class="fa fa-times-circle <?php echo !empty($n['candelete']) ? '' : 'hidden'?>" title="<?php echo _('Delete This')?>"></i>
							</div>
							<div class="panel-title"><?php echo $n['title']?></div>
						</div>
						<div id="link_<?php echo $n['id']?>" class="panel-collapse collapse">
							<div class="panel-body">
								<div class="extended_text">
									<?php echo $n['text']?>
								</div>
								<div class="timestamp alert-<?php echo $n['level']?>"><?php echo sprintf(_('%s ago'),$n['time'])?></div>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<script>
	$('.status-element').tooltip();
	$('.panel-collapse').on('shown.bs.collapse', function() { $('.page').packery(); });
	$('.panel-collapse').on('hidden.bs.collapse', function() { $('.page').packery(); });
	$('#notifications_group .actions i.fa-minus-circle').click(function() {
		$(this).parents('.panel').fadeOut('slow');
	})
	$('#notifications_group .actions i.fa-times-circle').click(function() {
		$(this).parents('.panel').fadeOut('slow');
	})
</script>
