<?php

class AdminDatasetSampleController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            //allow users to add samples to dataset
            array('allow',
                'actions' => array('create1', 'delete1', 'autocomplete'),
                'users' => array('@')),
            array('allow', // admin only
                'actions' => array('admin', 'delete', 'index', 'view', 'create', 'update'),
                'roles' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new DatasetSample;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['DatasetSample'])) {
            $model->attributes = $_POST['DatasetSample'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function actionAutocomplete() {
        $res = array();
        $result = array();
        if (isset($_GET['term'])) {
            $connection = Yii::app()->db;
            $sql = "Select common_name from species where common_name like :name";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":name", '%' . $_GET['term'] . '%', PDO::PARAM_STR);
            $res = $command->queryAll();
            if (!empty($res))
                foreach ($res as $mres) {
                    $result[] = $mres['common_name'];
                }
            echo CJSON::encode($result);
            Yii::app()->end();
        }
    }

    public function storeSample(&$model, &$id) {


        if (isset($_SESSION['dataset_id'])) {
            $dataset_id = $_SESSION['dataset_id'];
            //1) find species id
            $species_id = 0;
            $common_name = $model->species;
            //validate
            if (!$model->validate()) {
                return false;
            }

            $species = Species::model()->findByAttributes(array('common_name' => $common_name));
            if ($species != NULL) {
                $species_id = $species->id;
            } else {
                //insert a new species record
                $model->addError('error', 'No species record in our database');
                return false;
            }
            //2) insert sample 
            $sample = new Sample;
            $sample->species_id = $species_id;
            $sample->code = $model->code;
            $sample->s_attrs = $model->attribute;
            $sample_id = 0;
            if (!$sample->save()) {
                $model->addError('error', 'Sample save error');
                return false;
            }
            $sample_id = $sample->id;

            //3) insert dataset_sample 

            $model->sample_id = $sample_id;
            $model->dataset_id = $dataset_id;
            if (!$model->save()) {
                $model->addError('keyword', 'Dataset_Sample is not stored!');
                return false;
            }

            $id = $model->id;
            return true;
        }

        return false;
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate1() {

        $model = new DatasetSample;
        $model->dataset_id = 1;
        //$model->
        //update 
        if (!isset($_SESSION['samples']))
            $_SESSION['samples'] = array();

        $samples = $_SESSION['samples'];

        if (isset($_POST['DatasetSample'])) {


            $model->attributes = $_POST['DatasetSample'];

            $name = $_POST['DatasetSample']['code'];
            $species = $_POST['DatasetSample']['species'];
            $attrs = $_POST['DatasetSample']['attribute'];

//            $model->code = $name;
//            $model->species = $species;
//            $model->attribute = $attrs;

            $id = 0;

            if ($this->storeSample($model, $id)) {


                $newItem = array('id' => $id, 'name' => $name, 'species' => $species, 'attrs' => $attrs);

                array_push($samples, $newItem);
                $_SESSION['samples'] = $samples;
                $vars = array('samples');
                Dataset::storeSession($vars);
                $model = new DatasetSample;
            }
        }

        $sample_model = new CArrayDataProvider($samples);

        $this->render('create1', array(
            'model' => $model,
            'sample_model' => $sample_model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['DatasetSample'])) {
            $model->attributes = $_POST['DatasetSample'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    public function actionDelete1($id) {
        if (isset($_SESSION['samples'])) {
            $info = $_SESSION['samples'];
            foreach ($info as $key => $value) {
                if ($value['id'] == $id) {
                    unset($info[$key]);
                    $_SESSION['samples'] = $info;
                    $vars = array('samples');
                    Dataset::storeSession($vars);
                    $condition = 'id=' . $id;

                    $sample_id = DatasetSample::model()->findByAttributes(array('id' => $id))->sample_id;
                    DatasetSample::model()->deleteAll($condition);
                    Sample::model()->deleteAll('id=' . $sample_id);

                    $this->redirect("/adminDatasetSample/create1");
                }
            }
        }
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('DatasetSample');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new DatasetSample('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['DatasetSample']))
            $model->attributes = $_GET['DatasetSample'];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id) {
        $model = DatasetSample::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'dataset-sample-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
