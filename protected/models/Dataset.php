<?php

Yii::import('application.extensions.CAdvancedArBehavior');

/**
 * This is the model class for table "dataset".
 *
 * The followings are the available columns in table 'dataset':
 * @property integer $id
 * @property integer $submitter_id
 * @property integer $image_id
 * @property string $identifier
 * @property string $title
 * @property string $description
 * @property string $publisher
 * @property string $dataset_size
 * @property string $ftp_site
 * @property string $upload_status
 * @property string $excelfile
 * @property string $excelfile_md5
 * @property string $publication_date
 * @property string $modification_date
 * @property string $email 
 *
 * The followings are the available model relations:
 * @property Author[] $authors
 * @property DatasetAuthor[] $datasetAuthors
 * @property Project[] $projects
 * @property GigadbUser $submitter
 * @property Image $image
 * @property DatasetSample[] $datasetSamples
 * @property ExternalLink[] $externalLinks
 * @property DatasetType[] $datasetTypes
 * @property File[] $files
 * @property Relation[] $relations
 * @property Link[] $links
 * @property Manuscript[] $manuscripts
 */
class Dataset extends MyActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Dataset the static model class
     */
    public $dTypes = "";
    public $commonNames = "";
    public $email;
    /*
     * List of Many To Many RelationShip
     */
    public $new_ext_acc_mirror;
    public $new_ext_acc_link;

#    public $projectIDs = array();
#    public $authorIDs = array();
#    public $sampleIDs = array();
#    public $datasetTypeIDs = array();

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

#    public function behaviors(){
#          return array( 'CAdvancedArBehavior' => array(
#                'class' => 'application.extensions.CAdvancedArBehavior'));
#    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'dataset';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('submitter_id, title', 'required'),
            array('submitter_id, image_id, publisher_id,dataset_size', 'numerical', 'integerOnly' => true),
            array('identifier, excelfile_md5', 'length', 'max' => 32),
            array('title', 'length', 'max' => 30),
            array('upload_status', 'length', 'max' => 45),
            //  array('ftp_site', 'length', 'max'=>100),
            array('excelfile', 'length', 'max' => 50),
            array('description, publication_date, modification_date, image_id', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, submitter_id, image_id, identifier, title, description,
                publisher, dataset_size, ftp_site, upload_status, excelfile, 
                excelfile_md5, publication_date, modification_date', 'safe', 'on' => 'search'),
#            array('projectIDs , sampleIDs , authorIDs , datasetTypeIDs' , 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'authors' => array(self::MANY_MANY, 'Author', 'dataset_author(dataset_id,author_id)', 'order' => 'authors_authors.rank ASC',),
            'projects' => array(self::MANY_MANY, 'Project', 'dataset_project(dataset_id,project_id)'),
            'submitter' => array(self::BELONGS_TO, 'User', 'submitter_id'),
            'image' => array(self::BELONGS_TO, 'Images', 'image_id'),
            'samples' => array(self::MANY_MANY, 'Sample', 'dataset_sample(dataset_id,sample_id)'),
            'externalLinks' => array(self::HAS_MANY, 'ExternalLink', 'dataset_id'),
            'datasetTypes' => array(self::MANY_MANY, 'Type', 'dataset_type(dataset_id,type_id)'),
            'files' => array(self::HAS_MANY, 'File', 'dataset_id'),
            'relations' => array(self::HAS_MANY, 'Relation', 'dataset_id'),
            'links' => array(self::HAS_MANY, 'Link', 'dataset_id'),
            'manuscripts' => array(self::HAS_MANY, 'Manuscript', 'dataset_id'),
            'publisher' => array(self::BELONGS_TO, 'Publisher', 'publisher_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'submitter_id' => 'Submitter',
            'image_id' => 'Image',
            'identifier' => 'DOI',
            'title' => Yii::t('app', 'Title'),
            'description' => 'Description',
            'publisher' => 'Publisher',
            'dataset_size' => 'Dataset Size',
            'ftp_site' => 'Ftp Site',
            'upload_status' => 'Upload Status',
            'excelfile' => 'Excelfile',
            'excelfile_md5' => 'Excelfile Md5',
            'publication_date' => Yii::t('app', 'Publication Date'),
            'modification_date' => Yii::t('app', 'Modification Date'),
            'new_image_url' => 'Image URL',
            'new_image_location' => 'Image Location',
            'email' => 'email',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('submitter_id', $this->submitter_id);
        $criteria->compare('image_id', $this->image_id);
        $criteria->compare('LOWER(identifier)', strtolower($this->identifier), true);
        $criteria->compare('LOWER(title)', strtolower($this->title), true);
        $criteria->compare('LOWER(description)', strtolower($this->description), true);
        $criteria->compare('LOWER(publisher)', strtolower($this->publisher_id), true);
        $criteria->compare('LOWER(dataset_size)', strtolower($this->dataset_size), true);
        $criteria->compare('LOWER(ftp_site)', strtolower($this->ftp_site), true);
        $criteria->compare('LOWER(upload_status)', strtolower($this->upload_status), true);
        $criteria->compare('LOWER(excelfile)', strtolower($this->excelfile), true);
        $criteria->compare('LOWER(excelfile_md5)', strtolower($this->excelfile_md5), true);
        $criteria->compare('publication_date', $this->publication_date);
        $criteria->compare('modification_date', $this->modification_date);
        $criteria->compare('email', $this->email);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public static function sphinxSearch($criteria, $extraDatasetIds = array()) {
        $s = Utils::newSphinxClient();

        if (count($extraDatasetIds) > 0) {
            $keyword = '';
            $s->setSelect("id as myid");
            $s->SetFilter('myid', $extraDatasetIds);
        } else {
            $keyword = isset($criteria['keyword']) ? $criteria['keyword'] : "";
        }

        if (isset($criteria['exclude']) && !empty($criteria['exclude'])) {
            $s->setSelect("id as myidex");
            $s->setFilter('myidex', array_filter(explode(',', $criteria['exclude'])), true);
        }
//        var_dump("here");
        $dataset_type = isset($criteria['dataset_type']) ? $criteria['dataset_type'] : "";
        $common_name = isset($criteria['common_name']) ? $criteria['common_name'] : "";
        $project = isset($criteria['project']) ? $criteria['project'] : "";
        $pubdate_from = isset($criteria['pubdate_from']) ? $criteria['pubdate_from'] : "";
        $pubdate_to = isset($criteria['pubdate_to']) ? $criteria['pubdate_to'] : "";
        $moddate_from = isset($criteria['moddate_from']) ? $criteria['moddate_from'] : "";
        $moddate_to = isset($criteria['moddate_to']) ? $criteria['moddate_to'] : "";
        $external_link_type = isset($criteria['external_link_type']) ? $criteria['external_link_type'] : "";
        $pubdate_from_temp = Utils::convertDate($pubdate_from);
        $pubdate_to_temp = Utils::convertDate($pubdate_to);


        $moddate_from_temp = Utils::convertDate($moddate_from);
        $moddate_to_temp = Utils::convertDate($moddate_to);

        # KNN: -86400 to include the from day
        if ($pubdate_from_temp && !$pubdate_to_temp) {  # Set FromDate, Don't set To Date
            $pubdate_from = $pubdate_from_temp - 86400;
            $pubdate_to = floor(microtime(true));
        } else if (!$pubdate_from_temp && $pubdate_to_temp) { # Set To Date, Dont Set FromDate
            $pubdate_from = 1; # 1 is the very long time ago , near 1970
            $pubdate_to = $pubdate_to_temp;
        } else {
            $pubdate_from = $pubdate_from_temp - 86400;
            $pubdate_to = $pubdate_to_temp;
        }


        if ($moddate_from_temp && !$moddate_to_temp) {  # Set FromDate, Don't set To Date
            $moddate_from = $moddate_from_temp - 86400;
            $moddate_to = floor(microtime(true));
        } else if (!$moddate_from_temp && $moddate_to_temp) { # Set To Date, Dont Set FromDate
            $moddate_from = 1;  # 1 is the very long time ago , near 1970
            $moddate_to = $moddate_to_temp;
        } else {
            $moddate_from = $moddate_from_temp - 86400;
            $moddate_to = $moddate_to_temp;
        }


        if (is_array($dataset_type)) {
            $s->SetFilter('dataset_type_ids', $dataset_type);
        }


        if (is_array($common_name)) {
            $s->SetFilter('species_ids', $common_name);
        }

        if (is_array($project)) {
            $s->SetFilter('project_ids', $project);
        }
        if (is_array($external_link_type)) {
            $s->SetFilter('external_type_ids', $external_link_type);
        }


        if ($pubdate_from && $pubdate_to && $pubdate_to > $pubdate_from) {
            $s->SetFilterRange('publication_date', $pubdate_from, $pubdate_to);
        }

        if ($moddate_from && $moddate_to && $moddate_to > $moddate_from) {
            $s->SetFilterRange('modification_date', $moddate_from, $moddate_to);
        }

        $result = $s->query($keyword, "dataset");

        $matches = array();
        if (isset($result['matches'])) {
            $matches = $result['matches'];
        }

        $result = array_keys($matches);
        return $result;
    }

    public function getListTitles() {
        $models = Dataset::model()->findAll(array(
            'select' => 't.title',
            'distinct' => true,
        ));
        $list = array();
        foreach ($models as $key => $model) {
            $list[] = $model->title;
        }
        return $list;
    }

    public function afterFind() {
        // Dataset Types
        $dtypes = $this->datasetTypes;
        foreach ($dtypes as $key => $value) {
            if (!stristr($this->dTypes, $value->name)) {
                $this->dTypes = $this->dTypes . " " . $value->name;
            }
        }

        // Samples
        $samples = $this->samples;
        foreach ($samples as $key => $value) {
            if (!stristr($this->commonNames, $value->species->common_name)) {
                $this->commonNames = $this->commonNames . " " . $value->species->common_name;
            }
        }
    }

    public function getDatasetTypes() {
        $list = array();

        foreach ($this->datasetTypes as $key => $type) {
            $list[] = $type->name;
        }
        return $list;
    }

    public function getImageUrl($default = '') {
        if ($this->image) {
            $url = $this->image->url;
            if ($url) {
                if (!strstr($url, 'http://')) {
                    $url = 'http://' . $url;
                }
            } else {
                $url = $this->image->image('image_upload');
            }
            return $url;
        }
        return $default;
    }

    public static function getFileIdsByDatasetIds($datasetIds) {
        $criteria = new CDbCriteria();
        $criteria->addInCondition('t.id', $datasetIds);
        $criteria->distinct = true;
        $datasets = Dataset::model()->query($criteria, true);
        $result = array();
//        var_dump(count($datasets)." datasets model");
        foreach ($datasets as $dataset) {
            foreach ($dataset->files as $file) {
                array_push($result, $file->id);
            }
        }
        return $result;
    }

    public function behaviors() {
        return array(
            'commentable' => array(
                'class' => 'ext.comment-module.behaviors.CommentableBehavior',
                // name of the table created in last step
                'mapTable' => 'posts_comments_nm',
                // name of column to related model id in mapTable
                'mapRelatedColumn' => 'postId'
            ),
        );
    }

    static public function storeSession($vars) {

        if (isset($_SESSION['identifier']))
            $identifier = $_SESSION['identifier'];
        else
            return false;
        $dataset_session = DatasetSession::model()->findByAttributes(array('identifier' => $identifier));
        if ($dataset_session == NULL)
            $dataset_session = new DatasetSession;

        foreach ($vars as $var) {
            if (isset($_SESSION[$var])) {
                $db_var = CJSON::encode($_SESSION[$var]);
                $dataset_session->$var = $db_var;
            }
        }
        $dataset_session->identifier = $identifier;

        if ($dataset_session->save())
            return true;
        return false;
    }

    
       public static function clearDatasetSession(){
      $vars = array('dataset', 'images', 'authors', 'projects',
            'links', 'externalLinks', 'relations', 'samples','dataset_id','identifier','filecount',
          'link_database');
        foreach ($vars as $var) {
            unset($_SESSION[$var]);
            //    $_SESSION[$var] = CJSON::decode($dataset_session->$var);
        }   
    }
}
