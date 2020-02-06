<?php


class PerformanceCalculator
{
    private $performance;
    private $play;

    public function __construct($aPerformance, $aPlay)
    {
        $this->performance = $aPerformance;
        $this->play = $aPlay;
    }

    public function getPlay()
    {
        return $this->play;
    }
}