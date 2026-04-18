<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f4f6f8;margin:0;padding:24px;color:#111} .sheet{max-width:1100px;margin:0 auto;background:#fff;padding:28px;border:1px solid #d9dee5;border-radius:18px} .muted{color:#64748b} table{width:100%;border-collapse:collapse;margin-top:16px} th,td{border:1px solid #dbe2ea;padding:10px 12px;font-size:14px} th{background:#f8fafc;text-align:left} .text-end{text-align:right} .head{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
@media print{body{background:#fff;padding:0}.sheet{max-width:none;margin:0;border:0;border-radius:0;padding:0}}
</style>
</head>
<body onload="window.print()">
<div class="sheet">
  <div class="head">
    <div>
      <h2 style="margin:0 0 6px"><?= e($title) ?></h2>
      <div class="muted">Periode: <?= e($from ? date('d-m-Y', strtotime($from)) : '-') ?> s.d. <?= e($to ? date('d-m-Y', strtotime($to)) : '-') ?></div>
    </div>
    <div class="muted">Dicetak: <?= e(date('d-m-Y H:i')) ?></div>
  </div>
  <table>
    <thead><tr><?php foreach ($headers as $head): ?><th><?= e($head) ?></th><?php endforeach; ?></tr></thead>
    <tbody>
      <?php foreach ($rows as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= e($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="<?= count($headers) ?>" class="text-center muted">Tidak ada data.</td></tr><?php endif; ?>
    </tbody>
  </table>
  <?php if (!empty($summary) && is_array($summary)): ?>
    <table style="margin-top:14px">
      <tbody>
      <?php foreach ($summary as $label => $value): ?>
        <tr>
          <th style="width:280px"><?= e((string)$label) ?></th>
          <td class="text-end"><?= e((string)$value) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
