<?php

namespace Task\Queue\ORM;

use Exception;
use InvalidArgumentException;

use Task\Queue\Interfaces\ORM\IJob;
use Task\Queue\Service\DTO\ORM\Job;

use Bitrix\Main\{Entity, ArgumentException, ObjectPropertyException, SystemException};
use Bitrix\Main\Type\Date;
use Bitrix\Main\ORM\Data\AddResult;

/**
 * ORM, для взаимодействия с очередью, которая в текущий момен обрабатывается
 * или ожидает взаимодействия.
 *
 * @author Daniil Sholokhov <sholokhov.daniil@gmail.com>
 */
class JobsTable extends Base
{
    /**
     * Получение наименования таблицы с очередями.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return "b_task_queue_jobs";
    }

    /**
     * Получение описания таблицы по правилам ORM.
     *
     * <li>ID - Уникальный идентификатор очереди
     * <li>TASK - Обработчик очереди
     * <li>STATUS - Текущий статус задачи
     * <li>PARAMS - Параметры задачи
     * <li>DATE_CREATE - Дата создания очереди
     * <li>DATE_UPDATE - Дата последнего обновления задачи
     *
     * @return array
     */
    public static function getMap(): array
    {
        $parent = parent::getMap();

        $map = [
            new Entity\StringField(static::FIELD_TASK, []),
            new Entity\TextField(static::FIELD_PARAMS, []),
        ];

        return array_merge($map, $parent);
    }

    /**
     * Добавление новой задачи.
     *
     * @param IJob $dto
     * @return AddResult
     * @throws Exception
     */
    public static function append(IJob $dto): AddResult
    {
        $fields = [
            static::FIELD_TASK => $dto->getTask(),
            static::FIELD_PARAMS => serialize($dto->getParameters()),
            static::FIELD_DATE_UPDATE => $dto->getDateUpdate(),
            static::FIELD_DATE_CREATE => $dto->getDateCreate(),
        ];

        return parent::add($fields);
    }

    /**
     * Добавление новой задачи.
     *
     * @param array $data
     * @return AddResult
     * @throws Exception
     */
    public static function add(array $data = []): AddResult
    {
        if (empty($data['task'])) {
            throw new InvalidArgumentException('The task object was not found');
        }

        if (!isset($data['params']) || !is_array($data['params'])) {
            $data['params'] = [];
        }

        $fields = [
            static::FIELD_TASK => $data['task'],
            static::FIELD_PARAMS => serialize($data['params']),
            static::FIELD_DATE_UPDATE => new Date(),
            static::FIELD_DATE_CREATE => new Date(),
        ];

        return parent::add($fields);
    }

    /**
     * Получение списка задач.
     *
     * @param array $parameters
     * @return IJob[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getAll(array $parameters = []): array
    {
        $result = [];
        $iterator = static::getList($parameters);

        while ($item = $iterator->fetch()) {
            $id = intval($item['ID'] ?? 0);
            $result[$id] = static::arrayToJob($item);
        }

        return $result;
    }
}