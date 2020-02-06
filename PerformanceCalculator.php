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
        throw new Error('サブクラスの責務');
    }

    public function getPlay()
    {
        return $this->play;
    }

    public function volumeCredits()
    {
        return max($this->performance['audience'] - 30, 0);
    }
}