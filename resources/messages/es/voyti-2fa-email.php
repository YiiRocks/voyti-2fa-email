<?php

declare(strict_types=1);

return [
    // EmailTwoFactorMethod
    'voyti-2fa-email.view.two_factor_email.button_label' => 'Correo electrónico',
    'voyti-2fa-email.view.two_factor_email.method_name' => 'correo electrónico',

    // Email setup fragment
    'voyti-2fa-email.view.two_factor_email.enter_code' => 'Introduzca el código de verificación enviado a su correo electrónico',
    'voyti-2fa-email.view.two_factor_email.confirm_intro' => 'Se enviará un código de verificación a la dirección de correo electrónico indicada a continuación.',
    'voyti-2fa-email.view.two_factor_email.send_button' => 'Enviar código',

    // EmailValidator
    'voyti-2fa-email.validator.two_factor_not_configured' => 'La autenticación de dos factores por correo electrónico no está configurada.',
    // Email code mail
    'voyti-2fa-email.mail.subject' => 'Tu código de verificación',
    'voyti-2fa-email.mail.body' => 'Tu código de verificación es: {code}',

];
