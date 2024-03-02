<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\Products\ProductTabs;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
use File;
use Image;
use Mpdf\Tag\Tr;
use Storage;

//class ProductsImport implements ToModel, WithHeadingRow, WithValidation
class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, ToModel
{
    private $rows = 0;

    private $year = 0;
    private $month = 0;

    public function __construct()
    {
        $this->year = Carbon::now()->year;
        $this->month =  Carbon::now()->format('m');
    }

    public function collection(Collection $rows)
    {
        // echo '<pre>';
        // print_r($rows);
        $brands = Brand::all();
        $categories = Category::all();
        foreach ($rows as $row) {

            $sku = $this->cleanSKU($row['product_code']); 

            $imageArray = array_filter($row->toArray(), function($value,$key) {
                return (strpos($key, 'url') === 0 && trim($value) !== '' );
            }, ARRAY_FILTER_USE_BOTH);
            // print_r($imageArray);
            // // print_r($row);
            // echo '******************************************************************************************';
            $tabArray = array_filter($row->toArray(), function($key) {
                return strpos($key, 'tab') === 0;
            }, ARRAY_FILTER_USE_KEY);
            
            $productTabs = [];
            $productDescription = '';
            
            $sku = $this->cleanSKU($row['product_code']);

            $brand = null;
            $parent_id = 0;
            $main_category_id = 0;

            if (isset($row['brand'])) {
                $newBrand = trim($row['brand']);
                $brand = $brands->where('name',$newBrand)->first();
                if($brand){
                    $brand->id;
                }else{
                    $slug = \Str::slug($newBrand);
                    $brand = Brand::firstOrNew(array('name' => $newBrand,'slug' => $slug));
                    $brand->name = $newBrand;
                    $brand->slug = $slug;
                    $brand->save();
                }
            }

            if (isset($row['category'])) {
                $category = explode(':', $row['category']);
                foreach ($category as $key => $cat) {
                    $cat = trim($cat);
                    $c = $categories->where('name', 'LIKE', $cat)->where(
                        'parent_id',
                        $parent_id
                    )->first();

                    if ($c) {
                        $parent_id = $c->id;
                    } else {
                        $c_new = Category::create([
                            'name' => $cat,
                            'parent_id' => $parent_id,
                            'level' => $key + 1,
                            'slug' => $this->categorySlug($cat),
                        ]);
                        $categories->push($c_new);
                        $parent_id = $c_new->id;
                    }

                    if($key == 0){
                        $main_category_id = $parent_id;
                    }
                }
            }
          

            $productId = Product::where(['sku' => $sku])->get()->first();
            if ($productId) {
                if (isset($row['product_name'])) {
                    $productId->name = trim($row['product_name']);
                }
                $productId->main_category = $main_category_id;
                $productId->description = $productDescription;

                if (isset($row['category'])) {
                    $productId->category_id = $parent_id;
                }
                if (isset($brand)) {
                    $productId->brand_id = $brand->id;
                }
                if (isset($row['vat'])) {
                    $productId->vat = $row['vat'];
                }
                if (isset($row['keywords'])) {
                    $productId->tags = $row['keywords'];
                }

                if (isset($row['price'])) {
                    $productId->unit_price = $row['price'];
                }
                if (isset($row['mpn'])) {
                    $productId->mpn = $row['mpn'];
                }
                if (isset($row['google_product_category'])) {
                    $productId->google_category = $row['google_product_category'];
                }

                if (isset($row['return_available'])) {
                    $productId->return_refund = $row['return_available'];
                }

                if (isset($row['status'])) {
                    $productId->published = $row['status'];
                }

                if (isset($row['weights']) || isset($row['weight_type'])) {
                    $productId->unit = $row['weights'].' '.$row['weight_type'];
                }

                if (isset($row['discount_price']) && isset($row['discount_type']) && isset($row['discount_start_date']) && isset($row['discount_end_date'])) {
                    $productId->discount = $row['discount_price'];

                    if(strtolower($row['discount_type']) == 'percentage'){
                        $productId->discount_type = 'percent';
                    }elseif(strtolower($row['discount_type']) == 'fixed'){
                        $productId->discount_type = 'amount';
                    }
                    $start = Date::excelToDateTimeObject($row['discount_start_date'])->format('Y-m-d 00:00:00');
                    $end = Date::excelToDateTimeObject($row['discount_end_date'])->format('Y-m-d 23:59:00');

                    $discount_start_date = strtotime($start);
                    $discount_end_date = strtotime($end);

                    $productId->discount_start_date = $discount_start_date;
                    $productId->discount_end_date = $discount_end_date;
                }
                $productId->updated_by = Auth::user()->id;
            } else {
                $discount_price = $discount_type = $discount_type = $discount_start_date = $discount_end_date = NULL;
                if (isset($row['discount_price']) && isset($row['discount_type']) && isset($row['discount_start_date']) && isset($row['discount_end_date'])) {
                    $discount_price = $row['discount_price'];

                    if(strtolower($row['discount_type']) == 'percentage'){
                        $discount_type = 'percent';
                    }elseif(strtolower($row['discount_type']) == 'fixed'){
                        $discount_type = 'amount';
                    }
                    $start = Date::excelToDateTimeObject($row['discount_start_date'])->format('Y-m-d 00:00:00');
                    $end = Date::excelToDateTimeObject($row['discount_end_date'])->format('Y-m-d 23:59:00');

                    $discount_start_date = strtotime($start);
                    $discount_end_date = strtotime($end);
                }
               

                $productId = Product::create([
                    'sku' => $sku,
                    'name' => trim($row['product_name']) ?? '',
                    'description' => $productDescription,
                    'main_category' => $main_category_id,
                    'category_id' => $parent_id,
                    'brand_id' => $brand ? $brand->id : 0,
                    'vat' => $row['vat'] ?? 0,
                    'tags' => $row['keywords'] ?? NULL,
                    'unit_price' => $row['price'] ?? 1,
                    'return_refund' => $row['return_available'] ?? 0,
                    'published' => $row['status'] ?? 0,
                    'unit' => ($row['weights'] ?? '').' '.($row['weight_type'] ?? ''),
                    'discount' => $discount_price,
                    'discount_type' => $discount_type,
                    'discount_start_date' => $discount_start_date,
                    'discount_end_date' => $discount_end_date,
                    'slug' => $this->productSlug(trim($row['product_name'])),
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                    'mpn'        => $row['mpn'] ?? null,
                    'google_category' => $row['google_product_category'] ?? null
                ]);
             
            }

            $mainImage = $galleryImage = $mainImageUploaded = $galleryImageUploaded ='';
            if(!empty($imageArray)){
                if(isset($imageArray['url_1'])){
                    $mainImage = $imageArray['url_1'];
                    unset($imageArray['url_1']);
                }
                $galleryImage = $imageArray;
            }

            if($mainImage != ''){
                $mainImage = base_path('product_images').'/'.$mainImage;
                $mainImageUploaded = $this->downloadAndResizeImage($mainImage, $sku, true);
            }

            if (!empty($galleryImage)) {
                $galleryImage = $this->downloadGallery($galleryImage, $sku);
                $galleryImageUploaded = implode(',', $galleryImage);
            }

            if ($mainImageUploaded) {
                $productId->thumbnail_img = $mainImageUploaded;
            }
            if ($galleryImageUploaded) {
                $productId->photos = $galleryImageUploaded;
            }
            $productId->save();
            if ($productId) {
                ProductStock::updateOrCreate([
                    'product_id' => $productId->id,
                    'sku' => $sku,
                ], [
                    'qty' => (isset($row['quantity']) && $row['quantity'] !== NULL) ? $row['quantity'] : 2,
                    'price' => $row['price'] ?? 1,
                    'variant' => '',
                ]);

                if(!empty($tabArray)){
                    foreach($tabArray as $key=>$tba){
                        $key = Str::after($key,'tab');
                        if($key != 'description' && $tba != null && $tba != ''){
                            $productTabs[] = [
                                'product_id' => $productId->id,
                                'heading'      => ucfirst(str_replace('_', ' ',$key)),
                                'content'   => $tba,
                            ];
                        }else{
                            $productDescription = $tba;
                        }
                    }
                }
                if(!empty($productTabs)){
                    ProductTabs::where('product_id', $productId->id)->delete();
                    ProductTabs::insert($productTabs);
                }
                $productId->description = $productDescription;
                $productId->save();
            }
        }
        
        flash(translate('Products imported successfully'))->success();
    }

    public function model(array $row)
    {
        ++$this->rows;
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function productSlug($name)
    {
        $slug = Str::slug($name, '-');
        $same_slug_count = Product::where('slug', 'LIKE', $slug . '%')->count();
        $slug_suffix = $same_slug_count ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        return $slug;
    }
    public function categorySlug($name)
    {
        $slug = Str::slug($name, '-');
        $same_slug_count = Category::where('slug', 'LIKE', $slug . '%')->count();
        $slug_suffix = $same_slug_count ? '-' . $same_slug_count + 1 : '';
        $slug .= $slug_suffix;

        return $slug;
    }

    public function rules(): array
    {
        return [
            // 'product_code' => function ($attribute, $value, $onFailure) {
            //     if (!is_numeric($value)) {
            //         $onFailure('Unit price is not numeric');
            //     }
            // }
            'product_code' => 'required',
        ];
    }

    public function downloadGallery($urls, $sku)
    {
        $i = 0;
        $data = [];
        foreach ($urls as $index => $url) {
            $url = base_path('product_images').'/'.$url;
            if(file_exists($url)){
                $data[] = $this->downloadAndResizeImage($url, $sku, false, $i + 1);
                $i++;
            }
        }
        return $data;
    }


    public function downloadAndResizeImage($imageUrl, $sku, $mainImage = false, $count = 1)
    {
        $data_url = '';

        try {
            $ext = substr($imageUrl, strrpos($imageUrl, '.') + 1);
            $path = 'products/' . $this->year . '/' . $this->month . '/' . $sku . '/';

            if ($mainImage) {
                $filename = $path . $sku . '.' . $ext;
            } else {
                $n = $sku . '_gallery_' .  $count;
                $filename = $path . $n . '.' . $ext;
            }

            if(file_exists($imageUrl)){
                // Download the image from the given URL
                $imageContents = file_get_contents($imageUrl);
                
                // Save the original image in the storage folder
                Storage::disk('public')->put($filename, $imageContents);
                $data_url = Storage::url($filename);
                // Create an Intervention Image instance for the downloaded image
                $image = Image::make($imageContents);

                // Resize and save three additional copies of the image with different sizes
                $sizes = config('app.img_sizes'); // Specify the desired sizes in pixels

                foreach ($sizes as $size) {
                    $resizedImage = $image->resize($size, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    if ($mainImage) {
                        $filename2 = $path . $sku . "_{$size}px" . '.' . $ext;
                    } else {
                        $n = $sku . '_gallery_' .  $count . "_{$size}px";
                        $filename2 = $path . $n . '.' . $ext;
                    }

                    // Save the resized image in the storage folder
                    Storage::disk('public')->put($filename2, $resizedImage->encode('jpg'));

                    // $data_url[] = Storage::url($filename2);
                }
            }
        } catch (Exception $e) {
        }

        return $data_url;
    }

    // public function downloadImage($url, $sku, $mainImage = false, $count = 1)
    // {
    //     // File path = products/YEAR/MONTH/SKU/

    //     $path = 'products/' . $this->year . '/' . $this->month . '/' . $sku . '/';
    //     if ($mainImage) {
    //         $name = $path . $sku . '.' . substr($url, strrpos($url, '.') + 1);
    //     } else {
    //         $n = $sku . '_gallery_' .  $count;
    //         $name = $path . $n . '.' . substr($url, strrpos($url, '.') + 1);
    //     }

    //     $contents = file_get_contents($url);

    //     $img = Storage::disk('public')->put($name, $contents);

    //     $og_img = Storage::url($name);


    //     // resize 
    //     // 300*300
    //     // 500*500

    //     // dd(storage_path('app/public/'.$name));

    //     $sizes = config('app.img_sizes');

    //     foreach ($sizes as $size) {

    //         if ($mainImage) {
    //             $r_name = $path . $sku . '_' . $size . '.' . substr($url, strrpos($url, '.') + 1);
    //         } else {
    //             $n = $sku . '_gallery_' .  $count;
    //             $r_name = $path . $n . '_' . $size . '.' . substr($url, strrpos($url, '.') + 1);
    //         }

    //         $r_img = Image::make(storage_path('app/public/' . $name))->resize($size, $size, function ($constraint) {
    //             $constraint->aspectRatio();
    //         });

    //         $img = Storage::disk('public')->put($r_name, $r_img->__toString());
    //     }

    //     return $og_img;
    // }

    // // public function downloadGalleryImages($urls)
    // // {
    // //     $data = array();
    // //     foreach (explode(',', str_replace(' ', '', $urls)) as $url) {
    // //         $data[] = $this->downloadThumbnail($url);
    // //     }
    // //     return implode(',', $data);
    // // }

    public function cleanSKU($sku)
    {
        $sku = trim($sku);
        $sku = preg_replace('/[^a-zA-Z0-9\-\_]/i', '', $sku);
        return $sku;
    }
}
