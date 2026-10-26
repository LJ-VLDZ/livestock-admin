<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Error - Livestock Express</title>
    <style>
        body {
            background-color: #0A3622;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .card {
            background: #F8FBF8;
            padding: 28px 22px;
            border-radius: 20px;
            text-align: center;
            max-width: 250px;
            width: 85%;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }
        .icon-circle {
            background-color: #FFEBEE;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px auto;
            color: #C62828;
            font-size: 28px;
            font-weight: bold;
        }
        h2 {
            color: #C62828;
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 900;
        }
        p {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 20px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            box-sizing: border-box;
            background-color: #1B5E20;
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(27, 94, 32, 0.3);
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="icon-circle">✕</div>
        <h2>Verification Failed</h2>
        <p>{{ $message ?? 'An error occurred while creating your account. Please try again later.' }}</p>
        
    </div>


</body>
</html>