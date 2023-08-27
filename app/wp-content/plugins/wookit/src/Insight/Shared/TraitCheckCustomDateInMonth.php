<?php


namespace WooKit\Insight\Shared;


trait TraitCheckCustomDateInMonth
{
    private string $defineFilter = 'today';

    public function checkCustomDateInMonth($from, $to): bool
    {
        $fromDate = new \DateTime($from);
        $toDate = new \DateTime($to);
        if (date_format($fromDate, 'm') == date_format($toDate, 'm')) {
            return true;
        } else {
            return false;
        }
    }
}
