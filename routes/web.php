<?php

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Security\RolePermission;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManageStaffController;
use App\Http\Controllers\InventoryController; // Add this line
use App\Http\Controllers\SaleController; // Add this line
use App\Http\Controllers\NotificationController; // Add this line
use Illuminate\Support\Facades\Artisan;
// Packages
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

require __DIR__ . '/auth.php';

Route::get('/storage', function () {
    Artisan::call('storage:link');
});

//UI Pages Routs
Route::get('/', [HomeController::class, 'uisheet'])->name('uisheet');
Route::resource('users', UserController::class);

// Common routes (if applicable)
Route::group(['middleware' => ['auth']], function () {
    // Sale routes (shared between Manager and Staff)
    Route::post('/createSale', [SaleController::class, 'createSale'])->name('createSale');
    Route::post('/createProduct', [InventoryController::class, 'createProduct'])->name('createProduct');
    Route::post('/updateProduct/{id}', [InventoryController::class, 'updateProduct'])->name('updateProduct');
    Route::get('/sale/recordSale', [SaleController::class, 'recordSale'])->name('recordSale');
    Route::get('/get-batches', [SaleController::class, 'getBatches'])->name('getBatches');
    Route::get('/sale/viewSales', [SaleController::class, 'viewSales'])->name('viewSales');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('mark-as-viewed', [NotificationController::class, 'markAsViewed'])->name('markAsViewed');

    // Inventory Routes
    Route::get('/inventory/addProduct', [InventoryController::class, 'addProduct'])->name('addProduct');
    Route::get('/inventory/viewProducts', [InventoryController::class, 'viewProducts'])->name('viewProducts');
    Route::get('/inventory/deleteProductView', [InventoryController::class, 'deleteProductView'])->name('deleteProductView');

    Route::get('/inventory/trackInventory', [InventoryController::class, 'trackInventory'])->name('trackInventory');

    Route::post('/deleteBatch/{batch_id?}', [InventoryController::class, 'deleteBatch'])->name('deleteBatch');
    Route::post('/deleteProduct', [InventoryController::class, 'deleteProduct'])->name('deleteProduct');
    Route::get('/report/generate', [ReportController::class, 'generateReport'])->name('generateReport');
    Route::post('/report', [SaleController::class, 'report'])->name('report');
    Route::get('/products', [InventoryController::class, 'products'])->name('products');
    Route::get('/track', [InventoryController::class, 'track'])->name('track');
    Route::get('/inventory/viewInventoryBatches', [InventoryController::class, 'viewInventoryBatches'])->name('viewInventoryBatches');
    Route::get('/batches', [InventoryController::class, 'batches'])->name('batches');
    Route::get('/inventory/editBatch/{id?}', [InventoryController::class, 'editBatch'])->name('editBatch');
    Route::get('/inventory/addBatch', [InventoryController::class, 'addBatch'])->name('addBatch');
    Route::post('/createBatch', [InventoryController::class, 'createBatch'])->name('createBatch');
    Route::post('/updateBatch', [InventoryController::class, 'updateBatch'])->name('updateBatch');
    Route::get('/inventory/editProduct/{id?}', [InventoryController::class, 'editProduct'])->name('editProduct');
    Route::post('/inventory/updateProduct', [InventoryController::class, 'updateProduct'])->name('updateProduct');

    Route::get('/total-sales', [ManageStaffController::class, 'getTotalSales']);


    Route::get('/inventory/refund/{id?}', [InventoryController::class, 'refundView'])->name('refundView');
    Route::post('/refund', [InventoryController::class, 'refund'])->name('refund');

    Route::get('/inventory/viewExpiredBatches', [InventoryController::class, 'viewExpiredBatches'])->name('viewExpiredBatches');
    Route::get('/inventory/expiredBatches', [InventoryController::class, 'expiredBatches'])->name('expiredBatches');

    Route::get('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');
});

// Manager routes
Route::group(['middleware' => ['auth', 'role:Manager']], function () {
    // Permission Module
    Route::get('/role-permission', [RolePermission::class, 'index'])->name('role.permission.list');
    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);

    // Dashboard Routes
    Route::get('/admin/dashboard', [HomeController::class, 'index'])->name('manager.dashboard');

    // Staff Management
    Route::get('/admin/staff/addStaff', [ManageStaffController::class, 'addStaff'])->name('addStaff');
    Route::post('/createStaff', [ManageStaffController::class, 'createStaff'])->name('createStaff');
    Route::get('/admin/staff/viewStaff', [ManageStaffController::class, 'viewStaff'])->name('viewStaff');
    Route::get('/admin/staff/editStaff/{id?}', [ManageStaffController::class, 'addStaff'])->name('editStaff');
    Route::post('/updateStaff/{id}', [ManageStaffController::class, 'updateStaff'])->name('updateStaff');


    // Sale and Report Management




    // Sales Insights
    Route::get('/sale/count/{period?}', [SaleController::class, 'saleCount'])->name('sales.count');
    Route::get('/sale/revenue/{period?}', [SaleController::class, 'revenue'])->name('sales.revenue');
    Route::get('/sale/profit/{period?}', [SaleController::class, 'profit'])->name('sales.profit');
    Route::get('/sale/cost/{period?}', [SaleController::class, 'cost'])->name('sales.cost');
    Route::get('/sale/net-profit/{period?}', [SaleController::class, 'netProfit'])->name('inventory.net.profit');
    Route::get('/sale/gross/{period?}', [SaleController::class, 'gross'])->name('sales.gross');
    Route::get('/sale/top/{period?}', [SaleController::class, 'topProducts'])->name('sales.top');
    Route::get('/sale/trends/{period?}', [SaleController::class, 'trends'])->name('sales.trends');
    Route::get('/sale/predict/{period?}', [SaleController::class, 'predict'])->name('sales.predict');
    Route::get('/sale/stock', [SaleController::class, 'stock'])->name('sales.stock');
    Route::get('/sale/report', [SaleController::class, 'salesReport'])->name('sales.report');
    // Inventory Editing

});

// Staff routes
Route::group(['middleware' => ['auth', 'role:Staff']], function () {
    Route::get('/staff/dashboard', [HomeController::class, 'staffIndex'])->name('staff.dashboard');

    // Sale Routes

});















//App Details Page => 'Dashboard'], function() {
Route::group(['prefix' => 'menu-style'], function () {
    //MenuStyle Page Routs
    Route::get('horizontal', [HomeController::class, 'horizontal'])->name('menu-style.horizontal');
    Route::get('dual-horizontal', [HomeController::class, 'dualhorizontal'])->name('menu-style.dualhorizontal');
    Route::get('dual-compact', [HomeController::class, 'dualcompact'])->name('menu-style.dualcompact');
    Route::get('boxed', [HomeController::class, 'boxed'])->name('menu-style.boxed');
    Route::get('boxed-fancy', [HomeController::class, 'boxedfancy'])->name('menu-style.boxedfancy');
});

//App Details Page => 'special-pages'], function() {
Route::group(['prefix' => 'special-pages'], function () {
    //Example Page Routs
    Route::get('billing', [HomeController::class, 'billing'])->name('special-pages.billing');
    Route::get('calender', [HomeController::class, 'calender'])->name('special-pages.calender');
    Route::get('kanban', [HomeController::class, 'kanban'])->name('special-pages.kanban');
    Route::get('pricing', [HomeController::class, 'pricing'])->name('special-pages.pricing');
    Route::get('rtl-support', [HomeController::class, 'rtlsupport'])->name('special-pages.rtlsupport');
    Route::get('timeline', [HomeController::class, 'timeline'])->name('special-pages.timeline');
});

//Widget Routs
Route::group(['prefix' => 'widget'], function () {
    Route::get('widget-basic', [HomeController::class, 'widgetbasic'])->name('widget.widgetbasic');
    Route::get('widget-chart', [HomeController::class, 'widgetchart'])->name('widget.widgetchart');
    Route::get('widget-card', [HomeController::class, 'widgetcard'])->name('widget.widgetcard');
});

//Maps Routs
Route::group(['prefix' => 'maps'], function () {
    Route::get('google', [HomeController::class, 'google'])->name('maps.google');
    Route::get('vector', [HomeController::class, 'vector'])->name('maps.vector');
});

//Auth pages Routs
Route::group(['prefix' => 'auth'], function () {
    Route::get('signin', [HomeController::class, 'signin'])->name('auth.signin');
    Route::get('signup', [HomeController::class, 'signup'])->name('auth.signup');
    Route::get('confirmmail', [HomeController::class, 'confirmmail'])->name('auth.confirmmail');
    Route::get('lockscreen', [HomeController::class, 'lockscreen'])->name('auth.lockscreen');
    Route::get('recoverpw', [HomeController::class, 'recoverpw'])->name('auth.recoverpw');
    Route::get('userprivacysetting', [HomeController::class, 'userprivacysetting'])->name('auth.userprivacysetting');
});

//Error Page Route
Route::group(['prefix' => 'errors'], function () {
    Route::get('error404', [HomeController::class, 'error404'])->name('errors.error404');
    Route::get('error500', [HomeController::class, 'error500'])->name('errors.error500');
    Route::get('maintenance', [HomeController::class, 'maintenance'])->name('errors.maintenance');
});


//Forms Pages Routs
Route::group(['prefix' => 'forms'], function () {
    Route::get('element', [HomeController::class, 'element'])->name('forms.element');
    Route::get('wizard', [HomeController::class, 'wizard'])->name('forms.wizard');
    Route::get('validation', [HomeController::class, 'validation'])->name('forms.validation');
});


//Table Page Routs
Route::group(['prefix' => 'table'], function () {
    Route::get('bootstraptable', [HomeController::class, 'bootstraptable'])->name('table.bootstraptable');
    Route::get('datatable', [HomeController::class, 'datatable'])->name('table.datatable');
});

//Icons Page Routs
Route::group(['prefix' => 'icons'], function () {
    Route::get('solid', [HomeController::class, 'solid'])->name('icons.solid');
    Route::get('outline', [HomeController::class, 'outline'])->name('icons.outline');
    Route::get('dualtone', [HomeController::class, 'dualtone'])->name('icons.dualtone');
    Route::get('colored', [HomeController::class, 'colored'])->name('icons.colored');
});
//Extra Page Routs
Route::get('privacy-policy', [HomeController::class, 'privacypolicy'])->name('pages.privacy-policy');
Route::get('terms-of-use', [HomeController::class, 'termsofuse'])->name('pages.term-of-use');
