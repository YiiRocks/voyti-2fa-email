<?php

declare(strict_types=1);

return [
    // EmailTwoFactorMethod
    'voyti-2fa-email.view.two_factor_email.button_label' => 'E-Mail',
    'voyti-2fa-email.view.two_factor_email.method_name' => 'E-Mail',

    // Email setup fragment
    'voyti-2fa-email.view.two_factor_email.enter_code' => 'Geben Sie den per E-Mail gesendeten Verifizierungscode ein',
    'voyti-2fa-email.view.two_factor_email.confirm_intro' => 'Ein Verifizierungscode wird an die unten stehende E-Mail-Adresse gesendet.',
    'voyti-2fa-email.view.two_factor_email.send_button' => 'Code senden',

    // EmailValidator
    'voyti-2fa-email.validator.two_factor_not_configured' => 'E-Mail-Zwei-Faktor-Authentifizierung ist nicht konfiguriert.',
    // Email code mail
    'voyti-2fa-email.mail.subject' => 'Ihr Bestätigungscode',
    'voyti-2fa-email.mail.body' => 'Ihr Bestätigungscode lautet: {code}',

];
