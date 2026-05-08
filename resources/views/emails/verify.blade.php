<!DOCTYPE html>
<html>
<head>
    <title>Verifiko Email-in</title>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; background: #021044; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                <span style="color: #D8B129;">&#x1F3F0;</span>
            </div>
            <h1 style="color: #021044; margin: 0; font-size: 22px;">Gjueti Thesari Kosova</h1>
        </div>

        <h2 style="color: #021044; font-size: 18px; margin-bottom: 16px;">Përshëndetje {{ $user->name }}!</h2>

        <p style="color: #555; line-height: 1.7; margin-bottom: 20px; font-size: 15px;">
            Faleminderit që u bashkuat në gjuetinë e thesarit! Ju lutemi verifikoni adresën tuaj të email-it duke klikuar butonin më poshtë.
        </p>

        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $verificationUrl }}" style="background-color: #D8B129; color: #021044; padding: 14px 36px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px;">
                Verifiko Email-in
            </a>
        </div>

        <p style="color: #888; font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
            Nëse butoni nuk punon, kopjoni dhe ngjisni këtë lidhje në shfletuesin tuaj:
        </p>
        <p style="color: #999; font-size: 12px; word-break: break-all; background: #f9f9f9; padding: 10px; border-radius: 6px;">
            {{ $verificationUrl }}
        </p>

        <hr style="border: none; border-top: 1px solid #eee; margin: 24px 0;">

        <p style="color: #aaa; font-size: 11px; text-align: center; margin: 0;">
            Nëse nuk e keni krijuar këtë llogari, mund ta injoroni këtë email.
        </p>
    </div>
</body>
</html>
