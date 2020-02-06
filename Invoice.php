<?php


class Invoice
{
    private $invoice;
    private $plays;

    public function __construct($invoice, $plays)
    {
        $this->invoice = $invoice;
        $this->plays = $plays;
    }

    public function statement()
    {
        $statementData = [];
        return $this->renderPlainText($statementData);
    }

    protected function renderPlainText($data)
    {
        $result = "Statement for {$this->invoice['customer']}\n";

        foreach ($this->invoice['performances'] as $perf) {
            $result .= "  {$this->playFor($perf)['name']}: {$this->usd($this->amountFor($perf)/100)} ({$perf['audience']} seats)\n";
        }
        $result .= "Amount owed is {$this->usd($this->totalAmount()/100)}\n";
        $result .= "You earned {$this->totalVolumeCredits()} credits\n";
        return $result;
    }

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

    protected function playFor($aPerformance)
    {
        return $this->plays[$aPerformance['playID']];
    }

    protected function volumeCreditsFor($perf)
    {
        $result = 0;
        $result += max($perf['audience'] - 30, 0);
        if ('comedy' === $this->playFor($perf)['type']) {
            $result += floor($perf['audience'] / 5);
        }
        return $result;
    }

    protected function usd($aNumber)
    {
        return (new NumberFormatter('en_US', NumberFormatter::CURRENCY))->format($aNumber);
    }

    protected function totalVolumeCredits()
    {
        $result = 0;
        foreach ($this->invoice['performances'] as $perf) {
            $result += $this->volumeCreditsFor($perf);
        }
        return $result;
    }

    protected function totalAmount()
    {
        $result = 0;
        foreach ($this->invoice['performances'] as $perf) {
            $result += $this->amountFor($perf);
        }
        return $result;
    }
}