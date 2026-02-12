<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FillowAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::controller(FillowAdminController::class)->group(function () {
    Route::get('/', 'dashboard');
    Route::get('/index', 'dashboard')->name('dashboard');
    Route::get('/index-2','dashboard_2')->name('dashboard_2');
    Route::get('/reporting-dashboard','reporting_dashboard')->name('reporting_dashboard');
    Route::get('/project-page','project_page')->name('project_page');
	Route::get('/contacts', 'contacts')->name('contacts');
    Route::get('/kanban', 'kanban')->name('kanban');
	Route::get('/calendar-page', 'calendar_page')->name('calendar_page');
	Route::get('/message', 'message')->name('message');
	Route::get('/add-blog', 'add_blog')->name('add_blog');
	Route::get('/add-email', 'add_email')->name('add_email');
    Route::get('/app-calendar', 'app_calendar')->name('app_calendar');
    Route::get('/app-profile', 'app_profile')->name('app_profile');
    Route::get('/blog', 'blog')->name('blog');
    Route::get('/blog-category', 'blog_category')->name('blog_category');
    Route::get('/chart-chartist', 'chart_chartist')->name('chart_chartist');
    Route::get('/chart-chartjs', 'chart_chartjs')->name('chart_chartjs');
    Route::get('/chart-flot', 'chart_flot')->name('chart_flot');
    Route::get('/chart-morris', 'chart_morris')->name('chart_morris');
    Route::get('/chart-peity', 'chart_peity')->name('chart_peity');
    Route::get('/chart-sparkline', 'chart_sparkline')->name('chart_sparkline');
    Route::get('/content', 'content')->name('content');
    Route::get('/content-add', 'content_add')->name('content_add');
    Route::get('/ecom-checkout', 'ecom_checkout')->name('ecom_checkout');
    Route::get('/ecom-customers', 'ecom_customers')->name('ecom_customers');
    Route::get('/ecom-invoice', 'ecom_invoice')->name('ecom_invoice');
    Route::get('/ecom-product-detail', 'ecom_product_detail')->name('ecom_product_detail');
    Route::get('/ecom-product-grid', 'ecom_product_grid')->name('ecom_product_grid');
    Route::get('/ecom-product-list', 'ecom_product_list')->name('ecom_product_list');
    Route::get('/ecom-product-order', 'ecom_product_order')->name('ecom_product_order');
    Route::get('/edit-profile', 'edit_profile')->name('edit_profile');
    Route::match(['get','post'],'/email-compose','email_compose')->name('email_compose');
    Route::get('/email-inbox', 'email_inbox')->name('email_inbox');
    Route::get('/email-read', 'email_read')->name('email_read');
    Route::get('/email-template', 'email_template')->name('email_template');
    Route::get('/form-ckeditor', 'form_ckeditor')->name('form_ckeditor');
    Route::get('/form-element', 'form_element')->name('form_element');
    Route::get('/form-pickers', 'form_pickers')->name('form_pickers');
    Route::get('/form-validation', 'form_validation')->name('form_validation');
    Route::get('/form-wizard', 'form_wizard')->name('form_wizard');
    Route::get('/map-jqvmap', 'map_jqvmap')->name('map_jqvmap');
    Route::get('/menu-1', 'menu_1')->name('menu_1');
    Route::get('/page-error-400', 'page_error_400')->name('page_error_400');
    Route::get('/page-error-403', 'page_error_403')->name('page_error_403');
    Route::get('/page-error-404', 'page_error_404')->name('page_error_404');
    Route::get('/page-error-500', 'page_error_500')->name('page_error_500');
    Route::get('/page-error-503', 'page_error_503')->name('page_error_503');
    Route::get('/page-forgot-password', 'page_forgot_password')->name('page_forgot_password');
    Route::get('/page-lock-screen', 'page_lock_screen')->name('page_lock_screen');
    Route::get('/page-login', 'page_login')->name('page_login');
    Route::get('/page-register', 'page_register')->name('page_register');
    Route::match(['get','post'],'/post-details','post_details')->name('post_details');
    Route::get('/table-bootstrap-basic', 'table_bootstrap_basic')->name('table_bootstrap_basic');
    Route::get('/table-datatable-basic', 'table_datatable_basic')->name('table_datatable_basic');
    Route::get('/uc-lightgallery', 'uc_lightgallery')->name('uc_lightgallery');
    Route::get('/uc-nestable', 'uc_nestable')->name('uc_nestable');
    Route::get('/uc-noui-slider', 'uc_noui_slider')->name('uc_noui_slider');
    Route::get('/uc-select2', 'uc_select2')->name('uc_select2');
    Route::get('/uc-sweetalert', 'uc_sweetalert')->name('uc_sweetalert');
    Route::get('/uc-toastr', 'uc_toastr')->name('uc_toastr');
    Route::get('/ui-accordion', 'ui_accordion')->name('ui_accordion');
    Route::get('/ui-alert', 'ui_alert')->name('ui_alert');
    Route::get('/ui-badge', 'ui_badge')->name('ui_badge');
    Route::get('/ui-button', 'ui_button')->name('ui_button');
    Route::get('/ui-button-group', 'ui_button_group')->name('ui_button_group');
    Route::get('/ui-card', 'ui_card')->name('ui_card');
    Route::get('/ui-carousel', 'ui_carousel')->name('ui_carousel');
    Route::get('/ui-dropdown', 'ui_dropdown')->name('ui_dropdown');
    Route::get('/ui-grid', 'ui_grid')->name('ui_grid');
    Route::get('/ui-list-group', 'ui_list_group')->name('ui_list_group');
    Route::get('/ui-modal', 'ui_modal')->name('ui_modal');
    Route::get('/ui-pagination', 'ui_pagination')->name('ui_pagination');
    Route::get('/ui-popover', 'ui_popover')->name('ui_popover');
    Route::get('/ui-progressbar', 'ui_progressbar')->name('ui_progressbar');
    Route::get('/ui-tab', 'ui_tab')->name('ui_tab');
    Route::get('/ui-typography', 'ui_typography')->name('ui_typography');
    Route::get('/widget-basic', 'widget_basic')->name('widget_basic');
    Route::get('/empty-page', 'empty_page')->name('empty_page');

    
    Route::post('/ajax/seller-menus', 'ajax_seller_menus')->name('ajax_seller_menus');
    Route::post('/ajax/order-request', 'ajax_order_request')->name('ajax_order_request');
});

// Authentication & Account Management Routes
require __DIR__ . '/auth.php';

// Supplier Management Routes
require __DIR__ . '/supplier-management.php';

// Routing Rules Routes
require __DIR__ . '/routing-rules.php';
