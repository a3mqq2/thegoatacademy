<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Course #{{ $progressTest->course_id }}</title>

@php
    $fontRegular = 'file://' . storage_path('fonts/Cairo-Regular.ttf');
    $fontBold    = 'file://' . storage_path('fonts/Cairo-Bold.ttf');
    $bgData      = base64_encode(file_get_contents(public_path('images/exam.png')));
@endphp

<style>
@font-face {
    font-family: 'cairo';
    src: url('{{ $fontRegular }}') format('truetype');
    font-weight: 400;
}
@font-face {
    font-family: 'cairo';
    src: url('{{ $fontBold }}') format('truetype');
    font-weight: 700;
}
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
html, body {
    width: 1024px;
    height: 1024px;
    font-family: 'cairo', sans-serif;
    color: #fff;
}
body {
    background: url('data:image/png;base64,{{ $bgData }}') no-repeat center center;
    background-size: cover;
}
.container {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.header {
    text-align: center;
}
.title {
    font-size: 36px;
    font-weight: bold;
    margin-bottom: 20px;
}
.sub-details {
    display: flex;
    justify-content: space-between;
    font-size: 18px;
    margin-bottom: 30px;
}
.sub-details div {
    width: 48%;
}
.table-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}
table {
    width: 90%;
    margin: auto;
    border-collapse: collapse;
    position: absolute;
    top: 300px;
}
th, td {
    font-size: 20px;
    padding: 10px;
    background: #000;
    border: 1px solid #333;
    text-align: center;
}
.title {
    position: absolute;
    top: 160px;
    left: 40px;
}
</style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1 class="title">
         Progress Test Results
        </h1>
        <div class="sub-details">
            <div style="position: absolute; top:210px;">
            </div>
            <div style="text-align: right; position: absolute; top:210px; left:250px;">
            </div>
        </div>
    </div>

    <div class="table-container">
      


    </div>

</div>
</body>
</html>
