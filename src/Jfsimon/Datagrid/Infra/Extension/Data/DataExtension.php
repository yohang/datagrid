<?php

namespace Jfsimon\Datagrid\Infra\Extension\Data;

use Jfsimon\Datagrid\Infra\Extension\AbstractExtension;
use Jfsimon\Datagrid\Infra\Extension\Data\Formatter\FormatterInterface;
use Jfsimon\Datagrid\Model\Column;
use Jfsimon\Datagrid\Model\Component\Grid;
use Jfsimon\Datagrid\Model\Component\Row;
use Jfsimon\Datagrid\Model\Data\Collection;
use Jfsimon\Datagrid\Model\Schema;

/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class DataExtension extends AbstractExtension
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface $formatter
     *
     * @return DataExtension
     */
    public function registerFormatter(FormatterInterface $formatter)
    {
        $this->formatters[$formatter->getName()] = $formatter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildGrid(Grid $grid, Collection $collection, array $options = array())
    {
        $schema = $options['schema'];
        if (!$schema instanceof Schema) {
            throw new \InvalidArgumentException('Schema option must be an instance of Schema.');
        }

        $index = 0;
        while ($entity = $collection->next()) {
            $schema->build($row = new Row('data'), $entity);
            $grid->getBody()->add($entity->getId() ?: $index ++, $row);
        };

        return $grid;
    }

    /**
     * {@inheritdoc}
     */
    public function buildColumn(Column $column, $type, array $options = array())
    {
        if (!isset($this->formatters[$type])) {
            throw new \LogicException('No formatter found for given type.');
        }

        $column->register(new DataHandler($this->formatters[$type]));
    }
}