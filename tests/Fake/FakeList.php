<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Query.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Query;

use Ray\Di\Di\Named;

class FakeList implements QueryInterface
{
    /**
     * @var RowListInterface
     */
    private $func;

    /**
     * @Named("todo_item_by_id")
     */
    #[Named('todo_item_by_id')]
    public function __construct(RowListInterface $func)
    {
        $this->func = $func;
    }

    public function __invoke(array ...$queries)
    {
        $query = $queries[0];

        return ($this->func)($query);
    }
}
