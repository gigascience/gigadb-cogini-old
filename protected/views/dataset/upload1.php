<h2>Online Submission</h2>
<div class="clear"></div>

<div class="row">
    <div class="span8 offset2">
        <div class="form well">
            Provide all the information required for submission via a series of web-forms:
            <br/><br/>
            <ul>
                <li>Study details</li>

                <li>Authors</li>
                <li>Project details</li>
                <li>links and related datasets</li>
                <li>Sample information</li>

            </ul>

            <input id="agree-checkbox" type="checkbox" style="margin-right:5px"/><a target="_blank" href="/site/term">I have read GigaDB's Terms and Conditions</a>
            <br/>
            <div class="clear"></div>
            <?php echo MyHtml::form(Yii::app()->createUrl('dataset/create1'), 'post', array('enctype' => 'multipart/form-data')); ?>
            <div class="pull-right">
                <?php echo MyHtml::submitButton('Submission wizard', array('class' => 'btn-green upload-control', 'disabled' => 'disabled')); ?>
            </div>
            <br/>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('#agree-checkbox').click(function() {
            if ($(this).is(':checked')) {
                $('.upload-control').attr('disabled', false);
            } else {
                $('.upload-control').attr('disabled', true);
            }
        });
    });
</script>
