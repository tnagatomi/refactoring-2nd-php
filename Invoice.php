<?php


class Invoice
{
    private $invoice;
    private $plays;

    /**
     * Invoice constructor.
     * @param $invoice
     * @param $plays
     */
    public function __construct($invoice, $plays)
    {
        $this->invoice = $invoice;
        $this->plays = $plays;
    }

    /**
     * @return string
     */
    public function statement()
    {
        $totalAmount = 0;
        $result = "Statement for {$this->invoice['customer']}\n";

        foreach ($this->invoice['performances'] as $perf) {
            $result .= "  {$this->playFor($perf)['name']}: {$this->usd()->format($this->amountFor($perf)/100)} ({$perf['audience']} seats)\n";
            $totalAmount += $this->amountFor($perf);
        }
        $result .= "Amount owed is {$this->usd()->format($totalAmount/100)}\n";
        $result .= "You earned {$this->totalVolumeCredits()} credits\n";
        return $result;
    }

    /**
     * @param $aPerformance
     * @return float|int
     */
    protected function amountFor($aPerformance)
    {
        $result = 0;
        switch ($this->playFor($aPerformance)['type']) {
            case 'tragedy':
                $result = 40000;
                if ($aPerformance['audience'] > 30) {
                    $result += 1000 * ($aPerformance['audience'] - 30);
                }
                break;
            case 'comedy':
                $result = 30000;
                if ($aPerformance['audience'] > 20) {
                    $result += 10000 + 500 * ($aPerformance['audience'] - 20);
                }
                $result += 300 * $aPerformance['audience'];
                break;
            default:
                throw new Error("unknown type: {$this->playFor($aPerformance)['type']}");
        }
        return $result;
    }

    /**
     * @param $aPerformance
     * @return mixed
     */
    protected function playFor($aPerformance)
    {
        return $this->plays[$aPerformance['playID']];
    }

    /**
     * @param $perf
     * @return false|float|mixed
     */
    protected function volumeCreditsFor($perf)
    {
        $result = 0;
        $result += max($perf['audience'] - 30, 0);
        if ('comedy' === $this->playFor($perf)['type']) {
            $result += floor($perf['audience'] / 5);
        }
        return $result;
    }

    /**
     * @return NumberFormatter
     */
    protected function usd()
    {
        return new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    }

    /**
     * @return false|float|int|mixed
     */
    protected function totalVolumeCredits()
    {
        $result = 0;
        foreach ($this->invoice['performances'] as $perf) {
            $result += $this->volumeCreditsFor($perf);
        }
        return $result;
    }
}