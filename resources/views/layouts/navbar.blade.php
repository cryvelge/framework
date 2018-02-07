<nav class="navbar navbar-default navbar-static-top" style="margin-bottom: 0">
    <div class="navbar-header">

        <!-- Collapsed Hamburger -->
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle Navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <!-- Branding Image -->
        <a class="navbar-brand" href="{{ url('/') }}">
            不去就出局
        </a>
    </div>

    <!-- Right Side Of Navbar -->
    <ul class="nav navbar-top-links navbar-right">
        <!-- Authentication Links -->
        @guest
            <li><a href="{{ route('admin.login') }}">Login</a></li>
        @else
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                    <i class="fa fa-user"></i>
                    &nbsp;{{ Auth::user()->name }}&nbsp;
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu dropdown-user">
                    <li>
                        <a href="{{ route('admin.logout') }}">
                            Logout
                        </a>
                    </li>
                </ul>
            </li>
        @endguest
    </ul>

    <div class="navbar-default sidebar">
        <div class="sidebar-nav navbar-collapse">
            @guest
            @else
                <ul id="side-menu" class="nav">
                    <li><a href="/">首页</a></li>
                </ul>
            @endguest
        </div>
    </div>
</nav>