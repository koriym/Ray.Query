<?php

declare(strict_types=1);

namespace Ray\Query;

use FilesystemIterator;
use Ray\Di\AbstractModule;
use Ray\Query\Annotation\AliasQuery;
use Ray\Query\Annotation\Query;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class SqlQueryModule extends AbstractModule
{
    /**
     * @var string
     */
    private $sqlDir;

    /**
     * @var string
     */
    private $queryBuilderDir;

    public function __construct(string $sqlDir, string $queryBuilderDir = '', AbstractModule $module = null)
    {
        $this->sqlDir = $sqlDir;
        $this->queryBuilderDir = $queryBuilderDir;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        foreach ($this->files($this->sqlDir) as $fileInfo) {
            /* @var \SplFileInfo $fileInfo */
            $fullPath = $fileInfo->getPathname();
            $name = pathinfo($fileInfo->getRealPath())['filename'];
            $sqlId = 'sql-' . $name;
            $this->bind(QueryInterface::class)->annotatedWith($name)->toConstructor(
                SqlQueryRow::class,
                "sql={$sqlId}"
            );
            $this->bindCallableItem($name, $sqlId);
            $this->bindCallableList($name, $sqlId);

            $sql = trim((string) file_get_contents($fullPath));
            $this->bind('')->annotatedWith($sqlId)->toInstance($sql);
        }
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(Query::class),
            [QueryInterceptor::class]
        );
        // <=0.4.0
        /** @psalm-suppress DeprecatedClass */
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(AliasQuery::class),
            [SqlAliasInterceptor::class]
        );
    }

    protected function bindCallableItem(string $name, string $sqlId) : void
    {
        $this->bind(RowInterface::class)->annotatedWith($name)->toConstructor(
            SqlQueryRow::class,
            "sql={$sqlId}"
        );
    }

    protected function bindCallableList(string $name, string $sqlId) : void
    {
        $this->bind()->annotatedWith($name)->toConstructor(
            SqlQueryRowList::class,
            "sql={$sqlId}"
        );
        $this->bind(RowListInterface::class)->annotatedWith($name)->toConstructor(
            SqlQueryRowList::class,
            "sql={$sqlId}"
        );
    }

    private function files(string $dir) : RegexIterator
    {
        return
            new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $dir,
                        FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+\.sql$/',
                RecursiveRegexIterator::MATCH
            );
    }
}
