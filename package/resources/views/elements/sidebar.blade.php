<div class="dlabnav">
	<div class="dlabnav-scroll">
		<ul class="metismenu" id="menu">
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fas fa-home"></i>
					<span class="nav-text">Dashboard</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('index') }}">Dashboard Light</a></li>
					<li><a href="{{ url('index-2') }}">Dashboard Dark</a></li>
					<li><a href="{{ url('project-page') }}">Project</a></li>
					<li><a href="{{ url('contacts') }}">Contacts</a></li>
					<li><a href="{{ url('kanban') }}">Kanban</a></li>
					<li><a href="{{ url('calendar-page') }}">Calendar</a></li>
					<li><a href="{{ url('message') }}">Messages</a></li>	
				</ul>

			</li>
			<li>
				<a class="has-arrow" href="javascript:void(0);" aria-expanded="false">
					<i class="fas fa-chart-line"></i>
					<span class="nav-text">CMS</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('content') }}">Content</a></li>
					<li><a href="{{ url('content-add') }}"> Add Content</a></li>
					<li><a href="{{ url('menu-1') }}">Menus</a></li>
					<li><a href="{{ url('email-template') }}">Email Template</a></li>		
					<li><a href="{{ url('add-email') }}">Add Email</a></li>		
					<li><a href="{{ url('blog') }}">Blog</a></li>	
					<li><a href="{{ url('add-blog') }}">Add Blog</a></li>
					<li><a href="{{ url('blog-category') }}">Blog Category</a></li>	
				</ul>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
				<i class="fas fa-info-circle"></i>
					<span class="nav-text">Apps</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('app-profile') }}">Profile</a></li>
					<li><a href="{{ url('edit-profile') }}">Edit Profile</a></li>
					<li><a href="{{ url('post-details') }}">Post Details</a></li>
					<li><a class="has-arrow" href="javascript:void()" aria-expanded="false">Email</a>
						<ul aria-expanded="false">
							<li><a href="{{ url('email-compose') }}">Compose</a></li>
							<li><a href="{{ url('email-inbox') }}">Inbox</a></li>
							<li><a href="{{ url('email-read') }}">Read</a></li>
						</ul>
					</li>
					<li><a href="{{ url('app-calendar') }}">Calendar</a></li>
					<li><a class="has-arrow" href="javascript:void()" aria-expanded="false">Shop</a>
						<ul aria-expanded="false">
							<li><a href="{{ url('ecom-product-grid') }}">Product Grid</a></li>
							<li><a href="{{ url('ecom-product-list') }}">Product List</a></li>
							<li><a href="{{ url('ecom-product-detail') }}">Product Details</a></li>
							<li><a href="{{ url('ecom-product-order') }}">Order</a></li>
							<li><a href="{{ url('ecom-checkout') }}">Checkout</a></li>
							<li><a href="{{ url('ecom-invoice') }}">Invoice</a></li>
							<li><a href="{{ url('ecom-customers') }}">Customers</a></li>
						</ul>
					</li>
				</ul>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fas fa-chart-line"></i>
					<span class="nav-text">Charts</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('chart-flot') }}">Flot</a></li>
					<li><a href="{{ url('chart-morris') }}">Morris</a></li>
					<li><a href="{{ url('chart-chartjs') }}">Chartjs</a></li>
					<li><a href="{{ url('chart-chartist') }}">Chartist</a></li>
					<li><a href="{{ url('chart-sparkline') }}">Sparkline</a></li>
					<li><a href="{{ url('chart-peity') }}">Peity</a></li>
				</ul>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fab fa-bootstrap"></i>
					<span class="nav-text">Bootstrap</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{url('ui-accordion')}}">Accordion</a></li>
					<li><a href="{{url('ui-alert')}}">Alert</a></li>
					<li><a href="{{url('ui-badge')}}">Badge</a></li>
                    <li><a href="{{url('ui-button')}}">Button</a></li>
                    <li><a href="{{url('ui-modal')}}">Modal</a></li>
					<li><a href="{{url('ui-button-group')}}">Button Group</a></li>
					<li><a href="{{url('ui-list-group')}}">List Group</a></li>
					<li><a href="{{url('ui-card')}}">Cards</a></li>
					<li><a href="{{url('ui-carousel')}}">Carousel</a></li>
					<li><a href="{{url('ui-dropdown')}}">Dropdown</a></li>
					<li><a href="{{url('ui-popover')}}">Popover</a></li>
					<li><a href="{{url('ui-progressbar')}}">Progressbar</a></li>
					<li><a href="{{url('ui-tab')}}">Tab</a></li>
					<li><a href="{{url('ui-typography')}}">Typography</a></li>
					<li><a href="{{url('ui-pagination')}}">Pagination</a></li>
					<li><a href="{{url('ui-grid')}}">Grid</a></li>

				</ul>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fas fa-heart"></i>
					<span class="nav-text">Plugins</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('uc-select2') }}">Select 2</a></li>
                    <li><a href="{{ url('uc-nestable') }}">Nestedable</a></li>
                    <li><a href="{{ url('uc-noui-slider') }}">Noui Slider</a></li>
                    <li><a href="{{ url('uc-sweetalert') }}">Sweet Alert</a></li>
                    <li><a href="{{ url('uc-toastr') }}">Toastr</a></li>
                    <li><a href="{{ url('map-jqvmap') }}">Jqv Map</a></li>
                    <li><a href="{{ url('uc-lightgallery') }}">Light Gallery</a></li>
				</ul>
			</li>
			<li><a href="{{url('widget-basic')}}" aria-expanded="false">
					<i class="fas fa-user"></i>
					<span class="nav-text">Widget</span>
				</a>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fas fa-file-alt"></i>
					<span class="nav-text">Forms</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('form-element')}}">Form Elements</a></li>
                    <li><a href="{{ url('form-wizard')}}">Wizard</a></li>
					<li><a href="{{ url('form-ckeditor') }}">CkEditor</a></li>
					<li><a href="{{ url('form-pickers')}}">Pickers</a></li>
                    <li><a href="{{ url('form-validation')}}">Form Validate</a></li>
				</ul>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fas fa-table"></i>
					<span class="nav-text">Table</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('table-bootstrap-basic')}}">Bootstrap</a></li>
                    <li><a href="{{ url('table-datatable-basic')}}">Datatable</a></li>
				</ul>
			</li>
			<li><a class="has-arrow " href="javascript:void()" aria-expanded="false">
					<i class="fas fa-clone"></i>
					<span class="nav-text">Pages</span>
				</a>
				<ul aria-expanded="false">
					<li><a href="{{ url('page-login') }}">Login</a></li>
					<li><a href="{{ url('page-register') }}">Register</a></li>
					<li><a class="has-arrow" href="javascript:void()" aria-expanded="false">Error</a>
						<ul aria-expanded="false">
							<li><a href="{{ url('page-error-400') }}">Error 400</a></li>
                            <li><a href="{{ url('page-error-403') }}">Error 403</a></li>
                            <li><a href="{{ url('page-error-404') }}">Error 404</a></li>
                            <li><a href="{{ url('page-error-500') }}">Error 500</a></li>
                            <li><a href="{{ url('page-error-503') }}">Error 503</a></li>
						</ul>
					</li>
					<li><a href="{{ url('page-lock-screen') }}">Lock Screen</a></li>
                    <li><a href="{{ url('empty-page') }}">Empty Page</a></li>
				</ul>
			</li>
		</ul>
		<div class="side-bar-profile">
			<div class="d-flex align-items-center justify-content-between mb-3">
				<div class="side-bar-profile-img">
					<img src="{{ asset('images/user.jpg') }}" alt="">
				</div>
				<div class="profile-info1">
					<h4 class="fs-18 font-w500">Levi Siregar</h4>
					<span>leviregar@mail.com</span>
				</div>
				<div class="profile-button">
					<i class="fas fa-caret-downd scale5 text-light"></i>
				</div>
			</div>	
			<div class="d-flex justify-content-between mb-2 progress-info">
				<span class="fs-12"><i class="fas fa-star text-orange me-2"></i>Task Progress</span>
				<span class="fs-12">20/45</span>
			</div>
			<div class="progress default-progress">
				<div class="progress-bar bg-gradientf progress-animated" style="width: 45%; height:10px;" role="progressbar">
					<span class="sr-only">45% Complete</span>
				</div>
			</div>
		</div>
		
		<div class="copyright">
			<p><strong>Fillow Saas Admin</strong> © 2024 All Rights Reserved</p>
			<p class="fs-12">Made with <span class="heart"></span> by DexignLab</p>
		</div>
	</div>
</div>