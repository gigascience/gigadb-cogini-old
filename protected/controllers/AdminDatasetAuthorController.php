<?php

class AdminDatasetAuthorController extends Controller {

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
            array('allow', // admin only
                'actions' => array('admin', 'delete', 'index', 'view', 'create', 'update'),
                'roles' => array('admin'),
            ),
            array('allow',
                'actions' => array('create1', 'delete1', 'autocomplete'),
                'users' => array('@'),
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

    public function actionAutocomplete() {
        $res = array();
        $result = array();
        if (isset($_GET['term'])) {
            $connection = Yii::app()->db;
            $sql = "Select name from author where name like :name";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":name", '%' . $_GET['term'] . '%', PDO::PARAM_STR);
            $res = $command->queryAll();
            if (!empty($res))
                foreach ($res as $mres) {
                    $result[] = $mres['name'];
                }
            echo CJSON::encode($result);
            Yii::app()->end();
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model = new DatasetAuthor;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['DatasetAuthor'])) {
            $model->attributes = $_POST['DatasetAuthor'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function storeAuthor(&$datasetAuthor, &$id) {

        if (isset($_SESSION['dataset_id'])) {
            $dataset_id = $_SESSION['dataset_id'];
            $model->dataset_id = $dataset_id;
            $model = $datasetAuthor;
            //store author into table author
            $name = $datasetAuthor->author_name;
            $rank = $datasetAuthor->rank;

            //determine if the model is valid
            if (!$datasetAuthor->validate())
                return false;


            $author = Author::model()->findByAttributes(array('name' => $name,
                'rank' => $rank));
            if ($author != NULL) {
                $author_id = $author->id;
            } else {
                $author = new Author;
                $author->name = $name;
                //temporary
                $author->orcid = 1;
                $author->rank = $rank;
                if (!$author->save()) {
                    return false;
                }
                $author_id = $author->id;
            }

            $model->author_id = $author_id;
            $model->rank = $rank;

            if (!$model->save()) {
                Yii::app()->user->setFlash('keyword', 'Error: Author is not stored!');
                return false;
            }

            $id = $model->id;
            return true;
        }

        return false;
    }

    public function actionCreate1() {
        $model = new DatasetAuthor;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);
        //this is fake information
        $model->dataset_id = 1;
        //update 
        if (!isset($_SESSION['authors']))
            $_SESSION['authors'] = array();

        $authors = $_SESSION['authors'];

        if (isset($_POST['DatasetAuthor'])) {
            //store the information in session 
//            if (!isset($_SESSION['author_id']))
//                $_SESSION['author_id'] = 0;
//            $id = $_SESSION['author_id'];
//            $_SESSION['author_id'] += 1;


            $rank = $_POST['DatasetAuthor']['rank'];
            $name = $_POST['DatasetAuthor']['author_name'];
            //determin if the record has already in the session
            $valid = true;
            foreach ($authors as $key => $author) {
                if ($author['rank'] == $rank && $author['name'] == $name) {
                    $model->addError("error", "Duplicate input");
                    $valid = false;
                    break;
                }
            }
            if ($valid) {
                //store author dataset
                $model->rank = $rank;
                $model->author_name = $name;
                $id = 0;
                if ($this->storeAuthor($model, $id)) {

                    $newItem = array('rank' => $rank, 'id' => $id, 'name' => $name);

                    array_push($authors, $newItem);

                    $_SESSION['authors'] = $authors;
                    $vars = array('authors');
                    Dataset::storeSession($vars);
                    $model = new DatasetAuthor;
                }
            }
        }




        $author_model = new CArrayDataProvider($authors);

        $this->render('create1', array(
            'model' => $model,
            'author_model' => $author_model,
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

        if (isset($_POST['DatasetAuthor'])) {
            $model->attributes = $_POST['DatasetAuthor'];
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
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

    public function actionDelete1($id) {
        if (isset($_SESSION['authors'])) {
            $authors = $_SESSION['authors'];
            foreach ($authors as $key => $author) {
                if ($author['id'] == $id) {
                    unset($authors[$key]);
                    $_SESSION['authors'] = $authors;
                    $vars = array('authors');
                    Dataset::storeSession($vars);
                    //delete the record in table dataset_author
                    $condition = "id=" . $id;
                    DatasetAuthor::model()->deleteAll($condition);

                    $this->redirect("/adminDatasetAuthor/create1");
                }
            }
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {
        $dataProvider = new CActiveDataProvider('DatasetAuthor');
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin() {
        $model = new DatasetAuthor('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['DatasetAuthor']))
            $model->attributes = $_GET['DatasetAuthor'];

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
        $model = DatasetAuthor::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'dataset-author-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}

