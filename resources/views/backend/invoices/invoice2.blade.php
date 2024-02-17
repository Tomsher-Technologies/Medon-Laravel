<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ translate('INVOICE') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">

    <link rel="stylesheet" href="https://its.tomsher.net/assets/css/bulk-style.css">
    <link rel="stylesheet" href="https://its.tomsher.net/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://its.tomsher.net/assets/css/style.css">
    <style media="all">
        body {
            font-size: 10px;
        }

        p {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.6em;
            color: #666;
        }

        h5 {
            font-size: 10px;
        }

        h6 {
            font-size: 10px;
        }
    </style>

</head>

<body>

    @php
        $billing_address = $shipping_address = json_decode($order->shipping_address);
        if ($order->billing_address) {
            $billing_address = json_decode($order->billing_address);
        }

        $attributes = allAttributes();
    @endphp

    <section class="section pt-0">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card term-card mb-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card-header border-bottom-dashed p-4">
                                    <div class="row">
                                        <div class="col-4">
                                            <img class="card-logo card-logo-dark" alt="logo dark" height="40" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/img/logo.png'))) }}">
                                            <div class="mt-sm-3 mt-4">
                                                <h6 class="text-muted mb-2 text-uppercase fw-semibold "> Voyage
                                                    Marine Automation LLC</h6>
                                                <p class="text-muted mb-1" id="address-details">P.O.Box.119218, Dubai
                                                    Maritime City, UAE</p>
                                            </div>
                                        </div>

                                        <div class="col-4 text-center">
                                            <div class="mt-sm-3 mt-2">
                                                <h6 class="mb-2 text-uppercase  fs-1">TAX INVOICE</h6>
                                                <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">Invoice
                                                    No : <span class="fs-3 mb-0  fw-bold ">{{ $order->code }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-4 mt-sm-0 mt-3 text-end">
                                            <h6><span class="text-muted fw-normal">Email:</span> <span
                                                    id="email">voyage@voyagemarine.ae</span></h6>
                                            <h6>
                                                <span class="text-muted fw-normal">Website:</span> <a
                                                    href="https://voyagemarine.ae" class="link-primary" target="_blank"
                                                    id="website">www.voyagemarine.ae</a>
                                            </h6>
                                            <h6 class="mb-0"><span class="text-muted fw-normal">Contact No:
                                                </span><span id="contact-no">+ 971 04 363 8100</span></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-lg-3 col-3">
                                            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">Order Date
                                            </p>
                                            <h5 class="fs-15 mb-0">
                                                <span id="invoice-date">{{ date('d M Y, h:i:a', $order->date) }}</span>
                                            </h5>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">Payment
                                                Method</p>
                                            <span class="badge bg-success-subtle text-success "
                                                id="payment-status">{{ translate(ucfirst(str_replace('_', ' ', $order->payment_type))) }}</span>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">Total Amount
                                            </p>
                                            <h5 class="fs-15 mb-0">{{ single_price($order->grand_total) }}</h5>
                                        </div>
                                        <div class="col-lg-3 col-3">
                                            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">Delivery Status
                                            </p>
                                            <h5 class="fs-15 mb-0">
                                                {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card-body p-4 border-top border-top-dashed">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <h6 class="text-muted text-uppercase fw-semibold fs-14 mb-3">Billing
                                                Address</h6>
                                            <p class="fw-medium mb-2 fs-16" id="billing-name">
                                                {{ $billing_address->name }}</p>
                                            <p class="text-muted mb-1" id="billing-address-line-1">
                                                {{ $billing_address->address }}
                                                @if ($billing_address->city)
                                                    <br>
                                                    {{ \App\Models\City::find($billing_address->city)->name }}
                                                @endif
                                                @if ($billing_address->state)
                                                    <br>
                                                    {{ \App\Models\State::find($billing_address->state)->name }}
                                                    <br>
                                                @endif
                                                {{ $billing_address->postal_code }}
                                                @if ($billing_address->country)
                                                    <br>
                                                    {{ \App\Models\Country::find($billing_address->country)->name }}
                                                    <br>
                                                @endif
                                            </p>
                                            <p class="text-muted mb-1">
                                                <span>Phone: </span>
                                                <span id="billing-phone-no">{{ $billing_address->phone }}</span>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <span>Email: </span>
                                                <span id="billing-tax-no">{{ $billing_address->email }}</span>
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="text-muted text-uppercase fw-semibold fs-14 mb-3">Shipping
                                                Address</h6>
                                            <p class="fw-medium mb-2 fs-16" id="billing-name">
                                                {{ $shipping_address->name }}</p>
                                            <p class="text-muted mb-1" id="billing-address-line-1">
                                                {{ $shipping_address->address }}
                                                @if ($shipping_address->city)
                                                    <br>
                                                    {{ \App\Models\City::find($shipping_address->city)->name }}
                                                @endif
                                                @if ($shipping_address->state)
                                                    <br>
                                                    {{ \App\Models\State::find($shipping_address->state)->name }}
                                                    <br>
                                                @endif
                                                {{ $shipping_address->postal_code }}
                                                @if ($shipping_address->country)
                                                    <br>
                                                    {{ \App\Models\Country::find($shipping_address->country)->name }}
                                                    <br>
                                                @endif
                                            </p>
                                            <p class="text-muted mb-1">
                                                <span>Phone: </span>
                                                <span id="billing-phone-no">{{ $shipping_address->phone }}</span>
                                            </p>
                                            <p class="text-muted mb-0">
                                                <span>Email: </span>
                                                <span id="billing-tax-no">{{ $shipping_address->email }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-borderless  table-nowrap align-middle mb-0 text-start">
                                            <thead>
                                                <tr class="table-active">
                                                    <th scope="col" style="width: 50px;">#</th>
                                                    <th scope="col">Product Details</th>
                                                    <th scope="col">Rate</th>
                                                    <th scope="col">Quantity</th>
                                                    <th scope="col" class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody id="products-list">
                                                @foreach ($order->orderDetails as $key => $orderDetail)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration }}</th>
                                                        <td class="text-start">
                                                            <span class="fw-medium">
                                                                {{ $orderDetail->product->name }}
                                                            </span>
                                                            @if ($orderDetail->variation)
                                                                <p class="text-muted mb-0">
                                                                    @php
                                                                        $attribute = json_decode($orderDetail->product->attributes);
                                                                        $variation = explode('-', $orderDetail->variation);
                                                                        foreach ($attribute as $key => $attr_id) {
                                                                            $attr = $attributes->where('id', $attr_id)->first()->name;
                                                                            echo $attr . ':' . $variation[$key] . ',';
                                                                        }
                                                                    @endphp
                                                                </p>
                                                                <p class="text-muted mb-0">
                                                                    SKU:
                                                                    {{ $orderDetail->product->stocks()->where('variant', $orderDetail->variation)->first()->sku }}
                                                                </p>
                                                            @else
                                                                <p class="text-muted mb-0">
                                                                    SKU: {{ $orderDetail->product->sku }}
                                                                </p>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($orderDetail->og_price)
                                                                <del>{{ single_price($orderDetail->og_price) }}</del>
                                                                <br>
                                                            @endif
                                                            {{ single_price($orderDetail->price / $orderDetail->quantity) }}
                                                        </td>
                                                        <td>{{ $orderDetail->quantity }}</td>
                                                        <td class="text-end">
                                                            {{ single_price($orderDetail->price) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="border-top border-top-dashed mt-2">
                                        <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto"
                                            style="width:250px">
                                            <tbody>
                                                <tr>
                                                    <td>Sub Total</td>
                                                    <td class="text-end fw-bold">
                                                        {{ single_price($order->orderDetails->sum('price')) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Estimated Tax (5% vat included)</td>
                                                    <td class="text-end fw-bold">
                                                        {{ single_price($order->orderDetails->sum('tax')) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Discount </td>
                                                    <td class="text-end fw-bold">
                                                        {{ single_price($order->coupon_discount) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Shipping Charge</td>
                                                    <td class="text-end fw-bold">
                                                        {{ single_price($order->shipping_cost) }}
                                                    </td>
                                                </tr>
                                                <tr class="border-top border-top-dashed fs-5">
                                                    <th scope="row">Total Amount</th>
                                                    <th class="text-end fw-bold">
                                                        {{ single_price($order->grand_total) }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </div>
</body>

</html>
