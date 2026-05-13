<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email-i u Verifikua - Gjueti Thesari Kosova</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #021044 0%, #031a6e 100%);
            padding: 20px;
        }
        .container {
            text-align: center;
            background: white;
            padding: 48px 40px;
            border-radius: 20px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            line-height: 72px;
        }
        .icon.success { background: #16a34a; color: white; }
        .icon.error { background: #dc2626; color: white; }
        h1 { color: #021044; margin: 0 0 8px; font-size: 24px; }
        p { color: #666; margin: 0 0 32px; font-size: 15px; line-height: 1.6; }
        .btn {
            background-color: #D8B129;
            color: #021044;
            border: none;
            padding: 14px 40px;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn:hover { background-color: #c4a020; }
        .footer { margin-top: 24px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        @if(empty($error))
            <div class="icon success">&#10003;</div>
            <h1>Email-i u Verifikua!</h1>
            <p>Email-i juaj u verifikua me sukses.<br>Tani mund të hyni në llogarinë tuaj.</p>
        @elseif($error === 'Invalid verification link')
            <div class="icon error">!</div>
            <h1>Lidhje e Pavlefshme</h1>
            <p>Kjo lidhje verifikimi është e pavlefshme. Ju lutemi provoni të regjistroheni përsëri.</p>
        @else
            <div class="icon error">!</div>
            <h1>Email-i tashmë i Verifikuar</h1>
            <p>Email-i juaj tashmë është i verifikuar. Mund të hyni direkt në llogarinë tuaj.</p>
        @endif
        <a href="{{ $loginUrl }}" class="btn">Kthehu te Hyrja</a>
        <p class="footer">Gjueti Thesari Kosova</p>
    </div>
</body>
</html>
