<?php
/**
 * Pager Class
 *
 * @author    Ara Gasparyan <ara.gasparyan87@gmail.com>
 * @copyright Copyright(c) 2021 Lines Corporetion
 */

class Pager
{
    /**
     * Database connection
     *
     * @var \PDO $database
     */
    protected $database;

    /**
     * SQL query for fetching results
     *
     * @var string
     */
    protected $sql;

    /**
     * Bind params
     *
     * @var array
     */
    protected $bind;

    /**
     * Data that matches with given paging parameters
     *
     * @var array
     */
    protected $pagedData = [];

    /**
     * Total number of pages
     *
     * @var integer
     */
    protected $totalPages;

    /**
     * Total number of items
     *
     * @var integer
     */
    protected $totalItems;

    /**
     * Show element Last in pagination
     *
     * @var boolean
     */
    protected $last = true;

    /**
     * Show element First in pagination
     *
     * @var boolean
     */
    protected $first = true;

    /**
     * Show element Next in pagination
     *
     * @var boolean
     */
    protected $next = true;

    /**
     * Show element Previous in pagination
     *
     * @var boolean
     */
    protected $prev = true;

    /**
     * Number of current page
     *
     * @var integer
     */
    protected $current;

    /**
     * Is current page fixed?
     *
     * @var boolean
     */
    protected $currentFixed = false;

    /**
     * Page numbers and links
     *
     * @var array
     */
    protected $pages = [];

    /**
     * How many elements we need per page
     *
     * @var integer
     */
    protected $perPage;

    /**
     * How many page links should be visible
     *
     * @var integer
     */
    protected $linkCount;

    /**
     * Pager constructor.
     *
     * @param \PDO|null $database
     * @param int $currentPage
     * @param int $perPage
     * @param int $linkCount
     */
    public function __construct(\PDO $database = null, $currentPage = 1, $perPage = 30, $linkCount = 4)
    {
        $this->database = $database;
        $this->linkCount = $linkCount;
        $this->perPage = $perPage;
        $this->current = $currentPage;
    }

    /**
     * Detects if last link is needed
     *
     * @return boolean
     */
    public function hasLast()
    {
        return $this->last;
    }

    /**
     * Detects if first link is needed
     *
     * @return boolean
     */
    public function hasFirst()
    {
        return $this->first;
    }

    /**
     * Detects if next link is needed
     *
     * @return boolean
     */
    public function hasNext()
    {
        return $this->next;
    }

    /**
     * Detects if prev link is needed
     *
     * @return boolean
     */
    public function hasPrev()
    {
        return $this->prev;
    }

    /**
     * Get Paged Data
     *
     * @return array
     */
    public function getPagedData()
    {
        return $this->pagedData;
    }

    /**
     * Get count of total pages
     *
     * @return int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Get count of total items
     *
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Get pagination elements
     *
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Gets current page number
     *
     * @return int
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Detects if current page is fixed
     *
     * @return int
     */
    public function isCurrentFixed()
    {
        return $this->currentFixed;
    }

    /**
     * Gets count of items per page
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Gets the max count of page links
     *
     * @return int
     */
    public function getLinkCount()
    {
        return $this->linkCount;
    }

    /**
     * Sets total item count
     *
     * @param int $totalItems
     *
     * @return self
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;

        return $this;
    }

    /**
     * Sets raw sql query
     *
     * @param string $sql
     *
     * @return Pager
     */
    public function setSql($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Sets ready paged data
     *
     * @param mixed $pagedData
     *
     * @return Pager
     */
    public function setPagedData($pagedData)
    {
        $this->pagedData = $pagedData;

        return $this;
    }

    /**
     * Detects if we need textual links
     */
    protected function detectTextualLinks()
    {
        if ($this->current == 1) {
            $this->first = false;
            $this->prev = false;
        }

        if ($this->current == $this->totalPages) {
            $this->next = false;
            $this->last = false;
        }
    }

    /**
     * Generates page numbers for pagination
     */
    private function generatePageList()
    {
        if ($this->linkCount <= 0 || !is_int($this->linkCount)) {
            return false;
        }

        if ($this->totalPages <= $this->linkCount) {
            return $this->pages = range(1, $this->totalPages);
        }

        if ($this->current == 1) {
            return $this->pages = range(1, $this->linkCount);
        }

        $pagesAfter = $this->totalPages - $this->current;
        if ($pagesAfter >= ($this->linkCount) / 2) {
            if ($this->linkCount % 2 == 0) {
                return $this->pages = range($this->current + 1 - $this->linkCount / 2,
                    $this->current + $this->linkCount / 2);
            } else {
                return $this->pages = range($this->current - floor($this->linkCount / 2),
                    $this->current + floor($this->linkCount / 2));
            }
        } else {
            return $this->pages = range($this->totalPages - $this->linkCount + 1, $this->totalPages);
        }
    }

    /**
     * Pages the data
     */
    protected function pageData()
    {
        if (isset($this->sql)) {
            try {
                $sql = $this->sql;
                $sql .= " LIMIT " . ($this->current - 1) * $this->perPage . "," . $this->perPage;
                $stmt = $this->database->prepare($sql);
                $stmt->execute($this->bind);
                $this->pagedData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log($e->getTraceAsString());
            }
        }
    }

    /**
     * Counts total items
     */
    protected function countTotalItems()
    {
        if (isset($this->sql)) {
            try {
                $sql = $this->rewriteCountQuery($this->sql);
                $stmt = $this->database->prepare($sql);
                $stmt->execute($this->bind);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $this->totalItems = $result["COUNT(*)"];
            } catch (\Exception $e) {
                error_log($e->getTraceAsString());
            }
        }
    }

    /**
     * Counts total pages
     */
    protected function countTotalPages()
    {
        $this->countTotalItems();
        $this->totalPages = intval(ceil($this->totalItems / $this->perPage));
    }

    /**
     * Helper method - Rewrite the query into a "SELECT COUNT(*)" query.
     * Copied this function from the jobs project (see include/libs/Pager/examples/Pager_Wrapper.php)
     *
     * @param string $sql query
     *
     * @return string rewritten query OR false if the query can't be rewritten
     */
    protected function rewriteCountQuery($sql)
    {
        if (preg_match('/^\s*SELECT\s+\bDISTINCT\b/is', $sql) ||
            preg_match('/\s+GROUP\s+BY\s+/is', $sql) ||
            preg_match('/\s+UNION\s+/is', $sql)) {
            return false;
        }
        $open_parenthesis = '(?:\()';
        $close_parenthesis = '(?:\))';
        $subquery_in_select = $open_parenthesis . '.*\bFROM\b.*' . $close_parenthesis;
        $pattern = '/(?:.*' . $subquery_in_select . '.*)\bFROM\b\s+/Uims';
        if (preg_match($pattern, $sql)) {
            return false;
        }
        $subquery_with_limit_order = $open_parenthesis . '.*\b(LIMIT|ORDER)\b.*' . $close_parenthesis;
        $pattern = '/.*\bFROM\b.*(?:.*' . $subquery_with_limit_order . '.*).*/Uims';
        if (preg_match($pattern, $sql)) {
            return false;
        }
        $queryCount = preg_replace('/(?:.*)\bFROM\b\s+/Uims', 'SELECT COUNT(*) FROM ', $sql, 1);
        list($queryCount) = preg_split('/\s+ORDER\s+BY\s+/is', $queryCount);
        list($queryCount) = preg_split('/\bLIMIT\b/is', $queryCount);

        return trim($queryCount);
    }

    /**
     * Calls all the required methods for paging the data and creating pagination
     */
    public function paginateData()
    {
        $this->countTotalPages();
        if ($this->totalPages == 0) {
            return false;
        }
        $this->fixCurrentPage($this->current);
        $this->pageData();
        $this->detectTextualLinks();
        $this->generatePageList();
    }

    private function fixCurrentPage($currentPage = 1)
    {
        if (!is_int(intval($currentPage)) || $currentPage < 1) {
            $this->current = 1;
            $this->currentFixed = true;
        } elseif ($currentPage > $this->totalPages) {
            $this->current = $this->totalPages;
            $this->currentFixed = true;
        } else {
            $this->current = $currentPage;
        }
    }

    /**
     * Get the Previous Page
     *
     * @return int
     */
    public function getPreviousPage()
    {
        return $this->getCurrent() != 1 ? $this->getCurrent() - 1 : 1;
    }

    /**
     * Get Next Page
     *
     * @return int
     */
    public function getNextPage()
    {
        return $this->getCurrent() != $this->getTotalPages() ? $this->getCurrent() + 1 : false;
    }

    /**
     * Get meta data for pagination
     *
     * @return array
     */
    public function getPageMeta()
    {
        $meta = [];

        $meta['page'] = $this->getCurrent();
        $meta['perPage'] = $this->getPerPage();
        $meta['totalPages'] = $this->getTotalPages();
        $meta['total'] = $this->getTotalItems();

        if ($this->hasPrev()) {
            $meta['first_page'] = 1;
            $meta['prev_page'] = $this->getPreviousPage();
        }

        if ($this->hasNext()) {
            $meta['next_page'] = $this->getNextPage();
        }

        if ($this->hasLast()) {
            $meta['last_page'] = $this->getTotalPages();
        }

        return $meta;
    }

    /**
     * Get the value of Bind params
     *
     * @return array
     */
    public function getBind()
    {
        return $this->bind;
    }

    /**
     * Set the value of Bind params
     *
     * @param array bind
     *
     * @return self
     */
    public function setBind(array $bind)
    {
        $this->bind = $bind;

        return $this;
    }
}
