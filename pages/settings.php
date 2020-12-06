<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (rex_get('message') == 'newaccesstoken') {
    echo rex_view::success('Der neue Accesstoken wurde erfolgreich gesetzt.');
}

$rest = new rex_cr();
$rest_is_available = $rest->is_available();


$form = rex_config_form::factory('cleverreach');
$form->addFieldset('Cleverreach - Einstellungen');

$field = $form->addTextField('clientid');
$field->setLabel('Client Id');
$field->setNotice('Die Client Id der Rest API. z.B. <code>viF5IvjK9I</code>');

$field = $form->addTextField('clientsecret');
$field->setLabel('Client Secret');
$field->setNotice('Das Client Secret der Rest API. z.B. <code>2RFIq1V33ITbMto0VlMzmZx7mcbXyauX</code>.');

$field = $form->addTextField('groupid');
$field->setLabel('Group Id');
$field->setNotice('Dieser Eintrag ist optional. Wenn hier nichts eingetragen ist, so wird die erste Gruppe verwendet, um neue Empfänger hinzuzufügen. Wenn hier eine Id eingetragen ist, so wird diese Gruppe verwendet. z.B. <code>233519</code>');

$field = $form->addTextField('doiformid');
$field->setLabel('Double Opt In Form Id');
$field->setNotice('Falls Double Opt In verwendet werden soll, muss hier die Id des Opt In Formulars bei Cleverreach eingetragen werden. z.B. <code>335219</code>');

$field = $form->addCheckboxField('doi');
$field->setLabel('Double Opt In');
$field->addOption('Double Opt In verwenden', "1");
$field->setNotice('Wenn Double Opt In eingeschaltet ist, wird bei einem neuen E-Mail Empfänger zunächst eine Bestätigungsmail verschickt. Hinweis: das funktioniert nur, wenn die API durch Cleverreach zertifiziert wurde.');




/*

$field = $form->addTextAreaField('assocfields');
$field->setLabel('Assoziierte Feldnamen');
$field->setNotice('Datenbankfeldern zu XML Namen assoziieren <code>dbfieldname::xmlfield</code> z.B. <code>titel::titelkurz</code>.<br>Optional mit Format Option. z.B. <code>datestart::beginndat::date(Y-m-d)</code><br>Mit <code>protected</code> an der 4. Stelle kann ein Feld vor dem Überschreiben geschützt werden. Es wird dann nur beim ersten Import des Kurses befüllt. Beispiel: <code>text::titellang::::protected</code>');

$field = $form->addTextAreaField('bsmimport');
$field->setLabel('Felddefinitionen für BSM Import');
$field->setNotice('Feldbezeichnung und Feldname durch zwei Doppelpunkte <code>::</code> trennen.<br>Datumsfeld mit <code>::date(Y-m-d)</code> formatierbar.');

$field = $form->addTextAreaField('viuimport');
$field->setLabel('Felddefinitionen für VIU Import');

$field = $form->addTextAreaField('objektimport');
$field->setLabel('Felddefinitionen für Adressen/Objekte');

$field = $form->addCheckboxField('debug');
$field->setLabel('Debugmodus');
$field->addOption('Debugmodus ein', "1");
$field->setNotice('Im Debugmodus werden die E-Mails an vordefinierte E-Mail Adressen geschickt.');

$field = $form->addTextField('viuemail');
$field->setLabel('VIU E-Mail Adressen');
$field->setNotice('Mehrere E-Mail-Adressen durch Komma trennen.');

$field = $form->addTextField('bsmemail');
$field->setLabel('BSM E-Mail Adressen');
$field->setNotice('Mehrere E-Mail-Adressen durch Komma trennen.');

$field = $form->addTextField('bsm_locked_steps');
$field->setLabel('Arbeitsschritte, die für den BSM gesperrt sind');
$field->setNotice('Werte durch Komma trennen.');

$field = $form->addTextField('viu_locked_steps');
$field->setLabel('Arbeitsschritte, die für den VIU gesperrt sind');
*/


$content = $form->get();

$fragment = new rex_fragment();
$fragment->setVar('title', 'Einstellungen');
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;

if (rex_config::get('cleverreach','clientid') && rex_config::get('cleverreach','clientsecret')) {
    if ($rest_is_available) {
        echo rex_view::success('Cleverreach api ist verfügbar.');    
    } else {
        if ($rest->refresh_token()) {
            echo rex_view::success('Der Access Token wurde neu gesetzt.');
        } else {
            echo rex_view::error('Der Access Token konnte nicht neu gesetzt werden. Es muss ein neuer Token generiert werden. '.$rest->get_oauth_link());
        }
    }

} else {
    echo rex_view::error('Es muss eine gültige Client Id und ein gültiges Client Secret eingetragen werden.');
}


