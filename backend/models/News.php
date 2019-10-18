<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $title
 * @property string $short_text
 * @property string $text
 * @property string $posted_at
 * @property string $type
 * @property int $is_deleted
 * @property string $deleted_at
 */
class News extends \yii\db\ActiveRecord
{
    CONST ITEM_DELETED      = 1;
    CONST ITEM_UNDELETED    = 0;
    CONST NEWS_TYPE_NEWS    = 'news';
    CONST NEWS_TYPE_ARTICLE = 'article';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->is_deleted = static::ITEM_UNDELETED;
        }
        $this->posted_at = date('Y-m-d H:i:s');
        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'short_text', 'text', 'type'], 'required'],
            [['text'], 'string'],
            [['posted_at', 'deleted_at'], 'safe'],
            [['is_deleted'], 'integer'],
            [['title', 'type'], 'string', 'max' => 255],
            [['short_text'], 'string', 'max' => 700],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'short_text' => 'Short Text',
            'text' => 'Text',
            'posted_at' => 'Posted At',
            'type' => 'Type',
            'is_deleted' => 'Is Deleted',
            'deleted_at' => 'Deleted At',
        ];
    }
}
