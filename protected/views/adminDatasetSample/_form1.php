<div class="span12 form well">
    <div class="form-horizontal">

        <?php
        $this->widget('ext.selgridview.SelGridView', array(
            'id' => 'author-grid',
            'dataProvider' => $sample_model,
            'selectableRows' => 10,
            'itemsCssClass' => 'table table-bordered',
            'columns' => array(
                array('header' => 'Species Name', 'name' => 'species'),
                array('header' => 'Attributes', 'name' => 'attrs'),
                array('header' => 'Sample ID', 'name' => 'name'),
                array(
                    'class' => 'CButtonColumn',
                    'template' => '{delete}',
                    'buttons' => array
                        (
                        'delete' => array
                            (
                            'label' => 'delete this row',
                            'url' => 'Yii::app()->createUrl("/adminDatasetSample/delete1", array("id"=>$data["id"]))',
                        ),
                    ),
                ),
            ),
        ));
        ?>

        <div class="form">



            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'dataset-sample-form',
                'enableAjaxValidation' => false,
            ));
            ?>


            <p class="note">Fields with <span class="required">*</span> are required.</p>

            <?php echo $form->errorSummary($model); ?>









            <div class="control-group">
                <?php echo $form->labelEx($model, 'species', array('class' => 'control-label')); ?>
                <div class="controls">

                    <?php
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                        'name' => 'name',
                        'model' => $model,
                        'attribute' => 'species',
                        'source' => $this->createUrl('/adminDatasetSample/autocomplete'),
                        'options' => array(
                            'minLength' => '1',
                        ),
                        'htmlOptions' => array(
                            'placeholder' => 'name',
                        ),
                    ));
                    ?>

                    <?php echo $form->error($model, 'species'); ?>
                </div>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model, 'attribute', array('class' => 'control-label')); ?>
                <div class="controls">
                    <?php echo $form->textArea($model, 'attribute', array('rows' => 6, 'cols' => 50)); ?>
                    <?php echo $form->error($model, 'attribute'); ?>
                </div>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model, 'code', array('class' => 'control-label')); ?>
                <div class="controls">
                    <?php echo $form->textField($model, 'code', array('size' => 50, 'maxlength' => 50)); ?>
                    <?php echo $form->error($model, 'code'); ?>
                </div>
            </div>






            <div class="span12" style="text-align:center">
                <a href="/adminDatasetSample/admin" class="btn">Cancel</a>

                <?php echo CHtml::submitButton($model->isNewRecord ? 'Add' : 'Save', array('class' => 'btn')); ?>

                <a href="/adminRelation/create1" class="btn-green">Previous</a>
                <a href="/dataset/submit" class="btn-green">Submit</a>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>





