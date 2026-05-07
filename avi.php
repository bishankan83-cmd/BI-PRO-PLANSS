<?php
// index.php — Educational Aviator predictor (extended version)
// WARNING: This tool cannot predict real Aviator outcomes. It is for simulation only.

/**
 * Parses a string of multipliers into an array of floats.
 * Handles commas, spaces, newlines, semicolons as separators, and removes 'x'.
 * @param string $text The raw input string.
 * @return array An array of numeric multiplier values.
 */
function parse_input($text) {
    $text = str_replace(["\r", "\n", ";"], ",", $text);
    $parts = preg_split('/[, ]+/', trim($text));
    $vals = [];
    foreach ($parts as $p) {
        if ($p === '') continue;
        $p = str_ireplace('x', '', $p); // Remove 'x' (case-insensitive)
        if (is_numeric($p)) $vals[] = floatval($p);
    }
    return $vals;
}

/** Calculates the arithmetic mean (average) of an array of numbers. */
function mean($a) { return count($a) ? array_sum($a)/count($a) : 0; }

/** Calculates the sample standard deviation (N-1 method). */
function stdev($a) {
    $n = count($a);
    if ($n < 2) return 0;
    $m = mean($a);
    $sum = 0;
    foreach ($a as $v) $sum += ($v - $m) * ($v - $m);
    return sqrt($sum / ($n - 1));
}

/**
 * Calculates the specified percentile of an array of numbers.
 * @param array $a The input array.
 * @param int $p The percentile (e.g., 25, 50, 75).
 * @return float The calculated percentile value.
 */
function percentile($a, $p) {
    if (!$a) return 0;
    sort($a);
    $count = count($a);
    $idx = ($p/100) * ($count - 1);
    $lo = floor($idx);
    $hi = ceil($idx);
    if ($lo == $hi) return $a[$lo];
    // Linear interpolation
    return $a[$lo] + ($a[$hi]-$a[$lo]) * ($idx - $lo);
}

$results = null;
$predictions = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vals = parse_input($_POST['past'] ?? '');
    if (count($vals) >= 3) {
        $count = count($vals);
        $avg = round(mean($vals), 3);
        $sd = round(stdev($vals), 3);
        $p25 = round(percentile($vals, 25), 3);
        $p50 = round(percentile($vals, 50), 3);
        $p75 = round(percentile($vals, 75), 3);

        // --- Weighted Sampling Setup ---
        // Weights recent values more heavily (linear increase from 1 to 3)
        $weights = [];
        $total_weight = 0;
        for ($i=0; $i<$count; $i++) {
            // Newer results (larger $i$) get a larger weight.
            $w = 1 + ($i / max(1, $count-1)) * 2.0;
            $weights[] = $w;
            $total_weight += $w;
        }
        // Calculate cumulative weights for roulette wheel selection
        $cum = [];
        $c = 0;
        for ($i=0; $i<$count; $i++) { $c += $weights[$i]; $cum[] = $c; }

        // Generate 5 predictions
        for ($j=0; $j<5; $j++) {
            // 1. Weighted History Sample
            $r = mt_rand() / mt_getrandmax() * $total_weight;
            $sample_idx = 0;
            while ($sample_idx < $count && $r > $cum[$sample_idx]) $sample_idx++;
            if ($sample_idx >= $count) $sample_idx = $count - 1;
            $sample_val = $vals[$sample_idx];

            // 2. Log-Normal Estimate
            // Transform data to log space
            $logvals = array_map(fn($v)=>log(max(0.0001,$v)), $vals);
            $logmean = mean($logvals);
            $logsd = stdev($logvals) ?: 0.5; // Default to 0.5 if stdev is zero
            // Generate a random number from a standard uniform distribution (-1 to 1)
            $z = (mt_rand()/mt_getrandmax()) * 2 - 1;
            // Transform back to log-normal distribution
            $logsample = $logmean + $z * $logsd;
            $log_val = exp($logsample); // Transform back to normal space

            $predictions[] = [
                'round' => $j + 1,
                'weighted' => round($sample_val, 3),
                'lognormal' => round($log_val, 3)
            ];
        }

        $results = [
            'avg' => $avg, 'sd' => $sd,
            'p25'=>$p25, 'p50'=>$p50, 'p75'=>$p75,
            'count'=>$count
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Aviator Odds Predictor (Educational)</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;max-width:900px;margin:20px auto;padding:18px;background:#fafafa;}
textarea{width:100%;height:100px;padding:8px;font-size:14px;}
.btn{padding:8px 14px;border:none;border-radius:5px;background:#007BFF;color:#fff;cursor:pointer;}
table{border-collapse:collapse;width:100%;margin-top:15px;}
th,td{border:1px solid #ddd;padding:8px;text-align:center;}
th{background:#007BFF;color:white;}
/* Labeling Logic CSS */
.pred-good{color:green;font-weight:bold;} /* Multiplier >= 2x */
.pred-risk{color:red;font-weight:bold;} /* Multiplier < 2x */
</style>
</head>
<body>
<h2>🎯 Aviator “Next Odds Predictor” (Simulation Tool)</h2>
<p>Enter previous multipliers to generate <strong>5 next predicted rounds</strong> (simulation only).</p>

<form method="post">
<textarea name="past" placeholder="e.g. 1.23, 2.10, 1.01, 3.45, 5.20"><?= isset($_POST['past']) ? htmlspecialchars($_POST['past']) : "1.02, 1.30, 3.5, 1.01, 2.10, 1.05, 4.00, 1.01" ?></textarea><br>
<button class="btn" type="submit">Predict Next</button>
</form>

<?php if ($results): ?>
  <h3>📊 Summary (from <?= $results['count'] ?> rounds)</h3>
  <ul>
    <li>Average: <?= $results['avg'] ?></li>
    <li>Std Dev: <?= $results['sd'] ?></li>
    <li>25%: <?= $results['p25'] ?> | 50% (Median): <?= $results['p50'] ?> | 75%: <?= $results['p75'] ?></li>
  </ul>

  <h3>🔮 Next 5 Predicted Multipliers (Simulation)</h3>
  <table>
    <tr><th>Round</th><th>Weighted History Sample</th><th>Log-normal Estimate</th></tr>
    <?php foreach($predictions as $p): ?>
      <tr>
        <td><?= $p['round'] ?></td>
        <td class="<?= $p['weighted']>=2 ? 'pred-good':'pred-risk' ?>"><?= $p['weighted'] ?>x</td>
        <td class="<?= $p['lognormal']>=2 ? 'pred-good':'pred-risk' ?>"><?= $p['lognormal'] ?>x</td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><small>🧠 <strong>Disclaimer:</strong> This is not a real predictor. Real Aviator games use server-side random algorithms that cannot be predicted. This demo only shows how statistical modeling might simulate probabilities.</small></p>
<?php endif; ?>
</body>
</html>