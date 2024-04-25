<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>

        <!-- Jquery -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
        {{-- <script src="{{ asset('js/Jquery.js') }}" defer></script> --}}

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">        

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <marquee behavior="" height="100" width="200" direction="left">Welcome to my Store.</marquee>
                </div>
            </div>
            <div class="row">
                <div class="col"> 
                    <nav class="navbar navbar-expand-md bg-light navbar-light  fixed-top px-5">
                        <a class="navbar-brand" href="#"id="nav_heading"><span class="cheeky">Cheeky</span>Cheeku</a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                          <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="collapsibleNavbar">
                          @can('logged-in')
                          <ul class="navbar-nav">
                            <li class="nav-item">
                              <a class="nav-link" href="/">Home</a>
                            </li>
                          @can('is-admin')
                            <li class="nav-item">
                              <a class="nav-link" href="{{ route('admin.users.index') }}">Users</a>
                            </li>
                          @endcan
                          @can('is-mod')
                            <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Products
                              </a>
                              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('product.Products.create') }}">Add Product</a>
                              </div>
                            </li>
                            <li class="nav-item dropdown">
                              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Categories
                              </a>
                              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('category.categories.create') }}">Add Category</a>
                              </div>
                            </li>
                          @endcan
                          </ul>
                          @endcan
                          @if(Route::has('login'))
                            @auth
                            <ul class="navbar-nav ml-auto">
                            <li class="nav-item">
                              <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();  ">Logout</a>
                              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                              </form>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link" href="{{ route('user.profile') }}">Profile</a>
                            </li>    
                          </ul>
                          @else
                              <ul class="navbar-nav ml-auto">
                                <li class="nav-item">
                                  <a class="nav-link" href="{{ Route('login') }}">Login</a>
                                </li>
                                <li class="nav-item">
                                  <a class="nav-link" href="{{ Route('register') }}">Register</a>
                                </li>    
                              </ul>
                            @endauth
                          @endif
                        </div>  
                      </nav>
                </div>
            </div>
        </div>
    </head>
    <body class="">
      <div class="container">
        @include('partials.alerts')
      </div>
        @yield('content')
    </body>
</html>
