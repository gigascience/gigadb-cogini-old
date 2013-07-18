<?php
$this->breadcrumbs=array(
	'Dataset Authors'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List DatasetAuthor', 'url'=>array('index')),
	array('label'=>'Manage DatasetAuthor', 'url'=>array('admin')),
);
?>

<h2>Add Authors</h2>
<div class="clear"></div>


<a href="/dataset/create1" class="btn span1"><?= Yii::t('app' , 'Study')?></a>
<input type="submit" id="author-btn" class="btn-green-active nomargin" value="Author"></input>
<a href="/adminDatasetProject/create1" class="btn nomargin"><?= Yii::t('app' , 'Project')?></a>
<a href="/adminLink/create1" class="btn nomargin"><?= Yii::t('app' , 'Link')?></a>
<a href="/adminExternalLink/create1" class="btn nomargin"><?= Yii::t('app' , 'ExternalLink')?></a>
<a href="/adminRelation/create1" class="btn nomargin"><?= Yii::t('app' , 'Related Doi')?></a>
<a href="/adminDatasetSample/create1" class="btn nomargin"><?= Yii::t('app' , 'Sample')?></a>


<?php echo $this->renderPartial('_form1', array('model'=>$model,'author_model'=>$author_model)); ?>