<?php


namespace WooKit\Shared;


class Time
{
    public static function coverTimestamp($date)
    {
        return strtotime($date);
    }
    public static function coverResponseDate($aDate):array{
        $aData = [];
        $i = 1;
        $date = '';
        if (isset($aDate['type'])) {
            $date = ($aDate['type'] == 'lastMonth') ? date('Y-0' . (date('m') - 1) . '-01') : date('Y-m-01');
            unset($aDate['type']);
        }
        if (isset($aDate['custom'])) {
            unset($aDate['custom']);
            foreach ($aDate as $item) {
                $aData[date_format(date_create(date('Y-0' . $item['date'] . '-01')), 'Y-m-01')] = $item['sum'];
            }
        } else {
            foreach ($aDate as $item) {
                if (isset($item['year']) && !empty($item['year'])) {
                    if (($i == 1) && (in_array(date_format(date_isodate_set(date_create(), $item['year'], $item['date']), 'd'),['24','25','26','27','28','29','30','31','1','2','3','4','5']))) {
                        $aData[$date] = $item['sum'];
                    } else {
                        $aData[date_format(date_isodate_set(date_create(), $item['year'], $item['date']), 'Y-m-d')]
                            = $item['sum'];
                    }
                } else {
                    $aData[$item['date']] = $item['sum'];
                }
                $i++;
            }
        }
        return $aData;
    }
    public static function weekOfMonth($lastDateOfMonth): int
    {
        $lastDateOfMonth=strtotime($lastDateOfMonth);
        //Get the first day of the month.
        $firstOfMonth = strtotime(date("Y-m-01", $lastDateOfMonth));
        //Apply above formula.
        return self::weekOfYear($lastDateOfMonth) - self::weekOfYear($firstOfMonth) + 1;
    }
    public static function weekOfYear($date): int
    {
        $weekOfYear = intval(date("W", $date));
        if (date('n', $date) == "1" && $weekOfYear > 51) {
            // It's the last week of the previos year.
            $weekOfYear = 0;
        }
        return $weekOfYear;
    }
    public static function checkCustomDateInMonth($star, $end): bool
    {
        $starDate = new \DateTime($star);
        $endDate = new \DateTime($end);
        if (date_format($starDate, 'm') == date_format($endDate, 'm')) {
            return true;
        } else {
            return false;
        }
    }
}
