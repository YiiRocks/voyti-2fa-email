<?php

declare(strict_types=1);

return [
    // EmailTwoFactorMethod
    'voyti-2fa-email.view.two_factor_email.button_label' => 'Email',
    'voyti-2fa-email.view.two_factor_email.method_name' => 'email',

    // Email setup fragment
    'voyti-2fa-email.view.two_factor_email.enter_code' => 'Введите проверочный код, отправленный на ваш email',
    'voyti-2fa-email.view.two_factor_email.confirm_intro' => 'Проверочный код будет отправлен на указанный ниже адрес электронной почты.',
    'voyti-2fa-email.view.two_factor_email.send_button' => 'Отправить код',

    // EmailValidator
    'voyti-2fa-email.validator.two_factor_not_configured' => 'Двухфакторная аутентификация по электронной почте не настроена.',
    // Email code mail
    'voyti-2fa-email.mail.subject' => 'Ваш код подтверждения',
    'voyti-2fa-email.mail.body' => 'Ваш код подтверждения: {code}',

];
