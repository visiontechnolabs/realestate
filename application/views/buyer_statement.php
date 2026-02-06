<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Party Statement</title>

<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #000;
    }

    .header {
        text-align: center;
        margin-bottom: 10px;
    }

    .company-name {
        font-size: 18px;
        font-weight: bold;
    }

    .company-info {
        font-size: 11px;
        margin-top: 3px;
    }

    .title {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        text-decoration: underline;
        margin: 20px 0;
    }

    .party-name {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .info-table td {
        padding: 4px;
        vertical-align: top;
    }

    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .ledger-table th,
    .ledger-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 11px;
    }

    .ledger-table th {
        background-color: #eaeaea;
        text-align: center;
        font-weight: bold;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .total-row {
        font-weight: bold;
        background-color: #f0f0f0;
    }
</style>
</head>

<body>

<!-- ================= HEADER ================= -->
<div class="header">
    <?php if (!empty($user->profile_image)) { ?>
        <img src="<?= base_url($user->profile_image) ?>" height="50"><br>
    <?php } ?>

    <div class="company-name"><?= strtoupper($user->business_name ?? '-') ?></div>

    <div class="company-info">
        Phone: <?= $user->mobile ?? '-' ?> |
        Email: <?= $user->email ?? '-' ?>
    </div>
</div>

<hr>

<!-- ================= TITLE ================= -->
<div class="title">Party Statement</div>

<!-- ================= PARTY DETAILS ================= -->
<div class="party-name">
    Party name: <?= strtoupper($buyer->name ?? '-') ?>
</div>

<table class="info-table">
    <tr>
        <td width="15%">Contact No:</td>
        <td width="35%"><?= $buyer->mobile ?? '-' ?></td>

        
    </tr>

    <tr>
        <td>Address:</td>
        <td colspan="3"><?= $buyer->address ?? '-' ?></td>
    </tr>

   <tr>
    <td>Duration:</td>
    <td colspan="3">
        <?php
        $from = '-';
        $to   = '-';

        if (!empty($logs)) {
            $from = date('d/m/Y', strtotime($logs[0]->created_on));
            $to   = date('d/m/Y', strtotime($logs[count($logs)-1]->created_on));
        }
        ?>
        From <?= $from ?> to <?= $to ?>
    </td>
</tr>

</table>

<!-- ================= LEDGER ================= -->
<table class="ledger-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Txn Type</th>
            <th>Debit (₹)</th>
            <th>Credit (₹)</th>
            <th>Running Balance</th>
        </tr>
    </thead>

    <tbody>
    <?php
    $total_debit  = 0;
    $total_credit = 0;

    // ✅ Opening Balance (Plot Price)
    $opening_balance = !empty($logs) ? (float)$logs[0]->total_price : 0;

    $running_balance = $opening_balance;
    $total_debit     = $opening_balance;
    ?>

    <!-- 🔹 OPENING / RECEIVABLE ROW -->
    <tr>
        <td>
            <?= !empty($logs) ? date('d/m/Y', strtotime($logs[0]->created_on)) : '-' ?>
        </td>

        <td>Receivable Beginning Balance</td>

        <td class="text-right">
            ₹ <?= number_format($opening_balance, 2) ?>
        </td>

        <td class="text-right">-</td>

        <td class="text-right">
            ₹ <?= number_format($running_balance, 2) ?> (Dr)
        </td>
    </tr>

    <!-- 🔹 PAYMENT ROWS -->
    <?php foreach ($logs as $log) {

        $credit = (float) ($log->paid_amount ?? 0);

        if ($credit <= 0) {
            continue;
        }

        $running_balance -= $credit;
        $total_credit    += $credit;
    ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($log->created_on)) ?></td>

            <td>Payment</td>

            <td class="text-right">-</td>

            <td class="text-right">
                ₹ <?= number_format($credit, 2) ?>
            </td>

            <td class="text-right">
                ₹ <?= number_format(abs($running_balance), 2) ?>
                <?= $running_balance >= 0 ? '(Dr)' : '(Cr)' ?>
            </td>
        </tr>
    <?php } ?>

    <!-- 🔹 TOTAL ROW -->
    <tr class="total-row">
        <td colspan="2" class="text-right"><strong>Total</strong></td>

        <td class="text-right">
            <strong>₹ <?= number_format($total_debit, 2) ?></strong>
        </td>

        <td class="text-right">
            <strong>₹ <?= number_format($total_credit, 2) ?></strong>
        </td>

        <td class="text-right">
            <strong>
                ₹ <?= number_format(abs($running_balance), 2) ?>
                <?= $running_balance >= 0 ? '(Dr)' : '(Cr)' ?>
            </strong>
        </td>
    </tr>

    </tbody>
</table>




</body>
</html>
