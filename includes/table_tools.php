<?php
// Reusable helpers for table search/filter UI and building SQL filters
function render_table_tools($entity, $current_q = '', $from = '', $to = '') {
    $entityEsc = htmlspecialchars($entity);
    $qEsc = htmlspecialchars($current_q);
    $fromEsc = htmlspecialchars($from);
    $toEsc = htmlspecialchars($to);
    echo '<form method="GET" class="mb-4" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <input type="hidden" name="entity" value="'. $entityEsc .'">
        <input type="text" name="q" value="'. $qEsc .'" placeholder="Cari nama, alat, atau ID" style="padding:8px;border:1px solid #ccc;border-radius:6px;">
        <label style="font-size:13px;">Dari <input type="date" name="from" value="'. $fromEsc .'" style="margin-left:6px;padding:6px;border:1px solid #ccc;border-radius:6px;"></label>
        <label style="font-size:13px;">Sampai <input type="date" name="to" value="'. $toEsc .'" style="margin-left:6px;padding:6px;border:1px solid #ccc;border-radius:6px;"></label>
        <button type="submit" class="btn btn-primary" style="padding:8px 12px;border-radius:6px;">Filter</button>
        <a href="export.php?entity='. $entityEsc .'&format=csv&q='. urlencode($current_q) .'&from='. urlencode($from) .'&to='. urlencode($to) .'" class="btn" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;background:#fff;text-decoration:none;">Export CSV</a>
        <a href="export.php?entity='. $entityEsc .'&format=pdf&q='. urlencode($current_q) .'&from='. urlencode($from) .'&to='. urlencode($to) .'" class="btn" style="padding:8px 12px;border:1px solid #ccc;border-radius:6px;background:#fff;text-decoration:none;">Export PDF</a>
    </form>';
}

function build_filter_sql_and_params($entity, $params) {
    // returns array: [where_clause_string, types_string, params_array]
    $where = [];
    $types = '';
    $values = [];

    if (!empty($params['q'])) {
        // search on user name or alat name
        $where[] = "(u.nama LIKE CONCAT('%', ?, '%') OR a.nama_alat LIKE CONCAT('%', ?, '%'))";
        $types .= 'ss';
        $values[] = $params['q'];
        $values[] = $params['q'];
    }

    if (!empty($params['from'])) {
        $where[] = "pk.tanggal_dikembalikan >= ?";
        $types .= 's';
        $values[] = $params['from'];
    }
    if (!empty($params['to'])) {
        $where[] = "pk.tanggal_dikembalikan <= ?";
        $types .= 's';
        $values[] = $params['to'];
    }

    $where_sql = '';
    if (count($where) > 0) {
        $where_sql = 'WHERE ' . implode(' AND ', $where);
    }

    return [$where_sql, $types, $values];
}

?>
