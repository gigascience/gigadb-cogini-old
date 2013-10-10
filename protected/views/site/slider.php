
<!--<button type="button" onclick='/search/index/?type=' >show all</button>-->


<div id="slider_partial" style="display:inline">  
    <?
    //it means all
    if ($type == 0) {
        $suffix = "dataset_type[]=1";
        for($i=2;$i<=10;$i++){
           $suffix .= ("&dataset_type[]=".$i);
        }    
        echo Myhtml::link("show all", "/search/index?".$suffix, array('class' => 'myCenter'));
    } else {
        echo Myhtml::link("show all", "/search/index?dataset_type[]=" . $type, array('class' => 'myCenter'));
    }
    ?>

    <div id="myCarousel" class="carousel slide" >
        <!-- Carousel items -->
        <div class="carousel-inner module-inner" style='height:305px;'>
            <?php
            $active = "active";
            $itemPerSlide = 3;
            $i = 0;
//        echo count($datasets)." test";
            foreach ($datasets as $key => $dataset) {
                if ($i % $itemPerSlide == 0) {
                    ?>
                    <div class="<? echo $active; ?> item">
                    <? } ?>
                    <div class="data-block">
                        <?php
                        $url = $dataset->getImageUrl();

                        echo MyHtml::link(MyHtml::image($url, 'image'), "/dataset/" . $dataset->identifier, array('class' => 'image-hint',));
                        echo 'DOI: ' . MyHtml::link("10.5524/" . $dataset->identifier, "/dataset/" . $dataset->identifier);
                        echo '<br/><br/>';
                        $dtitle = strlen($dataset->title) > 70 ? strip_tags(substr($dataset->title, 0, 70)) . '...' : $dataset->title;
                        echo $dtitle;
                        echo '<br/><br/>';
                        echo MyHtml::encode($dataset->publication_date);
                        ?>
                    </div>
                    <?php if ($i % $itemPerSlide == ($itemPerSlide - 1) || $i == count($datasets) - 1) { ?>
                    </div>
                    <?
                }
                $i++;
                $active = "";
            }
            ?>

        </div>


        <!-- Carousel nav -->
        <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
        <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
    </div>

</div>
<script>
    $('#myCarousel').carousel({interval: 60000});
    $(".image-hint").tooltip({'placement': 'top'});
</script>
