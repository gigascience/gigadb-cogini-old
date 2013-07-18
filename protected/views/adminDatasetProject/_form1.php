


<div class="span12 form well">
    <div class="form-horizontal">



        <div class="form">

            <?php
            $this->widget('ext.selgridview.SelGridView', array(
                'id' => 'author-grid',
                'dataProvider' => $project_model,
                'selectableRows' => 10,
                'itemsCssClass' => 'table table-bordered',
                'columns' => array(
                    'name',
                    array(
                        'class' => 'CButtonColumn',
                        'template' => '{delete}',
                        'buttons' => array
                            (
                            'delete' => array
                                (
                                'label' => 'delete this row',
                                'url' => 'Yii::app()->createUrl("/adminDatasetProject/delete1", array("id"=>$data["id"]))',
                            ),
                        ),
                    ),
                ),
            ));
            ?>

            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'dataset-project-form',
                'enableAjaxValidation' => false,
            ));
            ?>

          
            
            <p class="note">If your dataset is part of a
                larger collaborative international project, 
                please select it from the list below.</p>
            <br/>
            <?php echo $form->errorSummary($model); ?>



            <div class="control-group">
                <?php echo $form->labelEx($model, 'project_id', array('class' => 'control-label')); ?>
                <div class="controls">
                    <?= CHtml::activeDropDownList($model, 'project_id', CHtml::listData(Project::model()->findAll(), 'id', 'name')); ?>
                    <?php echo $form->error($model, 'project_id'); ?>
                </div>
            </div>

            <div class="span12" style="text-align:center">
                <a href="/adminDatasetProject/admin" class="btn">Cancel</a>
                <?php echo CHtml::submitButton($model->isNewRecord ? 'Add' : 'Save', array('class' => 'btn')); ?>
                <a href="/adminDatasetAuthor/create1" class="btn-green">Previous</a>
                <a href="/adminLink/create1" class="btn-green">Next</a>
            </div>

            <?php $this->endWidget(); ?>

        </div><!-- form -->

    </div>
</div>
