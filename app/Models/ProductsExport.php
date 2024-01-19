<?php

namespace App\Models;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Product::all();
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            // 'description',
            // 'added_by',
            // 'user_id',
            'Category Name',
            'Brand Name',
            'Keywords',
            // 'video_provider',
            // 'video_link',
            'Unit Price',
            // 'Purchase Price',
            'VAT',
            'Unit',
            'Current Stock',
            'Return Available'
            // 'meta_title',
            // 'meta_description',
        ];
    }

    /**
    * @var Product $product
    */
    public function map($product): array
    {
        $qty = 0;
        foreach ($product->stocks as $key => $stock) {
            $qty += $stock->qty;
        }

        return [
            $product->sku,
            $product->name,
            // $product->description,
            // $product->added_by,
            // $product->user_id,
            $product->category->name ?? '',
            $product->brand->name ?? '',
            // $product->video_provider,
            // $product->video_link,
            $product->tags,
            $product->unit_price,
            // $product->purchase_price,
            $product->vat,
            $product->unit,
//            $product->current_stock,
            $qty,
            $product->return_refund,
        ];
    }
}
