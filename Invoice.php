<?php


class Invoice
{
    private $invoice;
    private $plays;
    private array $statementData;

    public function __construct($invoice, $plays)
    {
        $this->invoice = $invoice;
        $this->plays = $plays;
    }

    public function statement()
    {
        $this->statementData['customer'] = $this->invoice['customer'];
        $this->statementData['performances'] = array_map('Invoice::enrichPerformance', $this->invoice['performances']);
        $this->statementData['totalAmount'] = $this->totalAmount($this->statementData);
        $this->statementData['totalVolumeCredits'] = $this->totalVolumeCredits($this->statementData);

        return $this->renderPlainText();
    }

    protected function renderPlainText()
    {
        $result = "Statement for {$this->statementData['customer']}\n";

        foreach ($this->statementData['performances'] as $perf) {
            $result .= "  {$perf['play']['name']}: {$this->usd($perf['amount'])} ({$perf['audience']} seats)\n";
        }
        $result .= "Amount owed is {$this->usd($this->statementData['totalAmount'])}\n";
        $result .= "You earned {$this->statementData['totalVolumeCredits']} credits\n";
        return $result;
    }

    protected function amountFor($aPerformance)
    {
        $result = 0;
        switch ($aPerformance['play']['type']) {
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
        if ('comedy' === $perf['play']['type']) {
            $result += floor($perf['audience'] / 5);
        }
        return $result;
    }

    protected function usd($aNumber)
    {
        return (new NumberFormatter('en_US', NumberFormatter::CURRENCY))->format($aNumber / 100);
    }

    protected function totalVolumeCredits($data)
    {
        $result = 0;
        foreach ($this->statementData['performances'] as $perf) {
            $result += $perf['volumeCredits'];
        }
        return $result;
    }

    protected function totalAmount($data)
    {
        return array_reduce(($this->statementData['performances']), fn($total, $p) => $total + $p['amount'], 0);
    }

    protected function enrichPerformance($aPerformance)
    {
        $result = $aPerformance;
        $result['play'] = $this->playFor($result);
        $result['amount'] = $this->amountFor($result);
        $result['volumeCredits'] = $this->volumeCreditsFor($result);
        return $result;
    }
}