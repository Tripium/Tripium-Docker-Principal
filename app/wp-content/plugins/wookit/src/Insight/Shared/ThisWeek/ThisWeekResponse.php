<?php


namespace WooKit\Insight\Shared\ThisWeek;


use WooKit\Insight\Shared\Response\ReportResponse;

class ThisWeekResponse extends ReportResponse
{
    private string $dateFormat = 'Y-m-d';
    private int    $sum        = 0;

    public function parseData(): array
    {
        return [
            'timeline' => $this->getTimeline(),
            'summary'  => $this->sum
        ];
    }

    private function getTimeline(): array
    {
        $unixTime = strtotime("this week");

        $aTimeline = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = date($this->dateFormat, $unixTime);

            $aTimeline[] = [
                'from'    => (string)strtotime($date),
                'to'      => (string)strtotime($date),
                'summary' => $this->foundStatisticDataInWeek($date)
            ];
            $unixTime = $unixTime + 86400; // next day
        }

        return $aTimeline;
    }

    private function foundStatisticDataInWeek($date): int
    {
        if (empty($this->aRawData)) {
            return 0;
        }

        foreach ($this->aRawData as $order => $aDailyReport) {
            if ($aDailyReport['date'] == $date) {
                unset($this->aRawData[$order]);

                $this->calculateSum($aDailyReport['summary']);

                return $aDailyReport['summary'];
            }
        }

        return 0;
    }

    private function calculateSum(int $dailySum): void
    {
        $this->sum = $this->sum + $dailySum;
    }
}
