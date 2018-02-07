<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>不去就出局后台管理</title>

    <!-- Styles -->
    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <link href="/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/datatables/1.10.13/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/startbootstrap-sb-admin-2/3.3.7+1/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/metisMenu/2.7.0/metisMenu.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/morris.js/0.5.1/morris.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/select2/4.0.3/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet">
    <style>
        #page-wrapper {
            padding-top: 20px;
        }
        @media (min-width: 768px) {
            .sidebar {
                width: 150px;
            }
            #page-wrapper {
                margin-left: 150px;
            }
        }
        .form-inline label {
            margin-left: 10px;
            margin-right: 10px;
        }
    </style>
    @yield('stylesheets')
</head>
<body>
    <div id="wrapper">
        @component('layouts.navbar')
        @endcomponent
        <div id="page-wrapper">
            @if(session('notice'))
                <div class="alert alert-success">{{session('notice')}}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-success">{{session('error')}}</div>
            @endif

            @yield('content')
        </div>

        @yield('extra_content')
    </div>

    <!-- Scripts -->
    <script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery-ujs/1.2.2/rails.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.4/locales/bootstrap-datepicker.zh-CN.min.js"></script>
    <script src="/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    <script src="https://cdn.bootcss.com/datatables/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.bootcss.com/datatables/1.10.13/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdn.bootcss.com/raphael/2.2.7/raphael.min.js"></script>
    <script src="https://cdn.bootcss.com/graphael/0.5.1/g.raphael-min.js"></script>
    <script src="https://cdn.bootcss.com/flot/0.8.3/jquery.flot.min.js"></script>
    <script src="https://cdn.bootcss.com/flot.tooltip/0.9.0/jquery.flot.tooltip.min.js"></script>
    <script src="https://cdn.bootcss.com/metisMenu/2.7.0/metisMenu.min.js"></script>
    <script src="https://cdn.bootcss.com/morris.js/0.5.1/morris.min.js"></script>
    <script src="https://cdn.bootcss.com/startbootstrap-sb-admin-2/3.3.7+1/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.min.js"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>
    <script>
        (function () {
            var topOffset = 50;
            var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
            if (width < 768) {
                $('div.navbar-collapse').addClass('collapse');
                topOffset = 100; // 2-row-menu
            } else {
                $('div.navbar-collapse').removeClass('collapse');
            }

            var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
            height = height - topOffset;
            if (height < 1) height = 1;
            if (height > topOffset) {
                $("#page-wrapper").css("min-height", (height) + "px");
            }
        })();
        $.fn.select2.defaults.set("theme", "bootstrap");
    </script>
    @yield('scripts')
</body>
</html>
