<?php

declare(strict_types=1);

use YiiRocks\Voyti\Helper\LinkButtonHelper;
use YiiRocks\Voyti\TwoFactor\Form\TwoFactorCodeForm;
use Yiisoft\FormModel\Field;
use Yiisoft\Html\Html;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\View\Renderer\Csrf;

/**
 * @var WebView $this
 * @var TwoFactorCodeForm $form
 * @var array{emailCodeSent: bool, userEmail: string, sendCodeUrl: string, enableUrl: string} $data
 * @var TranslatorInterface $translator
 * @var Csrf $csrf
 */

if ($data['emailCodeSent']) {
    echo Html::div($translator->translate('voyti-2fa-email.view.two_factor_email.enter_code', category: 'voyti-2fa-email'))->class('alert alert-info');

    /** @psalm-suppress InvalidScope */
    echo $this->render('./_code-form', ['form' => $form, 'formSubmitUrl' => $data['enableUrl'], 'translator' => $translator, 'csrf' => $csrf]);
} else {
    echo Html::div()->class('alert alert-info')->open();
    echo Html::p($translator->translate('voyti-2fa-email.view.two_factor_email.confirm_intro', category: 'voyti-2fa-email'));
    echo Html::p(Html::strong($data['userEmail'])->render())->encode(false);
    echo Html::div()->close();

    echo Html::form()
        ->post($data['sendCodeUrl'])
        ->csrf($csrf)
        ->open();
    echo Field::buttonGroup()
        ->buttonsData([
            [$translator->translate('voyti-2fa-email.view.two_factor_email.send_button', category: 'voyti-2fa-email'), 'type' => 'submit', 'class' => LinkButtonHelper::submitButtonClass(), 'tabindex' => 1],
        ]);
    echo Html::form()->close();
}
