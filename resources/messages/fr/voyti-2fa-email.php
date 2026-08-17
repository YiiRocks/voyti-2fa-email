<?php

declare(strict_types=1);

return [
    // EmailTwoFactorMethod
    'voyti-2fa-email.view.two_factor_email.button_label' => 'E-mail',
    'voyti-2fa-email.view.two_factor_email.method_name' => 'e-mail',

    // Email setup fragment
    'voyti-2fa-email.view.two_factor_email.enter_code' => 'Saisissez le code de vérification envoyé à votre e-mail',
    'voyti-2fa-email.view.two_factor_email.confirm_intro' => "Un code de vérification sera envoyé à l'adresse e-mail ci-dessous.",
    'voyti-2fa-email.view.two_factor_email.send_button' => 'Envoyer le code',

    // EmailValidator
    'voyti-2fa-email.validator.two_factor_not_configured' => "L'authentification à deux facteurs par e-mail n'est pas configurée.",
    // Email code mail
    'voyti-2fa-email.mail.subject' => 'Votre code de vérification',
    'voyti-2fa-email.mail.body' => 'Votre code de vérification est : {code}',

];
