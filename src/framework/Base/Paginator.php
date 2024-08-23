<?php

namespace Framework;

class Paginator
{
    private $totalPartners;
    private $itemsLimit;
    private $currentPage;

    public function __construct(int $totalPartners, int $itemsLimit, int $currentPage)
    {
        $this->totalPartners = $totalPartners;
        $this->itemsLimit = $itemsLimit;
        $this->currentPage = $currentPage;
    }

    public function getLimit(): int
    {
        return $this->itemsLimit;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsLimit;
    }

    public function getTotalPages(): int
    {
        return ceil($this->totalPartners / $this->itemsLimit);
    }

    public function previousPage(): int
    {
        return $this->currentPage - 1;
    }

    public function nextPage(): int
    {
        return $this->currentPage + 1;
    }
}
