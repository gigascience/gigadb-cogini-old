<?php

/**
 * This is the model class for table "author".
 *
 * The followings are the available columns in table 'author':
 * @property integer $id
 * @property string $name$surname
 * @property string $middle_name
 * @property string $first_name
 * @property string $orcid
 * @property integer $position$gigadb_user_id
 *
 * The followings are the available model relations:
 * @property DatasetAuthor[] $datasetAuthors
 */
class Author extends CActiveRecord {
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Author the static model class
     */
    public $dois_search;

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'author';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('surname', 'required'),
            array('gigadb_user_id', 'numerical', 'integerOnly' => true),
            array('surname, middle_name, first_name', 'length', 'max' => 255),
            array('orcid', 'length', 'max' => 128),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, surname, middle_name, first_name, orcid, gigadb_user_id', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'datasetAuthors' => array(self::HAS_MANY, 'DatasetAuthor', 'author_id'),
            //'datasets' => array(self::MANY_MANY, 'Dataset', 'dataset_author(dataset_id,author_id)')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'surname' => 'Surname',
            'middle_name' => 'Middle Name',
            'first_name' => 'First Name',
            'orcid' => 'Orcid',
            'gigadb_user_id' => 'Gigadb User',
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
        $criteria->compare('LOWER(surname)', strtolower($this->surname), true);
        $criteria->compare('LOWER(middle_name)', strtolower($this->middle_name), true);
        $criteria->compare('LOWER(first_name)', strtolower($this->first_name), true);
        $criteria->compare('LOWER(orcid)', strtolower($this->orcid), true);
        $criteria->compare('gigadb_user_id', $this->gigadb_user_id);

        if ($this->dois_search) {
            $matchedSql = 'SELECT dataset_id, author_id FROM dataset, dataset_author WHERE dataset.identifier LIKE \'%' . $this->dois_search . '%\' AND dataset.id = dataset_author.dataset_id';
            $criteria->addInCondition('t.id', CHtml::listData(DatasetAuthor::model()->findAllBySql($matchedSql), 'dataset_id', 'author_id'));
        }

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function getFullAuthor() {
        //return $this->name . ' - ORCID:' . $this->orcid . ' - RANK:' . $this->rank;
        return $this->first_name . ' ' . $this->surname . ' - ORCID:' . $this->orcid;
    }

    /**
     * Return first name and surname
     * @return string
     */
    public function getName() {
        return $this->surname . ', ' . $this->first_name;
    }

    /**
     * Find an author by > surname . ' ' . first_name
     * @return string
     */
    public function findByCompleteName($name) {

        $criteria = new CDbCriteria;
        $criteria->limit = 1;
        $criteria->addSearchCondition("LOWER(surname) || ' ' || LOWER(first_name)", '%' . strtolower($name) . '%', false);
        $result = $this->findAll($criteria);

        return $result ? $result[0] : false;
    }

    public static function searchAuthor($criteria)
    {
        $keyword = $criteria['keyword'] ? $criteria['keyword'] : '';
        $criteria = new CDbCriteria;
        $criteria->select = 'id';
        $criteria->limit = 1;
        $criteria->addSearchCondition("LOWER(surname) || ' ' || LOWER(first_name)", '%' . strtolower($keyword) . '%', false);
        $result = new CActiveDataProvider('Author', array('criteria' => $criteria));
        
        $data = array();
        foreach ($result->getData() as $author) {
            $data[] = $author->id;
        }

        return $data;
    }
}
