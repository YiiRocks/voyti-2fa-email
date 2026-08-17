<?php

declare(strict_types=1);

return [
    // EmailTwoFactorMethod
    'voyti-2fa-email.view.two_factor_email.button_label' => 'E-mail',
    'voyti-2fa-email.view.two_factor_email.method_name' => 'e-mail',

    // Email setup fragment
    'voyti-2fa-email.view.two_factor_email.enter_code' => 'Voer de per e-mail verzonden verificatiecode in',
    'voyti-2fa-email.view.two_factor_email.confirm_intro' => 'Er wordt een verificatiecode verzonden naar onderstaand e-mailadres.',
    'voyti-2fa-email.view.two_factor_email.send_button' => 'Code verzenden',

    // EmailValidator
    'voyti-2fa-email.validator.two_factor_not_configured' => 'E-mail tweefactorauthenticatie is niet geconfigureerd.',
    // Email code mail
    'voyti-2fa-email.mail.subject' => 'Je verificatiecode',
    'voyti-2fa-email.mail.body' => 'Je verificatiecode is: {code}',

];
