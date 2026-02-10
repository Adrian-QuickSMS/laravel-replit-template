<?php

return [

        /*
        |--------------------------------------------------------------------------
        | Application Name
        |--------------------------------------------------------------------------
        |
        | This value is the name of your application. This value is used when the
        | framework needs to place the application's name in a notification or
        | any other location as required by the application or its packages.
        |
        */

        'name' => env('APP_NAME', 'QuickSMS'),

        'public' => [
                'global' => [
                        'css' => [
                                                'vendor/bootstrap-select/css/bootstrap-select.min.css',
                                                'css/style.css',
                        ],
                        'js' => [
                                'top'=> [
                                        'vendor/global/global.min.js',
                                        'vendor/bootstrap-select/js/bootstrap-select.min.js',
                                ],
                                'bottom'=> [
                                        'js/custom.min.js',
                                        'js/dlabnav-init.js',
                                ],
                        ],
                ],
                'pagelevel' => [
                        'css' => [
                                'FillowAdminController_dashboard' => [
                                        'vendor/owl-carousel/owl.carousel.css'
                                ],
                                'FillowAdminController_dashboard_2' => [
                                        'vendor/owl-carousel/owl.carousel.css'
                                ],
                                'FillowAdminController_orders_list' => [
                                        'vendor/datatables/css/jquery.dataTables.min.css',
                                ],
                                
                                'FillowAdminController_kanban' => [
                                ],
                                'FillowAdminController_calendar_page' => [
                                        'vendor/fullcalendar/css/main.min.css'
                                ],
                                
                                'FillowAdminController_analytics' => [
                                        'vendor/chartist/css/chartist.min.css',
                                ],
                                'FillowAdminController_customer_list' => [
                                        'vendor/datatables/css/jquery.dataTables.min.css',
                                ],
                                'FillowAdminController_content_add' => [
                                        'vendor/select2/css/select2.min.css'
                                ],
                                'FillowAdminController_email_template' => [
                                        'vendor/select2/css/select2.min.css',

                                ],
                                'FillowAdminController_add_email' => [
                                        'vendor/datatables/css/jquery.dataTables.min.css',
                                        'vendor/select2/css/select2.min.css',
                                ],
                                'FillowAdminController_menu_1' => [
                                        'vendor/select2/css/select2.min.css',
                                        'vendor/nestable2/css/jquery.nestable.min.css',
                                        
                                ],
                                'FillowAdminController_app_calendar' => [
                                        'vendor/fullcalendar/css/main.min.css'
                                ],
                                'FillowAdminController_app_profile' => [
                                        'vendor/lightgallery/dist/css/lightgallery.css',
                                        'vendor/lightgallery/dist/css/lg-thumbnail.css',
                                        'vendor/lightgallery/dist/css/lg-zoom.css',
                                        
                                ],

                                'FillowAdminController_edit_profile' => [
                                        'vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css',
                                ],
                                
                                'FillowAdminController_post_details' => [
                                        'vendor/lightgallery/dist/css/lightgallery.css',
                                        'vendor/lightgallery/dist/css/lg-thumbnail.css',
                                        'vendor/lightgallery/dist/css/lg-zoom.css',
                                ],
                                'FillowAdminController_add_blog' => [
                                        'vendor/select2/css/select2.min.css',
                                        'vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css',
                                ],
                                'FillowAdminController_blog_category' => [
                                        'vendor/datatables/css/jquery.dataTables.min.css',
                                        
                                ],

                                'FillowAdminController_chart_chartist' => [
                                        'vendor/chartist/css/chartist.min.css'
                                ],
                                'FillowAdminController_chart_chartjs' => [
                                ],
                                'FillowAdminController_chart_flot' => [
                                ],
                                'FillowAdminController_chart_morris' => [
                                ],
                                'FillowAdminController_chart_peity' => [
                                ],
                                'FillowAdminController_chart_sparkline' => [
                                ],
                                'FillowAdminController_ecom_checkout' => [
                                ],
                                'FillowAdminController_ecom_customers' => [
                                ],
                                'FillowAdminController_ecom_invoice' => [
                                        'vendor/bootstrap-select/dist/css/bootstrap-select.min.css'
                                ],
                                'FillowAdminController_ecom_product_detail' => [
                                        'vendor/star-rating/star-rating-svg.css',
                                ],
                                'FillowAdminController_ecom_product_grid' => [
                                ],
                                'FillowAdminController_ecom_product_list' => [
                                        'vendor/star-rating/star-rating-svg.css'
                                ],
                                'FillowAdminController_ecom_product_order' => [
                                ],
                                'FillowAdminController_email_compose' => [
                                        'vendor/dropzone/dist/dropzone.css'
                                ],
                                'FillowAdminController_email_inbox' => [
                                        
                                ],
                                'FillowAdminController_email_read' => [
                                        'vendor/jqueryui/css/jquery-ui.min.css'
                                ],
                                'FillowAdminController_form_ckeditor' => [
                                ],
                                'FillowAdminController_form_element' => [
                                ],
                                'FillowAdminController_form_pickers' => [
                                        'vendor/bootstrap-daterangepicker/daterangepicker.css',
                                        'vendor/clockpicker/css/bootstrap-clockpicker.min.css',
                                        'vendor/jquery-asColorPicker/css/asColorPicker.min.css',
                                        'vendor/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css',
                                        'vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css',
                                        'vendor/pickadate/themes/default.css',
                                        'vendor/pickadate/themes/default.date.css',
                                        'https://fonts.googleapis.com/icon?family=Material+Icons',
                                ],
                                

                                'FillowAdminController_blog' => [
                                        'vendor/select2/css/select2.min.css',
                                        'vendor/bootstrap-datepicker-master/css/bootstrap-datepicker.min.css',
                                ],
                                'FillowAdminController_content' => [
                                        'vendor/select2/css/select2.min.css',
                                        
                                        
                                ],
                                'FillowAdminController_form_validation_jquery' => [
                                ],
                                'FillowAdminController_form_wizard' => [
                                        'vendor/jquery-smartwizard/dist/css/smart_wizard.min.css',
                                ],
                                'FillowAdminController_map_jqvmap' => [
                                        'vendor/jqvmap/css/jqvmap.min.css'
                                ],
                                'FillowAdminController_page_error_400' => [
                                        'vendor/bootstrap-select/dist/css/bootstrap-select.min.css'
                                ],
                                'FillowAdminController_table_bootstrap_basic' => [
                                ],
                                'FillowAdminController_table_datatable_basic' => [
                                        'vendor/datatables/css/jquery.dataTables.min.css',
                                        'vendor/datatables/responsive/responsive.css',
                                ],
                                'FillowAdminController_uc_lightgallery' => [
                                        'vendor/lightgallery/dist/css/lightgallery.css',
                                        'vendor/lightgallery/dist/css/lg-thumbnail.css',
                                        'vendor/lightgallery/dist/css/lg-zoom.css',
                                        
                                ],
                                'FillowAdminController_uc_nestable' => [
                                        'vendor/nestable2/css/jquery.nestable.min.css'
                                ],
                                'FillowAdminController_uc_noui_slider' => [
                                        'vendor/nouislider/nouislider.min.css'
                                ],
                                'FillowAdminController_uc_select2' => [
                                        'vendor/select2/css/select2.min.css'
                                ],
                                'FillowAdminController_uc_sweetalert' => [
                                        'vendor/sweetalert2/sweetalert2.min.css'
                                ],
                                'FillowAdminController_uc_toastr' => [
                                        'vendor/toastr/css/toastr.min.css'
                                ],
                                'FillowAdminController_form_editor_summernote' => [
                                        'vendor/summernote/summernote.css',
                                ],
                                'FillowAdminController_ui_accordion' => [
                                ],
                                'FillowAdminController_ui_alert' => [
                                ],
                                'FillowAdminController_ui_badge' => [
                                ],
                                'FillowAdminController_ui_button' => [
                                ],
                                'FillowAdminController_ui_button_group' => [
                                ],
                                'FillowAdminController_ui_card' => [
                                ],
                                'FillowAdminController_ui_carousel' => [
                                ],
                                'FillowAdminController_ui_dropdown' => [
                                ],
                                'FillowAdminController_ui_grid' => [
                                ],
                                'FillowAdminController_ui_list_group' => [
                                ],
                                'FillowAdminController_ui_media_object' => [
                                ],
                                'FillowAdminController_ui_modal' => [
                                ],
                                'FillowAdminController_ui_pagination' => [
                                ],
                                'FillowAdminController_ui_popover' => [
                                ],
                                'FillowAdminController_ui_progressbar' => [
                                ],
                                'FillowAdminController_ui_tab' => [
                                ],
                                'FillowAdminController_ui_typography' => [
                                ],
                                'FillowAdminController_widget_basic' => [
                                        'vendor/chartist/css/chartist.min.css',
                                        'vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
                                ],
                        ],
                        'js' => [
                                'FillowAdminController_dashboard' => [
                                        'vendor/counter/counter.min.js',
                                        'vendor/counter/waypoint.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'js/dashboard/dashboard-1.js',
                                        'vendor/owl-carousel/owl.carousel.js',
                                ],
                                'FillowAdminController_dashboard_2' => [
                                        'vendor/counter/counter.min.js',
                                        'vendor/counter/waypoint.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'js/dashboard/dashboard-1.js',
                                        'vendor/owl-carousel/owl.carousel.js',
                                ],
                                'FillowAdminController_kanban' => [
                                        'vendor/draggable/draggable.js',
                                        
                                ],
                                'FillowAdminController_calendar_page' => [
                                        'vendor/moment/moment.min.js',
                                        'vendor/fullcalendar/js/main.min.js',
                                        'js/plugins-init/calendar.js',
                                        
                                ],
                                'FillowAdminController_email_template' => [
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/select2-init.js',
                                ],
                                'FillowAdminController_content_add' => [
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                        'vendor/ckeditor/ckeditor.js',
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/select2-init.js',
                                ],
                                'FillowAdminController_content' => [
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/select2-init.js',
                                        
                                ],
                                'FillowAdminController_menu_1' => [
                                        'vendor/nestable2/js/jquery.nestable.min.js',
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/nestable-init.js',
                                        'js/plugins-init/select2-init.js',
                                ],
                                'FillowAdminController_add_email' => [
                                        'vendor/ckeditor/ckeditor.js',
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/select2-init.js',
                                        'vendor/datatables/js/jquery.dataTables.min.js',
                                        'js/plugins-init/datatables.init.js',
                                ],
                                'FillowAdminController_blog' => [
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/select2-init.js',
                                ],
                                'FillowAdminController_blog_category' => [
                                ],
                                'FillowAdminController_add_blog' => [
                                        'vendor/select2/js/select2.full.min.js',
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                        'vendor/ckeditor/ckeditor.js',
                                        'js/plugins-init/select2-init.js',
                                ],
                                'FillowAdminController_order_detail' => [
                                        'vendor/chart.js/Chart.bundle.min.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'js/plugins-init/piety-init.js',
                                        'js/dashboard/order-detail.js',
                                        'vendor/apexchart/apexchart.js',
                                ],
                                'FillowAdminController_analytics' => [
                                        'vendor/chart.js/Chart.bundle.min.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'js/dashboard/analytics.js',
                                ],
                                'FillowAdminController_customer_list' => [
                                        'vendor/datatables/js/jquery.dataTables.min.js',
                                        'js/plugins-init/datatables.init.js',
                                ],
                                'FillowAdminController_market_capital' => [
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'js/dashboard/market-capital.js',
                                        'vendor/datatables/js/jquery.dataTables.min.js',
                                        'js/plugins-init/datatables.init.js',
                                ],
                                'FillowAdminController_app_calendar' => [
                                        'vendor/moment/moment.min.js',
                                        'vendor/fullcalendar/js/main.min.js',
                                        'js/plugins-init/fullcalendar-init.js',
                                ],
                                'FillowAdminController_app_profile' => [
                                        'vendor/lightgallery/dist/lightgallery.min.js',
                                        'vendor/lightgallery/dist/plugins/thumbnail/lg-thumbnail.min.js',
                                        'vendor/lightgallery/dist/plugins/zoom/lg-zoom.min.js',
                                        
                                ],

                                'FillowAdminController_edit_profile' => [
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                ],
                                'FillowAdminController_post_details' => [
                                        'vendor/lightgallery/dist/lightgallery.min.js',
                                        'vendor/lightgallery/dist/plugins/thumbnail/lg-thumbnail.min.js',
                                        'vendor/lightgallery/dist/plugins/zoom/lg-zoom.min.js',
                                ],
                                'FillowAdminController_chart_chartist' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/chartist/js/chartist.min.js',
                                        'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
                                        'js/plugins-init/chartist-init.js',
                                ],
                                'FillowAdminController_chart_chartjs' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'js/plugins-init/chartjs-init.js',
                                ],
                                'FillowAdminController_chart_flot' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/flot/jquery.flot.js',
                                        'vendor/flot/jquery.flot.pie.js',
                                        'vendor/flot/jquery.flot.resize.js',
                                        'vendor/flot-spline/jquery.flot.spline.min.js',
                                        'js/plugins-init/flot-init.js',
                                        
                                ],
                                'FillowAdminController_chart_morris' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/raphael/raphael.min.js',
                                        'vendor/morris/morris.min.js',
                                        'js/plugins-init/morris-init.js',
                                ],
                                'FillowAdminController_chart_peity' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'js/plugins-init/piety-init.js',
                                ],
                                'FillowAdminController_chart_sparkline' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/jquery-sparkline/jquery.sparkline.min.js',
                                        'js/plugins-init/sparkline-init.js',
                                ],
                                'FillowAdminController_ecom_checkout' => [
                                ],
                                'FillowAdminController_ecom_customers' => [
                                ],
                                'FillowAdminController_ecom_invoice' => [

                                ],
                                'FillowAdminController_ecom_product_detail' => [
                                        'vendor/star-rating/jquery.star-rating-svg.js',
                                ],
                                'FillowAdminController_ecom_product_grid' => [
                                ],
                                'FillowAdminController_ecom_product_list' => [
                                        'vendor/star-rating/jquery.star-rating-svg.js'
                                ],
                                'FillowAdminController_ecom_product_order' => [
                                ],
                                'FillowAdminController_email_compose' => [
                                        'vendor/dropzone/dist/dropzone.js'
                                ],
                                'FillowAdminController_email_inbox' => [
                                        'vendor/dropzone/dist/dropzone.js',
                                ],
                                'FillowAdminController_email_read' => [
                                        'vendor/jqueryui/js/jquery-ui.min.js'
                                ],
                                'FillowAdminController_form_ckeditor' => [
                                        'vendor/ckeditor/ckeditor.js'
                                ],
                                'FillowAdminController_form_editor_summernote' => [
                                        'vendor/summernote/js/summernote.min.js',
                                        'js/plugins-init/summernote-init.js',
                                ],
                                'FillowAdminController_form_element' => [
                                ],
                                'FillowAdminController_form_pickers' => [
                                        'vendor/moment/moment.min.js',
                                        'vendor/bootstrap-daterangepicker/daterangepicker.js',
                                        'vendor/clockpicker/js/bootstrap-clockpicker.min.js',
                                        'vendor/jquery-asColor/jquery-asColor.min.js',
                                        'vendor/jquery-asGradient/jquery-asGradient.min.js',
                                        'vendor/jquery-asColorPicker/js/jquery-asColorPicker.min.js',
                                        'vendor/bootstrap-datepicker-master/js/bootstrap-datepicker.min.js',
                                        'vendor/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js',
                                        'vendor/pickadate/picker.js',
                                        'vendor/pickadate/picker.time.js',
                                        'vendor/pickadate/picker.date.js',
                                        'js/plugins-init/bs-daterange-picker-init.js',
                                        'js/plugins-init/clock-picker-init.js',
                                        'js/plugins-init/jquery-asColorPicker.init.js',
                                        'js/plugins-init/material-date-picker-init.js',
                                        'js/plugins-init/pickadate-init.js',
                                ],
                                'FillowAdminController_form_validation_jquery' => [
                                ],
                                'FillowAdminController_form_wizard' => [
                                        'vendor/jquery-steps/build/jquery.steps.min.js',
                                        'vendor/jquery-validation/jquery.validate.min.js',
                                        'js/plugins-init/jquery.validate-init.js',
                                        'vendor/jquery-smartwizard/dist/js/jquery.smartWizard.js',
                                ],
                                'FillowAdminController_map_jqvmap' => [
                                        'vendor/jqvmap/js/jquery.vmap.min.js',
                                        'vendor/jqvmap/js/jquery.vmap.world.js',
                                        'vendor/jqvmap/js/jquery.vmap.usa.js',
                                        'js/plugins-init/jqvmap-init.js',
                                ],
                                'FillowAdminController_page_error_400' => [
                                ],
                                'FillowAdminController_page_error_403' => [
                                ],
                                'FillowAdminController_page_error_404' => [
                                ],
                                'FillowAdminController_page_error_500' => [
                                ],
                                'FillowAdminController_page_error_503' => [
                                ],
                                'FillowAdminController_page_forgot_password' => [
                                ],
                                'FillowAdminController_page_lock_screen' => [
                                        'vendor/deznav/deznav.min.js'
                                ],
                                'FillowAdminController_page_login' => [
                                ],
                                'FillowAdminController_page_register' => [
                                ],
                                'FillowAdminController_table_bootstrap_basic' => [
                                        

                                ],
                                'FillowAdminController_table_datatable_basic' => [
                                        'vendor/datatables/js/jquery.dataTables.min.js',
                                        'vendor/datatables/responsive/responsive.js',
                                        'js/plugins-init/datatables.init.js',
                                        
                                ],
                                'FillowAdminController_uc_lightgallery' => [
                                        'vendor/lightgallery/dist/lightgallery.min.js',
                                        'vendor/lightgallery/dist/plugins/thumbnail/lg-thumbnail.min.js',
                                        'vendor/lightgallery/dist/plugins/zoom/lg-zoom.min.js',
                                ],
                                'FillowAdminController_uc_nestable' => [
                                        'vendor/nestable2/js/jquery.nestable.min.js',
                                        'js/plugins-init/nestable-init.js',
                                ],
                                'FillowAdminController_uc_noui_slider' => [
                                        'vendor/nouislider/nouislider.min.js',
                                        'vendor/wnumb/wNumb.js',
                                        'js/plugins-init/nouislider-init.js',
                                ],
                                'FillowAdminController_uc_select2' => [
                                        'vendor/select2/js/select2.full.min.js',
                                        'js/plugins-init/select2-init.js',
                                ],
                                'FillowAdminController_uc_sweetalert' => [
                                        'vendor/sweetalert2/sweetalert2.min.js',
                                        'js/plugins-init/sweetalert.init.js',
                                ],
                                'FillowAdminController_uc_toastr' => [
                                        'vendor/toastr/js/toastr.min.js',
                                        'js/plugins-init/toastr-init.js',
                                ],
                                'FillowAdminController_ui_accordion' => [
                                        
                                ],
                                'FillowAdminController_ui_alert' => [
                                        
                                ],
                                'FillowAdminController_ui_badge' => [
                                        
                                ],
                                'FillowAdminController_ui_button' => [
                                        
                                ],
                                'FillowAdminController_ui_button_group' => [
                                        
                                ],
                                'FillowAdminController_ui_card' => [
                                        
                                ],
                                'FillowAdminController_ui_carousel' => [
                                        
                                ],
                                'FillowAdminController_ui_dropdown' => [
                                        
                                ],
                                'FillowAdminController_ui_grid' => [
                                ],
                                'FillowAdminController_ui_list_group' => [
                                ],
                                'FillowAdminController_ui_media_object' => [
                                ],
                                'FillowAdminController_ui_modal' => [
                                ],
                                'FillowAdminController_ui_pagination' => [
                                        
                                ],
                                'FillowAdminController_ui_popover' => [
                                ],
                                'FillowAdminController_ui_progressbar' => [
                                        
                                ],
                                'FillowAdminController_ui_tab' => [
                                        
                                ],
                                'FillowAdminController_ui_typography' => [
                                ],
                                'FillowAdminController_widget_basic' => [
                                        'vendor/chart-js/chart.bundle.min.js',
                                        'vendor/apexchart/apexchart.js',
                                        'vendor/counter/counter.min.js',
                                        'vendor/counter/waypoint.min.js',
                                        'vendor/chartist/js/chartist.min.js',
                                        'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
                                        'vendor/flot/jquery.flot.js',
                                        'vendor/flot/jquery.flot.pie.js',
                                        'vendor/flot/jquery.flot.resize.js',
                                        'vendor/flot-spline/jquery.flot.spline.min.js',
                                        'vendor/jquery-sparkline/jquery.sparkline.min.js',
                                        'js/plugins-init/sparkline-init.js',
                                        'vendor/peity/jquery.peity.min.js',
                                        'js/plugins-init/piety-init.js',
                                        'js/plugins-init/widgets-script-init.js',
                                        'vendor/bootstrap-select/js/bootstrap-select.min.js',
                                        

                                ],
                        ]
                ],
        ]
];
