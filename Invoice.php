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
            $thisAmount = $this->amountFor($play, $perf);
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

    /**
     * @param $play
     * @param $perf
     * @return float|int
     */
    protected function amountFor($play, $perf)
    {
        $result = 0;
        switch ($play['type']) {
            case 'tragedy':
                $result = 40000;
                if ($perf['audience'] > 30) {
                    $result += 1000 * ($perf['audience'] - 30);
                }
                break;
            case 'comedy':
                $result = 30000;
                if ($perf['audience'] > 20) {
                    $result += 10000 + 500 * ($perf['audience'] - 20);
                }
                $result += 300 * $perf['audience'];
                break;
            default:
                throw new Error("unknown type: {$play['type']}");
        }
        return $result;
    }
}