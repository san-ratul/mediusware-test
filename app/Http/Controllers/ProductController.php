<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $variants = Variant::all();
        $productVariantPrice = ProductVariantPrice::selectRaw('MAX(price) as max_price, MIN(price) as min_price')->first();
        $search = [];
        $pvpIds = [];
        $productIds = [];
        if($request->title){
            array_push($search,['title', 'like', "%$request->title%"]);
        }
        if($request->price_from && $request->price_to){
            $priceFrom = $request->price_from;
            $priceTo = $request->price_to;
        }else if($request->price_from){
            $priceFrom = $request->price_from;
            $priceTo = $productVariantPrice->max_price;
        }else if($request->price_to){
            $priceFrom = $productVariantPrice->min_price;
            $priceTo = $request->price_to;
        }
        if(isset($request->price_from) || isset($request->price_to)){
            $productVariantPrices = ProductVariantPrice::where([['price','>=', $priceFrom], ['price', '<=', $priceTo]]);
        }

        if($request->date){
            array_push($search,['created_at', 'like', "$request->date%"]);
        }
        if($request->variant){
            $variantIds = ProductVariant::where('variant', $request->variant)->pluck('id')->toArray();
            $productVariantPrices = ProductVariantPrice::whereIn('product_variant_one', $variantIds)->orWhereIn('product_variant_two', $variantIds)->orWhereIn('product_variant_three', $variantIds);
        }
        if($request->variant || $request->price_from || $request->price_to){
            $pvpIds = [];
            foreach($productVariantPrices->get() as $pvp){
                if($pvp->price >= $priceFrom && $pvp->price <= $priceTo){
                    array_push($pvpIds, $pvp->id);
                }
            }
            $productIds = $productVariantPrices->selectRaw('DISTINCT(product_id) as d_product_id')->pluck('d_product_id')->toArray();
        }
        if(count($productIds) > 0 && count($search) > 0){
            $products = Product::whereIn('id', $productIds)->where($search)->paginate(5);
        }else if(count($productIds) > 0){
            $products = Product::whereIn('id', $productIds)->paginate(5);
        }else if(count($search) > 0){
            $products = Product::where($search)->paginate(5);
        }else{
            $products = Product::paginate(5);
        }
        return view('products.index', [
            'products' => $products->appends($request->except('page')),
            'variants' => $variants,
            'pvpIds' => $pvpIds
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'sku' => ['required', 'unique:products,sku']
        ]);
        // return $request->product_variant[0]['tags'];
        $product = Product::create([
            'title' => $request->title,
            'sku' => $request->sku,
            'description' => $request->description,
        ]);
        foreach($request->product_variant as $pv){
            foreach($pv['tags'] as $tag){
                ProductVariant::create([
                    'variant' => $tag, 
                    'variant_id' => $pv['option'], 
                    'product_id' => $product->id,
                ]);
            }
        }
        foreach($request->product_variant_prices as $pvp){
            $title = explode('/',$pvp['title']);
            $pv1 = $product->productVariants()->where('variant', $title[0])->first();
            $pv2 = (array_key_exists(1, $title)) ? $product->productVariants()->where('variant', $title[1])->first() : null;
            $pv3 = (array_key_exists(2, $title)) ? $product->productVariants()->where('variant', $title[2])->first() : null;
            ProductVariantPrice::create([
                'price' => $pvp['price'], 
                'product_variant_one' => ($pv1) ? $pv1->id : null, 
                'product_variant_two'=> ($pv2) ? $pv2->id : null, 
                'product_variant_three' => ($pv3) ? $pv3->id : null, 
                'stock' => $pvp['stock'], 
                'product_id' => $product->id
            ]);
        }

        return $product;
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $parentVariant = [];
        $pvp = $product->productVariantPrices()->first();
        
        if($pvp->vairant1 != null){
            array_push($parentVariant, ['variant' => $pvp->vairant1->variantParent, 'tags' => ProductVariant::where([['product_id', $product->id], ['variant_id', $pvp->vairant1->variantParent->id]])->pluck('variant')->toArray()]);
        }
        if($pvp->vairant2 != null){
            array_push($parentVariant, ['variant' => $pvp->vairant2->variantParent, 'tags' => ProductVariant::where([['product_id', $product->id], ['variant_id', $pvp->vairant2->variantParent->id]])->pluck('variant')->toArray()]);
        }
        if($pvp->vairant3 != null){
            array_push($parentVariant, ['variant' => $pvp->vairant3->variantParent, 'tags' => ProductVariant::where([['product_id', $product->id], ['variant_id', $pvp->vairant3->variantParent->id]])->pluck('variant')->toArray()]);
        }
        return response()->json(['product'=>$product, 'parentVariant' => $parentVariant], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants' ,'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'sku' => ['required', 'unique:products,sku,'.$product->id]
        ]);
        // return $request->product_variant[0]['tags'];
        $product->update([
            'title' => $request->title,
            'sku' => $request->sku,
            'description' => $request->description,
        ]);
        ProductVariant::where('product_id', $product->id)->delete();
        ProductVariantPrice::where('product_id', $product->id)->delete();

        foreach($request->product_variant as $pv){
            foreach($pv['tags'] as $tag){
                ProductVariant::create([
                    'variant' => $tag, 
                    'variant_id' => $pv['option'], 
                    'product_id' => $product->id,
                ]);
            }
        }
        foreach($request->product_variant_prices as $pvp){
            $title = explode('/',$pvp['title']);
            $pv1 = $product->productVariants()->where('variant', $title[0])->first();
            $pv2 = (array_key_exists(1, $title)) ? $product->productVariants()->where('variant', $title[1])->first() : null;
            $pv3 = (array_key_exists(2, $title)) ? $product->productVariants()->where('variant', $title[2])->first() : null;
            ProductVariantPrice::create([
                'price' => $pvp['price'], 
                'product_variant_one' => ($pv1) ? $pv1->id : null, 
                'product_variant_two'=> ($pv2) ? $pv2->id : null, 
                'product_variant_three' => ($pv3) ? $pv3->id : null, 
                'stock' => $pvp['stock'], 
                'product_id' => $product->id
            ]);
        }
        $product = Product::find($product->id);
        $parentVariant = [];
        $pvp = $product->productVariantPrices()->first();
        
        if($pvp->vairant1 != null){
            array_push($parentVariant, ['variant' => $pvp->vairant1->variantParent, 'tags' => ProductVariant::where([['product_id', $product->id], ['variant_id', $pvp->vairant1->variantParent->id]])->pluck('variant')->toArray()]);
        }
        if($pvp->vairant2 != null){
            array_push($parentVariant, ['variant' => $pvp->vairant2->variantParent, 'tags' => ProductVariant::where([['product_id', $product->id], ['variant_id', $pvp->vairant2->variantParent->id]])->pluck('variant')->toArray()]);
        }
        if($pvp->vairant3 != null){
            array_push($parentVariant, ['variant' => $pvp->vairant3->variantParent, 'tags' => ProductVariant::where([['product_id', $product->id], ['variant_id', $pvp->vairant3->variantParent->id]])->pluck('variant')->toArray()]);
        }
        return response()->json(['product'=>$product, 'parentVariant' => $parentVariant], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
