<?php

namespace app\models\query;


/**
 * This is the ActiveQuery class for [[\app\models\Employee]].
 *
 * @see \app\models\Employee
 */
class EmployeeQuery extends \rgc\user\models\query\UEmployeeQuery
{
    /**
     * Adds conditions to find employees involved in the current project
     *
     */
    public function forCurrentProject()
    {
        return true;
    }
}