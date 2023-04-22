<?php

namespace Task\Queue\Service\Bus\Traits;

use Task\Queue\Interfaces\Bus\IShouldQueue;
use Task\Queue\ORM\JobsTable;
use Task\Queue\Service\DTO\ORM\Job;
use Task\Queue\Service\QueueManager;

use Bitrix\Main\ORM\Data\AddResult;

/**
 * Позволяет формировать задачу в очередь на основе механизма разбора очереди (Job).
 *
 * @author Daniil Sholokhov <sholokhov.daniil@gmail.com>
 */
trait Dispatchable
{
    /**
     * Формирование задачи с указанными параметрами.
     *
     * @param ...$arguments
     * @return AddResult
     */
    public static function dispatch(...$arguments): AddResult
    {
        $job = (new Job())->setTask(static::class)
            ->setParameters($arguments);

        return (new QueueManager(new JobsTable()))->push($job);
    }
}