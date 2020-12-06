<?php

/**
 * @var rex_addon $this
 */

// register a custom yrewrite scheme
// rex_yrewrite::setScheme(new rex_project_rewrite_scheme());

// register yform template path
// rex_yform::addTemplatePath($this->getPath('yform-templates'));


if (rex::isFrontend()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex_get('code') && rex_get('clientid')) {
            if (rex_get('func','string') == 'crnewtoken') {
                $rest = new rex_cr();
                if ($rest->clientid == rex_get('clientid')) {
                    $success = $rest->new_access_token();
                    if ($success) {
                        rex_response::sendRedirect(trim(rex::getServer(),'/').rex_url::backendPage('cleverreach/settings',['message'=>'newaccesstoken'],false));
                    }
                }
            }
        }
    });


}
