<?php

class SearchController extends Controller {

    public $layout = '//layouts/column2';

    public function actionEmailNewDatasets() {
        $this->render('emailNewDatasets');
    }

    public function actionEmailMatchedSearches() {
        $this->render('emailMatchedSearches');
    }

    public function actionResetPageSize() {



        if (isset($_POST['pageSize'])) {
// $_SESSION['pageSize'] = $_POST['pageSize'];  
            $cookie = new CHttpCookie('pageSize', $_POST['pageSize']);
//half year
            $cookie->expire = time() + 60 * 60 * 24 * 180;
            Yii::app()->request->cookies['pageSize'] = $cookie;
        }
        if (isset($_POST['url'])) {
            $this->redirect($_POST['url']);
        }
    }

    public function actionIndex($keyword = false) {
        if (count($_GET) == 1 && isset($_GET['keyword']) && $_GET['keyword'] == "") {
            Yii::app()->user->setFlash('keyword', 'Keyword can not be blank');
            $this->redirect(array("/site/index"));
        } else {

            $form = new SearchForm;
            $datasetTypes = Type::getListTypes();

            $file_types = FileType::getListTypes();
            $file_formats = FileFormat::getListFormats();
// $list_common_names=Species::getListCommonNames();
            $list_external_link_types = ExternalLinkType::getListTypes();

            $criteria = array();
//pre-process the keyword to escape some cahracters if the keyword applies to pattern
            $pattern = "/.*[0-9]{2}\.[0-9]{4}\/.*/";
            $keyword_origin = $keyword;
            if (preg_match($pattern, $keyword)) {
                $keyword = str_replace("/", "\/", $keyword);
            }


            $criteria['keyword'] = $keyword;

            $params = array('dataset_type', 'project', 'file_type',
                'file_format', 'pubdate_from', 'pubdate_to', 'moddate_from'
                , 'moddate_to', 'common_name', 'reldate_from', 'reldate_to'
                , 'size_from', 'size_to', 'exclude', 'external_link_type',
                'size_from_unit', 'size_to_unit');

            foreach ($_GET as $key => $value) {
                if (in_array($key, $params)) {
                    $criteria[$key] = $value;
                    $form->$key = $value;
                }
            }

            if (isset($_GET['tab'])) {
                $form->tab = $_GET['tab'];
            }

            $list_result_dataset = $this->searchDataset($criteria);
//            var_dump(count($list_result_dataset));
// clone searched dataset
            $list_result_dataset_criteria = $list_result_dataset;

            $list_result_file_criteria = Dataset::getFileIdsByDatasetIds($list_result_dataset);
//            var_dump(count($list_result_file_criteria)." before searchfile");
//important, filter again the file result
//            $list_result_file
            //if there is not conditon about file, we don't need to search files
            $file_type = isset($criteria['file_type']) ? $criteria['file_type'] : "";
            $file_format = isset($criteria['file_format']) ? $criteria['file_format'] : "";
            $reldate_from = isset($criteria['reldate_from']) ? $criteria['reldate_from'] : "";
            $reldate_to = isset($criteria['reldate_to']) ? $criteria['reldate_to'] : "";

            $total_file_count = count($list_result_file_criteria);
            $total_files_found = 0;
            if ($file_type == "" && $file_format == "" && $reldate_from == "" && $reldate_to == "") {
                
            } else {
                $criteria['keyword'] = '';

                $limit = 5000;
                if ($total_file_count <= $limit)
                    list($list_result_file_criteria, $total_files_found ) = $this->searchFile($criteria, $list_result_file_criteria);
                else {
                    $total_files = 0;
                    $loop = ($total_file_count + $limit - 1) / $limit;
                    $list_result_file_temp = array();
                    $found_temp = 0;
                    $list_result_file = array();
                    $end = $limit;
                    for ($i = 0; $i < $loop; $i++) {
                        $start = $i * $limit;
                        if ($i == $loop - 1)
                            $end = $total_file_count;
                        else
                            $end = ($i + 1) * $limit;
                        for ($j = $start; $j < $end; $j++)
                            $list_result_file_temp[] = $list_result_file_criteria[$j];

                        list($list_result_file_temp, $found_temp ) = $this->searchFile($criteria, $list_result_file_temp);

                        $total_files += $found_temp;
                        $list_result_file = array_merge($list_result_file, $list_result_file_temp);
                    }

                    $total_files_found = $total_files;
                    $list_result_file_criteria = $list_result_file;
                }

                $criteria['keyword'] = $keyword;
            }


//            var_dump(count($list_result_file_criteria)." after search file");
//            var_dump($total_files_found." totals_files_found");
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

// Refine dataset search again, in order to make it linked with files result
            $file_ids = CHtml::listData(File::model()->findAll($file_criteria), 'id', 'id');
//            var_dump(count($file_ids)." file fileter again");
            $datasetIdsBelongToFiles = File::getDatasetIdsByFileIds($file_ids);
//            var_dump(count($datasetIdsBelongToFiles)." get dataset ids ");
            $list_result_dataset_criteria = array_intersect($list_result_dataset_criteria, $datasetIdsBelongToFiles);
// Some datasets may not have files, so we need to add them in again
            foreach (array_diff($list_result_dataset, $datasetIdsBelongToFiles) as $d) {
                if (!File::model()->findByAttributes(array('dataset_id' => $d)))
                    $list_result_dataset_criteria[] = $d;
            }


//             var_dump(count($list_result_dataset_criteria)." empty files dataseta added after");
// 
// Dataset Criteria
            $dataset_criteria = new CDbCriteria();
            $dataset_criteria->addInCondition("id", $list_result_dataset_criteria);
            $dataset_criteria->addCondition("upload_status='Published'");

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


//            var_dump($dataset_result);
            $search_result = array('dataset_result' => $dataset_result, 'file_result' => $file_result);

            $form->keyword = $keyword_origin;
            $form->criteria = json_encode($criteria);

//Yii::beginProfile('getFullDatasetResultbyKeyword');
            $full_dataset_result = $this->getFullDatasetResultByKeyword($keyword);
//Yii::endProfile('getFullDatasetResultbyKeyword');
//Yii::beginProfile('getFullFILE ResultbyKeyword');
            $full_file_result = $this->getFullFileResultByKeyword($list_result_file_criteria);
//Yii::endProfile('getFullFILE ResultbyKeyword');
//to avoid memory overflow

            $list_common_names = array();
            for ($i = 0; $i < count($full_dataset_result); $i++) {
                $temp_dataset = $full_dataset_result[$i];
                $samples = $temp_dataset->samples;
                foreach ($samples as $key => $sample) {
                    if (is_numeric($sample->species_id)) {
                        $list_common_names[$sample->species_id] =
                                Species::model()->findByAttributes(array('id' => $sample->species_id))->common_name;
//                        $common_names[$sample->species_id] = $list_common_names[$sample->species_id];
                    }
                }
            }

            $this->render('index', array('model' => $form,
                'search_result' => $search_result,
                'full_dataset_result' => $full_dataset_result,
                'full_file_result' => $full_file_result,
                'datasetTypes' => $datasetTypes,
                'file_types' => $file_types,
                'file_formats' => $file_formats,
                'list_common_names' => $list_common_names,
                'list_external_link_types' => $list_external_link_types,
                'filesort_column' => $defaultFileSortColumn,
                'filesort_order' => $defaultFileSortOrder,
                'exclude' => (isset($_GET['exclude'])) ? 'True' : null,
                'total_files_found' => $total_files_found,
            ));
        }
    }

    public function actionIndex1($keyword = false) {
        if (count($_GET) == 1 && isset($_GET['keyword']) && $_GET['keyword'] == "") {
            Yii::app()->user->setFlash('keyword', 'Keyword can not be blank');
            $this->redirect(array("/site/index"));
        } else {
            $form = new SearchForm;
            $datasetTypes = Type::getListTypes();

            $file_types = FileType::getListTypes();
            $file_formats = FileFormat::getListFormats();

//            $list_common_names=Species::getListCommonNames();

            $list_external_link_types = ExternalLinkType::getListTypes();




            $criteria = array();
            $criteria['keyword'] = $keyword;

            $params = array('dataset_type', 'project',
                'file_type', 'file_format', 'pubdate_from', 'pubdate_to',
                'moddate_from', 'moddate_to', 'common_name', 'reldate_from',
                'reldate_to', 'size_from', 'size_to', 'exclude', 'external_link_type',
                'size_from_unit', 'size_to_unit',);

            foreach ($_GET as $key => $value) {
                if (in_array($key, $params)) {
//                    if($key=='type')
//                        $key='dataset_type';
                    $criteria[$key] = $value;
                    $form->$key = $value;
                }
            }

            if (isset($_GET['tab'])) {
                $form->tab = $_GET['tab'];
            }

            $list_result_dataset = $this->searchDataset($criteria);
//            var_dump($list_result_dataset);
            if ($keyword) {
                if (is_numeric($keyword) && strlen($keyword) == 6) {
                    $result = Dataset::model()->findByAttributes(array('identifier' => $keyword));
                    if ($result != NULL && $result->upload_status != 'Published') {
                        $this->render('invalid', array('model' => $form, 'keyword' => $keyword));
                        return;
                    }
                }

                if ($keyword != "" && count($list_result_dataset) == 0) {
                    $form = new SearchForm;
                    $this->render('invalid', array('model' => $form, 'keyword' => $keyword, 'general_search' => 1));
                    return;
                }
                if ($keyword == "") {
                    $list_result_dataset = array();
                    $results = Yii::app()->db->createCommand("select dataset.id from dataset where dataset.upload_status='Published'")->queryAll();
                    foreach ($results as $result) {
                        $list_result_dataset[] = $result['id'];
                    }
//                    var_dump("here");
                }
            } else if (isset($_GET['type'])) {
                $form = new SearchForm;
                $datasetTypes = Type::getListTypes();

                $file_types = FileType::getListTypes();
                $file_formats = FileFormat::getListFormats();

//            $list_common_names=Species::getListCommonNames();

                $list_external_link_types = ExternalLinkType::getListTypes();
                $criteria = array();
                $criteria['keyword'] = "";
                $type = $_GET['type'];
                if ($type > 0) {
                    $list_result_dataset = array();
                    $results = Yii::app()->db->createCommand("select dataset.id from dataset join dataset_type on dataset.id = dataset_type.dataset_id where dataset_type.type_id=:type and dataset.upload_status = 'Published'")->bindValue('type', $type)->queryAll();
                    foreach ($results as $result) {
                        $list_result_dataset[] = $result['id'];
                    }
                } else {
                    $list_result_dataset = array();
                    $results = Yii::app()->db->createCommand("select dataset.id from dataset where dataset.upload_status='Published'")->queryAll();
                    foreach ($results as $result) {
                        $list_result_dataset[] = $result['id'];
                    }
                }
            }
//            var_dump($list_result_dataset);
//redirect to the index when the search result is empty
// clone searched dataset
//            var_dump(count($list_result_dataset)." list_result_dataset");
            $list_result_dataset_criteria = $list_result_dataset;


            $list_result_file_criteria = Dataset::getFileIdsByDatasetIds($list_result_dataset);

            //important, filter again the file result
            list($list_result_file_criteria , $total_files_found )=$this->searchFile($criteria, $list_result_file_criteria);
//,$list_result_file_criteria);
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
            /* if (isset($criteria['exclude'])) {
              $file_criteria->addInCondition("dataset.id",array_diff(CHtml::listData(Dataset::model()->findAll(),'id','id'), explode(",", $criteria['exclude']))); // this line of code is to also hide the files belong to hidden datasets
              }LONG: Removing this line because exclude is now included in the dataset search already */

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
// $file_pagination->pageSize=100;
            $file_result = new CActiveDataProvider('File', array('criteria' => $file_criteria, 'sort' => $fsort, 'pagination' => $file_pagination));
            if ($this->hasSizeFilter($criteria))
                $total_files_found = $file_result->totalItemCount;

// Refine dataset search again, in order to make it linked with files result
//$datasetIdsBelongToFiles = File::getDatasetIdsByFileIds($list_result_file_criteria);
            $file_ids = CHtml::listData(File::model()->findAll($file_criteria), 'id', 'id');
            $datasetIdsBelongToFiles = File::getDatasetIdsByFileIds($file_ids);

            $list_result_dataset_criteria = array_intersect($list_result_dataset_criteria, $datasetIdsBelongToFiles);

// Some datasets may not have files, so we need to add them in again
            foreach (array_diff($list_result_dataset, $datasetIdsBelongToFiles) as $d) {
                if (!File::model()->findByAttributes(array('dataset_id' => $d)))
                    $list_result_dataset_criteria[] = $d;
            }
// var_dump(count($list_result_dataset_criteria)." empty files dataseta after");
// Dataset Criteria
            $dataset_criteria = new CDbCriteria();
            $dataset_criteria->addInCondition("id", $list_result_dataset_criteria);
//             $dataset_criteria->addCondition("upload_status!='Pending'");
            $dataset_criteria->addCondition("upload_status='Published'");


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
// $dataset_pagination->pageSize = 100;
            if (isset($_GET['type'])) {
                $type = $_GET['type'];
                $dataset_criteria = new CDbCriteria;
                if ($type > 0) {
                    $dataset_criteria->join = "join dataset_type on t.id=dataset_type.dataset_id";
                    $dataset_criteria->condition = "dataset_type.type_id=" . $type . " and t.upload_status = 'Published'";
                } else {

//                    $criteria->join = "join dataset_type on dataset.id=dataset_type.dataset_id";
                    $dataset_criteria->condition = "t.upload_status = 'Published'";
                }
//                $dataset_result = new CActiveDataProvider('Dataset', array('criteria' => $criteria, 'sort' => $dsort, 'pagination' => $dataset_pagination));
            }

            $dataset_result = new CActiveDataProvider('Dataset', array('criteria' => $dataset_criteria, 'sort' => $dsort, 'pagination' => $dataset_pagination));
//            var_dump(count($dataset_result));

            $search_result = array('dataset_result' => $dataset_result, 'file_result' => $file_result);
//            var_dump($search_result);
            $form->keyword = $criteria['keyword'];
            $form->criteria = json_encode($criteria);


            $full_dataset_result = $this->getFullDatasetResultByKeyword($keyword);
//since species table is so big, we can only retrieve the useful records rather than entire
            $list_common_names = array();

            for ($i = 0; $i < count($full_dataset_result); $i++) {
                $temp_dataset = $full_dataset_result[$i];
                $samples = $temp_dataset->samples;
                foreach ($samples as $key => $sample) {
                    if (is_numeric($sample->species_id)) {
                        $list_common_names[$sample->species_id] =
                                Species::model()->findByAttributes(array('id' => $sample->species_id))->common_name;
//                        $common_names[$sample->species_id] = $list_common_names[$sample->species_id];
                    }
                }
            }

            $this->render('index', array('model' => $form,
                'search_result' => $search_result,
                'full_dataset_result' => $full_dataset_result,
                'full_file_result' => $this->getFullFileResultByKeyword($list_result_file_criteria),
                'datasetTypes' => $datasetTypes,
                'file_types' => $file_types,
                'file_formats' => $file_formats,
                'list_common_names' => $list_common_names,
                'list_external_link_types' => $list_external_link_types,
                'filesort_column' => $defaultFileSortColumn,
                'filesort_order' => $defaultFileSortOrder,
                'exclude' => (isset($_GET['exclude'])) ? 'True' : null,
                'total_files_found' => $total_files_found,
            ));
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
        $s = Utils::newSphinxClient();

        if (count($extraFileIds) > 0) {
            $keyword = '';
            $s->SetSelect("id as myid");
            $s->SetFilter('myid', $extraFileIds);
        } else {
            $keyword = isset($criteria['keyword']) ? $criteria['keyword'] : "";
        }


        $file_type = isset($criteria['file_type']) ? $criteria['file_type'] : "";
        $file_format = isset($criteria['file_format']) ? $criteria['file_format'] : "";
        $reldate_from = isset($criteria['reldate_from']) ? $criteria['reldate_from'] : "";
        $reldate_to = isset($criteria['reldate_to']) ? $criteria['reldate_to'] : "";


        /* $size_from=isset($criteria['size_from'])?$criteria['size_from']:"";
          $size_to=isset($criteria['size_to'])?$criteria['size_to']:"";
          $size_from_unit=isset($criteria['size_from_unit'])?$criteria['size_from_unit']:"";
          $size_to_unit=isset($criteria['size_to_unit'])?$criteria['size_to_unit']:"";

          if ($size_from == "") {
          $size_from = 0;
          }elseif($size_from_unit==1){
          $size_from*=1024;
          }else if($size_from_unit==2){
          $size_from*=1024*1024;
          }else if($size_from_unit==3){
          $size_from*=1024*1024*1024;
          }else if($size_from_unit==4){
          $size_from*=1024*1024*1024*1024;
          }else {
          $size_from=0;
          }

          if ($size_to == "") {
          $size_to = 9999999999999;
          } elseif($size_to_unit==1){
          $size_to*=1024;
          }else if($size_to_unit==2){
          $size_to*=1024*1024;
          }else if($size_to_unit==3){
          $size_to*=1024*1024*1024;
          }else if($size_to_unit==4){
          $size_to*=1024*1024*1024*1024;
          }else {
          $size_to=0;
          } */

        $reldate_from_temp = Utils::convertDate($reldate_from);
        $reldate_to_temp = Utils::convertDate($reldate_to);


        if ($reldate_from_temp && !$reldate_to_temp) {  # Set FromDate, Don't set To Date
            $reldate_from = $reldate_from_temp - 86400;
            $reldate_to = floor(microtime(true));
        } else if (!$reldate_from_temp && $reldate_to_temp) { # Set To Date, Dont Set FromDate
            $reldate_from = 1;
            $reldate_to = $reldate_to_temp;
        } else {
            $reldate_from = $reldate_from_temp - 86400;
            $reldate_to = $reldate_to_temp;
        }



        if (is_array($file_type)) {
            $s->SetFilter('type_id', $file_type);
        }
        if (is_array($file_format)) {
            $s->SetFilter('format_id', $file_format);
        }

        if ($reldate_from && $reldate_to && $reldate_to > $reldate_from) {
            $s->SetFilterRange('date_stamp', $reldate_from, $reldate_to);
        }

        /* if(isset($criteria['size_from']) || isset($criteria['size_to'])){
          if ($size_from < $size_to) {
          $s->SetFilterRange('size',$size_from,$size_to);
          }
          } */

        $result = $s->query($keyword, "file");

        $matches = array();
        $total_found = $result['total_found'];
        if (isset($result['matches'])) {
            $matches = $result['matches'];
        }

        $result = array_keys($matches);
        return array($result, $total_found);
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
            $size_from*=1024;
        } else if ($size_from_unit == 2) {
            $size_from*=1024 * 1024;
        } else if ($size_from_unit == 3) {
            $size_from*=1024 * 1024 * 1024;
        } else if ($size_from_unit == 4) {
            $size_from*=1024 * 1024 * 1024 * 1024;
        } else {
            $size_from = 0;
        }

        if ($size_to == "") {
            $size_to = 9999999999999;
        } elseif ($size_to_unit == 1) {
            $size_to*=1024;
        } else if ($size_to_unit == 2) {
            $size_to*=1024 * 1024;
        } else if ($size_to_unit == 3) {
            $size_to*=1024 * 1024 * 1024;
        } else if ($size_to_unit == 4) {
            $size_to*=1024 * 1024 * 1024 * 1024;
        } else {
            $size_to = 0;
        }

        return array($size_from, $size_to);
    }

    private function hasSizeFilter($criteria) {
        return (isset($criteria['size_from']) || isset($criteria['size_to']));
    }

}

