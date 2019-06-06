<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
<div class="container" style="height: 100vh;">
    <div class="d-flex align-items-center justify-content-center h-100">
        <form method="POST" action="{{ route('download') }}" class="w-75">
            @csrf
            <div class="form-group">
                <label for="exampleInputEmail1">Path</label>
                <input name="path" class="form-control" id="exampleInputEmail1"
                       placeholder="public_html/premium/png/Abstract-Painted-Vectors.tarhan.ir.rar">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

</body>
</html>
