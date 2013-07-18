
<h1>Add a link to a genome browser?</h1>
<div class="clear"></div>



<a href="/dataset/create1" class="btn span1"><?= Yii::t('app' , 'Study')?></a>

<a href="/adminDatasetAuthor/create1" class="btn nomargin"><?= Yii::t('app' , 'Author')?></a>

<a href="/adminDatasetProject/create1" class="btn nomargin"><?= Yii::t('app' , 'Project')?></a>
<a href="/adminLink/create1" class="btn nomargin"><?= Yii::t('app' , 'Link')?></a>



<input type="submit" value="ExternalLink" class="btn-green-active  nomargin"></input>

<a href="/adminRelation/create1/" class="btn nomargin"><?= Yii::t('app' , 'Related Doi')?></a>
<a href="/adminDatasetSample/create1" class="btn nomargin"><?= Yii::t('app' , 'Sample')?></a>

<?php echo $this->renderPartial('_form1', array('model'=>$model,'externalLink_model'=>$externalLink_model)); ?>
