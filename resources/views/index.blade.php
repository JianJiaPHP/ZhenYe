<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$name}}</title>
    <style>
        *{
            padding: 0;
            margin: 0;
        }
        .footer {
            position: fixed;
            bottom: 0;
            height: 60px;
            display: flex;
            justify-content: center;
            width: 100%;
            align-items: center;
            background: #f5f5f54a;
        }
        a {
            text-decoration: none;
            color: black
        }
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            {{ $name }}
        </div>
    </div>
    <div class="footer">
        <a href="http://beian.miit.gov.cn/">{{$record}}</a>
    </div>
</div>
</body>
</html>

