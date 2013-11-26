<?php
$cs = Yii::app()->getClientScript();
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.8.21.custom.min.js');
?>

<h1>Update Files of Dataset: <? echo $identifier ?></h1>
<? if (Yii::app()->user->checkAccess('admin')) { ?>
    <div class="actionBar">
        [<?= MyHtml::link('Manage Files', array('admin')) ?>]
    </div>
<? } ?>
<br/>

<input type="text" class='date'></input>




<table class="table table-bordered tablesorter" id="file-table">
    <!--tr-->
    <thead>
        <!--th class="span2"><a href='#' onClick="setCookie('dataset.identifier')">DOI</a></th-->
        <?
        //TODO: This part is also dupicated
        $fsort = $files->getSort();
        $fsort_map = array(
            'name' => 'span5',
            'code' => 'span5',
            'type_id' => 'span2',
            'format_id' => 'span2',
            'size' => 'span2',
            'date_stamp' => 'span3',
            'description' => 'span2',
        );

        foreach ($fsort->directions as $key => $value) {
            if (!array_key_exists($key, $fsort_map)) {
                continue;
            }
            $direction = ($value == 1) ? ' sorted-down' : ' sorted-up';
            $fsort_map[$key] .= $direction;
        }
        ?>
        <?
        /* <th class="span5"><a href='#' onClick="setCookie('name')">File Name</a></th--><th class="span5"><?=$fsort->link('name')?></th>
          <th class="span5">Sample ID</th>
          <th class="span2"><a href='#' onClick="setCookie('type_id')">File Type</a></th>
          <th class="span2"><a href='#' onClick="setCookie('format_id')">File Format</a></th>
          <th class="span1"><a href='#' onClick="setCookie('size')">Size</a></th>
          <th class="span3"><a href='#' onClick="setCookie('date_stamp')">Release Date</a></th> */
        foreach ($fsort_map as $column => $css) {
            ?>
        <th class="<?= $css ?>"><?= $fsort->link($column) ?></th>
        <? }
        ?>
    <th class="span2"></th>
</thead>
<!--/tr-->
<?
//change pagination
$pageSize = isset(Yii::app()->request->cookies['filePageSize']) ?
        Yii::app()->request->cookies['filePageSize']->value : 10;

$files->getPagination()->pageSize = $pageSize;


$i = 0;


$form = $this->beginWidget('CActiveForm', array(
    'id' => 'file-forms',
    'enableAjaxValidation' => false,
    'htmlOptions' => array('class' => 'form-horizontal')
        ));

echo CHtml::submitButton("Update All", array('class' => 'btn', 'name' => 'files'));

echo CHtml::link('Display Settings', "", // the link for open the dialog
        array(
    'style' => 'cursor: pointer; text-decoration: underline;',
    'onclick' => "{ $('#dialogDisplay').dialog('open');}"));

//$forms = array();
foreach ($files->getData() as $file) {

    echo $form->hiddenField($file, '['.$i.']extension');
    echo $form->hiddenField($file, '['.$i.']id');
    echo $form->hiddenField($file, '['.$i.']dataset_id');
    echo $form->hiddenField($file, '['.$i.']location');
    echo $form->hiddenField($file, '['.$i.']extension');
//     var_dump($file->dataset_id);
    ?>
    <tr>

        <td class="left"><p class='filename'> <?php echo $form->textField($file, '['.$i.']name', array('size' => 60, 'maxlength' => 100, 'readonly' => 'readonly')); ?></p></td>
        <td class="left"><?php echo $form->textField($file, '['.$i.']code', array('size' => 60, 'maxlength' => 64)); ?></td>
        <td class="left"><?= CHtml::activeDropDownList($file, '['.$i.']type_id', CHtml::listData(FileType::model()->findAll(), 'id', 'name')); ?></td>

        <td> <?= CHtml::activeDropDownList($file, '['.$i.']format_id', CHtml::listData(FileFormat::model()->findAll(), 'id', 'name')); ?></td>
        <td><?php echo $form->textField($file, '['.$i.']size', array('size' => 40, 'maxlength' => 100, 'readonly' => 'readonly')); ?></p></td>
        <td><?php echo $form->textField($file, '['.$i.']date_stamp', array('class' => 'date','id'=>'date' . $i)); ?></td>
        <td><?php echo $form->textArea($file, '['.$i.']description', array('rows' => 6, 'cols' => 50)); ?></td>
        <td> <?php echo CHtml::submitButton("Update", array('class' => 'update btn','name'=>$i)); ?> </td>
    </tr>

    <?
   
     $i++;
}
 
//echo CHtml::submitButton("Update All", array('class' => 'btn', 'name' => 'files')); 
//    echo(CHtml::endForm());   
  $this->endWidget();
  
?>

  
</table>

<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(// the dialog
    'id' => 'dialogDisplay',
    'options' => array(
        'title' => 'Display Setting',
        'autoOpen' => false,
        'modal' => true,
        'width' => 300,
        'height' => 200,
        'buttons' => array(
            array('text' => 'Submit', 'click' => 'js:function(){ document.myform.submit();}'),
            array('text' => 'Cancel', 'click' => 'js:function(){$(this).dialog("close");}')),
    ),
));
?>
<div class="divForForm">
    <form name="myform" action="/dataset/resetPageSize" method="post">  
        Items per page:
        <select name="filePageSize" class="selectPageSize">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="200">200</option>                 
        </select>
        <input type="hidden" name="url" value="<? echo Yii::app()->request->requestUri; ?>" />

    </form>

</div>    

<?php $this->endWidget(); ?>


<?php
//                     $pageSize = isset(Yii::app()->request->cookies['filePageSize']) ?
//  Yii::app()->request->cookies['filePageSize']->value : 10;
$pagination = $files->getPagination();
$pagination->pageSize = $pageSize;

$this->widget('CLinkPager', array(
    'pages' => $pagination,
    'header' => '',
    'cssFile' => false,
));
 echo CHtml::submitButton("Update All", array('class' => 'btn', 'name' => 'files')); 
?>

<script>
//    $('.date').each(function() {
//        $(this).datepicker();
//    }
//    );
        $('.date').datepicker();
</script>