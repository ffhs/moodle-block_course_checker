<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_course_checker'.
 *
 * @package    block_course_checker
 * @copyright  2019 Liip SA <elearning@liip.ch>
 * @author     2019 Adrian Perez, Fernfachhochschule Schweiz (FFHS) <adrian.perez@ffhs.ch>
 * @author     2020 Christoph Karlen, Fernfachhochschule Schweiz (FFHS) <christoph.karlen@ffhs.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Kurs-Checker';
$string['privacy:metadata'] = 'The course checker block only contains anonymous data.';
$string['course_checker:addinstance'] = 'Einen neuen Kurs-Checker Block hinzufügen';
$string['course_checker:view'] = 'Den Kurs-Checker Block anzeigen';
$string['course_checker:view_report'] = 'Die Resultat-Seite anzeigen';
$string['course_checker:view_notification'] = 'Die Kurs-Checker Notifikationen anzeigen';
$string['messageprovider:checker_completed'] = 'Der Kurs ist kontrolliert';

// String specific for the checker settings.
$string['settings_general'] = '<p>If the checker is disabled (after save changes) there will be shown below a new setting to hide and show each checker results.</p>';
$string['settings_referencecourseid'] = 'Reference course id';
$string['settings_rolesallowedmanual'] = 'Roles for manual check';
$string['settings_rolesallowedmanual_description'] = 'Define the global roles which are allowed to use the manual check form.';
$string['settings_checker_header'] = '{$a} settings';
$string['settings_checker_toggle'] = '{$a} enabled';
$string['settings_checker_hide'] = '{$a} hidden';
$string['settings_checker_dependency'] = '<div class="alert alert-warning">Checker dependency failed, check if plugin <a href="/admin/modules.php" target="_blank">{$a}</a> installed and enabled.</div>';

// String for checker block and results page.
$string['noresults'] = 'Der Kurs wurde nie automatisch kontrolliert';
$string['nogroupresults'] = 'Es wurde nichts gefunden um zu testen. Alles sieht gut aus!';
$string['backtocourse'] = 'Zurück zum Kurs';
$string['resultpagegoto'] = 'Resultat-Seite anzeigen';
$string['resultpageheader'] = 'Resultat-Seite anzeigen';
$string['resultpagetitle'] = 'Detaillierte Checker-Resultate für den Kurs {$a->name} anzeigen';
$string['automaticcheck'] = 'Letzter automatischer Check';
$string['lastactivityedition'] = 'Letzte Aktivitäts-Änderungen';
$string['automaticcheckempty'] = 'Die Checks wurden nie für diesen Kurs ausgeführt';
$string['humancheckempty'] = 'Dieser Kurs wurde nie von Hand kontrolliert';
$string['humancheck'] = 'Letzte händische Kontrolle:';
$string['humancheck_comment_placeholder'] = 'Kommentar';
$string['humancheck_reason'] = 'Grund:';
$string['humancheck_title'] = 'Datum der letzten händischen Kontrolle:';
$string['humancheck_update'] = 'Aktualisieren';
$string['invalidtoken'] = 'Ihr Token ist nicht valid';
$string['runcheckbtn'] = 'Kurs checken';
$string['runcheckbtn_already'] = 'Dieser Kurs ist bereits für die automatische Kontrolle eingeplant.';
$string['runcheckbtn_nocheckers'] = 'Keine Checker sind aktiviert.';
$string['result'] = 'Resultat';
$string['resultpermissiondenied'] = 'Du hast keine Berechtigung um auf diese Seite zuzugreifen';
$string['message'] = 'Nachricht';
$string['link'] = 'Link';
$string['check_successful'] = 'Erfolg';
$string['check_failed'] = 'Fehler';
$string['resolutionlink'] = 'Auflösung: ';
$string['checker_col_block_header'] = 'Check';
$string['result_col_block_header'] = 'Resultat';
$string['rerun_col_block_header'] = 'Re-run';
$string['rerun_disabled_col_block_header'] = 'Dieser Check ist schon in der Warteschlange';
$string['result_col_page_header'] = 'Resultat';
$string['link_col_page_header'] = 'Link zum lösen des Problems';
$string['message_col_page_header'] = 'Nachricht';
$string['checker_last_run'] = 'Letzte Ausführung {$a}';
$string['checker_last_run_global'] = 'Unbekanntes Datum für diesen Checker. Die globale Kursüberprüfung war am {$a}';
$string['result_last_activity_header'] = 'Zuletzt geänderte Aktivitäten';
$string['result_last_activity_header_date'] = 'Zuletzt geänderte Aktivitäten seit {$a}';
$string['result_last_activity_empty'] = 'Keine geänderten Aktivitäten seit {$a}';
$string['result_checker_disabled'] = 'Dieser Check wurde vom Administrator deaktiviert.';

// Name of each group that can be assigned to checkers.
$string['group_course_settings'] = 'Kurs-Einstellungen';
$string['group_links'] = 'Link-Validator';
$string['group_activities'] = 'Aktivitäts-Einstellungen';

// Name and title of each checker.
$string['checker_groups'] = 'Prüfung der Gruppeneinreichung';
$string['checker_groups_display'] = 'Gruppen-Einreichung für Aufgaben';
$string['checker_link'] = 'Links-Check';
$string['checker_link_display'] = 'Links im Kurs';
$string['checker_attendance'] = 'Überprüfung der Anwesenheits-Lerneinheiten';
$string['checker_attendance_display'] = 'Anwesenheits-Lerneinheiten';
$string['checker_data'] = 'Prüfung der Datenbank-Aktivität';
$string['checker_data_display'] = 'Datenbank-Aktivität mit definierten Feldern';
$string['checker_subheadings'] = 'Label-Untertitel Check';
$string['checker_subheadings_display'] = 'Label-Untertitel';
$string['checker_referencesettings'] = 'Referenzeinstellungen-Check';
$string['checker_referencesettings_display'] = 'Einstellungen im Vergleich zum Referenzkurs';
$string['checker_activedates'] = 'Aktive-Termine-Check';
$string['checker_activedates_display'] = 'Aktive Termine in Aktivitäts-Konfigurationen';

// String specific for the link checker.
$string['checker_link_activity'] = 'Aktivität: {$a->name}  ({$a->modname})';
$string['checker_link_book_chapter'] = 'Buchkapitel: {$a->title}';
$string['checker_link_wiki_page'] = 'Wiki-Seite: {$a->title}';
$string['checker_link_summary'] = 'Kurs-Zusammenfassung';
$string['checker_link_error_curl'] =
        'cURL Error {$a->curl_errno} {$a->curl_error} bei {$a->url}'; // You can get any curl info or pare_url field in $a.
$string['checker_link_error_code'] =
        'HTTP Error {$a->http_code} bei {$a->url}'; // You can get any curl info or pare_url field in $a.
$string['checker_link_ok'] =
        '{$a->url} ist gültig (Code {$a->http_code})'; // You can get any curl info or pare_url field in $a.
$string['checker_link_error_skipped'] = 'Die Domain {$a->host} ist auf der Whitelist für {$a->url}';
$string['checker_link_error_undefined'] = 'Ein undefinierter Fehler beim Link ist aufgetreten';
$string['checker_link_setting_timeout'] = 'cURL Zeitüberschreitung';
$string['checker_link_setting_connect_timeout'] = 'cURL Verbindungs-Timeout';
$string['checker_link_setting_useragent'] = 'User Agent';
$string['checker_link_setting_useragent_help'] = 'User Agent';

$string['checker_link_setting_whitelist'] = 'Link-Checker-Whitelist';
$string['checker_link_setting_whitelist_help'] = 'Bitte fügen Sie eine Url pro Zeile hinzu. Beispiel: "www.google.com". Beachten Sie, dass www.w3.org vorhanden sein muss.';

// String specific for the group checker.
$string['groups_deactivated'] = 'Die Gruppeneinreichungs-Einstellung ist deaktiviert';
$string['groups_idmissing'] = 'GDie Gruppeneinreichung ist aktiv, aber es ist keine Gruppierung festgelegt.';
$string['groups_missing'] = 'Die Gruppierung wurde nicht korrekt eingerichtet';
$string['groups_lessthantwogroups'] = 'Es wurden weniger als 2 Gruppen für die Gruppierung eingerichtet';
$string['groups_success'] = 'Die Gruppeneinreichungs-Einstellung ist richtig eingerichtet';
$string['groups_activity'] = 'Aktivität "{$a->name}"';

// String specific for the activedates checker.
$string['activedates_noactivedates'] = 'Im Abschnitt "Aktivitätsabschluss" sollte es keine aktivierten Termine geben.';
$string['activedates_noactivedatesinactivity'] = 'In der Aktivität {$a->modtype} sollte es keine aktivierten Daten geben, suchen Sie nach {$a->adateissetin}.';

// String specific for the attendance checker.
$string['attendance_missingplugin'] = 'Überspringen des Testfalls, weil das Plugin mod_attendance nicht installiert ist';
$string['attendance_missingattendanceactivity'] = 'Anwesenheits-Check fehlgeschlagen - keine Anwesenheitsaktivität in diesem Kurs';
$string['attendance_onlyoneattendenceactivityallowed'] = 'Anwesenheits-Check fehlgeschlagen - nur eine Anwesenheits-Lerneineheit ist erlaubt';
$string['attendance_sessionsnotemty'] = 'Anwesenheits-Check fehlgeschlagen - es ist nicht erlaubt Lerneinheiten einzufügen';
$string['attendance_success'] = 'Die Anwesenheits-Lerneinheit ist korrekt konfiguriert';

// String specific for the data checker.
$string['data_nofieldsdefined'] = 'Für diese Datenbankaktivität sind keine Felder definiert.';
$string['data_fieldsdefined'] = 'Für diese Datenbankaktivität sind Felder definiert';

// String specific for the subheadings checker.
$string['subheadings_wrongfirsthtmltag'] = 'Der erste html-Tag ist kein {$a->htmltag}';
$string['subheadings_iconmissing'] = 'Das Icon fehlt im ersten html-Tag';
$string['subheadings_generalerror'] = 'Es gab ein Problem bei der Durchführung dieses Checks';
$string['subheadings_success'] = 'Dieses Label hat einen schönen Untertitel und ein Symbol';
$string['subheadings_labelignored'] = 'Dieses Label wird aufgrund der Whitelist in der Plugin-Konfiguration ignoriert.';

$string['checker_subheadings_setting_whitelist'] = 'Whitelist für Untertitel';
$string['checker_subheadings_setting_whitelist_help'] = 'Bitte fügen Sie eine Zeichenfolge pro Zeile hinzu. Beispiel: "Liebe(r) Modulentwickler".';

// String specific for the reference course settings checker.
$string['checker_referencesettings_comparison'] = ' (Referenzkurs: "{$a->settingvaluereference}" | Aktueller Kurs: "{$a->settingvaluecurrent}")';
$string['checker_referencesettings_settingismissing'] = 'Das "{$a->setting}" ist kein Kurssetting';
$string['checker_referencesettings_failing'] = 'Die Einstellung "{$a->setting}" ist nicht korrekt';
$string['checker_referencesettings_success'] = 'Die Einstellung "{$a->setting}" ist korrekt';
$string['checker_referencesettings_checklist'] = 'Checkliste für die Einstellungen des Referenz-Kurs-Checkers';
$string['checker_referencesettings_checklist_help'] = 'Bitte wählen Sie eine oder mehrere Einstellungen zum Vergleich mit dem Referenzkurs aus.';

// String specific for the reference course settings checker filters.
$string['checker_referencefilter_comparison'] = ' (Referenzkurs: "{$a->filtervaluereference}" | Aktueller Kurs: "{$a->filtervaluecurrent}")';
$string['checker_referencefilter_failing'] = 'Der Filter"{$a->filterkey}" ist nicht korrekt';
$string['checker_referencefilter_success'] = 'Alle Filter sind im aktuellen Kurs korrekt eingestellt';
$string['checker_referencefilter_enabled'] = 'Filterprüfung der Referenzeinstellungen ist aktiviert';
$string['checker_referencefilter_enabled_help'] = 'Bitte aktivieren Sie diese Checkbox, um alle Kursfilter mit dem Referenzkurs zu vergleichen..';
$string['checker_referencefilter_filternotsetincurrentcourse'] = 'Der Filter "{$a->filterkey}" fehlt im aktuellen Kurs.';

// String for messageprovider.
$string['messageprovider_allchecks_subject'] = 'Die Checks für den Kurs {$a->coursename} sind abgeschlossen';
$string['messageprovider_allchecks_completed'] = 'Die Checks sind abgeschlossen.';
$string['messageprovider_singlechecks_subject'] = 'Der Check {$a->checkername} ist für den Kurs {$a->coursename} abgeschlossen';
$string['messageprovider_singlechecks_completed'] = 'Der Check {$a->checkername} is komplett.';
$string['messageprovider_result_plain'] = 'Sie sehen das Ergebnis unter {$a->url}.';
$string['messageprovider_result_html'] = 'Sie sehen das Ergebnis unter {$a->urlhtml}';
$string['messageprovider_result_html_label'] = 'auf der Resultatseite';

// Admin component. Please add specific checker settings under the checker section.
$string['admin_restrictedint_min'] = 'Der minimale Wert ist {$a}';
$string['admin_restrictedint_max'] = 'Der maximale Wert ist {$a}';
$string['admin_domain_name_notvalid'] = 'Domainname ist nicht korrekt: {$a}. Bitte fügen Sie nur einen Domainnamen pro Zeile hinzu.';
$string['admin_domain_name_default_missing'] = 'Domainname feht: {$a}';
$string['admin_domain_list_notvalid'] = 'In dieser Liste hat es ungültige Domains';