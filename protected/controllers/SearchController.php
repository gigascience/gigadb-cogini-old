<?php
class SearchController extends Controller {
	public $layout = '//layouts/column2';

	public function actionEmailNewDatasets() {
		$this->render('emailNewDatasets');
	}

	public function actionEmailMatchedSearches() {
		$this->render('emailMatchedSearches');
	}

	public function actionIndex($keyword = false) {
		if ($keyword) {

			// Init the search form
			$form = new SearchForm;
			$criteria = array();

			//pre-process the keyword to escape some cahracters if the keyword applies to pattern
			$keyword_origin = $keyword;
			if (preg_match("/.*[0-9]{2}\.[0-9]{4}\/.*/", $keyword)) {
				$keyword = str_replace("/", "\/", $keyword);
			}

			$criteria['keyword'] = $keyword;
			$params = $form->params;

			foreach ($_GET as $key => $value) {
				if (in_array($key, $params)) {
					$criteria[$key] = $value;
					$form->$key = $value;
				}
			}

			if (isset($_GET['tab'])) {
				$form->tab = $_GET['tab'];
			}

            // Prepare list result criteria
			$list_result_dataset_criteria = $list_result_dataset = $this->searchDataset($criteria);
			$list_result_file_criteria = Dataset::getFileIdsByDatasetIds($list_result_dataset);

			//important, filter again the file result
			list($list_result_file_criteria, $total_files_found) = $this->searchFile($criteria, $list_result_file_criteria);

			//Now fetch files from Yii (we will put file size filter here)
			$file_criteria = new CDbCriteria();
			$file_criteria->addInCondition("t.id", $list_result_file_criteria);

			if ($this->hasSizeFilter($criteria)) {
				list($size_from, $size_to) = $this->getFileSizeFilter($criteria);
				if ($size_from < $size_to) {
					//$file_criteria->condition = "size >= $size_from AND size <= $size_to";
					$file_criteria->compare('size', ">= $size_from");
					$file_criteria->compare('size', "<= $size_to");
				}
			}

			$file_criteria->with = "dataset";

			// Prepare File Sort, Pagination and get the list of file results
			// check cookie for file sorted column
			$defaultFileSortColumn = 'dataset.identifier';
			$defaultFileSortOrder = CSort::SORT_DESC;
			if (isset($_GET['filesort'])) {
				// use new sort and save to cookie
				// check if desc or not
				$order = substr($_GET['filesort'], strlen($_GET['filesort']) - 5, 5);
				$columnName = 'dataset.identifier';
				if ($order == '.desc') {
					$columnName = substr($_GET['filesort'], 0, strlen($_GET['filesort']) - 5);
					$order = 1;
				} else {
					$columnName = $_GET['filesort'];
					$order = 0;
				}
				$defaultFileSortColumn = $columnName;
				$defaultFileSortOrder = $order;
				Yii::app()->request->cookies['file_sort_column'] = new CHttpCookie('file_sort_column', $columnName);
				Yii::app()->request->cookies['file_sort_order'] = new CHttpCookie('file_sort_order', $order);

			} else {
				// use old sort if exists
				if (isset(Yii::app()->request->cookies['file_sort_column'])) {
					$cookie = Yii::app()->request->cookies['file_sort_column']->value;
					$defaultFileSortColumn = $cookie;
				}
				if (isset(Yii::app()->request->cookies['file_sort_order'])) {
					$cookie = Yii::app()->request->cookies['file_sort_order']->value;
					$defaultFileSortOrder = $cookie;
				}
			}

			$fsort = new MySort;
			$fsort->sortVar = "filesort";
			$fsort->tab = "result_files";
			$fsort->attributes = array('*');
			$fsort->attributes[] = "dataset.identifier";
			$fsort->defaultOrder = array($defaultFileSortColumn => $defaultFileSortOrder);

			$file_pagination = new MyPagination;
			$file_pagination->tab = "result_files";
			$file_pagination->pageVar = "file_page";
			$file_result = new CActiveDataProvider('File', array('criteria' => $file_criteria, 'sort' => $fsort, 'pagination' => $file_pagination));
			if ($this->hasSizeFilter($criteria)) {
				$total_files_found = $file_result->totalItemCount;
			}

            $sample_result = new CActiveDataProvider('Sample', array('criteria' => $file_criteria));
            if ($this->hasSizeFilter($criteria)) {
                $total_files_found = $file_result->totalItemCount;
            }

			// Refine dataset search again, in order to make it linked with files result
			$file_ids = CHtml::listData(File::model()->findAll($file_criteria), 'id', 'id');
			$datasetIdsBelongToFiles = File::getDatasetIdsByFileIds($file_ids);
			$list_result_dataset_criteria = array_intersect($list_result_dataset_criteria, $datasetIdsBelongToFiles);
			// Some datasets may not have files, so we need to add them in again
			foreach (array_diff($list_result_dataset, $datasetIdsBelongToFiles) as $d) {
				if (!File::model()->findByAttributes(array('dataset_id' => $d))) {
					$list_result_dataset_criteria[] = $d;
				}
			}

			// Dataset Criteria
			$dataset_criteria = new CDbCriteria();
			$dataset_criteria->addInCondition("id", $list_result_dataset_criteria);
			$dataset_criteria->addCondition("upload_status!='Pending'");

			// check cookie for default dataset sorted column
			$defaultDatasetSortColumn = 'identifier';
			$defaultDatasetSortOrder = CSort::SORT_DESC;
			if (isset($_GET['datasetsort'])) {
				// use new sort and save to cookie

				// check if desc or not
				$order = substr($_GET['datasetsort'], strlen($_GET['datasetsort']) - 5, 5);
				$columnName = 'identifier';
				if ($order == '.desc') {
					$columnName = substr($_GET['datasetsort'], 0, strlen($_GET['datasetsort']) - 5);
					$order = 1;
				} else {
					$columnName = $_GET['datasetsort'];
					$order = 0;
				}
				Yii::app()->request->cookies['dataset_sort_column'] = new CHttpCookie('dataset_sort_column', $columnName);
				Yii::app()->request->cookies['dataset_sort_order'] = new CHttpCookie('dataset_sort_order', $order);
			} else {
				// use old sort if exists
				if (isset(Yii::app()->request->cookies['dataset_sort_column'])) {
					$cookie = Yii::app()->request->cookies['dataset_sort_column']->value;
					$defaultDatasetSortColumn = $cookie;
				}
				if (isset(Yii::app()->request->cookies['dataset_sort_order'])) {
					$cookie = Yii::app()->request->cookies['dataset_sort_order']->value;
					$defaultDatasetSortOrder = $cookie;
				}
			}

			// Prepare Dataset Sort, Pagination and get the list of file results
			$dsort = new MySort;
			$dsort->sortVar = "datasetsort";
			$dsort->tab = "result_dataset";
			$dsort->defaultOrder = array($defaultDatasetSortColumn => $defaultDatasetSortOrder);

			$dataset_pagination = new MyPagination;
			$dataset_pagination->tab = "result_dataset";
			$dataset_pagination->pageVar = "dataset_page";
			$dataset_result = new CActiveDataProvider('Dataset', array('criteria' => $dataset_criteria, 'sort' => $dsort, 'pagination' => $dataset_pagination));

			if ($keyword) {
				$result_count = count($dataset_result->getData());
				if (is_numeric($keyword) && strlen($keyword) == 6) {

					$result = Dataset::model()->findByAttributes(array('identifier' => $keyword));
					if ($result != NULL && $result->upload_status != 'Published') {
						$this->render('invalid', array('model' => $form, 'keyword' => $keyword));
						return;
					}
				}
				if ($result_count == 0) {
					$form = new SearchForm;
					$this->render('invalid', array('model' => $form, 'keyword' => $keyword, 'general_search' => 1));
					return;
				}
			}

            /*$datas = array();
            foreach ($dataset_result->getData() as $dataset) {
                $datas[$dataset->id] = array(
                    'data' => $dataset,
                    'files' => $dataset->files,
                    'samples' => $dataset->samples
                );
            }*/

			$search_result = array(
                'dataset_result' => $dataset_result, 
                'file_result' => $file_result, 
                'sample_result' => $sample_result
            );

			$form->keyword = $criteria['keyword'];
			$form->criteria = json_encode($criteria);

			$this->render('index', array(
				'model' => $form,
				'search_result' => $search_result,
                'total_files' => $search_result['file_result']->totalItemCount,
                'total_samples' => 0,
                'total_datasets' => $search_result['dataset_result']->totalItemCount,
				'datasetTypes' => Type::getListTypes(),
				'file_types' => FileType::getListTypes(),
				'file_formats' => FileFormat::getListFormats(),
				'list_common_names' => Species::getListCommonNames(),
				'list_external_link_types' => ExternalLinkType::getListTypes(),
				'filesort_column' => $defaultFileSortColumn,
				'filesort_order' => $defaultFileSortOrder,
				'exclude' => (isset($_GET['exclude'])) ? 'True' : null,
				'total_files_found' => $total_files_found,
			));
		} else {
			Yii::app()->user->setFlash('keyword', 'Keyword can not be blank');
			$this->redirect(array("/site/index"));
		}
	}

	/* return CActiveRecords */
	private function getFullFileResultByKeyword($fileIds) {
		$temp_file_criteria = new CDbCriteria();
		$temp_file_criteria->addInCondition("id", $fileIds);
		return File::model()->findAll($temp_file_criteria);
	}

	private function getFullDatasetResultByKeyword($keyword) {
		$wordCriteria = array();
		$wordCriteria['keyword'] = $keyword;
		$list_result_dataset_criteria = $this->searchDataset($wordCriteria);
		$temp_dataset_criteria = new CDbCriteria();
		$temp_dataset_criteria->addInCondition("id", $list_result_dataset_criteria);
		return Dataset::model()->findAll($temp_dataset_criteria);
	}

	public function actionRedirect($id) {
		$criteria = SearchRecord::model()->findByPk($id);
		if ($criteria == null) {
			throw new CHttpException(500, 'The requested record does not exist.');
		} else {
			$criteria = json_decode($criteria->query, true);
			$params = array();
			$params[] = 'search/index';
			foreach ($criteria as $key => $value) {
				if (stristr($key, "date")) {
					$params[$key] = str_replace("/", "-", $value);
				} else {
					$params[$key] = $value;
				}

			}
			$this->redirect($params);
		}

	}

	private function searchFile($criteria, $extraFileIds = array()) {
		return File::sphinxSearch($criteria, $extraFileIds);
	}

	private function searchDataset($criteria, $extraDatasetIds = array()) {
		return Dataset::sphinxSearch($criteria, $extraDatasetIds);
	}

	public function actionSave() {
		$result = array();

		if (Yii::app()->user->isGuest) {
			$result['status'] = "fail";
			$result['reason'] = "You must log in to save search query";
		} else {
			$criteriaStr = $_POST['criteria'];
			$criteria = json_decode($criteriaStr, true);
			if (isset($criteria['keyword']) && strlen($criteria['keyword']) > 0) {
				if (isset($criteria['exclude'])) {
					unset($criteria['exclude']);
				}

				$search = new SearchRecord;
				$search->user_id = Yii::app()->user->getId();
				$search->name = $criteria['keyword'];

				$temp_dataset_result = $this->searchDataset($criteria);

				$search->query = json_encode($criteria);
				$search->result = json_encode($temp_dataset_result);

				if ($search->save()) {
					$result['status'] = "success";
				} else {
					$result['status'] = "fail";
					$result['reason'] = "Unknown Reason";
				}

			} else {
				$result['status'] = "fail";
				$result['reason'] = "Problem with search query, pls check";
			}
		}
		echo json_encode($result);
	}

	public function actionDelete() {
		$id = $_POST['id'];
		$result = array();
		$model = SearchRecord::model()->findByPk($id);
		if ($model) {
			if ($model->user_id == Yii::app()->user->getId()) {
				if ($model->delete()) {
					$result['status'] = "success";
					$result['id'] = $id;
				} else {
					$result['status'] = "fail";
					$result['reason'] = "Unknown Error occur";
				}

			} else {
				$result['status'] = "fail";
				$result['reason'] = "This record does not belongs to you";
			}

		} else {
			$result['status'] = "fail";
			$result['reason'] = "Record Not Found";

		}

		echo json_encode($result);
	}

	private function getFileSizeFilter($criteria) {
		$size_from = isset($criteria['size_from']) ? $criteria['size_from'] : "";
		$size_to = isset($criteria['size_to']) ? $criteria['size_to'] : "";
		$size_from_unit = isset($criteria['size_from_unit']) ? $criteria['size_from_unit'] : "";
		$size_to_unit = isset($criteria['size_to_unit']) ? $criteria['size_to_unit'] : "";

		if ($size_from == "") {
			$size_from = 0;
		} elseif ($size_from_unit == 1) {
			$size_from *= 1024;
		} else if ($size_from_unit == 2) {
			$size_from *= 1024 * 1024;
		} else if ($size_from_unit == 3) {
			$size_from *= 1024 * 1024 * 1024;
		} else if ($size_from_unit == 4) {
			$size_from *= 1024 * 1024 * 1024 * 1024;
		} else {
			$size_from = 0;
		}

		if ($size_to == "") {
			$size_to = 9999999999999;
		} elseif ($size_to_unit == 1) {
			$size_to *= 1024;
		} else if ($size_to_unit == 2) {
			$size_to *= 1024 * 1024;
		} else if ($size_to_unit == 3) {
			$size_to *= 1024 * 1024 * 1024;
		} else if ($size_to_unit == 4) {
			$size_to *= 1024 * 1024 * 1024 * 1024;
		} else {
			$size_to = 0;
		}

		return array($size_from, $size_to);
	}

	private function hasSizeFilter($criteria) {
		return (isset($criteria['size_from']) || isset($criteria['size_to']));
	}

}
