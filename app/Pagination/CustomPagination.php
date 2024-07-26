<?php

namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomPagination extends LengthAwarePaginator
{
    public function toArray()
    {
        $data = parent::toArray();
        $page = null;
        if($this->nextPageUrl()){
            $page = $this->currentPage() + 1;
        }

        $data['next'] = $page;

        return $data;
    }
}


