<?php


class PerformanceCalculator
{
    protected $performance;
    protected $play;

    public function __construct($aPerformance, $aPlay)
    {
        $this->performance = $aPerformance;
        $this->play = $aPlay;
    }

    public function amount()
    {
        $result = 0;
        switch ($this->play['type']) {
            case 'tragedy':
                $result = 40000;
                if ($this->performance['audience'] > 30) {
                    $result += 1000 * ($this->performance['audience'] - 30);
                }
                break;
            case 'comedy':
                $result = 30000;
                if ($this->performance['audience'] > 20) {
                    $result += 10000 + 500 * ($this->performance['audience'] - 20);
                }
                $result += 300 * $this->performance['audience'];
                break;
            default:
                throw new Error("unknown type: {$this->play['type']}");
        }
        return $result;
    }

    public function getPlay()
    {
        return $this->play;
    }

    public function volumeCredits()
    {
        $result = 0;
        $result += max($this->performance['audience'] - 30, 0);
        if ('comedy' === $this->play['type']) {
            $result += floor($this->performance['audience'] / 5);
        }
        return $result;
    }
}