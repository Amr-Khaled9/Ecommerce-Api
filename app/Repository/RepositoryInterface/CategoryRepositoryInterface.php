<?php

namespace App\Repository\RepositoryInterface;

interface CategoryRepositoryInterface
{
    public function store( $request);
    public function update( $request ,$id);
}
