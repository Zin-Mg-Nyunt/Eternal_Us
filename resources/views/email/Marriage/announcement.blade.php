<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marriage Anniversary Announcement</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fff6fa; font-family: Arial, Helvetica, sans-serif; color: #4b2f3d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fff6fa; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 620px; background-color: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 24px rgba(229, 112, 164, 0.18);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #ff85b6 0%, #ffc3dc 100%); text-align: center; padding: 32px 20px;">
                            <div style="font-size: 32px; line-height: 1;">&#10084;&#65039;</div>
                            <h1 style="margin: 10px 0 8px; font-size: 28px; line-height: 1.3; color: #ffffff;">
                                Happy Marriage Anniversary
                            </h1>
                            <p style="margin: 0; font-size: 15px; color: #fff7fb;">
                                Celebrating another beautiful year of togetherness
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 28px 20px;">
                            <p style="margin: 0 0 14px; font-size: 16px; line-height: 1.7;">
                                မင်္ဂလာပါ,
                            </p>
                            <p style="margin: 0 0 14px; font-size: 16px; line-height: 1.8;">
                                ဒီနေ့က ကျွန်တော်တို့ရဲ့ အိမ်ထောင်သက်တမ်း
                                <strong style="color: #d94b8e;">{{ $anniversary['marriage']['label'] }}</strong>
                                မြောက်တဲ့ ထူးခြားတဲ့နေ့လေး ဖြစ်ပါတယ်။
                            </p>
                            <p style="margin: 0 0 14px; font-size: 16px; line-height: 1.8;">
                                ချစ်ခြင်းမေတ္တာ၊ ယုံကြည်မှု၊ နားလည်မှုတွေနဲ့ အတူတူလျှောက်လာခဲ့တဲ့
                                ဒီခရီးစဉ်ကို သင်တို့နဲ့အတူ မျှဝေရတာ အရမ်းဝမ်းသာပါတယ်။
                            </p>
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.8;">
                                ဒီအမှတ်တရနေ့လေးကို အတူတူဂုဏ်ပြုပေးဖို့ ဖိတ်ခေါ်ပါတယ်။
                                Happy Anniversary to us! 🌸
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fff0f7; border: 1px solid #ffd5e8; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 14px 16px; font-size: 14px; color: #7c4f64;">
                                        <strong style="color: #d94b8e;">Anniversary Date:</strong>
                                        {{ $anniversary['marriage']['dateFormat'] ?? 'Our special date' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 8px 28px 30px; text-align: center;">
                            <a href="{{ config('app.url') }}" style="display: inline-block; background-color: #eb529a; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; padding: 12px 22px; border-radius: 999px;">
                                Start Wishing Happy Anniversary 💌
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #fff9fc; text-align: center; padding: 18px 20px; border-top: 1px solid #ffe3ef;">
                            <p style="margin: 0; font-size: 13px; color: #9d7286; line-height: 1.6;">
                                Sent with love by <strong style="color: #d94b8e;">{{ config('app.name') }}</strong>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
