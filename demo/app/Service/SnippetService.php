<?php


namespace Service;

use DateTime;
use Model\Snippet;
use Mparaiso\SimpleRest\Model\AbstractModel;
use Mparaiso\SimpleRest\Service\Service;

class SnippetService extends Service
{
    function makeDate(DateTime $datetime)
    {
        return $datetime->format("Y-m-d H:i:s");
    }

    function create(AbstractModel $model)
    {
        /* @var Snippet $model */
        if ($model->getId() != NULL) {
            $model->setId(NULL);
        }
        $model->setCreatedAt($this->makeDate(new DateTime));
        $model->setUpdatedAt($this->makeDate(new DateTime));
        return parent::create($model);
    }


}