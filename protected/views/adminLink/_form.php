<div class="row">
    <div class="span8 offset2 form well">
        <div class="clear"></div>
        <div class="form">

            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'link-form',
                'enableAjaxValidation' => false,
                'htmlOptions' => array('class' => 'form-horizontal')
            ));
            ?>

            <p class="note">Fields with <span class="required">*</span> are required.</p>

            <?php echo $form->errorSummary($model); ?>

            <div class="control-group">
                <?php echo $form->labelEx($model, 'dataset_id', array('class' => 'control-label')); ?>
                <div class="controls">
                    <?= CHtml::activeDropDownList($model, 'dataset_id', CHtml::listData(Dataset::model()->findAll('1=1 order by id desc'), 'id', 'identifier')); ?>
                    <?php echo $form->error($model, 'dataset_id'); ?>
                </div>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model, 'is_primary', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="tick this if the SAME data is present in GigaDB AND the external database, leave it unticked if the external database holds related but not the same data. E.g. If GigaDB has assembly, SRA has raw sequences, leave it unticked."></a>
                <div class="controls">
                    <?php echo $form->checkBox($model, 'is_primary'); ?>
                    <?php echo $form->error($model, 'is_primary'); ?>
                </div>
            </div>


            <div class="control-group">

                <?php echo $form->labelEx($model, 'database', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="Pselect database/source of external link from the drop down menu."></a>
                <div class="controls">
                    <?= CHtml::activeDropDownList($model, 'database', $link_database) ?>
                    <?php echo $form->error($model, 'databse'); ?>
                </div>
            </div>

            <div class="control-group">
                <?php echo $form->labelEx($model, 'acc_num', array('class' => 'control-label')); ?>
                <a class="myHint" data-content="provide unique identifier of linked data, e.g. an SRA accession; SRS012345."></a>
                <div class="controls">
                    <?php echo $form->textField($model, 'acc_num', array('size' => 60, 'maxlength' => 100)); ?>
                    <?php echo $form->error($model, 'acc_num'); ?>
                </div>
            </div>

            <div class="pull-right">
                <a href="/adminLink/admin" class="btn">Cancel</a>
                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'btn')); ?>
            </div>

            <?php $this->endWidget(); ?>

        </div><!-- form -->
    </div>
</div>
<script>

    $('.hint').popover();
    $('.myHint').popover();
</script>
