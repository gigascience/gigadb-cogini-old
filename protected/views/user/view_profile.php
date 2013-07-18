<?
$this->pageTitle = 'GigaDB - My GigaDB Page';
?>
<? if (Yii::app()->user->hasFlash('keyword')) { ?>
    <font color="green">
    <div>
        <?= Yii::app()->user->getFlash('keyword'); ?>
    </div>
    </font>
<? } ?>

<h2><?= Yii::t('app', 'Your Profile Page') ?></h2>
<div class="clear"></div>
<div class="row">
    <div class="span8 offset2">
        <div class="form well">
            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'EditProfile-form',
                'enableAjaxValidation' => false,
                'htmlOptions' => array('class' => 'form-horizontal'),
            ));
            ?>
            <div class="control-group">
                    <?= $form->label($model, 'email', array('class' => 'control-label')) ?>
                <div class="controls">
                    <label class="profile-label"><?= $model->email ?></label>
<?= $form->textField($model, 'email', array('size' => 30, 'maxlength' => 128, 'class' => 'profile-textbox', 'style' => 'display:none')) ?>
<?= $form->error($model, 'email') ?>
                </div>
            </div>

            <div class="control-group">
                    <?= $form->label($model, 'first_name', array('class' => 'control-label')) ?>
                <div class="controls">
                    <label class="profile-label"><?= $model->first_name ?></label>
<?= $form->textField($model, 'first_name', array('size' => 30, 'maxlength' => 60, 'class' => 'profile-textbox', 'style' => 'display:none')) ?>
<?= $form->error($model, 'first_name') ?>
                </div>
            </div>

            <div class="control-group">
                
                 
                    <?= $form->label($model, 'last_name', array('class' => 'control-label')) ?>
                <div class="controls">
                    <label class="profile-label"><?= $model->last_name ?></label>
<?= $form->textField($model, 'last_name', array('size' => 30, 'maxlength' => 60, 'class' => 'profile-textbox', 'style' => 'display:none')) ?>
<?= $form->error($model, 'last_name') ?>
                </div>
            </div>

            <div class="control-group">
                    <?= $form->label($model, 'affiliation', array('class' => 'control-label')) ?>
                <div class="controls">
                    <label class="profile-label"><?= $model->affiliation ?></label>
<?= $form->textField($model, 'affiliation', array('size' => 30, 'maxlength' => 60, 'class' => 'profile-textbox', 'style' => 'display:none')) ?>
<?= $form->error($model, 'affiliation') ?>
                </div>
            </div>

            <div class="control-group">
                <div class="controls">
<?php echo $form->checkbox($model, 'newsletter', array('disabled' => 'disabled;', 'class' => 'profile-checkbox')); ?>
                    <label disabled="disabled"><?= Yii::t('app', 'Add me to GigaDB\'s mailing list') ?></label>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <a href="/user/changePassword" class="btn"><?= Yii::t('app', 'Change Password') ?></a>
                    <a id="edit-btn" class="btn"><?= Yii::t('app', 'Edit') ?></a>
                    <a id="cancel-btn" class="btn" style="display:none"><?= Yii::t('app', 'Cancel') ?></a>
<?= MyHtml::submitButton(Yii::t('app', 'Save'), array('id' => 'save-btn', 'class' => 'btn-green', 'style' => 'display:none')) ?>
                </div>
            </div>

        </div><!--well-->

<? $this->endWidget() ?>
    </div><!--span8-->
</div><!-- user-form -->
<div class="clear"></div>

<?= $this->renderPartial('uploadedDatasets', array('uploadedDatasets' => $uploadedDatasets)); ?>
<?= $this->renderPartial('searches', array('searchRecord' => $searchRecord)); ?>

<div class="row" id="user-form">
    <div class="span8 offset2">
        <div class="form form-horizontal">
            <div class="control-group">
                <div style="text-align:center">
                    <a class="btn-green" onclick ="{
                                            $('#dialogDisplay').dialog('open');
                                        }" >Submit new dataset</a>
                   
                </div>
            </div>
        </div>
    </div>
</div>


<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(// the dialog
    'id' => 'dialogDisplay',
    'options' => array(
        'title' => 'submisson choice',
        'autoOpen' => false,
        'modal' => true,
        'width' => 300,
        'height' => 200,
        'buttons' => array(
            array('text' => 'Cancel', 'click' => 'js:function(){$(this).dialog("close");}')),
    ),
));
?>


   
        <div class="control-group">
            <div style="text-align:center">


                <form  name="myform" action="/dataset/upload" method="post">  
                   
                </form>
                <a class="btn-green" onclick="{document.myform.submit();}">Upload new dataset</a>
                <form  name="myform1" action="/dataset/upload1" method="post">  
                
                </form>
                <a class="btn-green" onclick="{document.myform1.submit();}">Submission wizard</a>

            </div>
        </div>
    </div

<?php $this->endWidget(); ?>

<script>

                                    $(function() {
                                        $('#edit-btn').click(function() {
                                            $('#save-btn').css('display', '');
                                            $('#cancel-btn').css('display', '');
                                            $('#edit-btn').css('display', 'none');
                                            $('.profile-label').css('display', 'none');
                                            $('.profile-textbox').css('display', '');
                                            $('.profile-checkbox').attr('disabled', false);
                                        });
                                        $('#cancel-btn').click(function() {
                                            $('#save-btn').css('display', 'none');
                                            $('#cancel-btn').css('display', 'none');
                                            $('#edit-btn').css('display', '');
                                            $('.profile-label').css('display', '');
                                            $('.profile-textbox').css('display', 'none');
                                            $('.profile-checkbox').attr('disabled', true);
                                        });
                                    });

                                    var el = document.getElementById('test2');
                                    el.onclick = close();


                                    function redirect(url) {
                                        document.getElementById('dialogDisplay').dialog("close");
                                    }

                                    function close() {
                                        $('#displayDialog').dialog('close');
                                    }
</script>
