<!DOCTYPE html>
<html>
    <head>
        <title>email verification</title>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <p>
                  您的验证代码为
                </p>
                <p style="font-weight: bold; font-size: 24px;">
                  {{ $token }}
                </p>
                <p>该验证码在 {{ $avalible_before }} 前有效。</p>
            </div>
        </div>
    </body>
</html>
