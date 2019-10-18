<?php

namespace app\models;

use Yii;
use rgc\user\models\UEmployee;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class Employee extends UEmployee
{
    const WORK_FROM         = 'WorkSince';
    const WORK_TO           = 'EndOfWork';
    const ACCOUNT_ACTIVE    = 1;
    const ACCOUNT_INACTIVE  = 0;

    public function fields()
    {
        return [
            'id',
            'fio' => function ($model){
                return $model->user->getFullName();
            },
            'position' => function ($model) {
                if (!empty($model->position)) {
                    return [
                        'id' => $model->position->id,
                        'name' => $model->position->name
                    ];
                } else {
                    return [];
                }
            },
            'organization' => function ($model) {
                if ($model->department->organization) {
                    return [
                        'id' => $model->department->organization->id,
                        'name' => $model->department->organization->name,
                        'edrpou' => $model->department->organization->edrpou,
                        'prefix' => $model->department->organization->prefix,
                    ];
                } else {
                    return null;
                }
            },
            'department' => function ($model) {
                $parent = null;
                if (!empty($model->department))
                    $parent = Department::getParentDepartment($model->department->id);
                return $parent;
            },
            'partition' => function ($model) {
                if (!empty($model->department)) {
                    return [
                        'id' => $model->department->id,
                        'name' => $model->department->name
                    ];
                } else {
                    return null;
                }
            },
            'begin_work' => function ($model){
                return !empty($model->date_of_begin_work) ? date("d.m.Y", strtotime($model->date_of_begin_work)) : null;
            },
            'end_work' => function ($model){
                return !empty($model->date_of_end_work) ? date("d.m.Y", strtotime($model->date_of_end_work)) : null;
            },
            'full_address_office',
            'email',
            'inn' => function ($model){
                return (!empty($model->user)) ? $model->user->inn : null;
            },
        ];
    }

    public function getFullName()
    {
        return trim($this->user->last_name . ' ' . $this->user->first_name . ' ' . $this->user->patronymic);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\EmployeeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return (new \app\models\query\EmployeeQuery(get_called_class()))->alias('ue');
    }

    public static function getByEmployeeId($emp_id){
        $sql = "SELECT uu.id AS user_id,
                    uo.name AS organization_name,
                    uo.id AS organization_id,
                    udp.id AS department_id,
                    udp.name AS department_name , 
                    ud.id AS partition_id,
                    ud.name AS partition_name ,
                    up.name AS position_name,
                    up.id AS position_id,
                    ue.id AS employee_id,
                    CONCAT(uu.last_name, ' ', uu.first_name, ' ', uu.patronymic) AS fio,
                    ue.date_of_begin_work,
                    ue.date_of_end_work,
                    uu.birth_day,
                    ue.email
                FROM u_user uu 
                LEFT JOIN u_employee ue ON uu.id = ue.user_id
                LEFT JOIN u_department ud ON ue.department_id = ud.id
                LEFT JOIN u_department udp ON ud.parent_id = udp.id
                LEFT JOIN u_organization uo ON ud.organization_id = uo.id
                LEFT JOIN u_position up ON up.id = ue.position_id
                WHERE ue.id = :emp_id;";

        $resultProfile  = Yii::$app->db->createCommand($sql)->bindValue(':emp_id', $emp_id, \PDO::PARAM_INT)->queryOne();

        $resultContacts = [];
        if (!empty($resultProfile)){
            $user_id = intval($resultProfile['user_id']);
            $resultContacts = Contact::find()->with('contactType')->where(['user_id' => $user_id])->asArray()->all();
        }

        return [
            'profile' => $resultProfile,
            'contact' => $resultContacts,
        ];

    }

    public static function findIdentityByOwnAccessToken()
    {
        if (empty(Yii::$app->request->headers['authorization'])){
            throw new BadRequestHttpException('Auth token required');
        }

        $token = str_replace("Bearer", "", Yii::$app->request->headers['authorization']);
        $identity = self::findIdentityByAccessToken(trim($token));

        if (empty($identity))
            throw new NotFoundHttpException('Identity by auth token not found');
        else
            return $identity;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}