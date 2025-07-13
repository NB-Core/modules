<?php
/**
 * Helper functions for Translation Wizard forms.
 */

function tw_form_open(string $action = '', array $hidden = [], string $method = 'POST'): void
{
    $action = htmlspecialchars($action, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
    $url = "runmodule.php?module=translationwizard";
    if ('' !== $action) {
        $url .= "&op={$action}";
    }
    rawoutput("<form action='{$url}' method='{$method}' class='wizard-form'>");
    foreach ($hidden as $name => $value) {
        $name = htmlspecialchars($name, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
        $value = htmlspecialchars((string)$value, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
        rawoutput("<input type='hidden' name='{$name}' value='{$value}'>");
    }
}

function tw_form_close(?string $label = null): void
{
    if (null !== $label && '' !== $label) {
        $label = htmlspecialchars($label, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
        rawoutput("<input type='submit' value='{$label}' class='button wizard-button'>");
    }
    rawoutput('</form>');
}


