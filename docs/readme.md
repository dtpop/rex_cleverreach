# Cleverreach Addon für REDAXO

## Intro

Das Cleverreach AddOn für REDAXO erleichtert den Umgang mit der Cleverreach API. Das AddOn basiert auf der API v3, Stand Dezember 2020.

Zunächst wird ein Cleverreach Account benötigt. Dort muss mindestens eine Empfängergruppe angelegt sein. Außerdem muss man in Cleverreach einen Api Client einrichten. Dort findet man dann die entsprechenden Parameter, die in den Settings eingetragen werden.

Anschließend kann man einen Access Token generieren.

Grundsätzlich funktioniert die Cleverreach API in der Form, dass der Access Token für jeden Zugriff verwendet wird. Der Access Token hat eine Haltbarkeit von etwa einem Monat. Dann muss ein neuer Access Token generiert werden. Auch diesen Vorgang kann das AddOn automatisch ausführen.

Wenn die Einstellungsseite im Backend aufgerufen wird, wird die API aufgerufen und es wird der aktuelle Status angezeigt. Wenn der Access Token nicht mehr gültig ist, wird im Backend versucht über den Refresh Token einen neuen Access Token zu generieren. Erst wenn dieses fehlschlägt, muss man einen neuen Access Token manuell generieren. Ein entsprechender Link wird unten auf der Settings Seite angezeigt.

## Installation

Das Addon wird zunächst installiert und aktiviert. Anschließend wird Client Id und Client Secret eingetragen und initial der Access Token gesetzt. Nun sollte in den Einstellungen unten ein grüner Balken erscheinen und die funktionierende api anzeigen.

Nun brauchen wir ein Formular. Wir gehen hier mal von einem HTML Formular aus. Man kann selbstverständlich auch ein yform Formular machen und die Anmeldung z.B. über eine Callback Action umsetzen. In unserem Beispiel verarbeiten wir das Formular per Ajax.

```html
<form class="cleverreach-anmeldung">
    <label for="salutation">Anrede</label>
    <input name="salutation" type="text" id="salutation">
    <label for="firstname">Vorname</label>
    <input name="firstname" type="text" id="firstname">
    <label for="lastname">Nachname</label>
    <input name="lastname" type="text" id="lastname">
    <label for="email">E-Mail</label>
    <input name="email" type="text">
    <input name="sprache" type="hidden" value="de">
    <input name="func" type="hidden" value="subscribe">
    <button type="submit">Anmelden</button>
</form>
```

Script:

```js
<script>
$(".newsletter-anmeldung").on("submit", function (e) {
    var f = $(this);
    var origin = $(this).data("origin");
    e.preventDefault();
    $.ajax({
        url: "/",
        data: f.serialize(),
        type: "get",
        dataType: "json",
        complete: function (result) {
            $(".feedback_success").hide();
            $(".feedback_error").hide();
            if (result.responseText == "SUCCESS") {
                $("form").hide();
                $(".feedback_success").show();
            } else {
                $(".feedback_error").show();
            }
        },
    });
    return false;
});
</script>
```

Die PHP Verarbeitung kann beispielsweise über die boot.php des Project Addons umgesetzt werden. Im Beispielcode wird geprüft, ob der Access Token noch gültig ist. Falls dieser abgelaufen ist, wird versucht einen neuen Access Token aus dem Refresh Token zu generieren. Wenn das schief geht, wird ein Error ins Log geschrieben. Wenn im System die E-Mail Benachrichtigung im Fehlerfalle eingeschaltet ist, bekommt der Admin auch eine Nachricht, dass die Anmeldungen nicht mehr funktionieren.

```php
if (rex::isFrontend()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex_request::isXmlHttpRequest() && rex_request('func', 'string') == 'subscribe') {
            $use_doi = rex_config::get('cleverreach', 'doi');
            $api = new rex_cr();
            $rest = $api->get_api();
            if (!$api->is_available()) {
                if (!$api->refresh_token()) {
                    rex_logger::factory()->log('error','Cleverreach Access Key nicht mehr gültig und muss erneuert werden.');
                } else {
                    rex_logger::factory()->log('success','Cleverreach Access Key wurde aus dem Refresh Key erneuert.');
                    $api = new rex_cr();
                    $rest = $api->get_api();        
                }
            }

            $group_id = rex_config::get('cleverreach', 'groupid');
            if (!$group_id) {
                $groups = $rest->get("/groups");
                $group_id = $groups[0]->id;
            }

            $new_user = [
                "email" => rex_request('email'),
                "active" => false,
                "registered" => time(),
                "deactivated" => "0",
                "source" => "Website",
                "attributes" => [
                    "salutation" => rex_request('salutation'),
                    "firstname" => rex_request('firstname'),
                    "lastname" => rex_request('lastname')
                ],
                "tags" => [
                    rex_request('sprache')
                ]
            ];

            $success = false;

            if ($use_doi && rex_config::get('cleverreach', 'doiformid')) {
                if ($success = $rest->post('/groups/' . $group_id . '/receivers', $new_user)) {
                    $success2 = $rest->post("/forms/" . rex_config::get('cleverreach', 'doiformid') . "/send/activate", [
                        "email"   => $new_user["email"],
                        "doidata" => [
                            "user_ip"    => $_SERVER["REMOTE_ADDR"],
                            "referer"    => $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"],
                            "user_agent" => $_SERVER["HTTP_USER_AGENT"]
                        ]
                    ]);
                }
            } else {
                $new_user['active'] = true;
                $new_user['activated'] = time();
                $success = $rest->post('/groups/' . $group_id . '/receivers', $new_user);
            }
            echo $success ? 'SUCCESS' : 'ERROR';
            exit;
        }
    });
}

```


## Funktionen

Die Klasse kann auch für eigene Anwendungen verwendet werden.

Mit `$rest = new rex_cr(); $api = $rest->get_api();` kann man sich ein neues Objekt machen, die api holen und ab da auch mit CR kommunizieren. Infos darüber findet ihr in der entsprechenden Dokumentation bei Cleverreach.

## Weiterentwicklung

Wenn Du einen Fehler gefunden hast oder an der Weiterentwicklung mitarbeiten willst, kannst Du in github Issues schreiben. Diese werde ich wohlwollend lesen, aus Zeitmangel aber eher schleppend oder gar nicht bearbeiten. Pullrequests prüfe ich gerne und übernehme sie auch.


## Credits

Der Cleverreach Client stammt original und unverändert von Cleverreach und kann daher auch einfach upgedated werden.

Ich, Wolfang Bund, der Ersteller des AddOns stehe in keinem Zusammenhang mit Cleverreach. Ich habe dieses Addon für die Community entwickelt. Anlass hierfür war ein Kundenauftrag. Spenden sind möglich, gerne durch einen kleinen Supportauftrag.

## Lizenz

Das AddOn wird unter der MIT Lizenz veröffentlicht und kann sowohl für private als auch kommerzielle Zwecke frei verwendet werden.
