
<h1>Add Samples</h1>

<a href="/dataset/create1" class="btn span1"><?= Yii::t('app' , 'Study')?></a>
<input type="submit" id="sample-btn" class="btn nomargin" value="Author"></input>
<input type="submit" id="sample-btn" class="btn nomargin" value="Project"></input>
<input type="submit" id="author-btn" class="btn-green-active nomargin" value="Link"></input>
<input type="submit" id="sample-btn" class="btn nomargin" value="External Link"></input>
<input type="submit" id="sample-btn" class="btn nomargin" value="Related Doi"></input>
<input type="submit" id="sample-btn" class="btn nomargin" value="Samples"></input>

<?
$data = $model->search();
//$data->pagination->pageSize = $model->count();
?>

<div class="span12 form well">
    <div class="form-horizontal">
        <a href="/adminSample/create1" class="btn-green">Create New Sample</a>
        <button id="submit-btn" class="btn-green">View ID of all selected samples</button>
        <span id="selected-keys">Selected:</span>
<?php $this->widget('ext.selgridview.SelGridView', array(
    
	'id'=>'sample-grid',
	'dataProvider'=>$data,
    
        'selectableRows' => 10,
	'itemsCssClass'=>'table table-bordered',
	'filter'=>$model,
    
	'columns'=>array(
              array(
            'id'=>'autoId',           
            'class'=>'CCheckBoxColumn',          
        ),       
                'id',
		array('name'=> 'species_search', 'value'=>'$data->species->common_name'),
		array('name'=> 'dois_search', 'value'=>'implode(\', \',CHtml::listData($data->datasets,\'id\',\'identifier\'))'),
		's_attrs',
                     'code',
		
	),
)); ?>
    </div>
</div>


<br/>

<br/>
<div class="span12" style="text-align:center">
    <a href="<?= Yii::app()->createUrl('/user/view_profile') ?>" class="btn"/>Cancel</a>
    <a href="<?= Yii::app()->createUrl('/dataset/create1') ?>" class="btn"/>Previous</a>
      <a id="next-btn" class="btn-green"/>Next</a>
    
</div>
<script>
  $("#submit-btn").click(function(){
          var selected = $("#sample-grid").selGridView("getAllSelection"); 
          $("#selected-keys").html("Selected: [" + selected.join(", ") + "]");
      });
   $("#next-btn").click(function(){
       
        var selected = $("#sample-grid").selGridView("getAllSelection"); 
       window.location.href="/adminSample/choose?samples="+selected;
   });  
</script>