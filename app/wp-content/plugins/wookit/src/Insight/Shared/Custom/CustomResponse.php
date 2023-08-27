<?php


namespace WooKit\Insight\Shared\Custom;


use WooKit\Insight\Shared\Response\ReportResponse;
use WooKit\Insight\Shared\TraitCheckCustomDateInMonth;

class CustomResponse extends ReportResponse
{
    use TraitCheckCustomDateInMonth;

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
        $aTimeline = [];
        if ($this->checkCustomDateInMonth($this->aAdditional['from'], $this->aAdditional['to'])) {
            $unixTime = strtotime($this->aAdditional['from']);
            $condition = date_format(date_create($this->aAdditional['to']), 'd') -
                date_format(date_create($this->aAdditional['from']), 'd');
            if ($condition == 0) {
                $this->foundStatisticDataCustom(($this->aAdditional['to']));
                return [];
            }
            for ($i = 0; $i <= $condition; $i++) {
                $date = date($this->dateFormat, $unixTime);
                $aTimeline[] = [
                    'from'    => (string)strtotime($date),
                    'to'      => (string)strtotime($date),
                    'summary' => $this->foundStatisticDataCustom($date)
                ];
                $unixTime = $unixTime + 86400; // next day
            }
        } else {
            $condition = (int)date_format(date_create($this->aAdditional['to']), 'm') - date_format(date_create
                ($this->aAdditional['from']), 'm');
            for ($i = 0; $i <= $condition; $i++) {
                $month = (int)date_format(date_create($this->aAdditional['from']), 'm') + $i;
                $from = ($i == 0) ? $this->aAdditional['from'] :
                    date_format(date_create(date('Y-' . $month . '-d')), 'Y-m-01');
                $to = ($i == $condition) ? $this->aAdditional['to'] :
                    date_format(date_create(date('Y-' . $month . '-d')), 'Y-m-t');
                $aTimeline[] = [
                    'from'    => (string)strtotime($from),
                    'to'      => (string)strtotime($to),
                    'summary' => $this->foundStatisticDataCustom($month)
                ];
            }
        }

        return $aTimeline;
    }

    private function foundStatisticDataCustom($date): int
    {
        if (empty($this->aRawData)) {
            return 0;
        }

        foreach ($this->aRawData as $order => $aDailyReport) {
            if (isset($aDailyReport['date'])) {
                if (($aDailyReport['date'] == $date)) {
                    unset($this->aRawData[$order]);

                    $this->calculateSum($aDailyReport['summary']);

                    return $aDailyReport['summary'];
                }
            } else {
                if (($aDailyReport['month'] == $date) && isset($aDailyReport['month'])) {
                    unset($this->aRawData[$order]);

                    $this->calculateSum($aDailyReport['summary']);

                    return $aDailyReport['summary'];
                }
            }
        }

        return 0;
    }

    private function calculateSum(int $dailySum): void
    {
        $this->sum = $this->sum + $dailySum;
    }
}

