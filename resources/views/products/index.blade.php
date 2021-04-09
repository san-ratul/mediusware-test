@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{route('product.index')}}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control" value="{{request()->title ?? ''}}">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">-- Choose --</option>
                        @foreach ($variants as $variant)
                        <option value="" disabled>--{{$variant->title}}--</option>
                            @foreach ($variant->variations as $var)    
                            <option value="{{$var->variant}}" {{(request()->variant && request()->variant == $var->variant)?"selected":""}}>{{$var->variant}}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" value="{{request()->price_from ?? ''}}" placeholder="From" class="form-control">
                        <input type="text" name="price_to" value="{{request()->price_to ?? ''}}" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control" value="{{request()->date ?? ''}}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ( $products as $key => $product)
                    <tr>
                        <td>{{$key+$products->firstItem()}}</td>
                        <td>{{$product->title}} <br> Created at : {{$product->created_at->diffForHumans()}}</td>
                        <td style="width: 30%">{{$product->description}}</td>
                        <td style="width: 50%">
                            <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant-{{$key}}">
                                @forelse ($product->productVariantPrices as $pvp)
                                    @if(count($pvpIds) > 0)
                                        @if(in_array($pvp->id, $pvpIds))
                                            <dt class="col-sm-3 pb-0">
                                                {{($pvp->vairant1) ? "{$pvp->vairant1->variant}" : ''}}
                                                {{($pvp->vairant2) ? "/ {$pvp->vairant2->variant}" : ''}}
                                                {{($pvp->vairant3) ? "/ {$pvp->vairant3->variant}" : ''}}
                                            </dt>
                                            <dd class="col-sm-9">
                                                <dl class="row mb-0">
                                                    <dt class="col-sm-4 pb-0">Price : {{ number_format($pvp->price,2) }}</dt>
                                                    <dd class="col-sm-8 pb-0">InStock : {{ number_format($pvp->stock,2) }}</dd>
                                                </dl>
                                            </dd>
                                        @endif
                                    @else
                                        <dt class="col-sm-3 pb-0">
                                            {{($pvp->vairant1) ? "{$pvp->vairant1->variant}" : ''}}
                                            {{($pvp->vairant2) ? "/ {$pvp->vairant2->variant}" : ''}}
                                            {{($pvp->vairant3) ? "/ {$pvp->vairant3->variant}" : ''}}
                                        </dt>
                                        <dd class="col-sm-9">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 pb-0">Price : {{ number_format($pvp->price,2) }}</dt>
                                                <dd class="col-sm-8 pb-0">InStock : {{ number_format($pvp->stock,2) }}</dd>
                                            </dl>
                                        </dd>
                                    @endif
                                @empty
                                
                                @endforelse
                            </dl>
                            <button onclick="$('#variant-{{$key}}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-danger">No products found!</div>
                            </td>
                        </tr>
                    @endforelse
                        

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{$products->firstItem()}} to {{$products->lastItem()}} out of {{$products->total()}}</p>
                </div>
                <div class="col-md-2">
                    {{$products->links()}}
                </div>
            </div>
        </div>
    </div>

@endsection
