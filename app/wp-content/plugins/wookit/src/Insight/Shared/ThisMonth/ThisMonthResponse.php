<?php

namespace WooKit\Insight\Shared\ThisMonth;

use WooKit\Insight\Shared\Response\ReportResponse;
use function time;

class ThisMonthResponse extends ReportResponse
{
    private string $dateFormat = 'Y-m-d';
    private int    $sum        = 0;

    public function parseData(): array
    {
        return [
            'timeline' => $this->getTimeline(
                date('m', time()),
                date('Y', time())
            ),
            'summary'  => $this->sum
        ];
    }

    protected function getTimeline($month, $year): array
    {
        $aWeekInMonth = [];
        $week = date('W', strtotime($year . '-' . $month . '-01')); // weeknumber of first day of month

        $aDateRangeInWeek = [
            'from' => (string)strtotime(date($this->dateFormat, strtotime($year . '-' . $month . '-01')))
        ];

        $unix = strtotime($year . 'W' . $week . '+1 week');
        while (date('m', $unix) == $month) {
            $aDateRangeInWeek['to'] = (string)strtotime(date($this->dateFormat, $unix - 86400)); // Chu nhat tuan trc

            if (isset($aDateRangeInWeek['from']) && isset($aDateRangeInWeek['to'])) {
                $weekNumber = date('W', $unix - 1); // Xac dinh tuan hien tai
                $aDateRangeInWeek['summary'] = $this->foundStatisticDataInWeek($weekNumber);

                $aWeekInMonth[] = $aDateRangeInWeek;
                $aDateRangeInWeek = [];
            }

            $aDateRangeInWeek['from'] = (string)strtotime(date($this->dateFormat, $unix));

            $unix = $unix + (86400 * 7);
        }

        $aDateRangeInWeek['to'] = (string)strtotime(date(
            $this->dateFormat,
            $unix = strtotime('last day of ' . $year . "-" . $month)
        ));

        $aDateRangeInWeek['summary'] = $this->foundStatisticDataInWeek(date('W', $unix));
        $aWeekInMonth[] = $aDateRangeInWeek;

        return $aWeekInMonth;
    }

    private function foundStatisticDataInWeek($weekNumber): int
    {
        if (empty($this->aRawData)) {
            return 0;
        }

        foreach ($this->aRawData as $order => $aWeekReport) {
            if (isset($aWeekReport['weekNumber']) && $aWeekReport['weekNumber'] == $weekNumber) {
                unset($this->aRawData[$order]);

                $this->calculateSum($aWeekReport['summary']);

                return $aWeekReport['summary'];
            }
        }

        return 0;
    }

    private function calculateSum(int $weeklySum)
    {
        $this->sum = $this->sum + $weeklySum;
    }
}
