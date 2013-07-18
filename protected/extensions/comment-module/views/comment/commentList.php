<?php
 
/** @var CArrayDataProvider $comments */
$comments = $model->getCommentDataProvider();
$comments->pagination->pageSize = 20; //sets number of comments to display per page
 
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$comments,
    'itemView'=>'comment.views.comment._view',
        'template'=>'{items}{pager}<br/><br/>', //breaks are here because my css wasn't cooperating and it needed to be spaced because of overlap
));
 
$this->renderPartial('comment.views.comment._form', array(
    'comment'=>$model->commentInstance,
    'data' =>  Comment::model()
));