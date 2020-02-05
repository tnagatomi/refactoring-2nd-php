<?php


class Invoice
{
    public function statement($invoice, $plays)
    {
        $totalAmount = 0;
        $volumeCredits = 0;
        $result = "Statement for {$invoice['customer']}\n";
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);

        foreach ($invoice['performances'] as $perf) {
            $play = $plays[$perf['playID']];
            $thisAmount = 0;

            switch ($play['type']) {
                case 'tragedy':
                    $thisAmount = 40000;
                    if ($perf['audience'] > 30) {
                        $thisAmount += 1000 * ($perf['audience'] - 30);
                    }
                    break;
                case 'comedy':
                    $thisAmount = 30000;
                    if ($perf['audience'] > 20) {
                        $thisAmount += 10000 + 500 * ($perf['audience'] - 20);
                    }
                    $thisAmount += 300 * $perf['audience'];
                    break;
                default:
                    throw new Error("unknown type: {$play['type']}");
            }
            $volumeCredits += max($perf['audience'] - 30, 0);
            if ('comedy' === $play['type']) {
                $volumeCredits += floor($perf['audience'] / 5);
            }
            $result .= "  {$play['name']}: {$formatter->format($thisAmount/100)} ({$perf['audience']} seats)\n";
            $totalAmount += $thisAmount;
        }
        $result .= "Amount owed is {$formatter->format($totalAmount/100)}\n";
        $result .= "You earned {$volumeCredits} credits\n";
        return $result;
    }
}