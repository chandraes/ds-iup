<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="{{public_path('assets/plugins/bootstrap/css/bootstrap.min.css')}}">
    <title>@yield('title', 'Document')</title>
    <style>
        @page {
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
            }
        }
        header {
            position: fixed;
            display: block !important;
            float: right;
            top: -20px;
            width: 100% !important;
            left: 0px;
            height: 50px;
            text-align: right;
        }
        .table-pdf {
            border: 1px solid;
            padding-left: 5px;
            padding-right: 5px;
        }
        .text-pdf {
            font-size: 8pt;
        }
        .text-10 {
            font-size: 10pt;
        }

        .page-break {
            page-break-after: always;
        }
        .column-pdf {
            float: left;
            width: 50%;
        }
        .row-pdf:after {
            content: "";
            display: table;
            clear: both;
        }
        .column-4 {
            float: left;
            width: 25%;
        }
        .footer {
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            line-height: 50px;
        }

        .footer-content {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
        }

    </style>
</head>
<body style="margin-left:-50px">
    <br>
<div class="container-fluid">@yield('content')</div>

{{-- <div class="footer">
    <div class="footer-content">
        Page {PAGE_NUM} of {PAGE_COUNT}
    </div>
</div> --}}
</body>
</html>
