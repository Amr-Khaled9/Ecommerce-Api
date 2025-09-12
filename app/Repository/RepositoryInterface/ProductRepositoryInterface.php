<?php


namespace App\Repository\RepositoryInterface;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

interface ProductRepositoryInterface
{
    public function store( $request);
    public function update( $request ,$id);
    public function filterByPrice( $request  );
}
