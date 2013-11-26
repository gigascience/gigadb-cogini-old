<?php if (Yii::app()->user->isGuest) {
?><div class="ext-comment-not-loggedin">
    Sorry, you have to login to leave a comment.
</div><?php } else { ?>
<div id="ext-comment-form-<?php echo $comment->isNewRecord ? 'new' : 'edit-'.$comment->id; ?>" class="form">
 
<?php $form = $this->beginWidget('CActiveForm', array(
    'id'=>'ext-comment-form',
    'action'=>array('/comment/comment/create'),
    'enableAjaxValidation'=>false
)); ?>
 
    <?php echo $form->errorSummary($comment); ?>
<div class="ext-comment">
    <div class="comment-head">
        <span class="ext-comment-head">
        Create New Comment
        </span>
    </div>
    <div class="commentspacer">
       <!-- <div class="comment-img">
            <?php /*$this->widget('comment.extensions.gravatar.yii-gravatar.YiiGravatar', array(
                             'email'=>$comment->userEmail,
                             'size'=>80,
                             'defaultImage'=>'monsterid',
                             'secure'=>false,
                             'rating'=>'r',
                             'emailHashed'=>false,
                             'htmlOptions'=>array(
                             'alt'=>CHtml::encode($comment->userName),
                             'title'=>CHtml::encode($comment->userName)
                             )
                             ));*/ ?>
        </div> -->
        <?php echo $form->error($comment,'message'); ?>
 
        <div class="textfieldholder">
        <?php echo $form->textArea($comment,'message',array(
            'class'=>'commenttextfield', //height/width is set by this css class above
            'placeholder'=>'1,000 Charter Max...',//text to display when new message field is empty
            'maxlength'=>1000, //sets number of charters aloud to be input for comment.
        )); ?>
        </div>
 
    </div>
    <div class="commentbutton">
        <?php if ($comment->isNewRecord) {
 
            echo $form->hiddenField($comment, 'type');
            echo $form->hiddenField($comment, 'key');
 
            /* echo CHtml::hiddenField('returnUrl', $this->createUrl(''));}
            echo CHtml::submitButton('Save'); */
            echo CHtml::ajaxSubmitButton('Post Comment',
                array('/comment/comment/create'),
                array(
                    'replace'=>'#ext-comment-form-new',
                    'error'=>"function(){
                        $('#Comment_message').css('border-color', 'red');
                        $('#Comment_message').css('background-color', '#fcc');
                    }"
                ),
                array('id'=>'ext-comment-submit' . (isset($ajaxId) ? $ajaxId : ''),'class'=>'btn-green')
            );
        } else {
            echo CHtml::ajaxSubmitButton('Update Comment',
                array('/comment/comment/update', 'id'=>$comment->id),
                array(
                    'replace'=>'#ext-comment-form-edit-'.$comment->id,
                    'error'=>"function(){
                        $('#Comment_message').css('border-color', 'red');
                        $('#Comment_message').css('background-color', '#fcc');
                    }"
                ),
                array('id'=>'ext-comment-submit' . (isset($ajaxId) ? $ajaxId : ''),'class'=>'btn-green')
            );
        }
        ?>
    </div>
</div>
 
<?php $this->endWidget() ?>
 
</div><!-- form -->
<?php } ?>