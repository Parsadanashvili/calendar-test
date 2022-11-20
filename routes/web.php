<?php

use App\Models\Product;
use App\Models\ProductBook;
use App\Models\ProductSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $products = Product::latest()->paginate(8);

    return view('welcome', compact('products'));
})->name('home');


Route::get('/product/{product}', function (Product $product) {
    return view('product', compact('product'));
})->name('product');

Route::post('/schedule/{product}/validate', function (Product $product, Request $request) {
    $request->validate([
        'date' => 'required|date',
        'start_time' => 'required|date_format:H:i:s|before:end_time',
        'end_time' => 'required|date_format:H:i:s|after:start_time',
    ]);

    $schedule = $product->schedules()
        ->where('start_time', '<=', $request->input('start_time'))
        ->where('end_time', '>=', $request->input('end_time'))
        ->where('day_date', '=', $request->input('date'))
        ->count();

    // SQL Statement
    //
    //
    // DB::select(
    //     "SELECT * FROM product_schedules WHERE `product_id` = ? AND start_time <= ? AND end_time >= ? AND day_date = ?",
    //     [(int) $product->id, $request->input('start_time'), $request->input('end_time'), $request->input('date')]
    // );

    if (!$schedule) {
        return response()->json([
            'available' => 0
        ], 200);
    }

    $books = $product->books()
        ->where(
            'start_at',
            '<=',
            Carbon::parse(
                $request->input('date') . ' ' . $request->input('start_time')
            )->toDateTimeString()
        )
        ->where(
            'end_at',
            '>=',
            Carbon::parse(
                $request->input('date') . ' ' . $request->input('end_time')
            )->toDateTimeString()
        )
        ->count();

    // SQL Statement
    //
    //
    // DB::select(
    //     "SELECT * FROM product_books WHERE `product_id` = ? AND start_at <= ? AND end_at >= ?",
    //     [
    //         (int) $product->id,
    //         Carbon::parse($request->input('date') . ' ' . $request->input('start_time'))->toDateTimeString(),
    //         Carbon::parse($request->input('date') . ' ' . $request->input('end_time'))->toDateTimeString()
    //     ]
    // );

    if ($books > 0) {
        return response()->json([
            'available' => 0
        ], 200);
    }

    $newBook = new ProductBook([
        'start_at' => Carbon::parse($request->input('date') . ' ' . $request->input('start_time'))->toDateTimeString(),
        'end_at' => Carbon::parse($request->input('date') . ' ' . $request->input('end_time'))->toDateTimeString()
    ]);

    $product->books()->save($newBook);

    return response()->json([
        'available' => 1
    ], 200);
})->name('schedule.validate');

Route::get('/schedule/{product}', function (Request $request, Product $product) {
    $request->validate([
        'month' => 'required|digits:2|between:1,12',
    ]);

    return $product
        ->schedules()
        ->whereMonth('day_date', '=', $request->input('month'))
        ->get();
})->name('schedule');
