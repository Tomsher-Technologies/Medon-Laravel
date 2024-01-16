<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Search;
use App\Models\User;
use Artisan;
use Cache;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Auth;
use DB;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_dashboard(Request $request)
    {
        // //CoreComponentRepository::initializeCache();
        $counts = [];

        // $counts = Cache::remember('counts', 86400, function () {
        $a = [];
        $year = $request->has('year') ? $request->year : date('Y');
        if (Auth::user()->user_type == 'staff' && Auth::user()->shop_id != null){
            $orders = '';
            $a['shopOrderCount'] = Order::where('shop_id', Auth::user()->shop_id)->count();
            $a['shopPendingCount'] = Order::where('shop_id', Auth::user()->shop_id)->where('delivery_status', '!=', 'delivered')->count();
            $a['shopCompletedCount'] = Order::where('shop_id', Auth::user()->shop_id)->where('delivery_status', '=', 'delivered')->count();
            $a['shopTodayOrderCount'] = Order::where('shop_id', Auth::user()->shop_id)->whereDate('shop_assigned_date',date('Y-m-d'))->count();


            $monday = strtotime('next Monday -1 week');
            $monday = date('w', $monday)==date('w') ? strtotime(date("Y-m-d",$monday)." +7 days") : $monday;
            $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
            $this_week_sd = date("Y-m-d",$monday)."<br>";
            $this_week_ed = date("Y-m-d",$sunday)."<br>";

            $a['shopWeekOrderCount'] = Order::where('shop_id', Auth::user()->shop_id)
                                            ->whereDate('shop_assigned_date','>=', $this_week_sd)
                                            ->whereDate('shop_assigned_date','<=', $this_week_ed)
                                            ->count();

            $first_day_this_month = date('Y-m-01'); // hard-coded '01' for first day
            $last_day_this_month  = date('Y-m-t');
           
            $a['shopMonthOrderCount'] = Order::where('shop_id', Auth::user()->shop_id)
                                            ->whereDate('shop_assigned_date','>=', $first_day_this_month)
                                            ->whereDate('shop_assigned_date','<=', $last_day_this_month)
                                            ->count();

            $first_d_this_year = date("Y-m-d",strtotime("this year January 1st"));
            $last_d_this_year = date("Y-m-d",strtotime("this year December 31st"));

            $a['shopYearOrderCount'] = Order::where('shop_id', Auth::user()->shop_id)
                                            ->whereDate('shop_assigned_date','>=', $first_d_this_year)
                                            ->whereDate('shop_assigned_date','<=', $last_d_this_year)
                                            ->count();

            $first_d_last_year = date("Y-m-d",strtotime("last year January 1st"));
            $last_d_last_year = date("Y-m-d",strtotime("last year December 31st"));

            $a['shopLYearOrderCount'] = Order::where('shop_id', Auth::user()->shop_id)
                                            ->whereDate('shop_assigned_date','>=', $first_d_last_year)
                                            ->whereDate('shop_assigned_date','<=', $last_d_last_year)
                                            ->count();
        }else{
            $a['totalUsersCount'] = User::where('user_type', 'customer')->count();
            $a['totalProductsCount'] = Product::count();
            $a['categoryCount'] = Category::count();
            $a['brandCount'] = Brand::count();

            $a['orderCount'] = Order::count();
            $a['orderCompletedCount'] = Order::where('delivery_status', 'delivered')->count();
            $a['salesAmount'] = Order::where('delivery_status', 'delivered')->sum('grand_total');
            $a['productsSold'] = OrderDetail::where('delivery_status', 'delivered')->sum('quantity');
        }
            
            // return $a;
        $counts = $a; 
        // });
        // $counts['totalUsersCount'] = Cache::remember('totalUsersCount', 86400, function () {
        //     return User::where('user_type', 'customer')->count();
        // });
        // $counts['totalProductsCount'] = Cache::remember('totalProductsCount', 86400, function () {
        //     return
        // });
        // $counts['categoryCount'] = Cache::remember('categoryCount', 86400, function () {
        //     return
        // });
        // $counts['brandCount'] = Cache::remember('brandCount', 86400, function () {
        //     return ::count();
        // });

        $searches = Cache::remember('searches', 86400, function () {
            return Search::latest()->with(['user'])->limit(10)->get();
        });

        // Top Selling products
        // $topOrders = OrderDetail::groupBy('product_id')->selectRaw('product_id, sum(quantity) as sum')->orderBy('sum', 'DESC')->limit(1)->get()->pluck('product_id')->toArray();

        // $topProducts = Product::whereIn('id', $topOrders)->get();

        $topProducts = Cache::remember('topProducts', 86400, function () {
            return Product::withSum('orderDetails', 'quantity')->orderBy('order_details_sum_quantity', 'DESC')->get()
                ->where('order_details_sum_quantity', '>', 0);
        });

        $days = Cache::remember('days', 86400, function () {
            $period = CarbonPeriod::between(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
            $adDys = [];
            foreach ($period as $date) {
                $day = $date->format('d');
                $adDys[] = $day;
            }
            return $adDys;
        });

        // $months = Cache::remember('months', 86400, function () {
        //     $period = CarbonPeriod::between(Carbon::now()->start(), Carbon::now()->endOfMonth());
        //     $adDys = [];
        //     foreach ($period as $date) {
        //         $day = $date->format('d');
        //         $adDys[] = $day;
        //     }
        //     return $adDys;
        // });

        $orderMonthGraph = Cache::remember('orderMonthGraph', 86400, function () use ($days) {
            $graph = [];

            // All Orders this month
            $monthOrders = Order::whereMonth('created_at', Carbon::now()->month)
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('d'); // grouping by months
                });

            $monthOrdersData = [];
            foreach ($days as $day) {
                $monthOrdersData[] = isset($monthOrders[$day]) ?  $monthOrders[$day]->count() : 0;
            }
            $graph['monthOrdersData'] = implode(',', $monthOrdersData);


            // Completed Orders this month
            $monthOrdersCompleted = Order::where('delivery_status', 'delivered')
                ->whereMonth('created_at', Carbon::now()->month)
                ->get()
                ->groupBy(function ($date) {
                    // return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                    return Carbon::parse($date->created_at)->format('d'); // grouping by months
                });
            $monthOrdersCompletedData = [];
            foreach ($days as $day) {
                $monthOrdersCompletedData[] = isset($monthOrdersCompleted[$day]) ?  $monthOrdersCompleted[$day]->count() : 0;
            }
            $graph['monthOrdersCompletedData'] = implode(',', $monthOrdersCompletedData);

            return $graph;
        });

        Cache::forget('salesMonthGraph');
        $salesMonthGraph = Cache::remember('salesMonthGraph', 86400, function () use ($days) {
            $graph = [];

            // All Orders this month
            $monthOrders = Order::whereMonth('created_at', Carbon::now()->month)
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('d'); // grouping by months
                });

            
            $monthOrdersData = [];
            foreach ($days as $day) {
                $monthOrdersData[] = isset($monthOrders[$day]) ?  $monthOrders[$day]->sum('grand_total') : 0;
            }
            $graph['monthSalesData'] = implode(',', $monthOrdersData);
            return $graph;
        });

        // $months = Cache::remember('months', 86400, function () {
        //     $period = CarbonPeriod::create(Carbon::now()->subYear(), '1 month', Carbon::now());
        //     $adDys = [];
        //     foreach ($period as $key => $date) {
        //         $adDys[$key]['name'] = $date->format('M y');
        //         $adDys[$key]['date'] = $date->format('m-y');
        //     }
        //     return $adDys;
        // });

        // $orderYearGraph = Cache::remember('orderYearGraph', 86400, function () use ($months) {
        //     $graph = [];
        //     // All orders
        //     $allOrders = Order::whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
        //         ->get()
        //         ->groupBy(function ($date) {
        //             return Carbon::parse($date->created_at)->format('m-y'); // grouping by months
        //         });

        //     $data = [];
        //     foreach ($months as $month) {
        //         $data[] = isset($allOrders[$month['date']]) ?  $allOrders[$month['date']]->count() : 0;
        //     }
        //     $graph['all_orders_per_month'] = implode(',', $data);

        //     // Completed orders
        //     $completedOrders = Order::where('delivery_status', 'delivered')
        //         ->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])
        //         ->get()
        //         ->groupBy(function ($date) {
        //             return Carbon::parse($date->created_at)->format('m-y'); // grouping by months
        //         });

        //     $data = [];
        //     foreach ($months as $month) {
        //         $data[] = isset($completedOrders[$month['date']]) ?  $completedOrders[$month['date']]->count() : 0;
        //     }
        //     $graph['completed_orders_per_month'] = implode(',', $data);

        //     return $graph;
        // });

        $orderYearGraph = Cache::remember('orderYearGraph', 86400, function () {
            $graph = [];

            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            // All orders
            $data = Order::select(\DB::raw('MONTH(created_at) as month, COUNT(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subMonths(11))
                ->groupBy('month')
                ->get();

            $months = collect([]);
            $counts = collect([]);

            for ($i = 0; $i < 12; $i++) {
                $currentMonth = $startDate->copy()->addMonths($i);
                $monthData = $data->where('month', $currentMonth->month)->first();

                $months->push($currentMonth->format('M y'));
                $counts->push($monthData ? $monthData->count : 0);
            }
            $graph['all']['months'] = $months;
            $graph['all']['counts'] = $counts;



            // Completed orders
            unset($data);
            $data = Order::select(\DB::raw('MONTH(created_at) as month, COUNT(*) as count'))
                ->where('delivery_status', 'delivered')
                ->where('created_at', '>=', Carbon::now()->subMonths(11))
                ->groupBy('month')
                ->get();

            $months = collect([]);
            $counts = collect([]);

            for ($i = 0; $i < 12; $i++) {
                $currentMonth = $startDate->copy()->addMonths($i);
                $monthData = $data->where('month', $currentMonth->month)->first();

                $months->push($currentMonth->format('M y'));
                $counts->push($monthData ? $monthData->count : 0);
            }
            $graph['completed']['months'] = $months;
            $graph['completed']['counts'] = $counts;


            return $graph;
        });

        // Cache::forget('salesYearGraph');
        $salesYearGraph = Cache::remember('salesYearGraph', 86400, function () {
            $graph = [];

            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            // All orders
            $data = Order::select(\DB::raw('MONTH(created_at) as month, COUNT(*) as count, SUM(grand_total) as total'))
                ->where('delivery_status', 'delivered')
                ->where('created_at', '>=', Carbon::now()->subMonths(11))
                ->groupBy('month')
                ->get();

            $months = collect([]);
            $counts = collect([]);

            for ($i = 0; $i < 12; $i++) {
                $currentMonth = $startDate->copy()->addMonths($i);
                $monthData = $data->where('month', $currentMonth->month)->first();

                $months->push($currentMonth->format('M y'));
                $counts->push($monthData ? $monthData->total : 0);
            }

            $graph['months'] = $months;
            $graph['counts'] = $counts;

            return $graph;
        });

        $shopOrderYearGraph = [
            'all' => [
                'months' =>array(),
                'counts' => array()
            ],
            'completed' => [
                'months' =>array(),
                'counts' => array()
            ]
        ];
       
        // if (Auth::user()->user_type == 'staff' && Auth::user()->shop_id != null){
            // echo Carbon::now()->subMonths(11);
            // die;

            $graphShop = [];

            $startDateShop = Carbon::now()->startOfYear();
            // All orders
            // DB::enableQueryLog();
            $dataShop = Order::select(\DB::raw('MONTH(shop_assigned_date) as month, COUNT(*) as count'))
                ->where('shop_id', Auth::user()->shop_id)
                ->whereYear('shop_assigned_date', $year)
                ->groupBy('month')
                ->get();

                // dd(DB::getQueryLog());

            $monthsShop = collect([]);
            $countsShop = collect([]);

            for ($iShop = 0; $iShop < 12; $iShop++) {
                $currentMonthShop = $startDateShop->copy()->addMonths($iShop);
                $monthDataShop = $dataShop->where('month', $currentMonthShop->month)->first();

                $monthsShop->push($currentMonthShop->format('M '.$year));
                $countsShop->push($monthDataShop ? $monthDataShop->count : 0);
            }
            $graphShop['all']['months'] = $monthsShop;
            $graphShop['all']['counts'] = $countsShop;

            // Completed orders
            unset($dataShop);
            
            $dataShopCompleted = Order::select(\DB::raw('MONTH(shop_assigned_date) as month, COUNT(*) as count'))
                ->where('delivery_status', 'delivered')
                ->where('shop_id', Auth::user()->shop_id)
                ->whereYear('shop_assigned_date', $year)
                ->groupBy('month')
                ->get();

            $monthsShopCompleted = collect([]);
            $countsShopCompleted = collect([]);

            for ($iShopCompleted = 0; $iShopCompleted < 12; $iShopCompleted++) {
                $currentMonthShopCompleted = $startDateShop->copy()->addMonths($iShopCompleted);
                $monthDataShopCompleted = $dataShopCompleted->where('month', $currentMonthShopCompleted->month)->first();

                $monthsShopCompleted->push($currentMonthShopCompleted->format('M '.$year));
                $countsShopCompleted->push($monthDataShopCompleted ? $monthDataShopCompleted->count : 0);
            }
            $graphShop['completed']['months'] = $monthsShopCompleted;
            $graphShop['completed']['counts'] = $countsShopCompleted;

            $shopOrderYearGraph = $graphShop;
        // }
        // echo '<pre>';
        // print_r($shopOrderYearGraph);
        // die;
        return view(
            'backend.dashboard',
            compact('searches', 'counts', 'topProducts', 'orderMonthGraph', 'days','shopOrderYearGraph', 'orderYearGraph', 'salesYearGraph', 'salesMonthGraph','year')
        );
    }

    public function clearCache($type = null)
    {
        if ($type) {
            Cache::forget($type);

            if ($type == 'orderMonthGraph') {
                Cache::forget('days');
            }

            flash(translate('Data refreshed'))->success();
            return back();
        }

        Artisan::call('cache:clear');
        flash(translate('Cache cleared successfully'))->success();
        return back();
    }
}
