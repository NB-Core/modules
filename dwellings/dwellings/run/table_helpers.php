<?php

/**
 * Shared table rendering helpers for Dwellings list-style pages.
 */

/**
 * Loads DataTables assets and shared, minimal styling used by dwellings list tables.
 *
 * The stylesheet is added once per page request even if multiple tables are rendered.
 */
function dwellings_require_datatable_assets()
{
    static $assetsLoaded = false;

    if ($assetsLoaded) {
        return;
    }

    if (!class_exists('\\Lotgd\\Output')
        || !method_exists('\\Lotgd\\Output', 'requireVendorAsset')
        || !method_exists('\\Lotgd\\Output', 'addHeadMarkup')
    ) {
        // Legacy LotGD cores may not expose the modern Output asset helpers.
        // Leave the semantic table markup in place and skip DataTables enhancement.
        $assetsLoaded = true;
        return;
    }

    \Lotgd\Output::requireVendorAsset('jquery', 'js', \Lotgd\Output::VENDOR_BUCKET_MID);
    \Lotgd\Output::requireVendorAsset('datatables', 'css', \Lotgd\Output::VENDOR_BUCKET_PRE);
    \Lotgd\Output::requireVendorAsset('datatables', 'js', \Lotgd\Output::VENDOR_BUCKET_MID);

    \Lotgd\Output::addHeadMarkup("<style>
    .dwellings-datatable-wrapper {
        margin: 0.75rem auto;
        width: 100%;
    }
    .dwellings-datatable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .dwellings-datatable thead th {
        padding: 0.55rem 0.65rem;
        text-align: left;
        border-bottom: 2px solid ButtonBorder;
    }
    .dwellings-datatable tbody td {
        padding: 0.5rem 0.65rem;
        border-bottom: 1px solid color-mix(in srgb, CanvasText 20%, transparent);
    }
    /*
     * DataTables owns row striping because it redraws rows while sorting,
     * searching, and paging. Legacy server-side trlight/trdark output drifts
     * from visible row order after client-side interactions.
     */
    .dwellings-datatable-wrapper .dwellings-datatable tbody tr.trlight td {
        background-color: color-mix(in srgb, Canvas 94%, CanvasText 6%);
        color: #000;
    }
    .dwellings-datatable-wrapper .dwellings-datatable tbody tr.trdark td {
        background-color: color-mix(in srgb, Canvas 86%, CanvasText 14%);
        color: #000;
    }
    .dwellings-datatable .is-actions {
        white-space: nowrap;
    }
    .dwellings-datatable-wrapper .dataTables_length select,
    .dwellings-datatable-wrapper .dataTables_filter input {
        color: CanvasText;
        background-color: Canvas;
        border-color: ButtonBorder;
        color-scheme: light dark;
    }
    div.dt-container .dt-input {
        color: #000;
        background-color: color-mix(in srgb, Canvas 86%, CanvasText 14%);
    }
    </style>");

    $assetsLoaded = true;
}

/**
 * Starts a semantic DataTables-compatible table with translated headers.
 *
 * @param string $tableId Stable DOM id used by DataTables init.
 * @param array<int, string> $headers Already translated table header labels.
 */
function dwellings_render_datatable_open($tableId, array $headers)
{
    rawoutput("<div class='dwellings-datatable-wrapper'>");
    rawoutput("<table id='" . htmlspecialchars($tableId, ENT_QUOTES, getsetting('charset', 'UTF-8')) . "' class='dwellings-datatable'>");
    rawoutput("<thead><tr class='trhead'>");
    foreach ($headers as $header) {
        rawoutput('<th>' . $header . '</th>');
    }
    rawoutput("</tr></thead><tbody>");
}

/**
 * Ends a DataTables table and initializes client-side sorting/search/pagination.
 *
 * Striping is configured here (instead of per-renderer row classes) so every
 * dwellings list table keeps alternating parity after DataTables sort/filter/page redraws.
 * The callback is intentionally shared here so all dwellings DataTables stay
 * consistent without relying on PHP loop parity that can drift after redraws.
 *
 * @param string $tableId Stable DOM id used by DataTables init.
 * @param array<string, mixed> $options DataTables options encoded as JSON.
 */
function dwellings_render_datatable_close($tableId, array $options = [])
{
    rawoutput('</tbody></table></div>');

    $defaultOptions = [
        'paging' => true,
        'searching' => true,
        'lengthChange' => true,
        'autoWidth' => false,
        'pageLength' => 25,
        'lengthMenu' => [10, 25, 50, 100, -1],
        // Keep striping in DataTables so redraw operations always preserve parity.
        'stripeClasses' => ['trlight', 'trdark'],
        'language' => [
            'emptyTable' => translate_inline('None'),
        ],
    ];

    $config = array_merge($defaultOptions, $options);

    if (!class_exists('\\Lotgd\\Output')
        || !method_exists('\\Lotgd\\Output', 'requireVendorAsset')
        || !method_exists('\\Lotgd\\Output', 'addHeadMarkup')
    ) {
        // Without the modern asset loader, render a plain HTML table and skip JS initialization.
        return;
    }

    $idJson = json_encode('#' . $tableId);
    $configJson = json_encode($config);

    // DataTables manages list sorting/filtering/pagination in the browser for these static rows.
    // Re-apply striping in drawCallback so only currently visible rows receive alternating classes.
    rawoutput("<script>\n"
        . "jQuery(function ($) {\n"
        . "  if (!$.fn.DataTable) { return; }\n"
        . "  var tableSelector = " . $idJson . ";\n"
        . "  var config = " . $configJson . ";\n"
        . "  var priorDrawCallback = config.drawCallback;\n"
        . "  config.drawCallback = function (settings) {\n"
        . "    var api = new $.fn.dataTable.Api(settings);\n"
        . "    var rows = api.rows({ page: 'current', search: 'applied', order: 'applied' }).nodes();\n"
        . "    $(rows)\n"
        . "      .removeClass('trlight trdark')\n"
        . "      .each(function (index) {\n"
        . "        $(this).addClass((index % 2 === 0) ? 'trlight' : 'trdark');\n"
        . "      });\n"
        . "    if (typeof priorDrawCallback === 'function') {\n"
        . "      priorDrawCallback.call(this, settings);\n"
        . "    }\n"
        . "  };\n"
        . "  $(tableSelector).DataTable(config);\n"
        . "});\n"
        . "</script>");
}
