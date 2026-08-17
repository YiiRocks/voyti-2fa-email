<?php

declare(strict_types=1);

return [
    // EmailTwoFactorMethod
    'voyti-2fa-email.view.two_factor_email.button_label' => 'Email',
    'voyti-2fa-email.view.two_factor_email.method_name' => 'email',

    // Email setup fragment
    'voyti-2fa-email.view.two_factor_email.enter_code' => 'Enter the verification code sent to your email',
    'voyti-2fa-email.view.two_factor_email.confirm_intro' => 'A verification code will be sent to the email address below.',
    'voyti-2fa-email.view.two_factor_email.send_button' => 'Send Code',

    // EmailValidator
    'voyti-2fa-email.validator.two_factor_not_configured' => 'Email two factor authentication is not configured.',
    // Email code mail
    'voyti-2fa-email.mail.subject' => 'Your verification code',
    'voyti-2fa-email.mail.body' => 'Your verification code is: {code}',

];
