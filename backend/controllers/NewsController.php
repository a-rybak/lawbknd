<?php

namespace app\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class NewsController extends ActiveController
{
    public $modelClass = 'app\models\News';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => \Yii::$app->params['corsFilter.cors'],
        ];
        $behaviors['verbs']['class'] = VerbFilter::className();
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];
        unset($behaviors['authenticator']);
        $behaviors['authenticator']['class'] = HttpBearerAuth::className();
        return $behaviors;
    }

}
