<?php
    Yii::app()->clientScript->registerCss('ext-comment', "");
?>
<div class="ext-comment" id="ext-comment-<?php echo $data->id; ?>">
    <div class="comment-head">
        <span class="ext-comment-head">
            <span class="ext-comment-name"><?php echo CHtml::encode($data->userName); ?></span>
            wrote on
            <span class="ext-comment-date">
                <?php 
                
                
                   $epoch = $data->createDate;
                        $dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
                        $timezone="Asia/Hong_Kong";
                        $dt->setTimezone(new DateTimeZone($timezone));
                    
                        $TimeStr= $dt->format('Y-m-d H:i:s');
                        echo $TimeStr;
                
             
                ?>
            </span>
        </span>
        <span class="ext-comment-options">
            <?php if ( !Yii::app()->user->isGuest &&
                   ( (Yii::app()->user->id == $data->userId) || Yii::app()->user->roles=='admin' )   ) {
                echo CHtml::ajaxLink('delete', array('/comment/comment/delete', 'id'=>$data->id), array(
                    'success'=>'function(){ $("#ext-comment-'.$data->id.'").remove(); }',
                    'type'=>'POST',
                ), array(
                    'id'=>'delete-comment-'.$data->id,
                    'confirm'=>'Are you sure you want to delete this item?',
                ));
                echo " | ";
                echo CHtml::ajaxLink('edit', array('/comment/comment/update', 'id'=>$data->id), array(
                    'replace'=>'#ext-comment-'.$data->id,
                    'type'=>'GET',
                ), array(
                    'id'=>'ext-comment-edit-'.$data->id,
                ));
            }
            /* adds edit link to post if is not admin's post so they can still edit it */
            /*    elseif (Yii::app()->user->roles=='admin') {
                echo CHtml::ajaxLink('edit', array('/comment/comment/update', 'id'=>$data->id), array(
                    'replace'=>'#ext-comment-'.$data->id,
                    'type'=>'GET',
                ), array(
                    'id'=>'ext-comment-edit-'.$data->id,
                ));
            }*/
            ?>
        </span>
    </div>
    <div class="commentspacer">
        <!-- <div class="comment-img"> -->
            <?php /*$this->widget('comment.extensions.gravatar.yii-gravatar.YiiGravatar', array(
                          'email'=>$data->userEmail,
                          'size'=>80,
                          'defaultImage'=>'monsterid',
                          'secure'=>false,
                          'rating'=>'r',
                          'emailHashed'=>false,
                          'htmlOptions'=>array(
                          'alt'=>CHtml::encode($data->userName),
                          'title'=>CHtml::encode($data->userName)
                          )
                          )); */?>
       <!-- </div> -->
 
 
        <p><?php echo nl2br(CHtml::encode($data->message)); ?></p>
 
    </div>
</div>