

<h2>Add sample details</h2>
<div class="clear"></div>


<a href="/dataset/create1" class="btn span1"><?= Yii::t('app' , 'Study')?></a>
<a href="/adminDatasetAuthor/create1" class="btn nomargin"><?= Yii::t('app' , 'Author')?></a>

<a href="/adminDatasetProject/create1" class="btn nomargin"><?= Yii::t('app' , 'Project')?></a>
<a href="/adminLink/create1" class="btn nomargin"><?= Yii::t('app' , 'Link')?></a>
<a href="/adminExternalLink/create1" class="btn nomargin"><?= Yii::t('app' , 'ExternalLink')?></a>
<a href="/adminRelation/create1" class="btn nomargin"><?= Yii::t('app' , 'Related Doi')?></a>
<input type="submit" value="Sample" class="btn-green-active nomargin"></input>

<?php echo $this->renderPartial('_form1', array('model'=>$model,'sample_model'=>$sample_model)); ?>