<?php
namespace App\Service;

use Symfony\Component\Validator\Constraints\DateTime;

class TranscribeService
{
    /** @var \stdClass $transcription */
    private $transcription;

    /**
     * TranscribeService constructor.
     * @param array $transcription
     */
    public function __construct($transcription)
    {
        $this->transcription = $transcription;
    }

    /**
     * Returns files in the specified format.
     * @param array $subtitles
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getSubtitleFile($subtitles, $format)
    {
        if (strtolower($format) === 'subrip') {
            return $this->getSubRip($subtitles);
        }

        throw new \Exception('A subtitle format is required');
    }

    /**
     * Returns subtitles in SubRip format
     * @param array $subs
     * @return string $result
     */
    private function getSubRip($subs)
    {
        $result = [];
        $count = 1;

        foreach($subs as $line) {
            array_push($result, $count);
            $dateTime = new \DateTime('2000-01-01 00:00:00');
            $subRipStartTime = $dateTime->modify('+' . intval($line['start_time']) . ' seconds');
            $dateTime = new \DateTime('2000-01-01 00:00:00');
            $subRipEndTime = $dateTime->modify('+' . intval($line['end_time']) . ' seconds');
            $timestamp = $subRipStartTime->format('H:i:s,000') . " --> " . $subRipEndTime->format('H:i:s,000');
            array_push($result, $timestamp);
            $modifiedText = '>> ' . $line['text'];
            array_push($result, $modifiedText);
            array_push($result, PHP_EOL);
            $count++;
        }
        return implode(PHP_EOL, $result);
    }

    /**
     * Parses Amazon Transcribe Object and returns a subtitles array.
     * @return array
     */
    public function getSubtitles() {
        $totalItems = count($this->transcription->results->items);
        $subtitles = [];
        for ($i = 0; $i < $totalItems; $i++) {
            $item = $this->transcription->results->items[$i];
            $subtitle = $item->alternatives[0]->content . ' ';
            for ($j = $i +  1; $j < $totalItems; $j++ ) {
                $lookAheadItem = $this->transcription->results->items[$j];

                if (!property_exists($item, 'start_time') || !property_exists($lookAheadItem, 'end_time')) {
                    continue;
                }

                $startTime = $item->start_time;
                $endTime = $lookAheadItem->end_time;
                $alternatives = $lookAheadItem->alternatives;

                $subtitle .= $alternatives[0]->content . ' ';

                if ($endTime - $startTime > 3) {
                    $subtitles[] = ['start_time' => $startTime, 'end_time' => $endTime, 'text' => $subtitle];
                    $subtitle = '';
                    $i = $j;
                    $item = $lookAheadItem;
                }
            }
        }

        return $subtitles;
    }
}