<?php

require_once "./PerformanceCalculator.php";

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
        $this->createStatementData();
        return $this->renderPlainText();
    }

    public function htmlStatement()
    {
        $this->createStatementData();
        return $this->renderHtml();
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

    protected function renderHtml()
    {
        $result = "<h1>Statement for {$this->statementData['customer']}</h1>\n";
        $result .= "<table>\n";
        $result .= "  <tr><th>play</th><th>seats</th><th>cost</th></tr>\n";
        foreach ($this->statementData['performances'] as $perf) {
            $result .= "  <tr><td>{$perf['play']['name']}</td><td>{$perf['audience']}</td>";
            $result .= "<td>{$this->usd($perf['amount'])}</td></tr>\n";
        }
        $result .= "</table>\n";
        $result .= "<p>Amount owed is <em>{$this->usd($this->statementData['totalAmount'])}</em></p>\n";
        $result .= "<p>You earned <em>{$this->statementData['totalVolumeCredits']}</em> credits</p>\n";
        return $result;
    }

    protected function amountFor($aPerformance)
    {
        return (new PerformanceCalculator($aPerformance, $this->playFor($aPerformance)))->amount();
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
        return array_reduce(($this->statementData['performances']), fn($total, $p) => $total + $p['volumeCredits'], 0);
    }

    protected function totalAmount($data)
    {
        return array_reduce(($this->statementData['performances']), fn($total, $p) => $total + $p['amount'], 0);
    }

    protected function enrichPerformance($aPerformance)
    {
        $calculator = new PerformanceCalculator($aPerformance, $this->playFor($aPerformance));
        $result = $aPerformance;
        $result['play'] = $calculator->getPlay();
        $result['amount'] = $this->amountFor($result);
        $result['volumeCredits'] = $this->volumeCreditsFor($result);
        return $result;
    }

    protected function createStatementData(): void
    {
        $this->statementData['customer'] = $this->invoice['customer'];
        $this->statementData['performances'] = array_map('Invoice::enrichPerformance', $this->invoice['performances']);
        $this->statementData['totalAmount'] = $this->totalAmount($this->statementData);
        $this->statementData['totalVolumeCredits'] = $this->totalVolumeCredits($this->statementData);
    }
}