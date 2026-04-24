<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Anniversary Reminder</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fff7fb; font-family: Arial, Helvetica, sans-serif; color: #4b2e3f;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #fff7fb; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 620px; background-color: #ffffff; border: 1px solid #ffd8ec; border-radius: 18px; overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #ff7eb6, #ffb3d9); padding: 30px 24px; text-align: center; color: #ffffff;">
                            <div style="font-size: 30px; line-height: 1;">💕</div>
                            <h1 style="margin: 10px 0 0; font-size: 28px; line-height: 1.2;">Happy Anniversary!</h1>
                            <p style="margin: 10px 0 0; font-size: 15px; opacity: 0.95;">Years of Love, Moments of Grace</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px 24px 22px;">
                            <p style="margin: 0 0 14px; font-size: 16px;">မင်္ဂလာပါ,</p>
                            <p style="margin: 0 0 14px; font-size: 16px; line-height: 1.7;">
                                ဒီနေ့ဟာ ကျွန်တော်တို့ရဲ့ ချစ်သူသက်တမ်း <strong>{{ $anniversary['relationship']['label'] }}</strong> မြောက်တဲ့ ထူးခြားတဲ့နေ့လေး ဖြစ်ပါတယ်။ အမှတ်တရတွေ စတင်ခဲ့တဲ့နေ့ကနေ ဒီနေ့အထိ ကျွန်တော်တို့နဲ့အတူ ရှိနေပေးခဲ့တဲ့အတွက် ကျေးဇူးတင်ပါတယ်။ 
                            </p>
                            <p style="margin: 0 0 22px; font-size: 16px; line-height: 1.7;">
                                ကျွန်တော်တို့ရဲ့ ပျော်ရွှင်မှုတွေကို သင်တို့နဲ့အတူ ထပ်တူဝေမျှခွင့်ရလို့ အရမ်းဝမ်းသာပါတယ်။
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto 24px;">
                                <tr>
                                    <td style="border-radius: 999px; background-color: #ff4f9a;">
                                        <a href="{{ config('app.url') }}" style="display: inline-block; padding: 12px 24px; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold;">
                                            Start Wishing Happy Anniversary 💌
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 15px; color: #7e5570; line-height: 1.7;">
                                ကျွန်ုပ်တို့ရဲ့ နောက်ထပ်မှတ်တိုင်တစ်ခုကို အတူတူဂုဏ်ပြုလိုက်ရအောင်။ <br/>
                                Happy Anniversary! 🌸
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 24px 24px; text-align: center; font-size: 12px; color: #a17890;">
                            Sent with love by {{ config('app.name') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
