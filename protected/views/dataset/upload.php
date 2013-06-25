<h2>Dataset Upload</h2>
<div class="clear"></div>
<?php if (isset($_GET['status'])) { 
	if ($_GET['status'] == 'successful') { ?>
	<div class="row">
		<div class="span8 offset2">
			<div class="form well light-green">
				Your GigaDB submission has been received and is currently under review. If you do not hear from us within 5 working days please contact <a href="mailto:#"> database@gigasciencejournal.com </a>
				<br/><br/>
				<a href="/dataset/upload" class="btn">Back to upload new dataset</a>
			</div>
		</div>
	</div>
	<?php } elseif ($_GET['status'] == 'failed') { ?>
		<div class="row">
		<div class="span8 offset2">
			<div class="form well">
				<p class="error">
					Upload failed. Please contact <a href="mailto:#"> database@gigasciencejournal.com </a>
					<br/><br/>
					<a href="/dataset/upload" class="btn">Back to upload new dataset</a>
				</p>
			</div>
		</div>
	</div>
	<?php }
} else { ?>

<div class="row">
	<div class="span8 offset2">
		<div class="form well">
			You will need to fill out a template file and then give it a new file name. 
			<br/><br/>
			Click 'Download Template File' to get a copy:
			<br/><br/>
			<a href="/files/GigaDBUploadForm.xls" class="btn pull-right">Download Template File</a>
			<div class="clear"></div>
			<br/><br/>
			When filling out your dataset file, you may refer to the files below as examples.
			<br/><br/>
			<a href="/files/EcoliSubmission.xls" class="btn pull-right">Download Example File 1</a>
			<br/><br/>
			<div class="clear"></div>
			<a href="/files/SorghumSubmission.xls" class="btn pull-right">Download Example File 2</a>
			<div class="clear"></div>
		</div>
		<div class="form well">
			<input id="agree-checkbox" type="checkbox" style="margin-right:5px"/><a target="_blank" href="/site/term">I have read GigaDB's Terms and Conditions</a>
			<br/>
			<div class="clear"></div>
			<?php echo MyHtml::form(Yii::app()->createUrl('dataset/upload'), 'post', array('enctype' => 'multipart/form-data')); ?>
			<div class="pull-right">
			    <?php echo MyHtml::submitButton('Upload New Dataset',array('class'=>'btn-green upload-control','disabled'=>'disabled')); ?>
		    </div>
		    <?php echo MyHtml::hiddenField('userId', Yii::app()->user->id); ?>
		    <?php echo MyHtml::label('Excel File', 'xls'); ?>
		    <?php echo MyHtml::fileField('xls',null,array('disabled'=>'disabled','class'=>'upload-control')); ?>
		    <?php echo MyHtml::endForm(); ?>
		</div>
	</div>
</div>
<script>
$(function () {
	$('#agree-checkbox').click(function() {
		if ($(this).is(':checked')) {
			$('.upload-control').attr('disabled',false);
		} else {
			$('.upload-control').attr('disabled',true);
		}
	});
});
</script>
<? } ?>