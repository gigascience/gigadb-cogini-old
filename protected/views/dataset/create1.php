<h2>Create Dataset</h2>
<div class="clear"></div>

<font class="btn-green-active span1"><?= Yii::t('app' , 'Study')?></font>
<input type="submit" id="author-btn" class="btn nomargin next1" value="Author"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Project"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Link"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="External Link"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Related Doi"></input>
<input type="submit" id="sample-btn" class="btn nomargin next1" value="Sample"></input>


<? 
    $this->renderPartial('_form1', array('model'=>$model));     
?>


