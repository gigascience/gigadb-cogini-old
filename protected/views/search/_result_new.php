<div class="tab-content">
		<?php foreach ($search_result['dataset_result']->getData() as $dataset) : ?>
		<div class="dataset">

			<p class="link-result link-dataset">
				<a data-content="<?php echo MyHtml::encode($dataset->description) ?>" class="left content-popup" href="/dataset/<?php echo $dataset->identifier ?>"><?php echo $dataset->title ?></a>
			</p>
			<p class="informations-dataset">
				<strong>
				<?php foreach ($dataset->getAuthor() as $author) : ?>
					<a href="/search/index?keyword=<?php echo $author['surname'] . ', ' . $author['first_name'] ?>">
						<?php echo $author['surname'] . ', ' . $author['first_name'] ?>
					</a>; 
				<?php endforeach ?>
				</strong>
				<br/><?= Yii::t('app', 'DOI') ?>:<?php echo "10.5524/" . $dataset->identifier ?>
			</p>

			<?php foreach ($dataset->samples as $sample) : ?>
				<p class="link-result link-sample">
					<a class="" href="http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?mode=Info&id=<?php echo $sample->species->tax_id ?>"><?php echo $sample->name ?></a><br/>
					<strong><?php echo $sample->species->common_name ?></strong><br/>
					NCBI taxonomy : <?php echo $sample->species->tax_id ?>
				</p>
			<?php endforeach ?>

			<?php foreach ($search_result['file_result']->getData() as $file) : ?>
				<?php if ($file->dataset->id == $dataset->id) : ?>
					<p class="link-result link-file info-file first">
						<a class="" href="<?php echo $file->location ?>"><?php echo strlen($file->name) > 20 ? substr($file->name, 0, 20). '...' : $file->name ?></a>
					</p>
					<p class="info-file middle type"><?php echo $file->type->name ?></p>
					<p class="info-file middle size"><?php echo MyHtml::encode(File::staticBytesToSize($file->size))?></p>
					<p class="info-file middle"><input type="checkbox" name="files[]" value="<?php echo $file->id ?>"/></p>
					<div class="clear"></div>
				<?php endif ?>
			<?php endforeach ?>

		</div>
		<?php endforeach ?>
</div>