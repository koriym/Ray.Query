<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Query.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Query;

use Ray\Query\Annotation\Query;

class FakeAlias
{
    /**
     * @Query(id="todo_item_by_id", type="row")
     */
    #[Query('todo_item_by_id', type: 'row')]
    public function get(string $id)
    {
        return $this;
    }
}
