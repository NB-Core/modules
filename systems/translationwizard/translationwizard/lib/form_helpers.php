<?php
declare(strict_types=1);
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

/**
 * Open a table and optionally output a header row.
 */
function tw_table_open(array $headers = []): void
{
    rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
    if ([] !== $headers) {
        rawoutput("<tr class='trhead'>");
        foreach ($headers as $header) {
            $header = htmlspecialchars((string)$header, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
            rawoutput("<td>{$header}</td>");
        }
        rawoutput('</tr>');
    }
}

/**
 * Output a table row with alternating row classes.
 */
function tw_table_row(array $cells, bool $odd): void
{
    rawoutput("<tr class='" . ($odd ? 'trlight' : 'trdark') . "'>");
    foreach ($cells as $cell) {
        rawoutput("<td>{$cell}</td>");
    }
    rawoutput('</tr>');
}

/**
 * Close a table.
 */
function tw_table_close(): void
{
    rawoutput('</table>');
}


